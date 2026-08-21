<template>
  <div class="flex flex-col gap-5">

    <!-- ══ SKELETON CARGA INICIAL ══ -->
    <template v-if="loadingPage">
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="n in 4" :key="n" class="h-28 rounded-2xl bg-gray-100 animate-pulse" />
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="h-80 rounded-2xl bg-gray-100 animate-pulse" />
        <div class="h-80 rounded-2xl bg-gray-100 animate-pulse" />
      </div>
    </template>

    <!-- ══ SIN CAJA ABIERTA ══ -->
    <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 translate-y-2">
      <div v-if="!loadingPage && estado === 'sin_abrir'" class="flex flex-col items-center justify-center py-16 gap-6">

        <div class="w-20 h-20 rounded-3xl bg-gray-100 flex items-center justify-center">
          <BanknotesIcon class="w-10 h-10 text-gray-400" />
        </div>

        <div class="text-center">
          <h2 class="font-black text-[22px] text-gray-900 m-0 mb-1" style="font-family:'Plus Jakarta Sans',sans-serif;">
            Caja sin abrir
          </h2>
          <p class="text-gray-400 text-[14px] m-0">
            Abre la caja para comenzar a registrar movimientos
          </p>
        </div>

        <!-- Card apertura -->
        <div class="w-full max-w-sm bg-white rounded-3xl border border-gray-100
                    shadow-sm p-7 flex flex-col gap-4">

          <div class="flex flex-col gap-1.5">
            <label class="text-[11px] font-black uppercase tracking-widest text-gray-500">
              Monto de apertura (S/)
            </label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2
                           text-[14px] font-bold text-gray-400">S/</span>
              <input v-model.number="apertura.monto" type="number" min="0" step="0.50" placeholder="200.00" class="w-full pl-10 pr-4 py-3.5 rounded-2xl border-2 border-gray-100
                       bg-gray-50 text-[15px] font-bold text-gray-900 outline-none
                       placeholder:text-gray-300 placeholder:font-normal
                       focus:border-brand-red focus:bg-white
                       focus:shadow-[0_0_0_4px_rgba(var(--color-brand-primary-rgb,196,30,30),0.08)]
                       transition-all duration-200" />
            </div>
          </div>

          <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
            leave-to-class="opacity-0">
            <div v-if="apertura.error" class="flex items-center gap-2 px-4 py-3 rounded-2xl
                     bg-red-50 border border-red-200">
              <ExclamationCircleIcon class="w-4 h-4 text-red-500 shrink-0" />
              <p class="text-[12.5px] text-red-700 m-0">{{ apertura.error }}</p>
            </div>
          </Transition>

          <button @click="abrirCaja" :disabled="apertura.loading || apertura.monto <= 0" class="w-full py-3.5 rounded-2xl font-black text-[14px] text-white
                   bg-brand-red border-none cursor-pointer uppercase tracking-wide
                   shadow-[0_4px_20px_rgba(var(--color-brand-primary-rgb,196,30,30),0.25)]
                   hover:bg-red-700 hover:-translate-y-0.5 active:scale-[0.98]
                   disabled:opacity-40 disabled:cursor-not-allowed
                   disabled:hover:translate-y-0 transition-all duration-200
                   flex items-center justify-center gap-2" style="font-family:'Plus Jakarta Sans',sans-serif;">
            <span v-if="apertura.loading" class="w-4 h-4 border-2 border-white/30 border-t-white
                     rounded-full animate-spin" />
            <LockOpenIcon v-else class="w-4 h-4" />
            {{ apertura.loading ? 'Abriendo...' : 'Abrir caja' }}
          </button>
        </div>
      </div>
    </Transition>

    <!-- ══ Enlace a historial ══ -->
    <div v-if="!loadingPage" class="flex justify-end">
      <RouterLink to="/admin/caja/historial" class="flex items-center gap-1.5 text-[12.5px] font-semibold text-gray-500
               no-underline hover:text-brand-red transition-colors">
        <ClockIcon class="w-3.5 h-3.5" />
        Ver historial de cajas →
      </RouterLink>
    </div>

    <!-- ══ CAJA ABIERTA / CERRADA ══ -->
    <template v-if="!loadingPage && estado !== 'sin_abrir' && caja">

      <!-- Aviso: esta caja es de un día anterior, no de hoy -->
      <div v-if="esDiaAnterior" class="flex items-start gap-3 px-4 py-3.5 rounded-2xl
               bg-amber-50 border border-amber-200">
        <ExclamationTriangleIcon class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
        <div>
          <p class="text-[13.5px] font-bold text-amber-800 m-0">
            Esta caja quedó abierta del {{ formatFecha(caja.fecha) }}
          </p>
          <p class="text-[12.5px] text-amber-700 m-0 mt-0.5">
            Ciérrala para poder abrir la caja de hoy — no se puede abrir una nueva mientras esta siga pendiente.
          </p>
        </div>
      </div>

      <!-- ── KPIs ── -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Saldo principal -->
        <div class="lg:col-span-1 bg-white rounded-2xl border border-gray-100
                    shadow-sm p-5 flex flex-col gap-3">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">
              Saldo actual
            </span>
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full" :class="caja.estado === 'abierta'
              ? 'bg-green-50 border border-green-200'
              : 'bg-gray-100 border border-gray-200'">
              <div class="w-1.5 h-1.5 rounded-full" :class="caja.estado === 'abierta'
                ? 'bg-green-400 animate-pulse'
                : 'bg-gray-400'" />
              <span class="text-[10px] font-bold"
                :class="caja.estado === 'abierta' ? 'text-green-700' : 'text-gray-500'">
                {{ caja.estado === 'abierta' ? 'Abierta' : 'Cerrada' }}
              </span>
            </div>
          </div>
          <div class="flex items-baseline gap-1">
            <span class="text-[13px] font-semibold text-gray-400">S/</span>
            <span class="font-black text-[36px] text-gray-900 leading-none"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              {{ formatMonto(caja.saldo) }}
            </span>
          </div>
          <p class="text-[11.5px] text-gray-400 m-0">{{ fechaHoy }}</p>
          <p v-if="caja.saldo !== caja.saldo_efectivo" class="text-[11px] text-gray-400 m-0 -mt-2">
            S/ {{ formatMonto(caja.saldo_efectivo) }} en efectivo (lo que debería haber en el cajón)
          </p>
        </div>

        <!-- Ventas -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5
                    flex flex-col gap-3">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">
              Ventas
            </span>
            <div class="w-8 h-8 rounded-xl bg-green-50 flex items-center justify-center">
              <ShoppingBagIcon class="w-4 h-4 text-green-500" />
            </div>
          </div>
          <div class="flex items-baseline gap-1">
            <span class="text-[12px] font-semibold text-gray-400">S/</span>
            <span class="font-black text-[26px] text-green-600 leading-none"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              {{ formatMonto(caja.total_ventas) }}
            </span>
          </div>
          <p class="text-[11.5px] text-gray-400 m-0">
            {{ ventasCount }} pedidos entregados
          </p>
          <p v-if="caja.total_ventas_todas > caja.total_ventas" class="text-[11px] text-gray-400 m-0 mt-0.5">
            S/ {{ formatMonto(caja.total_ventas_todas) }} en total (todos los métodos)
          </p>
        </div>

        <!-- Ingresos -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5
                    flex flex-col gap-3">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">
              Ingresos extra
            </span>
            <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center">
              <ArrowTrendingUpIcon class="w-4 h-4 text-blue-500" />
            </div>
          </div>
          <div class="flex items-baseline gap-1">
            <span class="text-[12px] font-semibold text-gray-400">S/</span>
            <span class="font-black text-[26px] text-blue-600 leading-none"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              {{ formatMonto(caja.total_ingresos) }}
            </span>
          </div>
          <p class="text-[11.5px] text-gray-400 m-0">Ingresos manuales</p>
        </div>

        <!-- Gastos -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5
                    flex flex-col gap-3">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">
              Gastos
            </span>
            <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center">
              <ArrowTrendingDownIcon class="w-4 h-4 text-red-400" />
            </div>
          </div>
          <div class="flex items-baseline gap-1">
            <span class="text-[12px] font-semibold text-gray-400">S/</span>
            <span class="font-black text-[26px] text-red-500 leading-none"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              {{ formatMonto(caja.total_gastos) }}
            </span>
          </div>
          <p class="text-[11.5px] text-gray-400 m-0">
            Apertura: S/ {{ formatMonto(caja.monto_apertura) }}
          </p>
        </div>
      </div>

      <!-- ══ DEUDA AL SISTEMA ══ -->
      <div v-if="comisionesPendientes" class="bg-white rounded-2xl border border-purple-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center">
              <CpuChipIcon class="w-4 h-4 text-purple-500" />
            </div>
            <h3 class="font-black text-[15px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
              Debes al sistema
            </h3>
          </div>
          <span class="text-[11px] text-purple-600 font-bold px-2.5 py-1 rounded-full
                       bg-purple-50 border border-purple-200">
            Por comisiones
          </span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div v-for="item in deudaSistema" :key="item.label"
            class="text-center p-3 rounded-2xl bg-gray-50 border border-gray-100">
            <p class="text-[10.5px] font-black uppercase tracking-wider
                       text-gray-400 m-0 mb-1">
              {{ item.label }}
            </p>
            <div class="flex items-baseline justify-center gap-0.5">
              <span class="text-[11px] font-semibold text-purple-400">S/</span>
              <span class="font-black text-[20px] text-purple-600 leading-none"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                {{ formatMonto(item.value) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ DELIVERY ══ -->
      <div v-if="deliveryTotales" class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center">
              <TruckIcon class="w-4 h-4 text-blue-500" />
            </div>
            <h3 class="font-black text-[15px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
              Delivery
            </h3>
          </div>
          <span class="text-[11px] text-blue-600 font-bold px-2.5 py-1 rounded-full
                       bg-blue-50 border border-blue-200">
            Pedidos entregados
          </span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
          <div v-for="item in deliveryStats" :key="item.label"
            class="text-center p-3 rounded-2xl bg-gray-50 border border-gray-100">
            <p class="text-[10.5px] font-black uppercase tracking-wider
                       text-gray-400 m-0 mb-1">
              {{ item.label }}
            </p>
            <div class="flex items-baseline justify-center gap-0.5">
              <span class="text-[11px] font-semibold text-blue-400">S/</span>
              <span class="font-black text-[20px] text-blue-600 leading-none"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                {{ formatMonto(item.value) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Consultar un rango de fechas específico -->
        <div class="pt-4 border-t border-gray-100">
          <p class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-2">
            Ver otro rango de fechas
          </p>
          <div class="flex flex-wrap items-center gap-2">
            <input v-model="deliveryRango.desde" type="date" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-[13px] outline-none
                     focus:border-brand-red transition-all duration-200 text-gray-600" />
            <span class="text-gray-400 text-[12px]">al</span>
            <input v-model="deliveryRango.hasta" type="date" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-[13px] outline-none
                     focus:border-brand-red transition-all duration-200 text-gray-600" />
            <button @click="consultarRangoDelivery" :disabled="deliveryRango.loading"
              class="px-3.5 py-1.5 rounded-xl bg-blue-600 text-white text-[12.5px] font-bold
                     border-none cursor-pointer hover:bg-blue-700 disabled:opacity-50 transition-all duration-150">
              {{ deliveryRango.loading ? 'Consultando...' : 'Ver total' }}
            </button>
          </div>
          <p v-if="deliveryRango.error" class="text-[11.5px] text-red-500 mt-2 mb-0">{{ deliveryRango.error }}</p>
          <div v-if="deliveryRango.total !== null && !deliveryRango.error"
            class="flex items-center justify-between mt-3 px-3.5 py-2.5 rounded-xl bg-blue-50 border border-blue-200">
            <span class="text-[12px] text-blue-700 font-medium">
              {{ deliveryRango.pedidos }} pedido{{ deliveryRango.pedidos !== 1 ? 's' : '' }} con delivery
            </span>
            <span class="font-black text-[16px] text-blue-700" style="font-family:'Plus Jakarta Sans',sans-serif;">
              S/ {{ formatMonto(deliveryRango.total) }}
            </span>
          </div>
        </div>
      </div>

      <!-- ── Grid principal ── -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <!-- ── Registrar movimiento ── -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center">
              <PlusCircleIcon class="w-4 h-4 text-brand-red" />
            </div>
            <h3 class="font-black text-[15px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
              Registrar movimiento
            </h3>
          </div>

          <div class="p-5 flex flex-col gap-4">

            <!-- Tipo -->
            <div>
              <label class="block text-[11px] font-black uppercase
                            tracking-widest text-gray-500 mb-2">
                Tipo de movimiento
              </label>
              <div class="grid grid-cols-3 gap-2">
                <button v-for="t in TIPOS" :key="t.value" @click="mov.type = t.value" class="flex flex-col items-center gap-1.5 py-3 rounded-2xl
                         border-2 cursor-pointer text-[12px] font-bold
                         transition-all duration-150" :class="mov.type === t.value
                          ? t.activeClass
                          : 'border-gray-100 bg-gray-50 text-gray-500 hover:border-gray-200'">
                  <component :is="t.icon" class="w-4 h-4" />
                  {{ t.label }}
                </button>
              </div>
            </div>

            <!-- Monto -->
            <div class="flex flex-col gap-1.5">
              <label class="text-[11px] font-black uppercase tracking-widest text-gray-500">
                Monto (S/)
              </label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2
                             text-[14px] font-bold text-gray-400">S/</span>
                <input v-model.number="mov.amount" type="number" min="0.01" step="0.50" placeholder="0.00" class="w-full pl-10 pr-4 py-3 rounded-2xl border-2 text-[14px]
                         font-bold text-gray-900 outline-none bg-gray-50
                         placeholder:text-gray-300 placeholder:font-normal
                         border-gray-100 focus:border-brand-red focus:bg-white
                         focus:shadow-[0_0_0_3px_rgba(var(--color-brand-primary-rgb,196,30,30),0.08)]
                         transition-all duration-200" />
              </div>
            </div>

            <!-- Descripción -->
            <div class="flex flex-col gap-1.5">
              <label class="text-[11px] font-black uppercase tracking-widest text-gray-500">
                Descripción
              </label>
              <input v-model="mov.description" type="text" placeholder="Ej: Compra de insumos" maxlength="255" class="w-full px-4 py-3 rounded-2xl border-2 text-[13.5px]
                       text-gray-900 outline-none bg-gray-50
                       placeholder:text-gray-300
                       border-gray-100 focus:border-brand-red focus:bg-white
                       focus:shadow-[0_0_0_3px_rgba(var(--color-brand-primary-rgb,196,30,30),0.08)]
                       transition-all duration-200" />
            </div>

            <!-- Error -->
            <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
              leave-to-class="opacity-0">
              <div v-if="mov.error" class="flex items-center gap-2 px-4 py-3 rounded-2xl
                       bg-red-50 border border-red-200">
                <ExclamationCircleIcon class="w-4 h-4 text-red-500 shrink-0" />
                <p class="text-[12.5px] text-red-700 m-0">{{ mov.error }}</p>
              </div>
            </Transition>

            <!-- Éxito -->
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 scale-95"
              leave-to-class="opacity-0">
              <div v-if="mov.success" class="flex items-center gap-2 px-4 py-3 rounded-2xl
                       bg-green-50 border border-green-200">
                <CheckCircleIcon class="w-4 h-4 text-green-500 shrink-0" />
                <p class="text-[12.5px] text-green-700 m-0 font-semibold">
                  Movimiento registrado correctamente
                </p>
              </div>
            </Transition>

            <button @click="registrarMovimiento" :disabled="mov.loading || caja.estado === 'cerrada'" class="w-full py-3.5 rounded-2xl font-black text-[14px] text-white
                     bg-brand-red border-none cursor-pointer uppercase tracking-wide
                     shadow-[0_4px_16px_rgba(var(--color-brand-primary-rgb,196,30,30),0.25)]
                     hover:bg-red-700 hover:-translate-y-0.5 active:scale-[0.98]
                     disabled:opacity-40 disabled:cursor-not-allowed
                     disabled:hover:translate-y-0 transition-all duration-200
                     flex items-center justify-center gap-2" style="font-family:'Plus Jakarta Sans',sans-serif;">
              <span v-if="mov.loading" class="w-4 h-4 border-2 border-white/30 border-t-white
                       rounded-full animate-spin" />
              <PlusIcon v-else class="w-4 h-4" />
              {{ mov.loading ? 'Registrando...' : 'Registrar movimiento' }}
            </button>

            <!-- Cerrar caja -->
            <button v-if="caja.estado === 'abierta'" @click="showCerrar = true" class="w-full py-3 rounded-2xl font-semibold text-[13px]
                     text-gray-500 bg-transparent border-2 border-gray-200
                     cursor-pointer hover:bg-gray-50 hover:border-gray-300
                     transition-all duration-150
                     flex items-center justify-center gap-2">
              <LockClosedIcon class="w-4 h-4" />
              Cerrar caja del día
            </button>

            <!-- Reabrir caja -->
            <button v-if="caja.estado === 'cerrada'" @click="showReabrir = true" class="w-full py-3 rounded-2xl font-semibold text-[13px]
                     text-amber-700 bg-amber-50 border-2 border-amber-200
                     cursor-pointer hover:bg-amber-100 hover:border-amber-300
                     transition-all duration-150
                     flex items-center justify-center gap-2">
              <LockOpenIcon class="w-4 h-4" />
              Reabrir caja
            </button>
          </div>
        </div>

        <!-- ── Movimientos del día ── -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm
                    overflow-hidden flex flex-col">
          <div class="flex items-center justify-between px-5 py-4
                      border-b border-gray-100 shrink-0">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center">
                <ClipboardDocumentListIcon class="w-4 h-4 text-gray-500" />
              </div>
              <h3 class="font-black text-[15px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
                Movimientos del día
              </h3>
            </div>
            <span class="text-[12px] font-bold text-gray-400 bg-gray-100
                         px-2.5 py-1 rounded-full">
              {{ movimientos.length }}
            </span>
          </div>

          <!-- Lista -->
          <div class="flex-1 overflow-y-auto min-h-[300px] max-h-[420px]">

            <div v-if="movimientos.length === 0" class="flex flex-col items-center justify-center py-16
                     text-gray-400 gap-2">
              <ReceiptRefundIcon class="w-10 h-10 text-gray-200" />
              <p class="m-0 text-[13px]">Sin movimientos aún</p>
            </div>

            <TransitionGroup v-else tag="div" name="mov-item">
              <div v-for="m in movimientosOrdenados" :key="m.id" class="flex items-center gap-3.5 px-5 py-3.5
                       border-b border-gray-50 last:border-0
                       hover:bg-gray-50/60 transition-colors duration-100" :class="m.anulado ? 'opacity-50' : ''">

                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                  :class="tipoConfig(m.type).bgIcon">
                  <component :is="tipoConfig(m.type).icon" class="w-4 h-4" :class="tipoConfig(m.type).iconColor" />
                </div>

                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2">
                    <p class="font-semibold text-[13px] text-gray-900 m-0
                               leading-snug truncate" :class="m.anulado ? 'line-through' : ''">
                      {{ m.description }}
                    </p>
                    <span v-if="m.order_id" class="shrink-0 text-[9px] font-black px-1.5 py-0.5
                             rounded-full bg-purple-50 text-purple-600
                             border border-purple-200 leading-none">
                      AUTO
                    </span>
                    <span v-if="m.anulado" class="shrink-0 text-[9px] font-black px-1.5 py-0.5
                             rounded-full bg-gray-200 text-gray-500 leading-none">
                      ANULADO
                    </span>
                  </div>
                  <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-full" :class="tipoConfig(m.type).badge">
                      {{ tipoConfig(m.type).label }}
                    </span>
                    <span v-if="m.type === 'venta' && m.metodo_pago"
                      class="text-[10.5px] font-bold px-2 py-0.5 rounded-full"
                      :class="m.metodo_pago === 'efectivo' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'"
                      :title="m.metodo_pago === 'efectivo' ? 'Cuenta para el cuadre físico' : 'No cuenta para el cuadre — solo informativo'">
                      {{ metodoPagoLabel(m.metodo_pago) }}
                    </span>
                    <span class="text-[11px] text-gray-400 flex items-center gap-1">
                      <ClockIcon class="w-3 h-3" />
                      {{ m.created_at }}
                    </span>
                    <span v-if="m.delivery_fee" class="text-[11px] text-gray-400">
                      · Delivery S/ {{ formatMonto(m.delivery_fee) }}
                    </span>
                  </div>
                  <p v-if="m.anulado && m.motivo_anulacion" class="text-[11px] text-gray-400 m-0 mt-1 italic">
                    Motivo: {{ m.motivo_anulacion }}
                  </p>
                </div>

                <div class="flex flex-col items-end gap-1 shrink-0">
                  <p class="font-black text-[15px] leading-none m-0"
                    :class="m.anulado ? 'text-gray-400' : (m.type === 'gasto' ? 'text-red-500' : 'text-green-600')"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ m.type === 'gasto' ? '−' : '+' }}S/ {{ formatMonto(m.amount) }}
                  </p>
                  <button v-if="!m.anulado && caja.estado === 'abierta'" @click="askAnular(m)" class="text-[10.5px] font-semibold text-gray-400 bg-transparent border-none
                           cursor-pointer hover:text-red-500 transition-colors">
                    Anular
                  </button>
                </div>
              </div>
            </TransitionGroup>
          </div>

          <!-- Footer resumen -->
          <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50 shrink-0">
            <div class="grid grid-cols-3 gap-3 text-center">
              <div>
                <p class="text-[10px] font-bold uppercase tracking-wider
                           text-gray-400 m-0 mb-0.5">Ventas</p>
                <p class="font-black text-[14px] text-green-600 m-0"
                  style="font-family:'Plus Jakarta Sans',sans-serif;">
                  S/ {{ formatMonto(caja.total_ventas_todas) }}
                </p>
              </div>
              <div>
                <p class="text-[10px] font-bold uppercase tracking-wider
                           text-gray-400 m-0 mb-0.5">Ingresos</p>
                <p class="font-black text-[14px] text-blue-600 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
                  S/ {{ formatMonto(caja.total_ingresos) }}
                </p>
              </div>
              <div>
                <p class="text-[10px] font-bold uppercase tracking-wider
                           text-gray-400 m-0 mb-0.5">Gastos</p>
                <p class="font-black text-[14px] text-red-500 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
                  S/ {{ formatMonto(caja.total_gastos) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ══ MODAL CERRAR CAJA ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="showCerrar" class="fixed inset-0 z-[300] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="showCerrar = false">
          <Transition enter-active-class="transition-all duration-250 ease-out" enter-from-class="opacity-0 scale-95"
            leave-to-class="opacity-0 scale-95">
            <div v-if="showCerrar && caja" class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">

              <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center
                          justify-center mx-auto mb-5">
                <LockClosedIcon class="w-8 h-8 text-gray-500" />
              </div>

              <h3 class="font-black text-[20px] text-gray-900 m-0 mb-2"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                ¿Cerrar la caja?
              </h3>
              <p class="text-gray-400 text-[13.5px] m-0 mb-5 leading-relaxed">
                Cuenta el efectivo físico y compáralo contra lo que el sistema espera.
              </p>

              <div class="bg-gray-50 rounded-2xl p-4 mb-4 text-left
                          flex flex-col gap-2.5 border border-gray-100">
                <div class="flex justify-between items-center text-[13px]">
                  <span class="text-gray-500 flex items-center gap-1.5">
                    <BanknotesIcon class="w-3.5 h-3.5" />
                    Apertura
                  </span>
                  <span class="font-semibold text-gray-700">
                    S/ {{ formatMonto(caja.monto_apertura) }}
                  </span>
                </div>
                <div class="flex justify-between items-center text-[13px]">
                  <span class="text-gray-500 flex items-center gap-1.5">
                    <ShoppingBagIcon class="w-3.5 h-3.5" />
                    Ventas en efectivo
                  </span>
                  <span class="font-bold text-green-600">
                    +S/ {{ formatMonto(caja.total_ventas) }}
                  </span>
                </div>
                <div class="flex justify-between items-center text-[13px]">
                  <span class="text-gray-500 flex items-center gap-1.5">
                    <ArrowTrendingUpIcon class="w-3.5 h-3.5" />
                    Ingresos
                  </span>
                  <span class="font-bold text-blue-600">
                    +S/ {{ formatMonto(caja.total_ingresos) }}
                  </span>
                </div>
                <div class="flex justify-between items-center text-[13px]">
                  <span class="text-gray-500 flex items-center gap-1.5">
                    <ArrowTrendingDownIcon class="w-3.5 h-3.5" />
                    Gastos
                  </span>
                  <span class="font-bold text-red-500">
                    −S/ {{ formatMonto(caja.total_gastos) }}
                  </span>
                </div>
                <div class="flex justify-between items-center pt-2.5
                            border-t border-gray-200 text-[14px]">
                  <span class="font-bold text-gray-900">Esperado en caja</span>
                  <span class="font-black text-gray-900" style="font-family:'Plus Jakarta Sans',sans-serif;">
                    S/ {{ formatMonto(caja.saldo_efectivo) }}
                  </span>
                </div>
              </div>

              <!-- Conteo físico -->
              <div class="flex flex-col gap-1.5 mb-4 text-left">
                <label class="text-[11px] font-black uppercase tracking-widest text-gray-500">
                  ¿Cuánto contaste de verdad? (S/)
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-1/2 -translate-y-1/2
                               text-[14px] font-bold text-gray-400">S/</span>
                  <input v-model.number="cerrar.montoContado" type="number" min="0" step="0.50" placeholder="0.00"
                    class="w-full pl-10 pr-4 py-3 rounded-2xl border-2 text-[15px]
                           font-bold text-gray-900 outline-none bg-gray-50
                           placeholder:text-gray-300 placeholder:font-normal
                           border-gray-100 focus:border-brand-red focus:bg-white
                           transition-all duration-200" />
                </div>
              </div>

              <!-- Diferencia en vivo -->
              <div v-if="cerrar.montoContado !== null"
                class="mb-4 px-4 py-3 rounded-2xl text-left flex items-center justify-between" :class="diferenciaCalculada === 0
                  ? 'bg-green-50 border border-green-200'
                  : 'bg-amber-50 border border-amber-200'">
                <span class="text-[12.5px] font-semibold"
                  :class="diferenciaCalculada === 0 ? 'text-green-700' : 'text-amber-700'">
                  {{ diferenciaCalculada === 0 ? 'Cuadra perfecto' : (diferenciaCalculada > 0 ? 'Sobrante' : 'Faltante')
                  }}
                </span>
                <span class="font-black text-[15px]"
                  :class="diferenciaCalculada === 0 ? 'text-green-700' : 'text-amber-700'">
                  {{ diferenciaCalculada === 0 ? '✓' : (diferenciaCalculada > 0 ? '+' : '') }}
                  {{ diferenciaCalculada !== 0 ? `S/ ${formatMonto(Math.abs(diferenciaCalculada))}` : '' }}
                </span>
              </div>

              <!-- Motivo, solo si hay diferencia -->
              <div v-if="diferenciaCalculada !== 0" class="flex flex-col gap-1.5 mb-4 text-left">
                <label class="text-[11px] font-black uppercase tracking-widest text-gray-500">
                  Motivo de la diferencia
                </label>
                <input v-model="cerrar.motivoDiferencia" type="text"
                  placeholder="Ej: Vuelto mal dado, no se cobró un delivery..." maxlength="255" class="w-full px-4 py-3 rounded-2xl border-2 text-[13.5px]
                         text-gray-900 outline-none bg-gray-50
                         placeholder:text-gray-300
                         border-gray-100 focus:border-brand-red focus:bg-white
                         transition-all duration-200" />
              </div>

              <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
                leave-to-class="opacity-0">
                <div v-if="cerrar.error" class="flex items-center gap-2 px-4 py-3 rounded-2xl
                         bg-red-50 border border-red-200 mb-4 text-left">
                  <ExclamationCircleIcon class="w-4 h-4 text-red-500 shrink-0" />
                  <p class="text-[12.5px] text-red-700 m-0">{{ cerrar.error }}</p>
                </div>
              </Transition>

              <div class="flex gap-3">
                <button @click="showCerrar = false" class="flex-1 py-3 rounded-2xl border-2 border-gray-200
                         text-gray-600 font-semibold text-[13.5px]
                         cursor-pointer bg-white hover:border-gray-300
                         transition-all duration-150">
                  Cancelar
                </button>
                <button @click="cerrarCaja" :disabled="cerrar.loading || cerrar.montoContado === null" class="flex-1 py-3 rounded-2xl bg-gray-900 text-white
                         font-bold text-[13.5px] cursor-pointer border-none
                         hover:bg-gray-800 transition-all duration-150
                         disabled:opacity-50
                         flex items-center justify-center gap-2">
                  <span v-if="cerrar.loading" class="w-4 h-4 border-2 border-white/30 border-t-white
                           rounded-full animate-spin" />
                  {{ cerrar.loading ? 'Cerrando...' : 'Confirmar cierre' }}
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ MODAL REABRIR CAJA ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="showReabrir" class="fixed inset-0 z-[300] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="showReabrir = false">
          <Transition enter-active-class="transition-all duration-250 ease-out" enter-from-class="opacity-0 scale-95"
            leave-to-class="opacity-0 scale-95">
            <div v-if="showReabrir" class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">

              <div class="w-16 h-16 rounded-2xl bg-amber-50 flex items-center
                          justify-center mx-auto mb-5">
                <LockOpenIcon class="w-8 h-8 text-amber-500" />
              </div>

              <h3 class="font-black text-[20px] text-gray-900 m-0 mb-2"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                ¿Reabrir la caja?
              </h3>
              <p class="text-gray-400 text-[13.5px] m-0 mb-5 leading-relaxed">
                Queda registrado que esta caja se reabrió, y por qué.
              </p>

              <div class="flex flex-col gap-1.5 mb-4 text-left">
                <label class="text-[11px] font-black uppercase tracking-widest text-gray-500">
                  Motivo de la reapertura
                </label>
                <input v-model="reabrir.motivo" type="text" placeholder="Ej: Faltó registrar un pedido" maxlength="255"
                  class="w-full px-4 py-3 rounded-2xl border-2 text-[13.5px]
                         text-gray-900 outline-none bg-gray-50
                         placeholder:text-gray-300
                         border-gray-100 focus:border-brand-red focus:bg-white
                         transition-all duration-200" />
              </div>

              <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
                leave-to-class="opacity-0">
                <div v-if="reabrir.error" class="flex items-center gap-2 px-4 py-3 rounded-2xl
                         bg-red-50 border border-red-200 mb-4 text-left">
                  <ExclamationCircleIcon class="w-4 h-4 text-red-500 shrink-0" />
                  <p class="text-[12.5px] text-red-700 m-0">{{ reabrir.error }}</p>
                </div>
              </Transition>

              <div class="flex gap-3">
                <button @click="showReabrir = false" class="flex-1 py-3 rounded-2xl border-2 border-gray-200
                         text-gray-600 font-semibold text-[13.5px]
                         cursor-pointer bg-white hover:border-gray-300
                         transition-all duration-150">
                  Cancelar
                </button>
                <button @click="reabrirCaja" :disabled="reabrir.loading" class="flex-1 py-3 rounded-2xl bg-amber-600 text-white
                         font-bold text-[13.5px] cursor-pointer border-none
                         hover:bg-amber-700 transition-all duration-150
                         disabled:opacity-50
                         flex items-center justify-center gap-2">
                  <span v-if="reabrir.loading" class="w-4 h-4 border-2 border-white/30 border-t-white
                           rounded-full animate-spin" />
                  {{ reabrir.loading ? 'Reabriendo...' : 'Reabrir caja' }}
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ MODAL ANULAR MOVIMIENTO ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="anular.target" class="fixed inset-0 z-[300] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="anular.target = null">
          <Transition enter-active-class="transition-all duration-250 ease-out" enter-from-class="opacity-0 scale-95"
            leave-to-class="opacity-0 scale-95">
            <div v-if="anular.target" class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">

              <div class="w-16 h-16 rounded-2xl bg-red-50 flex items-center
                          justify-center mx-auto mb-5">
                <ExclamationTriangleIcon class="w-8 h-8 text-red-400" />
              </div>

              <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                ¿Anular este movimiento?
              </h3>
              <p class="text-gray-400 text-[13px] m-0 mb-5 leading-relaxed">
                "{{ anular.target?.description }}" — S/ {{ anular.target ? formatMonto(anular.target.amount) : '' }}.
                Queda visible tachado, con el motivo, nunca se borra.
              </p>

              <div class="flex flex-col gap-1.5 mb-4 text-left">
                <label class="text-[11px] font-black uppercase tracking-widest text-gray-500">
                  Motivo
                </label>
                <input v-model="anular.motivo" type="text" placeholder="Ej: Se registró por error" maxlength="255"
                  class="w-full px-4 py-3 rounded-2xl border-2 text-[13.5px]
                         text-gray-900 outline-none bg-gray-50
                         placeholder:text-gray-300
                         border-gray-100 focus:border-brand-red focus:bg-white
                         transition-all duration-200" />
              </div>

              <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
                leave-to-class="opacity-0">
                <div v-if="anular.error" class="flex items-center gap-2 px-4 py-3 rounded-2xl
                         bg-red-50 border border-red-200 mb-4 text-left">
                  <ExclamationCircleIcon class="w-4 h-4 text-red-500 shrink-0" />
                  <p class="text-[12.5px] text-red-700 m-0">{{ anular.error }}</p>
                </div>
              </Transition>

              <div class="flex gap-3">
                <button @click="anular.target = null" class="flex-1 py-3 rounded-2xl border-2 border-gray-200
                         text-gray-600 font-semibold text-[13.5px]
                         cursor-pointer bg-white hover:border-gray-300
                         transition-all duration-150">
                  Cancelar
                </button>
                <button @click="confirmarAnular" :disabled="anular.loading" class="flex-1 py-3 rounded-2xl bg-red-600 text-white
                         font-bold text-[13.5px] cursor-pointer border-none
                         hover:bg-red-700 transition-all duration-150
                         disabled:opacity-50
                         flex items-center justify-center gap-2">
                  <span v-if="anular.loading" class="w-4 h-4 border-2 border-white/30 border-t-white
                           rounded-full animate-spin" />
                  {{ anular.loading ? 'Anulando...' : 'Sí, anular' }}
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive, onMounted, onUnmounted } from 'vue'
import api from '@/utils/api'
import {
  BanknotesIcon, LockOpenIcon, LockClosedIcon,
  PlusIcon, PlusCircleIcon, CheckCircleIcon,
  ExclamationCircleIcon, ExclamationTriangleIcon, ClipboardDocumentListIcon,
  ClockIcon, ReceiptRefundIcon,
  ArrowTrendingUpIcon, ArrowTrendingDownIcon,
  ShoppingBagIcon, WrenchScrewdriverIcon,
  CpuChipIcon, TruckIcon,
} from '@heroicons/vue/24/outline'

// ── Tipos ─────────────────────────────────────────────────
interface CajaData {
  id: number
  fecha: string
  estado: 'abierta' | 'cerrada'
  monto_apertura: number
  monto_cierre: number | null
  monto_contado: number | null
  diferencia: number | null
  motivo_diferencia: string | null
  motivo_reapertura: string | null
  total_ventas: number
  total_ventas_todas: number
  total_gastos: number
  total_ingresos: number
  saldo: number
  saldo_efectivo: number
  abierta_por: string | null
  cerrada_por: string | null
}

interface Movimiento {
  id: number
  type: 'venta' | 'gasto' | 'ingreso'
  amount: number
  description: string
  order_id: number | null
  order_type: 'local' | 'recoger' | 'delivery' | null
  delivery_fee: number | null
  metodo_pago: 'efectivo' | 'yape' | 'tarjeta' | 'anticipado' | null
  created_at: string
  anulado: boolean
  motivo_anulacion: string | null
}

type EstadoCaja = 'sin_abrir' | 'abierta' | 'cerrada'

// ── Estado ────────────────────────────────────────────────
const estado = ref<EstadoCaja>('sin_abrir')
const caja = ref<CajaData | null>(null)
const movimientos = ref<Movimiento[]>([])
const showCerrar = ref(false)
const esDiaAnterior = ref(false)
const loadingPage = ref(true)  // ← true desde el inicio — evita el flash

const comisionesPendientes = ref<any>(null)

const deudaSistema = computed(() => [
  { label: 'Hoy', value: comisionesPendientes.value?.hoy ?? 0 },
  { label: 'Semana', value: comisionesPendientes.value?.semana ?? 0 },
  { label: 'Mes', value: comisionesPendientes.value?.mes ?? 0 },
  { label: 'Total', value: comisionesPendientes.value?.total ?? 0 },
])

// ── Delivery ────────────────────────────────────────────
const deliveryTotales = ref<any>(null)

const deliveryStats = computed(() => [
  { label: 'Hoy', value: deliveryTotales.value?.hoy ?? 0 },
  { label: 'Semana', value: deliveryTotales.value?.semana ?? 0 },
  { label: 'Mes', value: deliveryTotales.value?.mes ?? 0 },
  { label: 'Total', value: deliveryTotales.value?.total ?? 0 },
])

const deliveryRango = reactive({
  desde: '',
  hasta: '',
  total: null as number | null,
  pedidos: null as number | null,
  loading: false,
  error: '',
})

let refreshTimer: ReturnType<typeof setInterval> | null = null

// ── Forms ─────────────────────────────────────────────────
const apertura = reactive({ monto: 200, loading: false, error: '' })

const mov = reactive({
  type: 'venta' as 'venta' | 'gasto' | 'ingreso',
  amount: 0,
  description: '',
  loading: false,
  error: '',
  success: false,
})

const cerrar = reactive({
  loading: false,
  error: '',
  montoContado: null as number | null,
  motivoDiferencia: '',
})

const showReabrir = ref(false)
const reabrir = reactive({ loading: false, error: '', motivo: '' })

const anular = reactive({
  target: null as Movimiento | null,
  motivo: '',
  loading: false,
  error: '',
})

// ── Constantes ────────────────────────────────────────────
const TIPOS = [
  {
    value: 'venta' as const,
    label: 'Venta',
    icon: ShoppingBagIcon,
    activeClass: 'border-green-400 bg-green-50 text-green-700',
  },
  {
    value: 'ingreso' as const,
    label: 'Ingreso',
    icon: ArrowTrendingUpIcon,
    activeClass: 'border-blue-400 bg-blue-50 text-blue-700',
  },
  {
    value: 'gasto' as const,
    label: 'Gasto',
    icon: ArrowTrendingDownIcon,
    activeClass: 'border-red-400 bg-red-50 text-red-700',
  },
]

// ── Computed ──────────────────────────────────────────────
const fechaHoy = computed(() =>
  new Date().toLocaleDateString('es-PE', {
    weekday: 'long', day: 'numeric',
    month: 'long', year: 'numeric',
  })
)

const ventasCount = computed(() =>
  movimientos.value.filter(m => m.type === 'venta').length
)

const movimientosOrdenados = computed(() =>
  [...movimientos.value].reverse()
)

const diferenciaCalculada = computed(() => {
  if (cerrar.montoContado === null || !caja.value) return 0
  return Math.round((cerrar.montoContado - caja.value.saldo) * 100) / 100
})

// ── Helpers ───────────────────────────────────────────────
function formatMonto(n: number): string {
  return Number(n).toLocaleString('es-PE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

function formatFecha(f: string): string {
  return new Date(f + 'T00:00:00').toLocaleDateString('es-PE', {
    weekday: 'long', day: 'numeric', month: 'long',
  })
}

function tipoConfig(type: string) {
  const map: Record<string, any> = {
    venta: {
      label: 'Venta',
      icon: ShoppingBagIcon,
      bgIcon: 'bg-green-50',
      iconColor: 'text-green-500',
      badge: 'bg-green-50 text-green-700 border border-green-200',
    },
    ingreso: {
      label: 'Ingreso',
      icon: ArrowTrendingUpIcon,
      bgIcon: 'bg-blue-50',
      iconColor: 'text-blue-500',
      badge: 'bg-blue-50 text-blue-700 border border-blue-200',
    },
    gasto: {
      label: 'Gasto',
      icon: WrenchScrewdriverIcon,
      bgIcon: 'bg-red-50',
      iconColor: 'text-red-400',
      badge: 'bg-red-50 text-red-600 border border-red-200',
    },
  }
  return map[type] ?? map.venta
}

function metodoPagoLabel(metodo: 'efectivo' | 'yape' | 'tarjeta' | 'anticipado'): string {
  const labels: Record<'efectivo' | 'yape' | 'tarjeta' | 'anticipado', string> = {
    efectivo: 'Efectivo',
    yape: 'Yape',
    tarjeta: 'Tarjeta',
    anticipado: 'Anticipado',
  }
  return labels[metodo] ?? metodo
}

// ── API ───────────────────────────────────────────────────
async function loadCaja() {
  // No resetear loadingPage en refreshes automáticos (solo en carga inicial)
  try {
    const { data } = await api.get('/admin/caja/hoy')
    const res = data.data

    if (!res.caja) {
      estado.value = 'sin_abrir'
      caja.value = null
      movimientos.value = []
      esDiaAnterior.value = false
    } else {
      caja.value = res.caja
      movimientos.value = res.movimientos ?? []
      estado.value = res.caja.estado
      esDiaAnterior.value = res.es_dia_anterior ?? false
    }
  } catch (e) {
    console.error('Error cargando caja:', e)
  } finally {
    loadingPage.value = false  // ← solo aquí se quita el skeleton
  }

  // Comisiones pendientes — silencioso
  try {
    const { data } = await api.get('/admin/sistema/comisiones-pendientes')
    comisionesPendientes.value = data.data
  } catch {
    // Sin permiso — ignorar
  }

  // Totales de delivery — silencioso
  try {
    const { data } = await api.get('/admin/caja/delivery-total')
    deliveryTotales.value = data.data
  } catch {
    // Sin permiso — ignorar
  }
}

async function consultarRangoDelivery() {
  if (!deliveryRango.desde && !deliveryRango.hasta) return
  deliveryRango.loading = true
  deliveryRango.error = ''
  try {
    const { data } = await api.get('/admin/caja/delivery-total', {
      params: { desde: deliveryRango.desde || undefined, hasta: deliveryRango.hasta || undefined },
    })
    deliveryRango.total = data.data.rango?.total ?? 0
    deliveryRango.pedidos = data.data.rango?.pedidos ?? 0
  } catch (e: any) {
    deliveryRango.error = e.response?.data?.message ?? 'Error al consultar el rango'
  } finally {
    deliveryRango.loading = false
  }
}

async function abrirCaja() {
  if (apertura.monto <= 0) {
    apertura.error = 'El monto de apertura debe ser mayor a 0'
    return
  }
  apertura.loading = true
  apertura.error = ''
  try {
    await api.post('/admin/caja/abrir', { monto_apertura: apertura.monto })
    await loadCaja()
  } catch (e: any) {
    apertura.error = e.response?.data?.message ?? 'Error al abrir la caja'
  } finally {
    apertura.loading = false
  }
}

async function registrarMovimiento() {
  mov.error = ''
  mov.success = false

  if (!mov.amount || mov.amount <= 0) {
    mov.error = 'El monto debe ser mayor a 0'
    return
  }
  if (!mov.description.trim()) {
    mov.error = 'La descripción es requerida'
    return
  }

  mov.loading = true
  try {
    await api.post('/admin/caja/movimiento', {
      type: mov.type,
      amount: mov.amount,
      description: mov.description.trim(),
    })
    mov.amount = 0
    mov.description = ''
    mov.success = true
    await loadCaja()
    setTimeout(() => { mov.success = false }, 2_500)
  } catch (e: any) {
    mov.error = e.response?.data?.message ?? 'Error al registrar el movimiento'
  } finally {
    mov.loading = false
  }
}

async function cerrarCaja() {
  if (cerrar.montoContado === null) {
    cerrar.error = 'Indica cuánto contaste en efectivo'
    return
  }
  cerrar.loading = true
  cerrar.error = ''
  try {
    await api.post('/admin/caja/cerrar', {
      monto_contado: cerrar.montoContado,
      motivo_diferencia: cerrar.motivoDiferencia.trim() || undefined,
    })
    showCerrar.value = false
    cerrar.montoContado = null
    cerrar.motivoDiferencia = ''
    await loadCaja()
  } catch (e: any) {
    cerrar.error = e.response?.data?.message ?? 'Error al cerrar la caja'
  } finally {
    cerrar.loading = false
  }
}

async function reabrirCaja() {
  if (!reabrir.motivo.trim()) {
    reabrir.error = 'Indica el motivo de la reapertura'
    return
  }
  reabrir.loading = true
  reabrir.error = ''
  try {
    await api.post('/admin/caja/abrir', {
      monto_apertura: caja.value?.monto_apertura ?? 0,
      motivo_reapertura: reabrir.motivo.trim(),
    })
    showReabrir.value = false
    reabrir.motivo = ''
    await loadCaja()
  } catch (e: any) {
    reabrir.error = e.response?.data?.message ?? 'Error al reabrir la caja'
  } finally {
    reabrir.loading = false
  }
}

function askAnular(m: Movimiento) {
  anular.target = m
  anular.motivo = ''
  anular.error = ''
}

async function confirmarAnular() {
  if (!anular.target) return
  if (!anular.motivo.trim()) {
    anular.error = 'Indica el motivo de la anulación'
    return
  }
  anular.loading = true
  anular.error = ''
  try {
    await api.post(`/admin/caja/movimiento/${anular.target.id}/anular`, {
      motivo: anular.motivo.trim(),
    })
    anular.target = null
    await loadCaja()
  } catch (e: any) {
    anular.error = e.response?.data?.message ?? 'Error al anular el movimiento'
  } finally {
    anular.loading = false
  }
}

// ── Lifecycle ─────────────────────────────────────────────
onMounted(async () => {
  await loadCaja()
  refreshTimer = setInterval(loadCaja, 30_000)
})

onUnmounted(() => {
  if (refreshTimer) clearInterval(refreshTimer)
})
</script>

<style scoped>
.mov-item-enter-active {
  transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.mov-item-leave-active {
  transition: all 0.15s ease;
}

.mov-item-enter-from {
  opacity: 0;
  transform: translateX(-12px) scale(0.97);
}

.mov-item-leave-to {
  opacity: 0;
  transform: translateX(8px);
}
</style>