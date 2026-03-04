<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos Electrónicos</title>
    <link rel="stylesheet" href="<?= asset('estilos.css') ?>">
    <link rel="stylesheet" href="<?= asset('dashboard.css') ?>">
    <script src="<?= asset('theme.js') ?>" defer></script>
</head>
<body>
<button class="theme-toggle" type="button" data-theme-toggle aria-label="Cambiar tema"></button>
<div class="products-landing">
    <section class="panel panel--wide">
        <h1>Landing de Productos Electrónicos</h1>
        <p class="subtitle">Acceso habilitado para correos distintos de Gmail. Aquí tienes una vitrina rápida de productos destacados.</p>

        <div class="products-grid">
            <article class="product-card">
                <h4>Laptop Pro X15</h4>
                <p>Procesador i7, 16GB RAM y SSD de 1TB para trabajo profesional.</p>
            </article>
            <article class="product-card">
                <h4>Smartphone Nova 12</h4>
                <p>Cámara de 108MP, batería de larga duración y pantalla AMOLED.</p>
            </article>
            <article class="product-card">
                <h4>Audífonos SonicAir</h4>
                <p>Cancelación de ruido activa y autonomía de hasta 30 horas.</p>
            </article>
            <article class="product-card">
                <h4>Monitor UltraView 27"</h4>
                <p>Resolución 4K con tasa de refresco de 144Hz para alta productividad.</p>
            </article>
        </div>

        <div class="actions">
            <a class="btn-secondary" href="<?= route('dashboard', 'page_ventas') ?>">Ir al módulo de ventas</a>
            <a class="btn-primary" href="<?= route('dashboard', 'logout') ?>">Cerrar sesión</a>
        </div>
    </section>
</div>
</body>
</html>
