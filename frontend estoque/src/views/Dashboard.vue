<script setup>
import { ref, computed, onMounted } from 'vue'
import { Package, AlertTriangle, ArrowDownToLine, ArrowUpFromLine, ChevronRight, TrendingUp, TrendingDown } from 'lucide-vue-next'
import { getProducts } from '../Services/productService'
import { getEntries } from '../Services/entryProductService'
import { getExits } from '../Services/exitProductService'
import { getSuppliers } from '../Services/supplierService'
import { getUsers } from '../Services/userService'
import { mockData } from '../composables/useApi'
import { getSafeErrorMessage } from '../lib/safeErrorMessage'

const products = ref([])
const entries = ref([])
const exits = ref([])
const loading = ref(false)
const error = ref(null)
const suppliers = ref([])
const users = ref([])

const toNumber = (value) => {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : 0
}

const totalProducts = computed(() => products.value.length)
const totalStock = computed(() => products.value.reduce((sum, p) => sum + toNumber(p?.quantity), 0))
const lowStockProducts = computed(() =>
  products.value.filter((p) => p && toNumber(p.quantity) <= toNumber(p.minimumstock))
)
const totalEntries = computed(() => entries.value.reduce((sum, e) => sum + toNumber(e?.quantity), 0))
const totalExits = computed(() => exits.value.reduce((sum, e) => sum + toNumber(e?.quantity), 0))

const monthRange = computed(() => {
  const now = new Date()
  const currentStart = new Date(now.getFullYear(), now.getMonth(), 1)
  const currentEnd = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59, 999)
  const previousStart = new Date(now.getFullYear(), now.getMonth() - 1, 1)
  const previousEnd = new Date(now.getFullYear(), now.getMonth(), 0, 23, 59, 59, 999)

  return { currentStart, currentEnd, previousStart, previousEnd }
})

const parseDateSafe = (value) => {
  if (!value) return null
  const parsed = new Date(value)
  return Number.isNaN(parsed.getTime()) ? null : parsed
}

const isInRange = (value, start, end) => {
  const date = parseDateSafe(value)
  if (!date) return false
  return date >= start && date <= end
}

const getProductDate = (product = {}) => {
  return product.created_at || product.createdAt || product.date || product.data_cadastro || null
}

const sumActivityByRange = (list, start, end) => {
  return (Array.isArray(list) ? list : []).reduce((sum, item) => {
    if (!isInRange(item?.date, start, end)) return sum
    return sum + toNumber(item?.quantity)
  }, 0)
}

const calculateChange = (current, previous) => {
  const currentValue = toNumber(current)
  const previousValue = toNumber(previous)

  if (previousValue <= 0) {
    if (currentValue <= 0) {
      return { change: '0%', trend: 'up' }
    }
    return { change: '+100%', trend: 'up' }
  }

  const percent = ((currentValue - previousValue) / previousValue) * 100
  const rounded = Math.round(Math.abs(percent))
  const signal = percent >= 0 ? '+' : '-'

  return {
    change: `${signal}${rounded}%`,
    trend: percent >= 0 ? 'up' : 'down'
  }
}

const currentMonthEntries = computed(() =>
  sumActivityByRange(entries.value, monthRange.value.currentStart, monthRange.value.currentEnd)
)

const previousMonthEntries = computed(() =>
  sumActivityByRange(entries.value, monthRange.value.previousStart, monthRange.value.previousEnd)
)

const currentMonthExits = computed(() =>
  sumActivityByRange(exits.value, monthRange.value.currentStart, monthRange.value.currentEnd)
)

const previousMonthExits = computed(() =>
  sumActivityByRange(exits.value, monthRange.value.previousStart, monthRange.value.previousEnd)
)

const productsAddedCurrentMonth = computed(() =>
  products.value.filter((product) =>
    isInRange(getProductDate(product), monthRange.value.currentStart, monthRange.value.currentEnd)
  ).length
)

const previousTotalProductsEstimate = computed(() =>
  Math.max(totalProducts.value - productsAddedCurrentMonth.value, 0)
)

