<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
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
}
