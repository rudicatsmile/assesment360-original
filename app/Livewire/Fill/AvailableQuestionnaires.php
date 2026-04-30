<?php

namespace App\Livewire\Fill;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\Response;
use App\Services\QuestionnaireScorer;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.evaluator')]
class AvailableQuestionnaires extends Component
{
    /**
     * Ordered list of fillable questionnaire IDs (excludes already submitted).
     *
     * @var list<int>
     */
    public array $questionnaireIds = [];

    /**
     * Ordered list of ALL questionnaire IDs (includes submitted).
     *
     * @var list<int>
     */
    public array $allQuestionnaireIds = [];

    /** @var array<int, array{status: string, response_id: int|null, title: string, description: string, start_date: string|null, end_date: string|null, questions_count: int, target_label: string}> */
    public array $questionnaireMeta = [];

    /** @var array<int|string, array{answer_option_id: int|null, essay_answer: string}> */
    public array $answers = [];

    public bool $confirmSubmitAll = false;

    public ?string $lastDraftSavedAt = null;

    /** @var array<int, bool> */
    public array $dirtyQuestionIds = [];

    /** Whether the form is locked due to time expiry */
    public bool $timeExpired = false;

    /** Whether to show the start confirmation popup before beginning */
    public bool $showStartConfirmation = false;

    /** Timestamp when the current department's fill session started (from pivot.filling_started_at) */
    public ?string $fillingStartedAt = null;

    /** Total time limit in minutes (from user.time_limit_minutes) */
    public ?int $timeLimitMinutes = null;

    /** Name of the department whose timer just expired, for UI message */
    public ?string $expiredDepartmentName = null;

    /** 0-based index into questionnaireIds (the fillable list) */
    public int $currentIndex = 0;

    public ?int $selectedTargetDepartmentId = null;

    public bool $showDepartmentPicker = false;

    /** @var Collection<int, \App\Models\Departement>|array<int, \App\Models\Departement> */
    public Collection|array $evaluableDepartments = [];

    /** @var list<int> */
    public array $completedTargetDepartmentIds = [];

    public function mount(): void
    {
        $user = Auth::user();

        $evalDepts = $user?->evaluableDepartments()->orderBy('urut')->get() ?? collect();

        if ($evalDepts->count() > 1) {
            $this->evaluableDepartments = $evalDepts;
            $this->showDepartmentPicker = true;
            $this->timeLimitMinutes = $user?->time_limit_minutes;
            $this->loadCompletedDepartments();
            return;
        } elseif ($evalDepts->count() === 1) {
            $this->selectedTargetDepartmentId = $evalDepts->first()->id;
            $this->showDepartmentPicker = false;
            $this->evaluableDepartments = $evalDepts;
        } else {
            $this->selectedTargetDepartmentId = null;
            $this->showDepartmentPicker = false;
        }

        $this->loadQuestionnaires();
        $this->initializeDepartmentTimer();
        $this->checkDepartmentTimeExpiry();
    }

