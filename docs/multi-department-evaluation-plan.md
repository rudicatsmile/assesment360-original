# Rencana Implementasi: Multi-Department Evaluation

## Ringkasan

Fitur ini memungkinkan user (evaluator) untuk mengevaluasi lebih dari satu department. User yang di-assign oleh administrator bisa mengisi kuisioner yang sama beberapa kali, masing-masing untuk department yang berbeda. Hasil evaluasi muncul di analytics di bawah department yang dievaluasi (bukan department user sendiri).

---

## Prinsip Backward Compatibility

User yang **tidak** di-assign multi-department mengalami flow normal tanpa perubahan apa pun di UI maupun alur pengisian.

| Tipe User                   | Flow                                                                                 |
| --------------------------- | ------------------------------------------------------------------------------------ |
| Tanpa evaluable_departments | Langsung isi kuisioner. `target_department_id` = `null`.                             |
| 1 evaluable_department      | Langsung isi kuisioner. `target_department_id` = department yang di-assign.          |
| 2+ evaluable_departments    | Muncul department picker sebelum isi kuisioner. Bisa pilih department satu per satu. |

---

## Step 1: Database Migration

### 1.1. Add `target_department_id` to `responses`

File: `database/migrations/2026_04_29_xxxxxx_add_target_department_id_to_responses_table.php`

```php
Schema::table('responses', function (Blueprint $table) {
    $table->foreignId('target_department_id')
        ->nullable()
        ->after('user_id')
        ->constrained('departements')
        ->nullOnDelete();
});
```

### 1.2. Create `user_evaluable_departments` table

File: `database/migrations/2026_04_29_xxxxxx_create_user_evaluable_departments_table.php`

```php
Schema::create('user_evaluable_departments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('department_id')->constrained('departements')->cascadeOnDelete();
    $table->timestamps();
    $table->unique(['user_id', 'department_id']);
});
```

---

## Step 2: Update Model

### 2.1. `app/Models/Response.php`

- Tambah ke `$fillable`: `target_department_id`
- Tambah relasi:

```php
public function targetDepartment(): BelongsTo
{
    return $this->belongsTo(Departement::class, 'target_department_id');
}
```

### 2.2. `app/Models/User.php`

- Tambah relasi:

```php
public function evaluableDepartments(): BelongsToMany
{
    return $this->belongsToMany(
        Departement::class,
        'user_evaluable_departments',
        'user_id',
        'department_id'
    )->withTimestamps();
}
```

### 2.3. `app/Models/Departement.php`

- Tambah relasi (opsional):

```php
public function evaluators(): BelongsToMany
{
    return $this->belongsToMany(
        User::class,
        'user_evaluable_departments',
        'department_id',
        'user_id'
    )->withTimestamps();
}
```

---

## Step 3: Admin — Assign Evaluable Departments

### 3.1. `app/Livewire/Admin/UserDirectory.php`

Tambah:

- Property: `public array $selectedEvaluableDepartments = [];`
- Method `syncEvaluableDepartments()` untuk save ke pivot table
- Di `startEdit()`: populate `selectedEvaluableDepartments` dari user yang sedang diedit
- Di `saveUser()`: sync evaluable departments setelah user tersimpan

### 3.2. `resources/views/livewire/admin/user-directory.blade.php`

Tambah di form modal (Add/Edit User):

- Section baru "Department yang Bisa Dievaluasi"
- Multi-checkbox atau multi-select dari daftar department
- Jika tidak dipilih apa pun = user tidak bisa multi-evaluate (flow normal)

Tambah di tabel:

- Badge "Multi-Dept" jika user punya >1 evaluable department

---

## Step 4: User Flow — Department Picker

### 4.1. `app/Livewire/Fill/AvailableQuestionnaires.php`

Tambah property baru:

```php
public ?int $selectedTargetDepartmentId = null;
public bool $showDepartmentPicker = false;
public array $evaluableDepartmentIds = [];
public array $completedTargetDepartmentIds = [];
```

Ubah method `loadQuestionnaires()`:

```php
$evaluableDepartments = $user->evaluableDepartments()->pluck('department_id')->all();

if (count($evaluableDepartments) > 1) {
    $this->evaluableDepartmentIds = $evaluableDepartments;
    $this->showDepartmentPicker = true;
    // Tampilkan picker, belum load pertanyaan
} else {
    $this->selectedTargetDepartmentId = $evaluableDepartments[0] ?? $user->department_id;
    $this->showDepartmentPicker = false;
    // Flow normal
}
```

