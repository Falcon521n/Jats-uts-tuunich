<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ReportModel;

final class ReportController extends Controller
{
    public function index(): void
    {
        require_role($this->pdo, 'admin');
        $mode = $_GET['mode'] ?? 'daily';
        $mode = is_string($mode) && in_array($mode, ['daily', 'monthly'], true) ? $mode : 'daily';
        $today = new \DateTimeImmutable('today');
        $dateInput = $_GET['date'] ?? $today->format('Y-m-d');
        $date = is_string($dateInput) ? \DateTimeImmutable::createFromFormat('!Y-m-d', $dateInput) : false;
        if ($date === false || $date->format('Y-m-d') !== $dateInput || $date > $today) {
            $date = $today;
        }
        $currentYear = (int) $today->format('Y');
        $month = $_GET['month'] ?? $today->format('m');
        if (!is_string($month) || !preg_match('/^(0[1-9]|1[0-2])$/', $month)) {
            $month = $today->format('m');
        }
        $year = $_GET['year'] ?? (string) $currentYear;
        if (!is_string($year) || !preg_match('/^\d{4}$/', $year) || (int) $year < $currentYear - 10 || (int) $year > $currentYear) {
            $year = (string) $currentYear;
        }

        $report = new ReportModel($this->pdo);
        if ($mode === 'daily') {
            $start = $date->format('Y-m-d 00:00:00');
            $end = $date->modify('+1 day')->format('Y-m-d 00:00:00');
            $details = $report->dailyDetails($start, $end);
        } else {
            $start = sprintf('%04d-%02d-01 00:00:00', (int) $year, (int) $month);
            $end = (new \DateTimeImmutable($start))->modify('+1 month')->format('Y-m-d 00:00:00');
            $details = $report->monthlyByDay($start, $end);
        }
        $summary = $mode === 'daily'
            ? $report->dailySummary($start, $end)
            : $report->monthlySummary($start, $end);

        $this->render('admin/reports', [
            'pageTitle' => 'Reportes de ventas | Tuunich',
            'mode' => $mode,
            'date' => $date->format('Y-m-d'),
            'today' => $today->format('Y-m-d'),
            'month' => $month,
            'year' => $year,
            'currentYear' => $currentYear,
            'summary' => $summary,
            'details' => $details,
        ]);
    }
}
