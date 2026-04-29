<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Department Analytics Report</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 32px;
            background: #ffffff;
            line-height: 1.5;
        }

        .report-header {
            text-align: center;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2563eb;
        }

        .report-header h1 {
            margin: 0 0 6px 0;
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            letter-spacing: -0.3px;
        }

        .report-header .subtitle {
            margin: 0;
            font-size: 12px;
            color: #6b7280;
        }

        .report-header .date-range {
            margin-top: 8px;
            display: inline-block;
            background: #eff6ff;
            color: #1e40af;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .department-section {
            margin-bottom: 28px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .department-header {
            background: #f8fafc;
            padding: 14px 18px;
            border-bottom: 2px solid #e2e8f0;
        }

        .department-header h2 {
            margin: 0 0 10px 0;
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }

        .dept-metrics {
            display: flex;
            gap: 20px;
        }

        .metric-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 14px;
            min-width: 110px;
        }

        .metric-box .metric-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .metric-box .metric-value {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .role-section {
            padding: 14px 18px;
            border-bottom: 1px solid #f1f5f9;
        }

        .role-section:last-child {
            border-bottom: none;
        }

        .role-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .role-header h3 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .role-badges {
            display: flex;
            gap: 10px;
        }

        .badge {
            font-size: 10px;
            padding: 3px 10px;
            border-radius: 12px;
            font-weight: 600;
        }

        .badge-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-emerald {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-amber {
            background: #fef3c7;
            color: #92400e;
        }

        table.user-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        table.user-table thead th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            text-align: left;
            padding: 7px 10px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.3px;
        }

        table.user-table tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        table.user-table tbody tr:last-child td {
            border-bottom: none;
        }

        table.user-table tbody tr:hover td {
            background: #f8fafc;
        }

        .score-badge {
            display: inline-block;
            background: #ecfdf5;
            color: #065f46;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 16px;
            color: #94a3b8;
            font-size: 11px;
            font-style: italic;
        }

        @media print {
            body {
                padding: 16px;
            }

            .department-section {
                box-shadow: none;
                border: 1px solid #d1d5db;
            }
        }
    </style>
</head>

<body>
    <div class="report-header">
        <h1>Department Analytics Report</h1>
        <p class="subtitle">Laporan analisis hasil penilaian berdasarkan departemen, role, dan user</p>
        <span class="date-range">Periode: {{ $dateFrom ?: 'Semua' }} s/d {{ $dateTo ?: 'Semua' }}</span>
    </div>

    @forelse ($fullData as $dept)
        <div class="department-section">
            <div class="department-header">
                <h2>{{ $dept['department_name'] }}</h2>
                <div class="dept-metrics">
                    <div class="metric-box">
                        <div class="metric-label">Total Responden</div>
                        <div class="metric-value">{{ (int) $dept['total_respondents'] }}</div>
                    </div>
                    <div class="metric-box">
                        <div class="metric-label">Partisipasi</div>
                        <div class="metric-value">{{ number_format((float) $dept['participation_rate'], 2) }}%</div>
                    </div>
                    <div class="metric-box">
                        <div class="metric-label">Rata-rata Skor</div>
                        <div class="metric-value">{{ number_format((float) $dept['average_score'], 2) }}</div>
                    </div>
                </div>
            </div>

            @forelse ($dept['roles'] as $role)
                <div class="role-section">
                    <div class="role-header">
                        <h3>{{ $role['role_name'] }}</h3>
                        <div class="role-badges">
                            <span class="badge badge-blue">Responden: {{ (int) $role['total_respondents'] }}</span>
                            <span class="badge badge-amber">Partisipasi:
                                {{ number_format((float) $role['participation_rate'], 1) }}%</span>
                            <span class="badge badge-emerald">Avg Score:
                                {{ number_format((float) $role['average_score'], 2) }}</span>
                        </div>
                    </div>

                    @if ($role['users'] === [])
                        <div class="empty-state">Tidak ada data user untuk role ini.</div>
                    @else
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Nama User</th>
                                    <th style="width: 100px; text-align: center;">Submit</th>
                                    <th style="width: 120px; text-align: center;">Avg Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($role['users'] as $index => $user)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $user['user_name'] }}</td>
                                        <td style="text-align: center;">{{ (int) $user['total_submissions'] }}</td>
                                        <td style="text-align: center;">
                                            <span class="score-badge">{{ number_format((float) $user['average_score'], 2) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @empty
                <div class="empty-state" style="padding: 24px;">Tidak ada data role untuk departemen ini.</div>
            @endforelse
        </div>
    @empty
        <div style="text-align: center; padding: 40px; color: #94a3b8;">
            <p style="font-size: 14px;">Tidak ada data untuk ditampilkan.</p>
        </div>
    @endforelse

    <div
        style="margin-top: 28px; padding-top: 14px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #9ca3af;">
        Dicetak pada {{ now()->format('d M Y H:i') }} &middot; ASSesment 360
    </div>
</body>

</html>