const previousTotalStockEstimate = computed(() =>
  totalStock.value - currentMonthEntries.value + currentMonthExits.value
)

const productsChange = computed(() =>
  calculateChange(totalProducts.value, previousTotalProductsEstimate.value)
)

const stockChange = computed(() =>
  calculateChange(totalStock.value, previousTotalStockEstimate.value)
)

const entriesChange = computed(() =>
  calculateChange(currentMonthEntries.value, previousMonthEntries.value)
)

const exitsChange = computed(() =>
  calculateChange(currentMonthExits.value, previousMonthExits.value)
)

const stats = computed(() => [
  {
    title: 'Total de Produtos',
    value: totalProducts.value,
    icon: Package,
    color: 'bg-primary',
    change: productsChange.value.change,
    trend: productsChange.value.trend
  },
  {
    title: 'Estoque Total',
    value: totalStock.value,
    icon: Package,
    color: 'bg-success',
    change: stockChange.value.change,
    trend: stockChange.value.trend
  },
  {
    title: 'Entradas (Mês)',
    value: currentMonthEntries.value,
    icon: ArrowDownToLine,
    color: 'bg-primary',
    change: entriesChange.value.change,
    trend: entriesChange.value.trend
  },
  {
    title: 'Saídas (Mês)',
    value: currentMonthExits.value,
    icon: ArrowUpFromLine,
    color: 'bg-warning',
    change: exitsChange.value.change,
    trend: exitsChange.value.trend
  }
])

const recentEntries = computed(() => entries.value.filter(Boolean).slice(0, 5))
const recentExits = computed(() => exits.value.filter(Boolean).slice(0, 5))

const formatDate = (date) => {
  if (!date) return '-'
  const parsed = new Date(date)
  if (Number.isNaN(parsed.getTime())) return '-'
  return parsed.toLocaleDateString('pt-BR')
}

const ensureArray = (value) => {
  if (Array.isArray(value)) return value
  if (Array.isArray(value?.data)) return value.data
  if (Array.isArray(value?.data?.data)) return value.data.data
  if (Array.isArray(value?.items)) return value.items
  if (Array.isArray(value?.result)) return value.result
  return []
}

const normalizeUser = (user = {}) => ({
  ...user,
  id: user.id,
  name: user.name ?? user.nome ?? user.username ?? ''
})

const normalizeProduct = (product = {}) => ({
  ...product,
  id: product.id,
  name: product.name ?? product.nome ?? '-',
  quantity: toNumber(product.quantity),
  minimumstock: toNumber(product.minimumstock ?? product.minStock ?? product.min_stock)
})

const normalizeActivity = (item = {}, usersList = [], productsList = []) => {
  const userId = item.userId ?? item.user_id ?? item.registered_by ?? item.usuario_id ?? item.user?.id
  const productId = item.productId ?? item.product_id ?? item.produto_id ?? item.product?.id

  const matchedUser = usersList.find((u) => String(u.id) === String(userId))
  const matchedProduct = productsList.find((p) => String(p.id) === String(productId))
  const cleanUserName = (value) => {
    const text = String(value ?? '').trim()
    if (!text) return null
    if (/^n[aã]o informado$/i.test(text)) return null
    return text
  }

  return {
    id: item.id,
    name: item.name ?? item.productName ?? item.product?.name ?? matchedProduct?.name ?? '-',
    quantity: toNumber(item.quantity),
    userName:
      cleanUserName(item.userName) ??
      cleanUserName(item.user_name) ??
      cleanUserName(item.nome_usuario) ??
      cleanUserName(item.registeredByName) ??
      cleanUserName(item.registered_by_name) ??
      cleanUserName(item.registrado_por) ??
      cleanUserName(item.user?.name) ??
      cleanUserName(item.user?.username) ??
      cleanUserName(matchedUser?.name) ??
      (userId != null ? `Usuario #${userId}` : 'Sistema'),
    date: item.date ?? item.data_de_entrada ?? item.data_de_saida ?? item.created_at ?? item.createdAt ?? ''
  }
}

