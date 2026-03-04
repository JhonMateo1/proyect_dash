<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda para Clientes</title>
    <link rel="stylesheet" href="<?= asset('estilos.css') ?>">
    <link rel="stylesheet" href="<?= asset('client_products.css') ?>">
    <script src="<?= asset('theme.js') ?>" defer></script>
</head>
<body class="client-products-body">
<button class="theme-toggle" type="button" data-theme-toggle aria-label="Cambiar tema"></button>

<main class="client-products-main">
    <header class="client-products-header">
        <h1>Tienda exclusiva para clientes</h1>
        <p>Solo las cuentas de Gmail pueden acceder a este portal de compras.</p>
        <a class="client-logout" href="<?= route('dashboard', 'logout') ?>">Cerrar sesión</a>
    </header>

    <?php if (!empty($_SESSION['client_products_success'])): ?>
        <div class="client-alert client-alert--success"><?= htmlspecialchars($_SESSION['client_products_success']) ?></div>
        <?php unset($_SESSION['client_products_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['client_products_error'])): ?>
        <div class="client-alert client-alert--error"><?= htmlspecialchars($_SESSION['client_products_error']) ?></div>
        <?php unset($_SESSION['client_products_error']); ?>
    <?php endif; ?>

    <section class="product-grid" aria-label="Catálogo de productos">
        <article class="product-card">
            <h2>Laptop Pro 14</h2>
            <p>Rendimiento de alta gama para trabajo y estudio.</p>
            <strong>S/ 4,200.00</strong>
        </article>
        <article class="product-card">
            <h2>Mouse Inalámbrico</h2>
            <p>Ergonómico, preciso y con batería de larga duración.</p>
            <strong>S/ 90.00</strong>
        </article>
        <article class="product-card">
            <h2>Teclado Mecánico</h2>
            <p>Switches táctiles para una mejor experiencia de escritura.</p>
            <strong>S/ 250.00</strong>
        </article>
        <article class="product-card">
            <h2>Monitor 27&quot;</h2>
            <p>Pantalla IPS ideal para productividad y diseño.</p>
            <strong>S/ 980.00</strong>
        </article>
    </section>

    <section class="client-form-section">
        <h2>Registrar compra</h2>
        <form method="post" action="<?= route('dashboard', 'buyProduct') ?>">
            <label for="cliente_nombre">Nombre del cliente</label>
            <input id="cliente_nombre" name="cliente_nombre" type="text" required>

            <label for="producto">Producto</label>
            <select id="producto" name="producto" required>
                <option value="Laptop Pro 14">Laptop Pro 14</option>
                <option value="Mouse Inalámbrico">Mouse Inalámbrico</option>
                <option value="Teclado Mecánico">Teclado Mecánico</option>
                <option value="Monitor 27\"">Monitor 27&quot;</option>
            </select>

            <label for="cantidad">Cantidad</label>
            <input id="cantidad" name="cantidad" type="number" min="1" value="1" required>

            <button type="submit">Comprar</button>
        </form>
    </section>

    <section class="client-orders">
        <h2>Mis pedidos</h2>
        <?php if (empty($misPedidos)): ?>
            <p>Aún no has realizado compras.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($misPedidos as $pedido): ?>
                    <tr>
                        <td><?= htmlspecialchars($pedido['fecha'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($pedido['producto'] ?? '-') ?></td>
                        <td><?= (int)($pedido['cantidad'] ?? 1) ?></td>
                        <td>S/ <?= number_format((float)($pedido['total'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
