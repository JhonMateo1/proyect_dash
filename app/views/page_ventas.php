<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Ventas</title>
    <link rel="stylesheet" href="<?= asset('estilos.css') ?>">
    <link rel="stylesheet" href="<?= asset('dashboard.css') ?>">
    <script src="<?= asset('theme.js') ?>" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-body">
<button class="theme-toggle" type="button" data-theme-toggle aria-label="Cambiar tema"></button>

<div class="dashboard-layout">
    <aside class="dashboard-sidebar">
        <h3>Dashboard</h3>
        <p>Panel de Administración</p>

        <span class="sidebar-section">PANEL</span>
        <a class="sidebar-link" href="<?= route('dashboard', 'index') ?>">Inicio</a>
        <a class="sidebar-link" href="<?= route('dashboard', 'audit') ?>">Auditoría</a>
        <a class="sidebar-link active" href="<?= route('dashboard', 'page_ventas') ?>">Ventas</a>

        <span class="sidebar-section">GENERAL</span>
        <a class="sidebar-link" href="<?= route('dashboard', 'addEmployee') ?>">Agregar empleado</a>
        <a class="sidebar-link" href="<?= route('dashboard', 'logout') ?>">Cerrar sesión</a>
    </aside>

    <main class="dashboard-main">
        <section class="panel panel--wide hr-panel hr-panel--fullscreen">
            <h1>Módulo de Ventas</h1>

            <div class="module-headline">
                <h2>Gestión Comercial</h2>
                <p>Registro de ventas y métricas generales del módulo</p>
            </div>

            <div class="stats-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                <article class="stat-card">
                    <h4>Total Ventas</h4>
                    <p class="stat-value">$<?= number_format($totalMonto ?? 0, 2) ?></p>
                    <small>Monto acumulado</small>
                </article>
                <article class="stat-card">
                    <h4>N° Ventas</h4>
                    <p class="stat-value"><?= (int) ($cantidadVentas ?? 0) ?></p>
                    <small>Transacciones registradas</small>
                </article>
            </div>

            <div class="module-header">
                <div>
                    <h3>Nueva venta</h3>
                    <p class="subtitle">Completa los datos para registrar una operación</p>
                </div>
            </div>

            <form class="filter-row" method="post" action="<?= route('dashboard', 'page_ventas') ?>" style="grid-template-columns: 1fr 1fr 1fr auto; align-items: end;">
                <input type="hidden" name="operation" value="create">
                <input type="text" name="cliente" placeholder="Cliente" required>
                <input type="text" name="producto" placeholder="Producto" required>
                <input type="number" name="total" placeholder="Total" min="0.01" step="0.01" required>
                <button class="btn-primary btn-inline" type="submit">Agregar venta</button>
            </form>

            <div class="table-wrap table-wrap--dashboard">
                <table class="audit-table dashboard-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Producto</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($ventas ?? [])): ?>
                        <tr>
                            <td colspan="6">No hay ventas registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach (($ventas ?? []) as $venta): ?>
                            <tr>
                                <td><?= htmlspecialchars($venta['id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($venta['cliente'] ?? '') ?></td>
                                <td><?= htmlspecialchars($venta['producto'] ?? '') ?></td>
                                <td>$<?= number_format((float) ($venta['total'] ?? 0), 2) ?></td>
                                <td><?= htmlspecialchars($venta['fecha'] ?? '-') ?></td>
                                <td>
                                    <form method="post" action="<?= route('dashboard', 'page_ventas') ?>" style="display:inline;">
                                        <input type="hidden" name="operation" value="delete">
                                        <input type="hidden" name="venta_id" value="<?= htmlspecialchars($venta['id'] ?? '') ?>">
                                        <button class="btn-secondary btn-inline" type="submit">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="panel" style="width:100%; max-width:100%; margin-top:16px; padding:18px;">
                <h3 style="margin-top:0;">Panel analítico</h3>
                <canvas id="graficoVentas" height="120"></canvas>
            </div>
        </section>
    </main>
</div>

<script>
const ventasData = <?= json_encode($ventas ?? [], JSON_UNESCAPED_UNICODE) ?>;
const etiquetas = ventasData.map(v => v.producto || 'Producto');
const montos = ventasData.map(v => Number(v.total || 0));

const ctx = document.getElementById('graficoVentas');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: etiquetas,
        datasets: [{
            label: 'Ventas por producto',
            data: montos,
            backgroundColor: 'rgba(92, 160, 255, 0.45)',
            borderColor: 'rgba(92, 160, 255, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: { color: '#dce8ff' }
            }
        },
        scales: {
            x: { ticks: { color: '#bcd1f5' } },
            y: { ticks: { color: '#bcd1f5' } }
        }
    }
});
</script>

</body>
</html>