Tambah method `selectTargetDepartment(int $departmentId)`:

- Set `$selectedTargetDepartmentId`
- Hide department picker
- Load/load ulang daftar questionnaire dengan scope by `target_department_id`

Ubah query response dari:

```php
$response = Response::query()
    ->where('questionnaire_id', $questionnaire->id)
    ->where('user_id', $user->id)
    ->first();
```

Menjadi:

```php
$response = Response::query()
    ->where('questionnaire_id', $questionnaire->id)
    ->where('user_id', $user->id)
    ->where('target_department_id', $this->selectedTargetDepartmentId)
    ->first();
```

Ubah save answer (persistDraftForQuestions & doSubmitAll):

```php
// Sebelum
'department_id' => $user?->department_id,

// Sesudah
'department_id' => $this->selectedTargetDepartmentId ?? $user?->department_id,
```

Tambah method `finishAllEvaluations()`:

- Redirect ke dashboard
- Flash message: "Semua evaluasi berhasil dikirim. Data akan direview oleh administrator."

### 4.2. `resources/views/livewire/fill/available-questionnaires.blade.php`

Tambah step baru di awal (jika `$showDepartmentPicker = true`):

**Tampilan "Pilih Department"**

- Grid card/list department yang tersedia
- Department yang sudah selesai ditandai icon checklist hijau
- Department yang belum selesai bisa diklik untuk mulai mengisi
- Jika semua selesai, tampilkan button "Selesai — Kembali ke Dashboard"

**Tampilan "Isi Kuisioner"**

- Sama seperti sekarang, tapi tambah info: "Anda mengevaluasi: [Nama Department]"
- Button submit tetap sama

**Setelah Submit**

- Kembali ke tampilan "Pilih Department"
- Department yang baru selesai ditandai checklist

---

## Step 5: Update Analytics

### 5.1. `app/Services/DepartmentAnalyticsService.php`

Tidak perlu banyak perubahan karena `answers.department_id` sekarang berisi department yang dievaluasi.

Yang perlu dicek:

- Query `$employeesSub` masih pakai `users.department_id` (untuk hitung total karyawan per department)
- Query `$scoresSub` dan `$respondentsSub` sudah pakai `answers.department_id` dan `users.department_id` — pastikan join dan grouping masih benar

Tambahan (opsional): Di tabel detail user, tampilkan kolom "Department Dievaluasi" dari `response.target_department_id`.

---

## Daftar File yang Diubah

| No  | File                                                                | Jenis Perubahan                |
| --- | ------------------------------------------------------------------- | ------------------------------ |
| 1   | `database/migrations/xxx_add_target_department_id_to_responses.php` | Baru                           |
| 2   | `database/migrations/xxx_create_user_evaluable_departments.php`     | Baru                           |
| 3   | `app/Models/Response.php`                                           | Edit                           |
| 4   | `app/Models/User.php`                                               | Edit                           |
| 5   | `app/Models/Departement.php`                                        | Edit (opsional)                |
| 6   | `app/Livewire/Admin/UserDirectory.php`                              | Edit                           |
| 7   | `resources/views/livewire/admin/user-directory.blade.php`           | Edit                           |
| 8   | `app/Livewire/Fill/AvailableQuestionnaires.php`                     | Edit (besar)                   |
| 9   | `resources/views/livewire/fill/available-questionnaires.blade.php`  | Edit (besar)                   |
| 10  | `app/Services/DepartmentAnalyticsService.php`                       | Edit (minor, verifikasi query) |

---

## Catatan Penting

1. **Index/Performance**: Consider tambah composite index di `responses(user_id, questionnaire_id, target_department_id)` untuk query response lookup yang lebih cepat.
2. **Soft Deletes**: Response dan Answer menggunakan SoftDeletes. Pastikan query di `loadQuestionnaires` tetap memperhatikan `whereNull('deleted_at')`.
3. **Timer Session**: `filling_started_at` dan `time_limit_minutes` ada di level User. Jika user mengisi department A kemudian department B dalam waktu singkat, timer tetap berjalan global untuk user tersebut (tidak per department).
4. **Export Excel/PDF**: Jika ada export, pastikan grouping dan filtering masih sesuai dengan `answers.department_id` yang baru.