const mapSafe = (list, mapper) => {
  return (Array.isArray(list) ? list : []).map((item) => {
    try {
      return mapper(item)
    } catch (err) {
      console.error('Erro ao normalizar item do dashboard:', err, item)
      return {
        id: item?.id ?? Math.random().toString(36).slice(2),
        name: item?.name ?? item?.productName ?? '-',
        quantity: toNumber(item?.quantity),
        userName: 'Sistema',
        date: item?.date ?? item?.created_at ?? ''
      }
    }
  })
}

const applyDashboardData = ({ productsData = [], entriesData = [], exitsData = [], usersData = [], suppliersData = [] }) => {
  users.value = ensureArray(usersData).filter(Boolean).map(normalizeUser)
  products.value = ensureArray(productsData).filter(Boolean).map(normalizeProduct)
  entries.value = mapSafe(
    ensureArray(entriesData).filter(Boolean),
    (entry) => normalizeActivity(entry, users.value, products.value)
  )
  exits.value = mapSafe(
    ensureArray(exitsData).filter(Boolean),
    (exit) => normalizeActivity(exit, users.value, products.value)
  )
  suppliers.value = ensureArray(suppliersData).filter(Boolean)
}

const applyMockFallback = () => {
  applyDashboardData({
    productsData: mockData.products || [],
    entriesData: mockData.entries || [],
    exitsData: mockData.exits || [],
    usersData: mockData.users || [],
    suppliersData: mockData.suppliers || []
  })
}