    public function render()
    {
        $currentId = $this->questionnaireIds[$this->currentIndex] ?? null;
        $currentMeta = $currentId !== null ? ($this->questionnaireMeta[$currentId] ?? null) : null;
        $currentQuestions = collect();

        if ($currentId !== null && $currentMeta !== null && $currentMeta['status'] !== 'submitted' && !$this->timeExpired) {
            $currentQuestions = Question::where('questionnaire_id', $currentId)
                ->with('answerOptions')
                ->orderBy('order')
                ->get();
        }

        // Overall progress across ALL fillable questionnaires
        $totalQuestions = 0;
        $answeredCount = 0;
        $requiredQuestionCount = 0;
        $answeredRequiredCount = 0;

        foreach ($this->questionnaireMeta as $qId => $meta) {
            if ($meta['status'] === 'submitted') {
                continue;
            }

            $questions = Question::where('questionnaire_id', $qId)->orderBy('order')->get();

            foreach ($questions as $question) {
                $totalQuestions++;
                $answer = $this->answers[$question->id] ?? ['answer_option_id' => null, 'essay_answer' => ''];
                $isRequired = $question->is_required;

                $isAnswered = match ($question->type) {
                    'single_choice' => $answer['answer_option_id'] !== null,
                    'essay' => trim($answer['essay_answer'] ?? '') !== '',
                    'combined' => $answer['answer_option_id'] !== null,
                    default => false,
                };

                if ($isAnswered) {
                    $answeredCount++;
                }

                if ($isRequired) {
                    $requiredQuestionCount++;
                    if ($isAnswered) {
                        $answeredRequiredCount++;
                    }
                }
            }
        }

        $progressPercent = $totalQuestions > 0
            ? (int) round(($answeredCount / $totalQuestions) * 100)
            : 0;

        // Check if ALL required questions in the CURRENT questionnaire are answered
        $currentQuestionnaireComplete = false;
        if ($currentId !== null && $currentQuestions->count() > 0) {
            $currentComplete = true;
            foreach ($currentQuestions as $question) {
                $isRequired = $question->is_required;
                if (!$isRequired) continue;

                $answer = $this->answers[$question->id] ?? ['answer_option_id' => null, 'essay_answer' => ''];
                $isAnswered = match ($question->type) {
                    'single_choice' => $answer['answer_option_id'] !== null,
                    'essay' => trim($answer['essay_answer'] ?? '') !== '',
                    'combined' => $answer['answer_option_id'] !== null,
                    default => false,
                };

                if (!$isAnswered) {
                    $currentComplete = false;
                    break;
                }
            }
            $currentQuestionnaireComplete = $currentComplete;
        }

        $totalFillable = count($this->questionnaireIds);
        $submittedCount = count($this->allQuestionnaireIds) - $totalFillable;
        $isLast = $this->currentIndex >= $totalFillable - 1;

        // Build per-department time limit info (from pivot table)
        // Do NOT build timeLimitInfo when start confirmation popup is showing –
        // the timer must not tick until the user explicitly clicks "Mulai Sekarang".
        $timeLimitInfo = null;
        if (!$this->showStartConfirmation && $this->timeLimitMinutes !== null && $this->fillingStartedAt !== null) {
            $deadline = CarbonImmutable::parse($this->fillingStartedAt)->addMinutes($this->timeLimitMinutes);
            $remainingSeconds = max(0, (int) now()->diffInSeconds($deadline, false));
            $timeLimitInfo = [
                'deadline' => $deadline->toIso8601String(),
                'remaining_seconds' => $remainingSeconds,
                'expired' => $remainingSeconds <= 0,
                'time_limit_minutes' => $this->timeLimitMinutes,
            ];
        }

        // Build per-department timer info for the department picker view
        $departmentTimerInfo = [];
        if ($this->showDepartmentPicker && $this->timeLimitMinutes !== null) {
            $user = Auth::user();
            $depts = $user->evaluableDepartments()->get();
            foreach ($depts as $dept) {
                $startedAt = $dept->pivot->filling_started_at;
                $info = ['started' => false, 'expired' => false, 'remaining_seconds' => 0];
                if ($startedAt) {
                    $info['started'] = true;
                    $deadline = CarbonImmutable::parse($startedAt)->addMinutes($this->timeLimitMinutes);
                    $remaining = max(0, (int) now()->diffInSeconds($deadline, false));
                    $info['remaining_seconds'] = $remaining;
                    $info['expired'] = $remaining <= 0;
                }
                $departmentTimerInfo[$dept->id] = $info;
            }
        }

        return view('livewire.fill.available-questionnaires', [
            'currentId' => $currentId,
            'currentMeta' => $currentMeta,
            'currentQuestions' => $currentQuestions,
            'totalFillable' => $totalFillable,
            'isLast' => $isLast,
            'totalQuestions' => $totalQuestions,
            'answeredCount' => $answeredCount,
            'progressPercent' => $progressPercent,
            'requiredQuestionCount' => $requiredQuestionCount,
            'answeredRequiredCount' => $answeredRequiredCount,
            'submittedCount' => $submittedCount,
            'timeLimitInfo' => $timeLimitInfo,
            'currentQuestionnaireComplete' => $currentQuestionnaireComplete,
            'departmentTimerInfo' => $departmentTimerInfo,
        ]);
    }

