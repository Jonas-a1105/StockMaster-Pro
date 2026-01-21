<?php
namespace App\Domain\DTOs;

class VentaCheckoutDTO extends BaseDTO {
    public array $carrito = [];
    public ?float $tasa = null;
    public ?int $cliente_id = null;
    public ?string $estado_pago = null;
    public string $metodo_pago = 'Efectivo';
    public ?string $notas = null;

    /**
     * Factory method to create from Request data
     */
    public static function fromRequest(array $data): self {
        return new self([
            'carrito' => $data['carrito'] ?? [],
            'tasa' => isset($data['tasa']) ? (float)$data['tasa'] : null,
            'cliente_id' => !empty($data['cliente_id']) ? (int)$data['cliente_id'] : null,
            'estado_pago' => $data['estado_pago'] ?? null,
            'metodo_pago' => $data['metodo_pago'] ?? 'Efectivo',
            'notas' => $data['notas'] ?? null,
        ]);
    }
}
