<?php

namespace App\Http\Controllers\Api;

use App\Models\Comision;
use App\Models\ConfiguracionSistema;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SistemaController extends Controller
{
    // GET /api/v1/admin/sistema/dashboard
    // Acceso: sistema + admin + super_admin
    public function dashboard(Request $request): JsonResponse
    {
        $periodo = $request->get('periodo', 'mes');
        $filtro  = $request->get('filtro', 'todos'); // todos|pendiente|cobrado
        $desde   = $this->getDesde($periodo, $request->get('desde'));
        $hasta   = $this->getHasta($periodo, $request->get('hasta'));

        // ── KPIs ──────────────────────────────────────────
        $pedidos = Order::where('status', 'entregado')
            ->whereBetween('updated_at', [$desde, $hasta])
            ->count();

        $baseQuery = Comision::whereBetween('fecha', [
            $desde->toDateString(),
            $hasta->toDateString(),
        ]);

        $totalComision  = (float) (clone $baseQuery)->sum('monto_comision');
        $totalCobrado   = (float) (clone $baseQuery)->where('cobrado', true)->sum('monto_comision');
        $totalPendiente = $totalComision - $totalCobrado;

        // Crecimiento vs período anterior
        $diff      = $desde->diffInDays($hasta) + 1;
        $prevQuery = Comision::whereBetween('fecha', [
            $desde->copy()->subDays($diff)->toDateString(),
            $desde->copy()->subDay()->toDateString(),
        ]);
        $prevComision = (float) $prevQuery->sum('monto_comision');
        $crecimiento  = $prevComision > 0
            ? round((($totalComision - $prevComision) / $prevComision) * 100, 1)
            : 0;

        // ── Detalle paginado con filtro ───────────────────
        $detalleQuery = Comision::with('order:id,client_name,type,total,updated_at')
            ->whereBetween('fecha', [
                $desde->toDateString(),
                $hasta->toDateString(),
            ]);

        if ($filtro === 'pendiente') {
            $detalleQuery->where('cobrado', false);
        } elseif ($filtro === 'cobrado') {
            $detalleQuery->where('cobrado', true);
        }

        $detalle = $detalleQuery
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 10));

        // ── Gráfico por día ───────────────────────────────
        $porDia = Comision::selectRaw(
            'fecha, COUNT(*) as pedidos, SUM(monto_comision) as total,
             SUM(CASE WHEN cobrado = true THEN monto_comision ELSE 0 END) as cobrado'
        )
            ->whereBetween('fecha', [
                $desde->toDateString(),
                $hasta->toDateString(),
            ])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(fn($r) => [
                'fecha'   => $r->fecha->format('Y-m-d'),
                'label'   => $r->fecha->format('d/m'),
                'pedidos' => (int) $r->pedidos,
                'total'   => (float) $r->total,
                'cobrado' => (float) $r->cobrado,
            ]);

        return $this->success([
            'kpis' => [
                'pedidos'         => $pedidos,
                'total_comision'  => $totalComision,
                'total_cobrado'   => $totalCobrado,
                'total_pendiente' => $totalPendiente,
                'crecimiento_pct' => $crecimiento,
            ],
            'por_dia' => $porDia,
            'detalle' => $detalle,
            'periodo' => [
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
                'label' => $periodo,
            ],
        ]);
    }

    // GET /api/v1/admin/sistema/config — solo sistema
    public function getConfig(): JsonResponse
    {
        $comision = ConfiguracionSistema::get('comision_por_pedido', '0.30');
        return $this->success([
            'comision_por_pedido' => (float) $comision,
        ]);
    }

    // PUT /api/v1/admin/sistema/config — solo sistema
    public function updateConfig(Request $request): JsonResponse
    {
        $data = $request->validate([
            'comision_por_pedido' => 'required|numeric|min:0|max:10',
        ]);

        ConfiguracionSistema::set(
            'comision_por_pedido',
            number_format($data['comision_por_pedido'], 2, '.', '')
        );

        return $this->success([
            'comision_por_pedido' => (float) $data['comision_por_pedido'],
        ], 'Configuración actualizada');
    }

    // POST /api/v1/admin/sistema/cobrar — solo sistema
    public function marcarCobrado(Request $request): JsonResponse
    {
        $data = $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
            'ids'   => 'nullable|array',   // opcional: IDs específicos
            'ids.*' => 'integer|exists:comisiones,id',
        ]);

        $query = Comision::where('cobrado', false);

        if (!empty($data['ids'])) {
            // Cobro por IDs específicos
            $query->whereIn('id', $data['ids']);
        } else {
            // Cobro por rango de fechas
            $query->whereBetween('fecha', [$data['desde'], $data['hasta']]);
        }

        $count = $query->update([
            'cobrado'    => true,
            'cobrado_at' => now(),
        ]);

        return $this->success([
            'marcadas' => $count,
        ], "{$count} comisiones marcadas como cobradas");
    }

    // GET /api/v1/admin/sistema/comisiones-pendientes
    // Acceso: sistema + admin + cajero
    public function comisionesPendientesCaja(): JsonResponse
    {
        return $this->success([
            'hoy'    => (float) Comision::whereDate('fecha', today())
                ->where('cobrado', false)->sum('monto_comision'),
            'semana' => (float) Comision::where('fecha', '>=', now()->startOfWeek()->toDateString())
                ->where('cobrado', false)->sum('monto_comision'),
            'mes'    => (float) Comision::where('fecha', '>=', now()->startOfMonth()->toDateString())
                ->where('cobrado', false)->sum('monto_comision'),
            'total'  => (float) Comision::where('cobrado', false)->sum('monto_comision'),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────
    private function getDesde(string $periodo, ?string $custom): \Carbon\Carbon
    {
        return match ($periodo) {
            'hoy'    => now()->startOfDay(),
            'semana' => now()->startOfWeek(),
            'mes'    => now()->startOfMonth(),
            'año'    => now()->startOfYear(),
            'custom' => \Carbon\Carbon::parse($custom)->startOfDay(),
            default  => now()->startOfMonth(),
        };
    }

    private function getHasta(string $periodo, ?string $custom): \Carbon\Carbon
    {
        return match ($periodo) {
            'custom' => \Carbon\Carbon::parse($custom)->endOfDay(),
            default  => now()->endOfDay(),
        };
    }
}
