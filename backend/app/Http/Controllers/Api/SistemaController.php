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

    // GET /api/v1/pedido-config — público, lo consume el checkout y el admin
    public function getPedidoConfig(): JsonResponse
    {
        return $this->success($this->pedidoConfigData());
    }

    // POST /api/v1/admin/sistema/pedido-config — solo admin/sistema
    public function updatePedidoConfig(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mensaje_activo'             => 'sometimes|boolean',
            'mensaje_label'              => 'sometimes|string|max:60',
            'entrega_programada_activo'  => 'sometimes|boolean',
            'entrega_programada_label'   => 'sometimes|string|max:60',
        ]);

        foreach ($data as $clave => $valor) {
            ConfiguracionSistema::set(
                "pedido_{$clave}",
                is_bool($valor) ? ($valor ? '1' : '0') : $valor
            );
        }

        return $this->success($this->pedidoConfigData(), 'Configuración de pedido actualizada');
    }

    private function pedidoConfigData(): array
    {
        return [
            'mensaje_activo'            => (bool) (int) ConfiguracionSistema::get('pedido_mensaje_activo', '1'),
            'mensaje_label'             => ConfiguracionSistema::get('pedido_mensaje_label', 'Mensaje para la tarjeta'),
            'entrega_programada_activo' => (bool) (int) ConfiguracionSistema::get('pedido_entrega_programada_activo', '1'),
            'entrega_programada_label'  => ConfiguracionSistema::get('pedido_entrega_programada_label', '¿Cuándo lo necesitas?'),
        ];
    }

    // GET /api/v1/branding — público, lo consume el catálogo y el login
    public function getBranding(): JsonResponse
    {
        return $this->success($this->brandingData());
    }

    // PUT /api/v1/admin/sistema/branding — solo admin/sistema
    public function updateBranding(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre_negocio'      => 'sometimes|string|max:80',
            'color_primario'      => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_primario_dark' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'telefono'            => 'sometimes|nullable|string|max:20',
            'whatsapp'            => 'sometimes|nullable|string|max:20',
            'direccion'           => 'sometimes|nullable|string|max:255',
            'logo'                => 'sometimes|image|mimes:png,jpg,jpeg,webp,svg|max:5120',
        ]);

        foreach ($data as $clave => $valor) {
            if ($clave === 'logo') continue;
            ConfiguracionSistema::set($clave, $valor);
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('branding', 'public');
            // URL absoluta (con dominio del backend) — el frontend corre en
            // otro puerto/origen, así que una ruta relativa como "/storage/..."
            // se intentaría cargar desde el frontend y nunca se encontraría.
            ConfiguracionSistema::set('logo_url', asset('storage/' . $path));
        }

        return $this->success($this->brandingData(), 'Marca actualizada');
    }

    private function brandingData(): array
    {
        return [
            'nombre_negocio'      => ConfiguracionSistema::get('nombre_negocio', 'Mi Negocio'),
            'logo_url'            => ConfiguracionSistema::get('logo_url', '/images/logobirds.png'),
            'color_primario'      => ConfiguracionSistema::get('color_primario', '#C41E1E'),
            'color_primario_dark' => ConfiguracionSistema::get('color_primario_dark', '#9B1717'),
            'telefono'            => ConfiguracionSistema::get('telefono'),
            'whatsapp'            => ConfiguracionSistema::get('whatsapp'),
            'direccion'           => ConfiguracionSistema::get('direccion'),
        ];
    }

    // GET /api/v1/admin/sistema/config — solo sistema
    public function getConfig(): JsonResponse
    {
        $comision = ConfiguracionSistema::get('comision_por_pedido', '0.30');
        $deliveryFallback = ConfiguracionSistema::get('delivery_fee_fallback', '5.00');
        return $this->success([
            'comision_por_pedido'   => (float) $comision,
            'delivery_fee_fallback' => (float) $deliveryFallback,
        ]);
    }

    // PUT /api/v1/admin/sistema/config — solo sistema
    public function updateConfig(Request $request): JsonResponse
    {
        $data = $request->validate([
            'comision_por_pedido'   => 'required|numeric|min:0|max:10',
            'delivery_fee_fallback' => 'sometimes|numeric|min:0',
        ]);

        ConfiguracionSistema::set(
            'comision_por_pedido',
            number_format($data['comision_por_pedido'], 2, '.', '')
        );

        if (isset($data['delivery_fee_fallback'])) {
            ConfiguracionSistema::set(
                'delivery_fee_fallback',
                number_format($data['delivery_fee_fallback'], 2, '.', '')
            );
        }

        return $this->success([
            'comision_por_pedido'   => (float) $data['comision_por_pedido'],
            'delivery_fee_fallback' => (float) ($data['delivery_fee_fallback'] ?? ConfiguracionSistema::get('delivery_fee_fallback', '5.00')),
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
