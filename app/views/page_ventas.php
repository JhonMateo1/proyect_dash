<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Ventas</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif}
        body{background:linear-gradient(135deg,#0a192f,#0f2747);color:#fff}
        header{background:#081424;padding:20px 40px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 4px 20px rgba(0,0,0,.4)}
        h1{font-size:24px}
        .btn{background:#1e3a8a;border:none;padding:8px 14px;border-radius:8px;color:#fff;cursor:pointer;transition:.3s}
        .btn:hover{background:#2563eb}
        .btn-danger{background:#7f1d1d}
        .btn-danger:hover{background:#b91c1c}
        .container{padding:40px}
        .grid{display:grid;grid-template-columns:2fr 1fr;gap:30px}
        .card{background:#112240;padding:20px;border-radius:16px;box-shadow:0 8px 25px rgba(0,0,0,.4)}
        table{width:100%;border-collapse:collapse;margin-top:15px}
        th,td{padding:12px;text-align:left}
        th{background:#0f1f36;color:#94a3b8}
        tr:nth-child(even){background:#0d1b2a}
        tr:hover{background:#1e3a8a}
        input{padding:8px;border-radius:6px;border:none;background:#1e293b;color:#fff;margin-right:5px}
        .form-inline{margin-top:10px}
        .analytics-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:15px;margin-bottom:20px}
        .analytics-cards div{background:#0f1f36;padding:15px;border-radius:12px;text-align:center}
        .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.6);justify-content:center;align-items:center}
        .modal-content{background:#112240;padding:25px;border-radius:12px;width:350px}
        .modal-content h3{margin-bottom:10px}
    </style>
</head>
<body>

<header>
    <h1>Módulo de Ventas</h1>
    <button class="btn" onclick="openModal()">+ Nueva Venta (Modal)</button>
</header>

<div class="container">
<div class="grid">

    <!-- SECCIÓN CRUD -->
    <div class="card">
        <h2>Gestión de Ventas</h2>

        <!-- OPCIÓN 1: CRUD Inline -->
        <div class="form-inline">
            <input type="text" id="cliente" placeholder="Cliente">
            <input type="text" id="producto" placeholder="Producto">
            <input type="number" id="total" placeholder="Total">
            <button class="btn" onclick="agregarVenta()">Agregar (Inline)</button>
        </div>

        <table id="tablaVentas">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Producto</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- SECCIÓN ANALÍTICA -->
    <div class="card">
        <h2>Panel Analítico</h2>
        <div class="analytics-cards">
            <div>
                <h4>Total Ventas</h4>
                <p id="totalVentas">$0</p>
            </div>
            <div>
                <h4>N° Ventas</h4>
                <p id="cantidadVentas">0</p>
            </div>
        </div>
        <canvas id="graficoVentas"></canvas>
    </div>

</div>
</div>

<!-- OPCIÓN 2: CRUD con Modal -->
<div class="modal" id="modalVenta">
    <div class="modal-content">
        <h3>Nueva Venta</h3>
        <input type="text" id="mCliente" placeholder="Cliente"><br><br>
        <input type="text" id="mProducto" placeholder="Producto"><br><br>
        <input type="number" id="mTotal" placeholder="Total"><br><br>
        <button class="btn" onclick="guardarDesdeModal()">Guardar</button>
        <button class="btn btn-danger" onclick="closeModal()">Cancelar</button>
    </div>
</div>

<script>
let ventas = [];
let contador = 1;

function agregarVenta(clienteParam, productoParam, totalParam){
    let cliente = clienteParam || document.getElementById('cliente').value;
    let producto = productoParam || document.getElementById('producto').value;
    let total = parseFloat(totalParam || document.getElementById('total').value);

    if(!cliente || !producto || !total) return;

    ventas.push({id:contador++, cliente, producto, total});
    renderTabla();
    actualizarAnalitica();

    document.getElementById('cliente').value='';
    document.getElementById('producto').value='';
    document.getElementById('total').value='';
}

function renderTabla(){
    const tbody = document.querySelector('#tablaVentas tbody');
    tbody.innerHTML='';
    ventas.forEach(v=>{
        tbody.innerHTML += `
        <tr>
            <td>${v.id}</td>
            <td>${v.cliente}</td>
            <td>${v.producto}</td>
            <td>$${v.total}</td>
            <td>
                <button class="btn" onclick="editarVenta(${v.id})">Editar</button>
                <button class="btn btn-danger" onclick="eliminarVenta(${v.id})">Eliminar</button>
            </td>
        </tr>`;
    });
}

function eliminarVenta(id){
    ventas = ventas.filter(v=>v.id!==id);
    renderTabla();
    actualizarAnalitica();
}

function editarVenta(id){
    const venta = ventas.find(v=>v.id===id);
    const nuevoCliente = prompt('Cliente:', venta.cliente);
    const nuevoProducto = prompt('Producto:', venta.producto);
    const nuevoTotal = prompt('Total:', venta.total);
    if(nuevoCliente && nuevoProducto && nuevoTotal){
        venta.cliente = nuevoCliente;
        venta.producto = nuevoProducto;
        venta.total = parseFloat(nuevoTotal);
        renderTabla();
        actualizarAnalitica();
    }
}

function actualizarAnalitica(){
    const total = ventas.reduce((acc,v)=>acc+v.total,0);
    document.getElementById('totalVentas').innerText='$'+total;
    document.getElementById('cantidadVentas').innerText=ventas.length;

    grafico.data.labels = ventas.map(v=>v.producto);
    grafico.data.datasets[0].data = ventas.map(v=>v.total);
    grafico.update();
}

function openModal(){document.getElementById('modalVenta').style.display='flex';}
function closeModal(){document.getElementById('modalVenta').style.display='none';}
function guardarDesdeModal(){
    agregarVenta(
        document.getElementById('mCliente').value,
        document.getElementById('mProducto').value,
        document.getElementById('mTotal').value
    );
    closeModal();
}

const ctx = document.getElementById('graficoVentas');
const grafico = new Chart(ctx,{
    type:'bar',
    data:{labels:[],datasets:[{label:'Ventas por Producto',data:[]}]},
    options:{responsive:true,plugins:{legend:{labels:{color:'white'}}},scales:{x:{ticks:{color:'white'}},y:{ticks:{color:'white'}}}}
});
</script>

</body>
</html>
