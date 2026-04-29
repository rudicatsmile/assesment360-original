<?php

namespace App\Exports;

use App\Services\DepartmentAnalyticsService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DepartmentAnalyticsExport implements FromArray, WithHeadings
{
    public function __construct(
        private readonly DepartmentAnalyticsService $service,
        private readonly ?string $dateFrom,
        private readonly ?string $dateTo,
        private readonly ?int $departmentId
    ) {
    }

    public function headings(): array
    {
        return [
            'Nama Departemen',
            'Total Responden (Dept)',
            'Partisipasi (Dept) %',
            'Rata-rata Skor (Dept)',
            'Role',
            'Total Responden (Role)',
            'Partisipasi (Role) %',
            'Rata-rata Skor (Role)',
            'Nama User',
            'Submit',
            'Avg Score (User)',
        ];
    }

    public function array(): array
    {
        $fullData = $this->service->summarizeFull(
            $this->dateFrom,
            $this->dateTo,
            $this->departmentId
        );

        $rows = [];
        foreach ($fullData as $dept) {
            foreach ($dept['roles'] as $role) {
                if ($role['users'] === []) {
                    $rows[] = [
                        $dept['department_name'],
                        $dept['total_respondents'],
                        $dept['participation_rate'],
                        $dept['average_score'],
                        $role['role_name'],
                        $role['total_respondents'],
                        $role['participation_rate'],
                        $role['average_score'],
                        '-',
                        '-',
                        '-',
                    ];
                    continue;
                }

                foreach ($role['users'] as $user) {
                    $rows[] = [
                        $dept['department_name'],
                        $dept['total_respondents'],
                        $dept['participation_rate'],
                        $dept['average_score'],
                        $role['role_name'],
                        $role['total_respondents'],
                        $role['participation_rate'],
                        $role['average_score'],
                        $user['user_name'],
                        $user['total_submissions'],
                        $user['average_score'],
                    ];
                }
            }
        }

        return $rows;
    }
}