const loadData = async () => {
  loading.value = true
  error.value = null

  const errors = []

  const collectError = (label, err) => {
    const message = getSafeErrorMessage(err, 'Nao foi possivel carregar este recurso agora.')
    errors.push(`${label}: ${message}`)
  }

  try {
    const [productsResp, entriesResp, exitsResp, suppliersResp, usersResp] = await Promise.all([
      getProducts().catch((err) => {
        collectError('Produtos', err)
        return mockData.products || []
      }),
      getEntries().catch((err) => {
        collectError('Entradas', err)
        return mockData.entries || []
      }),
      getExits().catch((err) => {
        collectError('Saídas', err)
        return mockData.exits || []
      }),
      getSuppliers().catch((err) => {
        collectError('Fornecedores', err)
        return mockData.suppliers || []
      }),
      getUsers().catch((err) => {
        collectError('Usuários', err)
        return mockData.users || []
      })
    ])

    applyDashboardData({
      productsData: productsResp,
      entriesData: entriesResp,
      exitsData: exitsResp,
      usersData: usersResp,
      suppliersData: suppliersResp
    })

    if (errors.length) {
      error.value = `Alguns dados não puderam ser carregados da API (${errors.join(' | ')}). Exibindo fallback quando necessário.`
    }
  } catch (err) {
    console.error('Erro inesperado ao carregar dashboard:', err)
    error.value = 'Falha inesperada ao carregar dashboard.'
    applyMockFallback()
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  // Render immediate fallback data to avoid a blank dashboard during initial API latency.
  applyMockFallback()
  await loadData()
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between rounded-xl border border-border bg-card/70 px-4 py-3 backdrop-blur-sm">
      <div>
        <p class="text-sm font-medium text-foreground">Dashboard</p>
        <p class="text-xs text-muted-foreground">Acompanhe indicadores, alertas e movimentacoes recentes</p>
      </div>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary mx-auto"></div>
        <p class="mt-3 text-sm text-muted-foreground">Carregando dados...</p>
      </div>
    </div>

    <div v-if="error" class="rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
      {{ error }}
    </div>

    <!-- Stats Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div
        v-for="stat in stats"
        :key="stat.title"
        class="rounded-xl border border-border bg-card p-6 transition-shadow hover:shadow-md"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-muted-foreground">{{ stat.title }}</p>
            <p class="mt-2 text-3xl font-bold text-card-foreground">{{ stat.value }}</p>
            <div class="mt-2 flex items-center gap-1">
              <TrendingUp v-if="stat.trend === 'up'" class="h-4 w-4 text-success" />
              <TrendingDown v-else class="h-4 w-4 text-destructive" />
              <span class="text-sm font-medium" :class="stat.trend === 'up' ? 'text-success' : 'text-destructive'">
                {{ stat.change }}
              </span>
              <span class="text-sm text-muted-foreground">vs mês anterior</span>
            </div>
          </div>
          <div class="flex h-12 w-12 items-center justify-center rounded-lg" :class="stat.color">
            <component :is="stat.icon" class="h-6 w-6 text-white" />
          </div>
        </div>
      </div>
    </div>

    <!-- Low Stock Alert -->
    <div v-if="lowStockProducts.length > 0" class="rounded-xl border border-warning/30 bg-warning/10 p-4">
      <div class="flex items-start gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-warning">
          <AlertTriangle class="h-5 w-5 text-white" />
        </div>
        <div class="flex-1">
          <h3 class="font-semibold text-foreground">Alerta de Estoque Baixo</h3>
          <p class="mt-1 text-sm text-muted-foreground">
            {{ lowStockProducts.length }} produto(s) estão com estoque abaixo do mínimo
          </p>
          <div class="mt-3 flex flex-wrap gap-2">
            <router-link
              v-for="product in lowStockProducts.slice(0, 5)"
              :key="product.id"
              to="/products"
              class="inline-flex items-center gap-1 rounded-lg bg-card px-3 py-1.5 text-sm font-medium text-foreground transition-colors hover:bg-muted"
            >
              {{ product.name }}
              <span class="text-destructive">({{ product.quantity }})</span>
            </router-link>
            <router-link
              v-if="lowStockProducts.length > 5"
              to="/products"
              class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
            >
              Ver todos
              <ChevronRight class="h-4 w-4" />
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid gap-6 lg:grid-cols-2">
      <div class="rounded-xl border border-border bg-card">
        <div class="flex items-center justify-between border-b border-border p-4">
          <div class="flex items-center gap-2">
            <ArrowDownToLine class="h-5 w-5 text-success" />
            <h3 class="font-semibold text-card-foreground">Entradas Recentes</h3>
          </div>
          <router-link to="/stock-entry" class="flex items-center gap-1 text-sm font-medium text-primary hover:underline">
            Ver todos
            <ChevronRight class="h-4 w-4" />
          </router-link>
        </div>
        <div class="divide-y divide-border">
          <div v-for="item in recentEntries" :key="item.id" class="flex items-center justify-between p-4 transition-colors hover:bg-muted/50">
            <div>
              <p class="font-medium text-card-foreground">{{ item.name }}</p>
              <p class="text-sm text-muted-foreground">Registrado por {{ item.userName || 'Sistema' }}</p>
            </div>
            <div class="text-right">
              <p class="font-semibold text-success">+{{ item.quantity ?? 0 }} un.</p>
              <p class="text-sm text-muted-foreground">{{ formatDate(item.date) }}</p>
            </div>
          </div>
          <div v-if="recentEntries.length === 0" class="p-8 text-center text-muted-foreground">
            Nenhuma entrada encontrada
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-border bg-card">
        <div class="flex items-center justify-between border-b border-border p-4">
          <div class="flex items-center gap-2">
            <ArrowUpFromLine class="h-5 w-5 text-warning" />
            <h3 class="font-semibold text-card-foreground">Saídas Recentes</h3>
          </div>
          <router-link to="/stock-exit" class="flex items-center gap-1 text-sm font-medium text-primary hover:underline">
            Ver todos
            <ChevronRight class="h-4 w-4" />
          </router-link>
        </div>
        <div class="divide-y divide-border">
          <div v-for="item in recentExits" :key="item.id" class="flex items-center justify-between p-4 transition-colors hover:bg-muted/50">
            <div>
              <p class="font-medium text-card-foreground">{{ item.name }}</p>
              <p class="text-sm text-muted-foreground">Registrado por {{ item.userName || 'Sistema' }}</p>
            </div>
            <div class="text-right">
              <p class="font-semibold text-warning">-{{ item.quantity ?? 0 }} un.</p>
              <p class="text-sm text-muted-foreground">{{ formatDate(item.date) }}</p>
            </div>
          </div>
          <div v-if="recentExits.length === 0" class="p-8 text-center text-muted-foreground">
            Nenhuma saída encontrada
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