    public function nextQuestionnaire(): void
    {
        if (!empty($this->dirtyQuestionIds)) {
            $this->persistAllDrafts();
        }

        $currentId = $this->questionnaireIds[$this->currentIndex] ?? null;
        if ($currentId !== null && !$this->isQuestionnaireComplete($currentId)) {
            return;
        }

        $max = count($this->questionnaireIds) - 1;
        if ($this->currentIndex < $max) {
            $this->currentIndex++;
            $this->dispatch('questionnaire-changed');
        }
    }

    private function isQuestionnaireComplete(int $questionnaireId): bool
    {
        $questions = Question::where('questionnaire_id', $questionnaireId)->orderBy('order')->get();
        foreach ($questions as $question) {
            $isRequired = $question->is_required;
            if (!$isRequired) {
                continue;
            }

            $answer = $this->answers[$question->id] ?? ['answer_option_id' => null, 'essay_answer' => ''];
            $isAnswered = match ($question->type) {
                'single_choice' => $answer['answer_option_id'] !== null,
                'essay' => trim($answer['essay_answer'] ?? '') !== '',
                'combined' => $answer['answer_option_id'] !== null,
                default => false,
            };

            if (!$isAnswered) {
                return false;
            }
        }
        return true;
    }

    public function previousQuestionnaire(): void
    {
        if (!empty($this->dirtyQuestionIds)) {
            $this->persistAllDrafts();
        }

        if ($this->currentIndex > 0) {
            $this->currentIndex--;
        }
    }

    public function goToQuestionnaire(int $index): void
    {
        if (!empty($this->dirtyQuestionIds)) {
            $this->persistAllDrafts();
        }

        $max = count($this->questionnaireIds) - 1;
        $this->currentIndex = max(0, min($index, $max));
    }

    public function updatedAnswers(mixed $value, string $key): void
    {
        if ($this->timeExpired) {
            return;
        }

        $questionId = (int) explode('.', $key)[0];
        $this->dirtyQuestionIds[$questionId] = true;
    }

    public function saveAllDrafts(): void
    {
        $this->persistAllDrafts();
    }

    public function openSubmitAllConfirmation(): void
    {
        $this->persistAllDrafts();
        $this->validateAllRequired();
        $this->confirmSubmitAll = true;
    }

    public function closeSubmitAllConfirmation(): void
    {
        $this->confirmSubmitAll = false;
    }

    public function submitAllFinal(): void
    {
        $this->doSubmitAll(false);
    }

    /**
     * Called by client-side timer when time expires.
     * Auto-submits answers for the current department only.
     */
    public function autoSubmitOnTimeExpired(): void
    {
        if ($this->timeExpired) {
            return;
        }

        $this->timeExpired = true;
        $deptId = $this->selectedTargetDepartmentId;

        // Get department name for UI message
        $dept = collect($this->evaluableDepartments)->firstWhere('id', $deptId);
        $this->expiredDepartmentName = $dept?->name ?? 'Department';

        // Persist any dirty drafts before auto-submitting
        $this->persistAllDrafts();

        $this->doSubmitAllForDepartment($deptId, true);
        $this->loadCompletedDepartments();

        // For multi-department users, allow them to go back to picker
        // Don't lock the entire form
    }

    public function loadCompletedDepartments(): void
    {
        $user = Auth::user();
        $roleSlug = $user?->roleSlug() ?? '';
        $targetAliases = (array) config('rbac.questionnaire_target_aliases', []);
        $targetGroups = array_values(array_unique(array_filter([
            $roleSlug,
            (string) ($targetAliases[$roleSlug] ?? ''),
        ])));

        $targetedQuestionnaireIds = Questionnaire::where('status', 'active')
            ->whereHas('targets', function ($q) use ($targetGroups) {
                $q->whereIn('target_group', $targetGroups);
            })
            ->pluck('id');

        $this->completedTargetDepartmentIds = [];

        foreach ($this->evaluableDepartments as $dept) {
            $submittedCount = Response::where('user_id', $user?->id)
                ->where('target_department_id', $dept->id)
                ->where('status', 'submitted')
                ->whereIn('questionnaire_id', $targetedQuestionnaireIds)
                ->count();

            if ($submittedCount >= $targetedQuestionnaireIds->count() && $targetedQuestionnaireIds->count() > 0) {
                $this->completedTargetDepartmentIds[] = $dept->id;
            }
        }
    }

