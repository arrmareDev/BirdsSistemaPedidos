// Mapa Leaflet + GPS + detección de zona de delivery.
//
// Extraído porque PedidosView.vue (modal "Nuevo Pedido") y
// CheckoutView.vue (checkout público) tenían esta misma lógica
// duplicada casi al carácter — mismos nombres de variable, mismo flujo,
// solo cambiaba el id del elemento del mapa y el tamaño del ícono.
//
// El composable necesita escribir lat/lng/address/delivery_zone_id, así
// que recibe el `form` reactivo de quien lo use en vez de tener su
// propio estado de formulario.

import { ref, computed } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'
import api from '@/utils/api'

// Fix del ícono default de Leaflet roto por los bundlers (Vite/webpack) —
// antes esto estaba duplicado en cada vista que usaba el mapa.
delete (L.Icon.Default.prototype as any)._getIconUrl
L.Icon.Default.mergeOptions({ iconUrl: markerIcon, iconRetinaUrl: markerIcon2x, shadowUrl: markerShadow })

export interface DeliveryZone { id: number; nombre: string; precio: number }

interface DeliveryFormLike {
  lat: number | null
  lng: number | null
  address: string
  delivery_zone_id: number
}

export interface UseDeliveryMapOptions {
  // id del elemento del DOM donde Leaflet monta el mapa — distinto en
  // cada vista ('admin-delivery-map' vs 'delivery-map').
  mapElementId: string
  // tamaño en px del pin — PedidosView usa 28, CheckoutView usa 32.
  iconSize?: number
  // Solo PedidosView lo necesita: el mapa vive dentro de un modal que
  // recién se hace visible, así que Leaflet necesita recalcular el
  // tamaño del contenedor una vez que ya tiene dimensiones reales. Si no
  // se pasa, no se llama invalidateSize().
  invalidateSizeDelayMs?: number
}

const CHICLAYO_LAT = -6.7741
const CHICLAYO_LNG = -79.8409

