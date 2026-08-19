<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(): JsonResponse
    {
        $today      = now()->startOfDay();
        $yesterday  = now()->subDay()->startOfDay();
        $weekStart  = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        // ── Totales ──
        $salesToday  = $this->sumOrders($today);
        $salesYester = $this->sumOrders($yesterday, $today);
        $salesWeek   = $this->sumOrders($weekStart);
        $salesMonth  = $this->sumOrders($monthStart);

        $ordersToday  = Order::where('created_at', '>=', $today)->count();
        $ordersMonth  = Order::where('created_at', '>=', $monthStart)->count();

        // ── Ticket promedio ──
        $avgTicket = Order::where('status', '!=', 'cancelado')
            ->where('created_at', '>=', $monthStart)
            ->avg('total') ?? 0;

        // ── Ventas por tipo ──
        $byType = Order::select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->where('status', '!=', 'cancelado')
            ->where('created_at', '>=', $monthStart)
            ->groupBy('type')
            ->get()
            ->map(fn($r) => [
                'type'  => $r->type,
                'count' => (int) $r->count,
                'total' => (float) $r->total,
            ]);

        // ── Ventas por estado ──
        $byStatus = Order::select('status', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $monthStart)
            ->groupBy('status')
            ->get()
            ->map(fn($r) => [
                'status' => $r->status,
                'count'  => (int) $r->count,
            ]);

        // ── Ventas por hora hoy ──
        $byHour = Order::select(
            DB::raw('EXTRACT(HOUR FROM created_at) as hour'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total) as total')
        )
            ->where('status', '!=', 'cancelado')
            ->where('created_at', '>=', $today)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($r) => [
                'hour'  => (int) $r->hour,
                'count' => (int) $r->count,
                'total' => (float) $r->total,
            ]);

        // ── Ventas últimos 7 días ──
        $last7 = collect(range(6, 0))->map(function ($d) {
            $date  = now()->subDays($d)->startOfDay();
            $end   = now()->subDays($d)->endOfDay();
            $total = Order::where('status', '!=', 'cancelado')
                ->whereBetween('created_at', [$date, $end])
                ->sum('total');
            return [
                'date'  => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'total' => (float) $total,
            ];
        });

        // ── Top productos ──
        $topProducts = OrderItem::select(
            'product_id',
            DB::raw('SUM(qty) as total_qty'),
            DB::raw('SUM(subtotal) as total_revenue')
        )
            ->with('product:id,name,icon')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(8)
            ->get()
            ->map(fn($item) => [
                'product' => $item->product?->name ?? 'Producto eliminado',
                'icon'    => $item->product?->icon ?? 'package',
                'qty'     => (int) $item->total_qty,
                'revenue' => (float) $item->total_revenue,
            ]);

        // ── Crecimiento vs ayer ──
        $growthPct = $salesYester > 0
            ? round((($salesToday - $salesYester) / $salesYester) * 100, 1)
            : 0;

        return $this->success([
            'sales_today'    => (float) $salesToday,
            'sales_yesterday' => (float) $salesYester,
            'sales_week'     => (float) $salesWeek,
            'sales_month'    => (float) $salesMonth,
            'orders_today'   => $ordersToday,
            'orders_month'   => $ordersMonth,
            'avg_ticket'     => round((float) $avgTicket, 2),
            'growth_pct'     => $growthPct,
            'by_type'        => $byType,
            'by_status'      => $byStatus,
            'by_hour'        => $byHour,
            'last_7_days'    => $last7,
            'top_products'   => $topProducts,
        ]);
    }

    // Cuenta cuántas veces se eligió cada opción, agrupado por sección de
    // personalización — de forma genérica, sin asumir qué secciones existen
    // (cada negocio define las suyas: "envoltura"/"lazo" para una florería,
    // "salsas"/"papas" para un restaurante, etc.)
    public function customizations(): JsonResponse
    {
        $items = OrderItem::whereNotNull('customization')
            ->whereRaw("customization::text != 'null'")
            ->whereRaw("customization::text != '[]'")
            ->get(['customization']);

        // seccion => ['label' => ..., 'counts' => [opcion => qty]]
        $bySection = [];

        foreach ($items as $item) {
            $customization = is_string($item->customization)
                ? json_decode($item->customization, true)
                : ($item->customization ?? []);

            if (!is_array($customization)) continue;

            foreach ($customization as $section) {
                $seccion    = $section['seccion']    ?? '';
                $label      = $section['label']      ?? $seccion;
                $selections = $section['selections'] ?? [];

                if (empty($seccion)) continue;

                if (!isset($bySection[$seccion])) {
                    $bySection[$seccion] = ['label' => $label, 'counts' => []];
                }

                foreach ($selections as $sel) {
                    $name = $sel['name'] ?? '';
                    if (empty($name)) continue;

                    $bySection[$seccion]['counts'][$name] =
                        ($bySection[$seccion]['counts'][$name] ?? 0) + 1;
                }
            }
        }

        $secciones = collect($bySection)->map(function ($data, $seccion) {
            $counts = $data['counts'];
            arsort($counts);

            return [
                'seccion' => $seccion,
                'label'   => $data['label'],
                'options' => collect($counts)
                    ->map(fn($qty, $name) => compact('name', 'qty'))
                    ->values(),
            ];
        })->values();

        return $this->success([
            'secciones'               => $secciones,
            'total_items_analizados'  => $items->count(),
        ]);
    }

    private function sumOrders($from, $to = null): float
    {
        $q = Order::where('status', '!=', 'cancelado')
            ->where('created_at', '>=', $from);
        if ($to) $q->where('created_at', '<', $to);
        return (float) $q->sum('total');
    }

    // GET /admin/reports/historico?periodo=dia|semana|mes|anio&anio=2026&mes=8
    //
    // Un solo endpoint flexible para las 4 granularidades — cada una
    // trae su comparación automática contra el período equivalente del
    // año anterior (excepto "anio", que ya es una comparación en sí
    // misma al mostrar varios años seguidos).
    public function historico(Request $request): JsonResponse
    {
        $periodo   = $request->query('periodo', 'mes');
        $anio      = (int) $request->query('anio', now()->year);
        $mes       = (int) $request->query('mes', now()->month);

        return match ($periodo) {
            'dia'    => $this->historicoDia($anio, $mes),
            'semana' => $this->historicoSemana($anio, $mes),
            'anio'   => $this->historicoAnio(),
            default  => $this->historicoMes($anio),
        };
    }

    private function historicoMes(int $anio): JsonResponse
    {
        $actual   = $this->ventasPorMes($anio);
        $anterior = $this->ventasPorMes($anio - 1);

        return $this->success([
            'periodo'         => 'mes',
            'anio_actual'     => $anio,
            'anio_anterior'   => $anio - 1,
            'series_actual'   => $actual,
            'series_anterior' => $anterior,
            'total_actual'    => round(array_sum(array_column($actual, 'total')), 2),
            'total_anterior'  => round(array_sum(array_column($anterior, 'total')), 2),
        ]);
    }

    private function historicoDia(int $anio, int $mes): JsonResponse
    {
        $actual   = $this->ventasPorDia($anio, $mes);
        $anterior = $this->ventasPorDia($anio - 1, $mes);

        return $this->success([
            'periodo'         => 'dia',
            'anio_actual'     => $anio,
            'anio_anterior'   => $anio - 1,
            'mes'             => $mes,
            'series_actual'   => $actual,
            'series_anterior' => $anterior,
            'total_actual'    => round(array_sum(array_column($actual, 'total')), 2),
            'total_anterior'  => round(array_sum(array_column($anterior, 'total')), 2),
        ]);
    }

    private function historicoSemana(int $anio, int $mes): JsonResponse
    {
        $actual   = $this->ventasPorSemana($anio, $mes);
        $anterior = $this->ventasPorSemana($anio - 1, $mes);

        return $this->success([
            'periodo'         => 'semana',
            'anio_actual'     => $anio,
            'anio_anterior'   => $anio - 1,
            'mes'             => $mes,
            'series_actual'   => $actual,
            'series_anterior' => $anterior,
            'total_actual'    => round(array_sum(array_column($actual, 'total')), 2),
            'total_anterior'  => round(array_sum(array_column($anterior, 'total')), 2),
        ]);
    }

    private function historicoAnio(): JsonResponse
    {
        // Todos los años que de verdad tienen pedidos — no inventamos
        // años vacíos de relleno.
        $anios = Order::selectRaw('DISTINCT EXTRACT(YEAR FROM created_at)::int as anio')
            ->orderBy('anio')
            ->pluck('anio');

        $series = $anios->map(fn($a) => [
            'clave' => (string) $a,
            'total' => $this->sumOrders(
                now()->setDate($a, 1, 1)->startOfDay(),
                now()->setDate($a + 1, 1, 1)->startOfDay(),
            ),
        ]);

        return $this->success([
            'periodo'      => 'anio',
            'series'       => $series,
            'total'        => round($series->sum('total'), 2),
        ]);
    }

    private function ventasPorMes(int $anio): array
    {
        $filas = Order::selectRaw('EXTRACT(MONTH FROM created_at)::int as mes, SUM(total) as total')
            ->where('status', '!=', 'cancelado')
            ->whereYear('created_at', $anio)
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        return collect(range(1, 12))->map(fn($m) => [
            'clave' => $meses[$m - 1],
            'total' => (float) ($filas[$m] ?? 0),
        ])->all();
    }

    private function ventasPorDia(int $anio, int $mes): array
    {
        $diasEnMes = now()->setDate($anio, $mes, 1)->daysInMonth;

        $filas = Order::selectRaw('EXTRACT(DAY FROM created_at)::int as dia, SUM(total) as total')
            ->where('status', '!=', 'cancelado')
            ->whereYear('created_at', $anio)
            ->whereMonth('created_at', $mes)
            ->groupBy('dia')
            ->pluck('total', 'dia');

        return collect(range(1, $diasEnMes))->map(fn($d) => [
            'clave' => (string) $d,
            'total' => (float) ($filas[$d] ?? 0),
        ])->all();
    }

    private function ventasPorSemana(int $anio, int $mes): array
    {
        // Semana del mes = en qué bloque de 7 días cae el día (1-7 → sem 1,
        // 8-14 → sem 2, etc.) — simple e intuitivo para el dueño, no usa
        // semana ISO que puede cruzar meses de forma confusa.
        $filas = Order::selectRaw("CEIL(EXTRACT(DAY FROM created_at) / 7.0)::int as semana, SUM(total) as total")
            ->where('status', '!=', 'cancelado')
            ->whereYear('created_at', $anio)
            ->whereMonth('created_at', $mes)
            ->groupBy('semana')
            ->pluck('total', 'semana');

        return collect(range(1, 5))->map(fn($s) => [
            'clave' => "Sem {$s}",
            'total' => (float) ($filas[$s] ?? 0),
        ])->all();
    }
}
