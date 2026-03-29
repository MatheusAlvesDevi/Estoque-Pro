<script setup>
import { ref, computed, onMounted } from 'vue'
import { Plus, ArrowUpFromLine, AlertCircle } from 'lucide-vue-next'
import DataTable from '../components/ui/DataTable.vue'
import Modal from '../components/ui/Modal.vue'
import Button from '../components/ui/Button.vue'
import Input from '../components/ui/Input.vue'
import Select from '../components/ui/Select.vue'
import { getExits, createExit } from '../Services/exitProductService'
import { getProducts } from '../Services/productService'
import { getUsers } from '../Services/userService'
import { mockData } from '../composables/useApi'
import { getSafeErrorMessage } from '../lib/safeErrorMessage'
import { useAuthStore } from '../stores/auth'
import { useUiStore } from '../stores/ui'

const exits = ref([])
const products = ref([])
const users = ref([])

const loading = ref(false)
const error = ref(null)
const showModal = ref(false)
const insufficientStock = ref(false)
const authStore = useAuthStore()
const uiStore = useUiStore()

const formData = ref({
  productId: '',
  quantity: '',
  userId: ''
})

const formErrors = ref({})

const columns = [
  { key: 'date', label: 'Data', sortable: true },
  { key: 'productName', label: 'Produto', sortable: true },
  { key: 'supplierName', label: 'Razão de Saída', sortable: true },
  { key: 'quantity', label: 'Quantidade', sortable: true },
  { key: 'userName', label: 'Registrado por', sortable: true }
]

const productOptions = computed(() => 
  products.value.map(p => ({ 
    value: p.id, 
    label: `${p.code} - ${p.name} (Estoque: ${p.quantity})` 
  }))
)

const isActiveUser = (user = {}) => {
  if (user.status == null) return true
  const status = String(user.status).toLowerCase()
  return status === 'ativo' || status === 'active' || status === '1' || status === 'true'
}

const userOptions = computed(() =>
  users.value
    .filter(isActiveUser)
    .map((u) => ({ value: u.id, label: u.name ?? u.nome ?? u.username ?? `Usuario #${u.id}` }))
)

