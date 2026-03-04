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
                    <h3>Ventas registradas</h3>
                    <p class="subtitle">Ahora puedes agregar, ver y editar ventas desde modales</p>
                </div>
                <button class="btn-primary btn-inline" type="button" data-open-modal="createVentaModal">Agregar nueva venta</button>
            </div>

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
                                <td><?= (int) ($venta['id'] ?? 0) ?></td>
                                <td><?= htmlspecialchars($venta['cliente'] ?? '') ?></td>
                                <td><?= htmlspecialchars($venta['producto'] ?? '') ?></td>
                                <td>$<?= number_format((float) ($venta['total'] ?? 0), 2) ?></td>
                                <td><?= htmlspecialchars($venta['fecha'] ?? '-') ?></td>
                                <td>
                                    <button class="btn-secondary btn-inline" type="button"
                                            data-open-modal="viewVentaModal"
                                            data-venta='<?= json_encode($venta, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>'>Ver</button>
                                    <button class="btn-primary btn-inline" type="button"
                                            data-open-modal="editVentaModal"
                                            data-venta='<?= json_encode($venta, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>'>Editar</button>
                                    <form method="post" action="<?= route('dashboard', 'page_ventas') ?>" style="display:inline;">
                                        <input type="hidden" name="operation" value="delete">
                                        <input type="hidden" name="venta_id" value="<?= (int) ($venta['id'] ?? 0) ?>">
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

<div class="custom-modal" id="createVentaModal" aria-hidden="true">
    <div class="custom-modal__box">
        <div class="custom-modal__head">
            <h3>Agregar nueva venta</h3>
            <button type="button" class="custom-modal__close" data-close-modal>&times;</button>
        </div>
        <form class="form-grid" method="post" action="<?= route('dashboard', 'page_ventas') ?>">
            <input type="hidden" name="operation" value="create">
            <div class="field"><label>Cliente</label><input type="text" name="cliente" required></div>
            <div class="field"><label>Producto</label><input type="text" name="producto" required></div>
            <div class="field"><label>Total</label><input type="number" name="total" min="0.01" step="0.01" required></div>
            <button class="btn-primary" type="submit">Guardar venta</button>
        </form>
    </div>
</div>

<div class="custom-modal" id="viewVentaModal" aria-hidden="true">
    <div class="custom-modal__box">
        <div class="custom-modal__head">
            <h3>Detalle de venta</h3>
            <button type="button" class="custom-modal__close" data-close-modal>&times;</button>
        </div>
        <ul class="info-list">
            <li><strong>ID:</strong> <span data-view="id"></span></li>
            <li><strong>Cliente:</strong> <span data-view="cliente"></span></li>
            <li><strong>Producto:</strong> <span data-view="producto"></span></li>
            <li><strong>Total:</strong> <span data-view="total"></span></li>
            <li><strong>Fecha:</strong> <span data-view="fecha"></span></li>
        </ul>
    </div>
</div>

<div class="custom-modal" id="editVentaModal" aria-hidden="true">
    <div class="custom-modal__box">
        <div class="custom-modal__head">
            <h3>Editar venta</h3>
            <button type="button" class="custom-modal__close" data-close-modal>&times;</button>
        </div>
        <form class="form-grid" method="post" action="<?= route('dashboard', 'page_ventas') ?>">
            <input type="hidden" name="operation" value="update">
            <input type="hidden" name="venta_id" id="editVentaId">
            <div class="field"><label>Cliente</label><input type="text" name="cliente" id="editCliente" required></div>
            <div class="field"><label>Producto</label><input type="text" name="producto" id="editProducto" required></div>
            <div class="field"><label>Total</label><input type="number" name="total" id="editTotal" min="0.01" step="0.01" required></div>
            <button class="btn-primary" type="submit">Actualizar venta</button>
        </form>
    </div>
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

const modals = document.querySelectorAll('.custom-modal');
const openButtons = document.querySelectorAll('[data-open-modal]');
const closeButtons = document.querySelectorAll('[data-close-modal]');

function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
}

function closeModal(modal) {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
}

openButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const targetId = button.getAttribute('data-open-modal');
        const ventaRaw = button.getAttribute('data-venta');
        const venta = ventaRaw ? JSON.parse(ventaRaw) : null;

        if (targetId === 'viewVentaModal' && venta) {
            document.querySelector('[data-view="id"]').textContent = venta.id ?? '-';
            document.querySelector('[data-view="cliente"]').textContent = venta.cliente ?? '-';
            document.querySelector('[data-view="producto"]').textContent = venta.producto ?? '-';
            document.querySelector('[data-view="total"]').textContent = `$${Number(venta.total || 0).toFixed(2)}`;
            document.querySelector('[data-view="fecha"]').textContent = venta.fecha ?? '-';
        }

        if (targetId === 'editVentaModal' && venta) {
            document.getElementById('editVentaId').value = venta.id ?? '';
            document.getElementById('editCliente').value = venta.cliente ?? '';
            document.getElementById('editProducto').value = venta.producto ?? '';
            document.getElementById('editTotal').value = venta.total ?? '';
        }

        openModal(targetId);
    });
});

closeButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const modal = button.closest('.custom-modal');
        if (modal) closeModal(modal);
    });
});

modals.forEach((modal) => {
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal(modal);
        }
    });
});
</script>

</body>
</html>