    public function selectTargetDepartment(int $departmentId): void
    {
        $user = auth()->user();

        // Server-side authorization: verify user is allowed to evaluate this department
        if ($user && $user->evaluableDepartments()->exists()) {
            $isAllowed = $user->evaluableDepartments()->whereKey($departmentId)->exists();
            if (!$isAllowed) {
                abort(403, 'Anda tidak diizinkan mengevaluasi department ini.');
            }
        }

        $this->selectedTargetDepartmentId = $departmentId;
        $this->showDepartmentPicker = false;
        $this->timeExpired = false;
        $this->expiredDepartmentName = null;

        $this->loadQuestionnaires();

        // Check background timers first (auto-submit expired departments)
        $this->checkBackgroundTimers();

        // Initialize timer for this specific department
        $this->initializeDepartmentTimer();

        // If this department already expired during background check
        if ($this->timeExpired) {
            $this->doSubmitAllForDepartment($departmentId, true);
            $this->loadCompletedDepartments();
            $this->showDepartmentPicker = true;
            return;
        }

        // If timer already running, dispatch to Alpine
        if ($this->timeLimitMinutes !== null && $this->fillingStartedAt !== null && !$this->showStartConfirmation) {
            $deadline = CarbonImmutable::parse($this->fillingStartedAt)->addMinutes($this->timeLimitMinutes);
            $remainingSeconds = max(0, (int) now()->diffInSeconds($deadline, false));
            $this->dispatch('start-timer', remainingSeconds: $remainingSeconds);
        }
    }

    public function backToDepartmentPicker(): void
    {
        // Persist any dirty drafts before leaving the current department
        $this->persistAllDrafts();

        $this->showDepartmentPicker = true;
        $this->selectedTargetDepartmentId = null;
        $this->timeExpired = false;
        $this->showStartConfirmation = false;
        $this->expiredDepartmentName = null;

        // Check if any background timers expired while user was filling
        $this->checkBackgroundTimers();

        $this->loadCompletedDepartments();

        $this->questionnaireIds = [];
        $this->allQuestionnaireIds = [];
        $this->questionnaireMeta = [];
        $this->answers = [];
        $this->currentIndex = 0;
        $this->confirmSubmitAll = false;
        $this->dirtyQuestionIds = [];
        $this->lastDraftSavedAt = null;
    }

    private function loadQuestionnaires(): void
    {
        $user = Auth::user();
        $roleSlug = $user?->roleSlug() ?? '';
        $targetAliases = (array) config('rbac.questionnaire_target_aliases', []);
        $targetGroups = array_values(array_unique(array_filter([
            $roleSlug,
            (string) ($targetAliases[$roleSlug] ?? ''),
        ])));

        $questionnaires = Questionnaire::query()
            ->where('status', 'active')
            ->whereHas('targets', fn($query) => $query->whereIn('target_group', $targetGroups))
            ->with(['targets'])
            ->withCount('questions')
            ->orderBy('start_date')
            ->get();

        $roleLabels = (array) config('rbac.role_labels', []);
        $this->questionnaireMeta = [];
        $this->questionnaireIds = [];
        $this->allQuestionnaireIds = [];

        foreach ($questionnaires as $questionnaire) {
            $matchedTarget = $questionnaire->targets
                ->whereIn('target_group', $targetGroups)
                ->first()?->target_group ?? 'other';

            $targetLabel = $roleLabels[$matchedTarget] ?? ucfirst(str_replace('_', ' ', $matchedTarget));

            $response = Response::query()
                ->where('questionnaire_id', $questionnaire->id)
                ->where('user_id', $user->id)
                ->where('target_department_id', $this->selectedTargetDepartmentId)
                ->first();

            $status = 'not_started';
            $responseId = null;

            if ($response) {
                $status = $response->status === 'submitted' ? 'submitted' : 'draft';
                $responseId = $response->id;
            }

            $this->allQuestionnaireIds[] = $questionnaire->id;

            if ($status !== 'submitted') {
                $this->questionnaireIds[] = $questionnaire->id;
            }

            $this->questionnaireMeta[$questionnaire->id] = [
                'status' => $status,
                'response_id' => $responseId,
                'title' => $questionnaire->title,
                'description' => $questionnaire->description ?? '',
                'start_date' => $questionnaire->start_date?->format('d M Y H:i'),
                'end_date' => $questionnaire->end_date?->format('d M Y H:i'),
                'questions_count' => $questionnaire->questions_count,
                'target_label' => $targetLabel,
            ];
        }

        $this->currentIndex = 0;
        $this->loadAllAnswers();
    }