const selectedProduct = computed(() => {
  if (!formData.value.productId) return null
  return products.value.find(p => p.id === parseInt(formData.value.productId))
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR')
}

const resolveLoggedUserId = () => {
  if (authStore.user?.id != null) return String(authStore.user.id)

  const authEmail = String(authStore.user?.email || '').toLowerCase()
  if (authEmail) {
    const byEmail = users.value.find((u) => String(u.email || '').toLowerCase() === authEmail)
    if (byEmail?.id != null) return String(byEmail.id)
  }

  const authName = String(authStore.user?.name || '').toLowerCase()
  if (authName) {
    const byName = users.value.find((u) => String(u.name || u.nome || '').toLowerCase() === authName)
    if (byName?.id != null) return String(byName.id)
  }

  return ''
}

const openModal = () => {
  const loggedUserId = resolveLoggedUserId()
  const defaultUserId = loggedUserId || (userOptions.value[0] ? String(userOptions.value[0].value) : '')

  formData.value = {
    productId: '',
    quantity: '',
    userId: defaultUserId
  }
  formErrors.value = {}
  insufficientStock.value = false
  showModal.value = true
}

const validateForm = () => {
  const errors = {}
  insufficientStock.value = false

  if (!formData.value.productId) {
    errors.productId = 'Selecione um produto'
  }
  
  if (!formData.value.quantity || formData.value.quantity <= 0) {
    errors.quantity = 'Quantidade deve ser maior que zero'
  } else if (selectedProduct.value && parseInt(formData.value.quantity) > selectedProduct.value.quantity) {
    errors.quantity = 'Quantidade maior que o estoque disponível'
    insufficientStock.value = true
  }
  if (!formData.value.userId) {
    errors.userId = 'Selecione o usuário'
  }
  
  formErrors.value = errors
  return Object.keys(errors).length === 0
}

const unwrapData = (value) => value?.data ?? value

const ensureArray = (value) => {
  const unwrapped = unwrapData(value)
  if (Array.isArray(unwrapped)) return unwrapped
  if (unwrapped == null) return []
  return [unwrapped]
}

const normalizeUser = (user = {}) => ({
  ...user,
  id: user.id ?? user.user_id ?? user.userId ?? user.id_user ?? user.usuario_id ?? user.users_id ?? null,
  name: user.name ?? user.nome ?? user.username ?? user.user_name ?? 'Usuario'
})

const extractUserIdFromRecord = (record = {}) => {
  const direct =
    record.userId ??
    record.user_id ??
    record.userid ??
    record.id_user ??
    record.users_id ??
    record.registered_by ??
    record.registeredBy ??
    record.usuario_id ??
    null

  if (direct !== null && direct !== undefined && direct !== '') {
    return direct
  }

  const dynamicKey = Object.keys(record).find((key) =>
    /(registered|registrado|user|usuario).*(id)|id.*(user|usuario)/i.test(key)
  )

  return dynamicKey ? record[dynamicKey] : null
}

const normalizeExit = (exit) => {
  const product = exit.product || exit.productData || {}
  const rawUser = exit.user || exit.userData || {}
  const user = typeof rawUser === 'object' && rawUser !== null ? rawUser : {}

  const userId =
    extractUserIdFromRecord(exit) ??
    (typeof rawUser !== 'object' ? rawUser : null) ??
    user.id

  const findName = (list, id) => {
    const match = list.find((item) => String(item.id) === String(id))
    return match?.name ?? match?.nome ?? match?.username ?? ''
  }

  const getFirst = (...values) => values.find((v) => v !== undefined && v !== null && v !== '')
  const cleanUserName = (value) => {
    const text = String(value ?? '').trim()
    if (!text) return null
    if (/^n[aã]o informado$/i.test(text)) return null
    return text
  }

  const supplierName = getFirst(
    exit.reason,
    'Não informado'
  )

  const userName =
    cleanUserName(exit.userName) ??
    cleanUserName(exit.user_name) ??
    cleanUserName(exit.nome_usuario) ??
    cleanUserName(exit.registeredByName) ??
    cleanUserName(exit.registered_by_name) ??
    cleanUserName(exit.registrado_por) ??
    cleanUserName(typeof rawUser === 'string' ? rawUser : null) ??
    cleanUserName(user.name) ??
    cleanUserName(user.nome) ??
    cleanUserName(findName(users.value, userId)) ??
    (userId != null ? `Usuario #${userId}` : 'Sistema')

  return {
    id: exit.id,
    productId: exit.productId ?? exit.product_id ?? product.id,
    productName: exit.productName ?? exit.name ?? product.name ?? product.productName ?? '',
    supplierName,
    quantity: exit.quantity,
    userId,
    userName,
    date:
      exit.date ||
      exit.data_de_saida ||
      exit.data_de_entrada ||
      exit.created_at ||
      exit.createdAt ||
      exit.updatedAt ||
      ''
  }
}

const loadExits = async () => {
  try {
    exits.value = ensureArray(await getExits()).map(normalizeExit)
  } catch (err) {
    console.error('Erro ao carregar saídas:', err)
    const safeMessage = getSafeErrorMessage(err, 'Nao foi possivel carregar saidas agora.')
    error.value = `${safeMessage} Mostrando dados de exemplo.`
    exits.value = mockData.exits
  }
}

const loadProducts = async () => {
  try {
    products.value = ensureArray(await getProducts())
  } catch (err) {
    console.error('Erro ao carregar produtos:', err)
    const safeMessage = getSafeErrorMessage(err, 'Nao foi possivel carregar produtos agora.')
    error.value = `${safeMessage} Mostrando dados de exemplo.`
    products.value = mockData.products
  }
}

const loadUsers = async () => {
  try {
    users.value = ensureArray(await getUsers()).map(normalizeUser)
  } catch (err) {
    console.error('Erro ao carregar usuários:', err)
    const safeMessage = getSafeErrorMessage(err, 'Nao foi possivel carregar usuarios agora.')
    error.value = `${safeMessage} Mostrando dados de exemplo.`
    users.value = mockData.users.map(normalizeUser)
  }
}

const loadData = async () => {
  loading.value = true
  try {
    // Carrega usuarios primeiro para resolver "Registrado por" durante a normalizacao das saidas.
    await loadUsers()
    await Promise.all([loadExits(), loadProducts()])
  } finally {
    loading.value = false
  }
}

const saveExit = async () => {
  if (!formData.value.userId) {
    formData.value.userId = resolveLoggedUserId() || (userOptions.value[0] ? String(userOptions.value[0].value) : '')
  }

  if (!validateForm()) return

  const selectedProductName = selectedProduct.value?.name ?? ''

  const payload = {
    productId: Number.parseInt(formData.value.productId, 10),
    quantity: Number.parseInt(formData.value.quantity, 10),
    userId: Number.parseInt(formData.value.userId, 10),
    name: selectedProductName,
    userName: userOptions.value.find((u) => String(u.value) === String(formData.value.userId))?.label ?? ''
  }

  try {
    error.value = null
    await createExit(payload)
    await loadData()
    showModal.value = false
  } catch (err) {
    console.error('Erro ao salvar saída:', err)
    error.value = getSafeErrorMessage(err, 'Nao foi possivel registrar a saida.')
  }
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between rounded-xl border border-border bg-card/70 px-4 py-3 backdrop-blur-sm">
      <div>
        <p class="text-sm font-medium text-foreground">Saida de estoque</p>
        <p class="text-xs text-muted-foreground">Registre saidas de produtos do estoque</p>
      </div>
      <Button @click="openModal">
        <Plus class="h-4 w-4" />
        Nova Saída
      </Button>
    </div>

    <div v-if="error" class="rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
      {{ error }}
    </div>

    <!-- Summary Card -->
    <div class="rounded-xl border border-border bg-card p-6">
      <div class="flex items-center gap-4">
        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-warning/10">
          <ArrowUpFromLine class="h-6 w-6 text-warning" />
        </div>
        <div>
          <p class="text-sm text-muted-foreground">Total de saídas registradas</p>
          <p class="text-2xl font-bold text-card-foreground">{{ exits.length }}</p>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div v-if="loading" class="flex items-center justify-center py-8">
      <div class="text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
        <p class="mt-2 text-sm text-muted-foreground">Carregando saídas...</p>
      </div>
    </div>

    <DataTable v-else :columns="columns" :data="exits" :externalSearchQuery="uiStore.globalSearch">
      <template #cell-date="{ item }">
        <span class="text-muted-foreground">{{ formatDate(item.date) }}</span>
      </template>
      <template #cell-productName="{ item }">
        <span class="font-medium">{{ item.productName }}</span>
      </template>
      <template #cell-quantity="{ item }">
        <span class="font-semibold text-warning">-{{ item.quantity }} un.</span>
      </template>
    </DataTable>

    <!-- Exit Modal -->
    <Modal :show="showModal" title="Nova Saída de Estoque" @close="showModal = false">
      <form @submit.prevent="saveExit" class="space-y-4">
        <Select
          v-model="formData.productId"
          label="Produto"
          :options="productOptions"
          placeholder="Selecione um produto"
          :error="formErrors.productId"
          required
        />

        <!-- Stock Info -->
        <div v-if="selectedProduct" class="rounded-lg border border-border bg-muted/50 p-3">
          <p class="text-sm text-muted-foreground">
            Estoque disponível: 
            <span class="font-semibold text-foreground">{{ selectedProduct.quantity }} unidades</span>
          </p>
        </div>

        <!-- Insufficient Stock Warning -->
        <div v-if="insufficientStock" class="flex items-start gap-3 rounded-lg border border-destructive/30 bg-destructive/10 p-3">
          <AlertCircle class="h-5 w-5 shrink-0 text-destructive" />
          <div>
            <p class="font-medium text-destructive">Estoque Insuficiente</p>
            <p class="text-sm text-destructive/80">
              A quantidade solicitada é maior que o estoque disponível.
            </p>
          </div>
        </div>

        <Input
          v-model="formData.quantity"
          label="Quantidade"
          type="number"
          min="1"
          :max="selectedProduct?.quantity"
          placeholder="0"
          :error="formErrors.quantity"
          required
        />
        <Select
          v-model="formData.userId"
          label="Registrado por"
          :options="userOptions"
          :error="formErrors.userId"
          required
        />
        <div class="flex justify-end gap-2 pt-4">
          <Button variant="outline" type="button" @click="showModal = false">
            Cancelar
          </Button>
          <Button type="submit">
            Registrar Saída
          </Button>
        </div>
      </form>
    </Modal>
  </div>
</template>
