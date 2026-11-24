<?php $months = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']; ?>
<section class="content-page">
    <p class="eyebrow">ADMINISTRACIÓN</p><h1>Reportes de ventas</h1>
    <section class="panel-card">
        <form method="get" class="report-filters">
            <fieldset><legend>Periodo</legend>
                <label class="check-option"><input type="radio" name="mode" value="daily" <?= $mode === 'daily' ? 'checked' : '' ?> onchange="this.form.submit()"> Diario</label>
                <label class="check-option"><input type="radio" name="mode" value="monthly" <?= $mode === 'monthly' ? 'checked' : '' ?> onchange="this.form.submit()"> Mensual</label>
            </fieldset>
            <?php if ($mode === 'daily'): ?>
                <label>Fecha<input type="date" name="date" value="<?= e($date) ?>" max="<?= e($today) ?>"></label>
            <?php else: ?>
                <label>Mes<select name="month"><?php foreach ($months as $number => $label): ?><option value="<?= sprintf('%02d', $number) ?>" <?= $month === sprintf('%02d', $number) ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
                <label>Año<select name="year"><?php for ($optionYear = $currentYear; $optionYear >= $currentYear - 10; $optionYear--): ?><option value="<?= $optionYear ?>" <?= (int) $year === $optionYear ? 'selected' : '' ?>><?= $optionYear ?></option><?php endfor; ?></select></label>
            <?php endif; ?>
            <button class="button button-dark" type="submit">Consultar</button>
        </form>
    </section>
    <div class="metrics-grid">
        <article class="metric-card"><p>Ventas completadas</p><strong><?= (int) $summary['sale_count'] ?></strong></article>
        <article class="metric-card"><p>Total vendido</p><strong>$<?= number_format((float) $summary['total'], 2) ?></strong></article>
    </div>
    <section class="panel-card history-panel"><h2><?= $mode === 'daily' ? 'Detalle del día' : 'Resumen por día' ?></h2>
        <?php if ($details === []): ?><p class="form-intro">No hay ventas en el periodo seleccionado.</p>
        <?php elseif ($mode === 'daily'): ?>
            <div class="table-wrap"><table><thead><tr><th>Venta</th><th>Hora</th><th>Cliente</th><th>Productos</th><th>Método</th><th>Total</th></tr></thead><tbody>
                <?php foreach ($details as $sale): ?><tr><td>#<?= (int) $sale['id'] ?></td><td><?= e(date('H:i', strtotime($sale['created_at']))) ?></td><td><?= e($sale['customer_name'] ?? 'Cliente') ?></td><td><?= e($sale['items'] ?? '') ?></td><td><?= e(payment_label($sale['payment_method'])) ?></td><td>$<?= number_format((float) $sale['total'], 2) ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php else: ?>
            <div class="table-wrap"><table><thead><tr><th>Fecha</th><th>Ventas</th><th>Total vendido</th></tr></thead><tbody>
                <?php foreach ($details as $day): ?><tr><td><?= e($day['sale_day']) ?></td><td><?= (int) $day['sale_count'] ?></td><td>$<?= number_format((float) $day['total'], 2) ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
</section>