    private function loadAllAnswers(): void
    {
        $this->answers = [];

        foreach ($this->questionnaireMeta as $questionnaireId => $meta) {
            if ($meta['status'] === 'submitted') {
                continue;
            }

            $questions = Question::where('questionnaire_id', $questionnaireId)
                ->orderBy('order')
                ->get();

            foreach ($questions as $question) {
                $this->answers[$question->id] = [
                    'answer_option_id' => null,
                    'essay_answer' => '',
                ];
            }

            $responseId = $meta['response_id'] ?? null;

            if ($responseId) {
                $draftAnswers = Answer::where('response_id', $responseId)->get()->keyBy('question_id');

                foreach ($questions as $question) {
                    $existing = $draftAnswers->get($question->id);

                    if ($existing) {
                        $this->answers[$question->id] = [
                            'answer_option_id' => $existing->answer_option_id,
                            'essay_answer' => (string) ($existing->essay_answer ?? ''),
                        ];
                    }
                }
            }
        }
    }

    private function persistAllDrafts(): void
    {
        if (empty($this->dirtyQuestionIds)) {
            return;
        }

        $user = Auth::user();

        // Get all question IDs grouped by their questionnaire
        $allQuestions = Question::whereIn('id', array_keys($this->dirtyQuestionIds))
            ->select(['id', 'questionnaire_id'])
            ->get()
            ->groupBy('questionnaire_id');

        foreach ($allQuestions as $questionnaireId => $questions) {
            $questionIds = $questions->pluck('id')->map(fn($id) => (int) $id)->all();
            $this->persistDraftForQuestions($questionnaireId, $questionIds, $user);
        }

        $this->dirtyQuestionIds = [];
        $this->lastDraftSavedAt = now()->format('H:i:s');
    }

