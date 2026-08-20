<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarioExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $productos) {}

    public function collection()
    {
        return $this->productos;
    }

    public function headings(): array
    {
        return [
            'Producto',
            'Categoría',
            'Stock actual',
            'Stock mínimo',
            'Estado',
            'Precio unitario (S/)',
            'Valor en inventario (S/)',
            'Disponible',
            'Popular',
            'Descuento activo',
        ];
    }

    public function map($p): array
    {
        $estado = $p->stock <= 0
            ? 'Agotado'
            : ($p->stock_bajo ? 'Stock bajo' : 'Normal');

        return [
            $p->name,
            $p->category?->name ?? '—',
            $p->stock,
            $p->stock_minimo ?? '—',
            $estado,
            number_format((float) $p->price, 2),
            number_format($p->stock * (float) $p->price, 2),
            $p->available ? 'Sí' : 'No',
            $p->popular ? 'Sí' : 'No',
            $p->tieneDescuentoActivo() ? "-{$p->descuento_porcentaje}%" : '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
