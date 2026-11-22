<?php

declare(strict_types=1);

namespace Aplicacion\Controladores;

use Aplicacion\Modelos\ReportModel;

final class ReportController extends Controller
{
    public function index(): void
    {
        require_role('admin');
        $mode = $_GET['modo'] ?? 'diario';
        $mode = is_string($mode) && in_array($mode, ['diario', 'mensual'], true) ? $mode : 'diario';
        $today = new \DateTimeImmutable('today');
        $dateInput = $_GET['fecha'] ?? $today->format('Y-m-d');
        $date = is_string($dateInput) ? \DateTimeImmutable::createFromFormat('!Y-m-d', $dateInput) : false;
        if ($date === false || $date->format('Y-m-d') !== $dateInput || $date > $today) {
            $date = $today;
        }
        $currentYear = (int) $today->format('Y');
        $month = $_GET['mes'] ?? $today->format('m');
        if (!is_string($month) || !preg_match('/^(0[1-9]|1[0-2])$/', $month)) {
            $month = $today->format('m');
        }
        $year = $_GET['anio'] ?? (string) $currentYear;
        if (!is_string($year) || !preg_match('/^\d{4}$/', $year) || (int) $year < $currentYear - 10 || (int) $year > $currentYear) {
            $year = (string) $currentYear;
        }

        if ($mode === 'diario') {
            $start = $date->format('Y-m-d 00:00:00');
            $end = $date->modify('+1 day')->format('Y-m-d 00:00:00');
            $details = (new ReportModel($this->pdo))->dailyDetails($start, $end);
        } else {
            $start = sprintf('%04d-%02d-01 00:00:00', (int) $year, (int) $month);
            $end = (new \DateTimeImmutable($start))->modify('+1 month')->format('Y-m-d 00:00:00');
            $details = (new ReportModel($this->pdo))->monthlyByDay($start, $end);
        }
        $report = new ReportModel($this->pdo);
        $summary = $mode === 'diario'
            ? $report->dailySummary($start, $end)
            : $report->monthlySummary($start, $end);

        $this->render('administracion/reports', [
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