    /**
     * @param array<int, int> $questionIds
     */
    private function persistDraftForQuestions(int $questionnaireId, array $questionIds, $user): void
    {
        $questions = Question::where('questionnaire_id', $questionnaireId)
            ->with('answerOptions')
            ->orderBy('order')
            ->get();

        $responseId = $this->questionnaireMeta[$questionnaireId]['response_id'] ?? null;

        if (!$responseId) {
            $response = Response::create([
                'questionnaire_id' => $questionnaireId,
                'user_id' => $user->id,
                'status' => 'draft',
                'submitted_at' => null,
                'target_department_id' => $this->selectedTargetDepartmentId,
            ]);
            $responseId = $response->id;
            $this->questionnaireMeta[$questionnaireId]['response_id'] = $responseId;
        }

        $timestamp = now();
        $upsertRows = [];
        $deleteQuestionIds = [];

        foreach ($questionIds as $questionId) {
            $question = $questions->firstWhere('id', $questionId);

            if (!$question) {
                continue;
            }

            $state = $this->answers[$questionId] ?? ['answer_option_id' => null, 'essay_answer' => ''];
            $optionId = $this->normalizeOptionId($question, Arr::get($state, 'answer_option_id'));
            $essayAnswer = trim((string) Arr::get($state, 'essay_answer', ''));
            $essayValue = $essayAnswer !== '' ? $essayAnswer : null;

            if ($optionId === null && $essayValue === null) {
                $deleteQuestionIds[] = (int) $questionId;

                continue;
            }

            $upsertRows[] = [
                'response_id' => $responseId,
                'question_id' => (int) $questionId,
                'department_id' => $this->selectedTargetDepartmentId ?? $user?->department_id,
                'answer_option_id' => $optionId,
                'essay_answer' => $essayValue,
                'calculated_score' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::transaction(function () use ($upsertRows, $deleteQuestionIds, $responseId): void {
            if (!empty($upsertRows)) {
                Answer::upsert(
                    $upsertRows,
                    ['response_id', 'question_id'],
                    ['department_id', 'answer_option_id', 'essay_answer', 'calculated_score', 'updated_at']
                );
            }

            if (!empty($deleteQuestionIds)) {
                Answer::where('response_id', $responseId)
                    ->whereIn('question_id', $deleteQuestionIds)
                    ->delete();
            }
        });

        Response::where('id', $responseId)->update([
            'status' => 'draft',
            'submitted_at' => null,
        ]);

        $this->questionnaireMeta[$questionnaireId]['status'] = 'draft';
    }

    private function validateAllRequired(): void
    {
        $rules = [];
        $messages = [];

        foreach ($this->questionnaireMeta as $questionnaireId => $meta) {
            if ($meta['status'] === 'submitted') {
                continue;
            }

            $questions = Question::where('questionnaire_id', $questionnaireId)
                ->with('answerOptions')
                ->orderBy('order')
                ->get();

            foreach ($questions as $question) {
                $prefix = 'answers.' . $question->id;

                if ($question->type === 'single_choice' && $question->is_required) {
                    $rules[$prefix . '.answer_option_id'] = ['required', 'integer'];
                    $messages[$prefix . '.answer_option_id.required'] = 'Pilih salah satu opsi jawaban.';
                }

                if ($question->type === 'essay' && $question->is_required) {
                    $rules[$prefix . '.essay_answer'] = ['required', 'string', 'min:3', 'max:2000'];
                    $messages[$prefix . '.essay_answer.required'] = 'Jawaban esai wajib diisi.';
                }

                if ($question->type === 'combined' && $question->is_required) {
                    $rules[$prefix . '.answer_option_id'] = ['required', 'integer'];
                    $messages[$prefix . '.answer_option_id.required'] = 'Pilih salah satu opsi jawaban.';
                }
            }
        }

        if ($rules !== []) {
            $this->validate($rules, $messages);
        }
    }

    private function normalizeOptionId(Question $question, mixed $optionId): ?int
    {
        if (!is_numeric($optionId)) {
            return null;
        }

        $normalized = (int) $optionId;
        $exists = $question->answerOptions->contains(fn($option): bool => (int) $option->id === $normalized);

        return $exists ? $normalized : null;
    }

    /**
     * Initialize the department timer from the pivot table's filling_started_at.
     * Reads per-department timer state for the currently selected department.
     */
    private function initializeDepartmentTimer(): void
    {
        $user = Auth::user();
        $this->timeLimitMinutes = $user?->time_limit_minutes;

        if ($this->timeLimitMinutes === null) {
            // No time limit configured
            $this->fillingStartedAt = null;
            $this->showStartConfirmation = false;
            return;
        }

        $deptId = $this->selectedTargetDepartmentId;

        if ($deptId !== null) {
            // Read filling_started_at from pivot for current department
            $pivotData = $user->evaluableDepartments()
                ->where('department_id', $deptId)
                ->first();

            $this->fillingStartedAt = $pivotData?->pivot?->filling_started_at
                ? CarbonImmutable::parse($pivotData->pivot->filling_started_at)->toIso8601String()
                : null;
        } else {
            // Fallback for users without evaluable departments: use user-level field
            $this->fillingStartedAt = $user?->filling_started_at?->toIso8601String();
        }

        if ($this->fillingStartedAt === null) {
            // Department timer not started yet
            $this->showStartConfirmation = true;
        } else {
            $this->showStartConfirmation = false;
            // Check if already expired
            $deadline = CarbonImmutable::parse($this->fillingStartedAt)->addMinutes($this->timeLimitMinutes);
            if (now()->isAfter($deadline)) {
                $this->timeExpired = true;
            }
        }
    }

    /**
     * User confirmed to start the fill session – start the timer for the current department.
     */
    public function confirmStart(): void
    {
        $user = Auth::user();
        $deptId = $this->selectedTargetDepartmentId;

        if ($deptId !== null) {
            // Multi/single-dept user with pivot entry: update pivot table
            $user->evaluableDepartments()->updateExistingPivot($deptId, [
                'filling_started_at' => now(),
            ]);

            $pivotData = $user->evaluableDepartments()
                ->where('department_id', $deptId)
                ->first();
            $this->fillingStartedAt = $pivotData?->pivot?->filling_started_at
                ? CarbonImmutable::parse($pivotData->pivot->filling_started_at)->toIso8601String()
                : now()->toIso8601String();
        } else {
            // User without evaluable departments: fallback to user-level timestamp
            $user->update(['filling_started_at' => now()]);
            $user->refresh();
            $this->fillingStartedAt = $user->filling_started_at?->toIso8601String() ?? now()->toIso8601String();
        }

        $this->showStartConfirmation = false;

        $deadline = CarbonImmutable::parse($this->fillingStartedAt)->addMinutes($this->timeLimitMinutes);
        $remainingSeconds = max(0, (int) now()->diffInSeconds($deadline, false));

        // Dispatch Livewire event – will be caught by @script $wire.on() listener
        $this->dispatch('start-timer', remainingSeconds: $remainingSeconds);
    }

    /**
     * User cancelled – redirect back to role-appropriate dashboard.
     */
    public function cancelStart()
    {
        return redirect()->to('/fill/dashboard/guru');
    }

    /**
     * Check if the current department's time limit has expired.
     * If so, auto-submit all answers for that department and lock the form.
     */
    private function checkDepartmentTimeExpiry(): void
    {
        if ($this->timeLimitMinutes === null || $this->fillingStartedAt === null) {
            return;
        }

        $deadline = CarbonImmutable::parse($this->fillingStartedAt)->addMinutes($this->timeLimitMinutes);

        if (now()->isAfter($deadline)) {
            $this->timeExpired = true;
            $this->doSubmitAll(true);
        }
    }

    /**
     * Check all departments for expired background timers.
     * Auto-submits answers for any department whose timer has run out.
     */
    private function checkBackgroundTimers(): void
    {
        if ($this->timeLimitMinutes === null) {
            return;
        }

        $user = Auth::user();
        $depts = $user->evaluableDepartments()->get();

        foreach ($depts as $dept) {
            $startedAt = $dept->pivot->filling_started_at;
            if ($startedAt === null) {
                continue; // Not started yet
            }

            // Skip already completed departments
            if (in_array($dept->id, $this->completedTargetDepartmentIds)) {
                continue;
            }

            $deadline = CarbonImmutable::parse($startedAt)->addMinutes($this->timeLimitMinutes);
            if (now()->isAfter($deadline)) {
                // This department's timer expired - auto-submit its answers
                $this->doSubmitAllForDepartment($dept->id, true);
            }
        }

        // Refresh completed departments list
        $this->loadCompletedDepartments();
    }

    /**
     * Submit all questionnaire responses for a specific department.
     * Used for auto-submit when a department's timer expires (both current and background).
     *
     * @param int $departmentId The department to submit responses for
     * @param bool $isAutoSubmit When true, skips validation and forces submit of whatever is filled
     */
    private function doSubmitAllForDepartment(int $departmentId, bool $isAutoSubmit = false): void
    {
        $user = Auth::user();

        // Get all active questionnaire IDs targeted at this user's role
        $roleSlug = $user?->roleSlug() ?? '';
        $targetAliases = (array) config('rbac.questionnaire_target_aliases', []);
        $targetGroups = array_values(array_unique(array_filter([
            $roleSlug,
            (string) ($targetAliases[$roleSlug] ?? ''),
        ])));

        $targetedQuestionnaireIds = Questionnaire::where('status', 'active')
            ->whereHas('targets', fn($q) => $q->whereIn('target_group', $targetGroups))
            ->pluck('id');

        foreach ($targetedQuestionnaireIds as $questionnaireId) {
            $response = Response::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'questionnaire_id' => $questionnaireId,
                    'target_department_id' => $departmentId,
                ],
                ['status' => 'draft']
            );

            if ($response->status !== 'submitted') {
                $response->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);
            }
        }
    }

    /**
     * Shared submit logic for both manual and auto-submit.
     *
     * @param bool $isAutoSubmit When true, skips validation and forces submit of whatever is filled
     */
    private function doSubmitAll(bool $isAutoSubmit): void
    {
        if (!$isAutoSubmit) {
            $this->validateAllRequired();
        }

        // Validate target department authorization before submission
        if ($this->selectedTargetDepartmentId !== null) {
            $user = auth()->user();
            if ($user && $user->evaluableDepartments()->exists()) {
                $isAllowed = $user->evaluableDepartments()->whereKey($this->selectedTargetDepartmentId)->exists();
                if (!$isAllowed) {
                    abort(403, 'Anda tidak diizinkan mengevaluasi department ini.');
                }
            }
        }

        $user = Auth::user();
        $scorer = app(QuestionnaireScorer::class);
        $timestamp = now();

        foreach ($this->questionnaireMeta as $questionnaireId => $meta) {
            if ($meta['status'] === 'submitted') {
                continue;
            }

            $questions = Question::where('questionnaire_id', $questionnaireId)
                ->with('answerOptions')
                ->orderBy('order')
                ->get();

            $responseId = $meta['response_id'] ?? null;

            if (!$responseId) {
                $response = Response::create([
                    'questionnaire_id' => $questionnaireId,
                    'user_id' => $user->id,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'target_department_id' => $this->selectedTargetDepartmentId,
                ]);
                $responseId = $response->id;
            } else {
                Response::where('id', $responseId)
                    ->where('target_department_id', $this->selectedTargetDepartmentId)
                    ->update([
                        'status' => 'submitted',
                        'submitted_at' => now(),
                    ]);
            }

            DB::transaction(function () use ($questions, $responseId, $scorer, $timestamp, $user): void {
                foreach ($questions as $question) {
                    $state = $this->answers[$question->id] ?? ['answer_option_id' => null, 'essay_answer' => ''];
                    $optionId = $this->normalizeOptionId($question, Arr::get($state, 'answer_option_id'));
                    $essayAnswer = trim((string) Arr::get($state, 'essay_answer', ''));
                    $essayValue = $essayAnswer !== '' ? $essayAnswer : null;

                    if ($optionId === null && $essayValue === null) {
                        Answer::where('response_id', $responseId)
                            ->where('question_id', $question->id)
                            ->delete();

                        continue;
                    }

                    Answer::upsert(
                        [[
                            'response_id' => $responseId,
                            'question_id' => $question->id,
                            'department_id' => $this->selectedTargetDepartmentId ?? $user?->department_id,
                            'answer_option_id' => $optionId,
                            'essay_answer' => $essayValue,
                            'calculated_score' => $scorer->calculateScoreForAnswer($question, $optionId),
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ]],
                        ['response_id', 'question_id'],
                        ['department_id', 'answer_option_id', 'essay_answer', 'calculated_score', 'updated_at']
                    );
                }
            });

            $this->questionnaireMeta[$questionnaireId]['status'] = 'submitted';
            $this->questionnaireMeta[$questionnaireId]['response_id'] = $responseId;
        }

        $this->confirmSubmitAll = false;
        $this->dirtyQuestionIds = [];
        $this->questionnaireIds = [];

        if ($isAutoSubmit) {
            $this->timeExpired = true;
            session()->flash('error', 'Batas waktu pengisian kuisioner telah habis. Jawaban yang sudah diisi telah dikirim secara otomatis.');
        } else {
            $this->timeExpired = true;
            $count = count($this->allQuestionnaireIds);
            session()->flash('success', "Semua {$count} kuisioner berhasil dikirim!");
        }

        if (count($this->evaluableDepartments) > 1) {
            $this->backToDepartmentPicker();
            return;
        }
    }
}
