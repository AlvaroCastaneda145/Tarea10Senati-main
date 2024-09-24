<?php
require_once 'Cliente.php';
require_once 'Producto.php';
require_once 'DocumentoFiscal.php';

class Factura {
    private $cliente;
    private $productos = [];

    public function __construct(Cliente $cliente) {
        $this->cliente = $cliente;
    }

    public function agregarProducto(Producto $producto) {
        $this->productos[] = $producto;
    }

    public function calcularTotal() {
        $total = 0;
        foreach ($this->productos as $producto) {
            $total += $producto->getPrecio();
        }
        return $total;
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function getTotal() {
        return $this->calcularTotal();
    }

    public function generarHTML() {
        // Agregar la clase "documento" para estilos consistentes
        $html = "<div class='documento'>";
        $html .= "<h2>Factura</h2>";
        $html .= "<p><strong>Cliente:</strong> " . $this->cliente->getNombre() . ", <strong>Email:</strong> " . $this->cliente->getEmail() . "</p>";
        
        // Lista de productos
        $html .= "<h3>Productos:</h3><ul style='padding-left: 20px; list-style-type: none;'>";  // Eliminar marcadores de lista
        foreach ($this->productos as $producto) {
            $html .= "<li>" . $producto->getNombre() . ": <strong>$" . $producto->getPrecio() . "</strong></li>";
        }
        $html .= "</ul>";
        
        // Total
        $html .= "<p><strong>Total:</strong> <strong>$" . $this->getTotal() . "</strong></p>";
        
        // Cerrar el div del documento
        $html .= "</div>";
        return $html;
    }
}