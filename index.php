<?php
// Incluir clases y funciones necesarias
require_once 'clases/Cliente.php';
require_once 'clases/Producto.php';
require_once 'clases/Factura.php';
require_once 'clases/Boleta.php';
require_once 'funciones/crudClientes.php';
require_once 'funciones/crudProductos.php';

session_start();

// Inicializar los arrays en la sesión si no existen
if (!isset($_SESSION['clientes'])) {
    $_SESSION['clientes'] = [];
}
if (!isset($_SESSION['productos'])) {
    $_SESSION['productos'] = [];
}

$clientes = &$_SESSION['clientes'];
$productos = &$_SESSION['productos'];

$facturaGenerada = false;
$documento = null;

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // EDITAR CLIENTE
    if (isset($_POST['editar_cliente'])) {
        $nombreActualCliente = $_POST['nombre_actual_cliente'];
        $nuevoNombreCliente = $_POST['nuevo_nombre_cliente'];
        $nuevoEmailCliente = $_POST['nuevo_email_cliente'];
        editarCliente($clientes, $nombreActualCliente, $nuevoNombreCliente, $nuevoEmailCliente);
    }

    // EDITAR PRODUCTO
    if (isset($_POST['editar_producto'])) {
        $nombreActualProducto = $_POST['nombre_actual_producto'];
        $nuevoNombreProducto = $_POST['nuevo_nombre_producto'];
        $nuevoPrecioProducto = $_POST['nuevo_precio_producto'];
        editarProducto($productos, $nombreActualProducto, $nuevoNombreProducto, $nuevoPrecioProducto);
    }

    // AGREGAR CLIENTE
    if (isset($_POST['agregar_cliente'])) {
        $nombreCliente = $_POST['nombre_cliente'];
        $emailCliente = $_POST['email_cliente'];
        agregarCliente($clientes, $nombreCliente, $emailCliente);
    }

    // AGREGAR PRODUCTO
    if (isset($_POST['agregar_producto'])) {
        $nombreProducto = $_POST['nombre_producto'];
        $precioProducto = $_POST['precio_producto'];
        agregarProducto($productos, $nombreProducto, $precioProducto);
    }

    // ELIMINAR CLIENTE
    if (isset($_POST['eliminar_cliente'])) {
        $nombreClienteEliminar = $_POST['nombre_cliente_eliminar'];
        eliminarCliente($clientes, $nombreClienteEliminar);
    }

    // ELIMINAR PRODUCTO
    if (isset($_POST['eliminar_producto'])) {
        $nombreProductoEliminar = $_POST['nombre_producto_eliminar'];
        eliminarProducto($productos, $nombreProductoEliminar);
    }

    // GENERAR DOCUMENTO
    if (isset($_POST['generar_documento'])) {
        $clienteSeleccionado = $_POST['cliente'];
        $tipoDocumento = $_POST['tipo_documento'];
        $productosSeleccionados = $_POST['productos'];

        // Buscar el cliente seleccionado
        foreach ($clientes as $cliente) {
            if ($cliente->getNombre() === $clienteSeleccionado) {
                $clienteActual = $cliente;
                break;
            }
        }

        // Crear el documento adecuado según el tipo seleccionado
        if ($tipoDocumento === 'factura') {
            $documento = new Factura($clienteActual);
        } else {
            $documento = new Boleta($clienteActual);
        }

        // Agregar productos al documento
        foreach ($productos as $producto) {
            if (in_array($producto->getNombre(), $productosSeleccionados)) {
                $documento->agregarProducto($producto);
            }
        }

        $facturaGenerada = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Facturación Electrónica</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>Sistema de Facturación Electrónica</h1>
</header>

<div class="container">

    <!-- Columna izquierda: Formularios -->
    <div class="formulario">
        <!-- Formulario para agregar cliente -->
        <form action="index.php" method="POST">
            <h2>Agregar Cliente</h2>
            <input type="text" name="nombre_cliente" placeholder="Nombre del Cliente" required>
            <input type="email" name="email_cliente" placeholder="Email del Cliente" required>
            <input type="submit" name="agregar_cliente" value="Agregar Cliente">
        </form>

        <!-- Formulario para agregar producto -->
        <form action="index.php" method="POST">
            <h2>Agregar Producto</h2>
            <input type="text" name="nombre_producto" placeholder="Nombre del Producto" required>
            <input type="number" name="precio_producto" placeholder="Precio del Producto" required>
            <input type="submit" name="agregar_producto" value="Agregar Producto">
        </form>

        <!-- Formulario para generar Factura o Boleta -->
        <form action="index.php" method="POST">
            <h2>Generar Documento</h2>
            <label>Seleccionar Cliente:</label>
            <select name="cliente" required>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?php echo $cliente->getNombre(); ?>"><?php echo $cliente->getNombre(); ?></option>
                <?php endforeach; ?>
            </select>

            <label>Seleccionar Productos:</label>
            <select name="productos[]" multiple required>
                <?php foreach ($productos as $producto): ?>
                    <option value="<?php echo $producto->getNombre(); ?>"><?php echo $producto->getNombre() . ": $" . $producto->getPrecio(); ?></option>
                <?php endforeach; ?>
            </select>

            <label>Tipo de Documento:</label>
            <select name="tipo_documento" required>
                <option value="factura">Factura</option>
                <option value="boleta">Boleta</option>
            </select>
            <input type="submit" name="generar_documento" value="Generar Documento">
        </form>

       <!-- Mostrar documento generado -->
        <?php if ($facturaGenerada && $documento): ?>
            <div class="documento">
                <h3>Documento Generado:</h3>
                <?php echo $documento->generarHTML(); ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- Columna derecha: Tablas -->
    <div class="tablas">
        <!-- Tabla de clientes -->
        <h2>Clientes</h2>
        <table>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($clientes as $cliente): ?>
                <tr>
                    <td><?php echo $cliente->getNombre(); ?></td>
                    <td><?php echo $cliente->getEmail(); ?></td>
                    <td>
                        <button type="button" onclick="mostrarEditarCliente('<?php echo $cliente->getNombre(); ?>', '<?php echo $cliente->getEmail(); ?>')">Editar</button>
                        <form action="index.php" method="POST" style="display:inline;">
                            <input type="hidden" name="nombre_cliente_eliminar" value="<?php echo $cliente->getNombre(); ?>">
                            <button class="eliminar" type="submit" name="eliminar_cliente">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Tabla de productos -->
        <h2>Productos</h2>
        <table>
            <tr>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?php echo $producto->getNombre(); ?></td>
                    <td><?php echo $producto->getPrecio(); ?></td>
                    <td>
                        <button type="button" onclick="mostrarEditarProducto('<?php echo $producto->getNombre(); ?>', '<?php echo $producto->getPrecio(); ?>')">Editar</button>
                        <form action="index.php" method="POST" style="display:inline;">
                            <input type="hidden" name="nombre_producto_eliminar" value="<?php echo $producto->getNombre(); ?>">
                            <button class="eliminar" type="submit" name="eliminar_producto">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Formulario para editar cliente -->
        <div id="editar_cliente_form" style="display:none;">
            <form action="index.php" method="POST">
                <h2>Editar Cliente</h2>
                <input type="hidden" name="nombre_actual_cliente">
                <input type="text" name="nuevo_nombre_cliente" placeholder="Nuevo Nombre" required>
                <input type="email" name="nuevo_email_cliente" placeholder="Nuevo Email" required>
                <input type="submit" name="editar_cliente" value="Guardar Cambios">
            </form>
        </div>

        <!-- Formulario para editar producto -->
        <div id="editar_producto_form" style="display:none;">
            <form action="index.php" method="POST">
                <h2>Editar Producto</h2>
                <input type="hidden" name="nombre_actual_producto">
                <input type="text" name="nuevo_nombre_producto" placeholder="Nuevo Nombre" required>
                <input type="number" name="nuevo_precio_producto" placeholder="Nuevo Precio" required>
                <input type="submit" name="editar_producto" value="Guardar Cambios">
            </form>
        </div>
    </div>
</div>
<script>
// Funciones de mostrar formularios de edición
function mostrarEditarCliente(nombre,email) {
    document.getElementById('editar_cliente_form').style.display = 'block';
    document.querySelector('[name="nombre_actual_cliente"]').value = nombre;
    document.querySelector('[name="nuevo_nombre_cliente"]').value = nombre;
    document.querySelector('[name="nuevo_email_cliente"]').value = email;
}

function mostrarEditarProducto(nombre, precio) {
    document.getElementById('editar_producto_form').style.display = 'block';
    document.querySelector('[name="nombre_actual_producto"]').value = nombre;
    document.querySelector('[name="nuevo_nombre_producto"]').value = nombre;
    document.querySelector('[name="nuevo_precio_producto"]').value = precio;
}
</script>

</body>
</html>