export function useDeliveryMap(form: DeliveryFormLike, options: UseDeliveryMapOptions) {
  const iconSize = options.iconSize ?? 28

  let map: L.Map | null = null
  let marker: L.Marker | null = null
  let searchTimer: ReturnType<typeof setTimeout> | null = null

  const zones = ref<DeliveryZone[]>([])
  const loadingZones = ref(false)
  const detectedZone = ref<DeliveryZone | null>(null)
  const detectingZone = ref(false)
  const zoneNotFound = ref(false)

  const loadingGPS = ref(false)
  const gpsError = ref('')

  const mapSearch = ref('')
  const mapResults = ref<any[]>([])
  const mapSearching = ref(false)

  const selectedZone = computed<DeliveryZone | null>(() => {
    if (detectedZone.value) return detectedZone.value
    return zones.value.find(z => z.id === form.delivery_zone_id) ?? null
  })

  async function fetchZones() {
    loadingZones.value = true
    try {
      const { data } = await api.get('/delivery-zones')
      zones.value = data.data
    } catch { }
    finally { loadingZones.value = false }
  }

  // Deliberadamente no hace nada — el <select> ya está en v-model con
  // form.delivery_zone_id, y selectedZone reacciona solo. Se mantiene el
  // handler solo por paridad con el @change del template original.
  function onManualZoneChange() { }

  async function detectarZona(lat: number, lng: number) {
    detectingZone.value = true
    zoneNotFound.value = false
    detectedZone.value = null
    form.delivery_zone_id = 0

    try {
      const { data } = await api.get('/delivery-zones/detectar', { params: { lat, lng } })
      detectedZone.value = data.data
      form.delivery_zone_id = data.data.id
    } catch {
      zoneNotFound.value = true
      await fetchZones()
    } finally {
      detectingZone.value = false
    }
  }

  function usarGPS() {
    gpsError.value = ''
    if (!navigator.geolocation) {
      gpsError.value = 'Tu navegador no soporta geolocalización'
      return
    }
    loadingGPS.value = true
    navigator.geolocation.getCurrentPosition(
      async (position) => {
        const lat = position.coords.latitude
        const lng = position.coords.longitude
        if (map && marker) { map.setView([lat, lng], 17); marker.setLatLng([lat, lng]) }
        form.lat = lat
        form.lng = lng
        await Promise.all([reverseGeocode(lat, lng), detectarZona(lat, lng)])
        loadingGPS.value = false
      },
      (error) => {
        loadingGPS.value = false
        const messages: Record<number, string> = {
          1: 'Permiso de ubicación denegado.',
          2: 'No se pudo obtener tu ubicación. Márcala en el mapa.',
          3: 'Tiempo de espera agotado. Intenta de nuevo.',
        }
        gpsError.value = messages[error.code] ?? 'Error al obtener ubicación.'
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    )
  }

  function initMap() {
    if (map) return
    const el = document.getElementById(options.mapElementId)
    if (!el) return

    map = L.map(options.mapElementId, { center: [CHICLAYO_LAT, CHICLAYO_LNG], zoom: 14 })
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap', maxZoom: 19,
    }).addTo(map)

    const redIcon = L.divIcon({
      className: '',
      html: `<div style="width:${iconSize}px;height:${iconSize}px;background:var(--color-brand-primary,#C41E1E);border:3px solid white;
        border-radius:50% 50% 50% 0;transform:rotate(-45deg);
        box-shadow:0 2px 8px rgba(var(--color-brand-primary-rgb,196,30,30),0.4);"></div>`,
      iconSize: [iconSize, iconSize], iconAnchor: [iconSize / 2, iconSize],
    })

    marker = L.marker([CHICLAYO_LAT, CHICLAYO_LNG], { draggable: true, icon: redIcon }).addTo(map)
    marker.on('dragend', () => {
      const pos = marker!.getLatLng()
      form.lat = pos.lat; form.lng = pos.lng
      reverseGeocode(pos.lat, pos.lng); detectarZona(pos.lat, pos.lng)
    })
    map.on('click', (e: L.LeafletMouseEvent) => {
      marker!.setLatLng(e.latlng)
      form.lat = e.latlng.lat; form.lng = e.latlng.lng
      reverseGeocode(e.latlng.lat, e.latlng.lng); detectarZona(e.latlng.lat, e.latlng.lng)
    })
    form.lat = CHICLAYO_LAT; form.lng = CHICLAYO_LNG

    if (options.invalidateSizeDelayMs) {
      const delay = options.invalidateSizeDelayMs
      setTimeout(() => map?.invalidateSize(), delay)
    }
  }

  function destroyMap() {
    if (map) { map.remove(); map = null }
  }

  async function reverseGeocode(lat: number, lng: number) {
    try {
      const res = await fetch(
        `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`,
        { headers: { 'Accept-Language': 'es' } }
      )
      const data = await res.json()
      if (data.display_name) {
        form.address = data.display_name.split(',').slice(0, 3).join(',').trim()
      }
    } catch { }
  }

  function debouncedMapSearch() {
    clearTimeout(searchTimer!)
    if (mapSearch.value.length < 3) { mapResults.value = []; return }
    searchTimer = setTimeout(searchAddress, 500)
  }

  async function searchAddress() {
    mapSearching.value = true
    try {
      const query = encodeURIComponent(`${mapSearch.value}, Chiclayo, Peru`)
      const res = await fetch(
        `https://nominatim.openstreetmap.org/search?q=${query}&format=json&limit=5`,
        { headers: { 'Accept-Language': 'es' } }
      )
      mapResults.value = await res.json()
    } catch { mapResults.value = [] }
    finally { mapSearching.value = false }
  }

  function selectMapResult(result: any) {
    const lat = parseFloat(result.lat)
    const lng = parseFloat(result.lon)
    if (map && marker) { map.setView([lat, lng], 17); marker.setLatLng([lat, lng]) }
    form.lat = lat; form.lng = lng
    form.address = result.display_name.split(',').slice(0, 3).join(',').trim()
    mapResults.value = []; mapSearch.value = ''
    detectarZona(lat, lng)
  }

  // Para el watch de form.type cuando deja de ser 'delivery'.
  function resetZoneAndGps() {
    detectedZone.value = null
    zoneNotFound.value = false
    form.delivery_zone_id = 0
    gpsError.value = ''
  }

  // Para cuando se abre/cierra el modal (o se reinicia el formulario).
  function resetMapSearch() {
    mapSearch.value = ''
    mapResults.value = []
  }

  return {
    // estado
    zones, loadingZones, detectedZone, detectingZone, zoneNotFound,
    loadingGPS, gpsError, mapSearch, mapResults, mapSearching,
    selectedZone,
    // acciones
    fetchZones, onManualZoneChange, detectarZona, usarGPS,
    initMap, destroyMap, selectMapResult, debouncedMapSearch,
    resetZoneAndGps, resetMapSearch,
  }
}
