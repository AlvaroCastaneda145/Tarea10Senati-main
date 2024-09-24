<?php
require_once 'DocumentoFiscal.php';

class Boleta extends DocumentoFiscal {
    public function calcularTotal() {
        $total = 0;
        foreach ($this->productos as $producto) {
            $total += $producto->getPrecio();
        }
        return $total * 0.95; // Aplicar descuento del 5%
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function getTotal() {
        return $this->calcularTotal();
    }

    public function generarHTML() {
        // Agregar la clase "documento" para un estilo uniforme
        $html = "<div class='documento'>";
        $html .= "<h2>Boleta</h2>";
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
