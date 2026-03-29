<script setup>
import { ref, computed, onMounted } from 'vue'
import { Plus, ArrowDownToLine } from 'lucide-vue-next'
import DataTable from '../components/ui/DataTable.vue'
import Modal from '../components/ui/Modal.vue'
import Button from '../components/ui/Button.vue'
import Input from '../components/ui/Input.vue'
import Select from '../components/ui/Select.vue'
import { getEntries, createEntry } from '../Services/entryProductService'
import { getProducts } from '../Services/productService'
import { getSuppliers } from '../Services/supplierService'
import { getUsers } from '../Services/userService'
import { mockData } from '../composables/useApi'
import { getSafeErrorMessage } from '../lib/safeErrorMessage'
import { useAuthStore } from '../stores/auth'
import { useUiStore } from '../stores/ui'

const entries = ref([])
const products = ref([])
const suppliers = ref([])
const users = ref([])

const loading = ref(false)
const error = ref(null)
const showModal = ref(false)
const authStore = useAuthStore()
const uiStore = useUiStore()

const formData = ref({
  productId: '',
  supplierId: '',
  quantity: '',
  userId: ''
})

const formErrors = ref({})

const columns = [
  { key: 'date', label: 'Data', sortable: true },
  { key: 'productName', label: 'Produto', sortable: true },
  { key: 'supplierName', label: 'Razão de Entrada', sortable: true },
  { key: 'quantity', label: 'Quantidade', sortable: true },
  { key: 'userName', label: 'Registrado por', sortable: true }
]

const productOptions = computed(() => 
  products.value.map(p => ({ value: p.id, label: `${p.code} - ${p.name}` }))
)

const supplierOptions = computed(() => 
  suppliers.value.map(s => ({ value: s.id, label: s.name }))
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
    supplierId: '',
    quantity: '',
    userId: defaultUserId
  }
  formErrors.value = {}
  showModal.value = true
}

const validateForm = () => {
  const errors = {}
  if (!formData.value.productId) errors.productId = 'Selecione um produto'
  if (!formData.value.supplierId) errors.supplierId = 'Selecione um fornecedor'
  if (!formData.value.quantity || formData.value.quantity <= 0) errors.quantity = 'Quantidade deve ser maior que zero'
  if (!formData.value.userId) errors.userId = 'Selecione o usuário'
  
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

const normalizeEntry = (entry) => {
  const product = entry.product || entry.productData || {}
  const supplier = entry.supplier || entry.supplierData || {}
  const rawUser = entry.user || entry.userData || {}
  const user = typeof rawUser === 'object' && rawUser !== null ? rawUser : {}

  const supplierId = entry.supplierId ?? entry.supplier_id ?? entry.fornecedor_id ?? supplier.id
  const userId =
    extractUserIdFromRecord(entry) ??
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
    entry.reason,
    'Não informado'
  )

  const userName =
    cleanUserName(entry.userName) ??
    cleanUserName(entry.user_name) ??
    cleanUserName(entry.nome_usuario) ??
    cleanUserName(entry.registeredByName) ??
    cleanUserName(entry.registered_by_name) ??
    cleanUserName(entry.registrado_por) ??
    cleanUserName(typeof rawUser === 'string' ? rawUser : null) ??
    cleanUserName(user.name) ??
    cleanUserName(user.nome) ??
    cleanUserName(findName(users.value, userId)) ??
    (userId != null ? `Usuario #${userId}` : 'Sistema')

  return {
    id: entry.id,
    productId: entry.productId ?? entry.product_id ?? product.id,
    productName: entry.productName ?? entry.name ?? product.name ?? product.productName ?? '',
    supplierId,
    supplierName,
    quantity: entry.quantity,
    userId,
    userName,
    date:
      entry.date ||
      entry.data_de_saida ||
      entry.data_de_entrada ||
      entry.created_at ||
      entry.createdAt ||
      entry.updatedAt ||
      ''
  }
}

const loadEntries = async () => {
  try {
    entries.value = ensureArray(await getEntries()).map(normalizeEntry)
  } catch (err) {
    console.error('Erro ao carregar entradas:', err)
    const safeMessage = getSafeErrorMessage(err, 'Nao foi possivel carregar entradas agora.')
    error.value = `${safeMessage} Mostrando dados de exemplo.`
    entries.value = mockData.entries
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

const loadSuppliers = async () => {
  try {
    suppliers.value = ensureArray(await getSuppliers())
  } catch (err) {
    console.error('Erro ao carregar fornecedores:', err)
    const safeMessage = getSafeErrorMessage(err, 'Nao foi possivel carregar fornecedores agora.')
    error.value = `${safeMessage} Mostrando dados de exemplo.`
    suppliers.value = mockData.suppliers
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
    // User lookup must be ready before mapping entries to resolve registrar names.
    await loadUsers()
    await Promise.all([loadEntries(), loadProducts(), loadSuppliers()])
  } finally {
    loading.value = false
  }
}

const saveEntry = async () => {
  if (!formData.value.userId) {
    formData.value.userId = resolveLoggedUserId() || (userOptions.value[0] ? String(userOptions.value[0].value) : '')
  }

  if (!validateForm()) return

  const selectedProduct = products.value.find(
    (product) => String(product.id) === String(formData.value.productId)
  )

  const payload = {
    productId: Number.parseInt(formData.value.productId, 10),
    supplierId: Number.parseInt(formData.value.supplierId, 10),
    quantity: Number.parseInt(formData.value.quantity, 10),
    userId: Number.parseInt(formData.value.userId, 10),
    name: selectedProduct?.name ?? '',
    userName: userOptions.value.find((u) => String(u.value) === String(formData.value.userId))?.label ?? ''
  }

  try {
    error.value = null
    await createEntry(payload)
    await loadData()
    showModal.value = false
  } catch (err) {
    console.error('Erro ao salvar entrada:', err)
    error.value = getSafeErrorMessage(err, 'Nao foi possivel registrar a entrada.')
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
        <p class="text-sm font-medium text-foreground">Entrada de estoque</p>
        <p class="text-xs text-muted-foreground">Registre novas entradas de produtos no estoque</p>
      </div>
      <Button @click="openModal">
        <Plus class="h-4 w-4" />
        Nova Entrada
      </Button>
    </div>

    <div v-if="error" class="rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
      {{ error }}
    </div>

    <!-- Summary Card -->
    <div class="rounded-xl border border-border bg-card p-6">
      <div class="flex items-center gap-4">
        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-success/10">
          <ArrowDownToLine class="h-6 w-6 text-success" />
        </div>
        <div>
          <p class="text-sm text-muted-foreground">Total de entradas registradas</p>
          <p class="text-2xl font-bold text-card-foreground">{{ entries.length }}</p>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div v-if="loading" class="flex items-center justify-center py-8">
      <div class="text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
        <p class="mt-2 text-sm text-muted-foreground">Carregando entradas...</p>
      </div>
    </div>

    <DataTable v-else :columns="columns" :data="entries" :externalSearchQuery="uiStore.globalSearch">
      <template #cell-date="{ item }">
        <span class="text-muted-foreground">{{ formatDate(item.date) }}</span>
      </template>
      <template #cell-productName="{ item }">
        <span class="font-medium">{{ item.productName }}</span>
      </template>
      <template #cell-quantity="{ item }">
        <span class="font-semibold text-success">+{{ item.quantity }} un.</span>
      </template>
    </DataTable>

    <!-- Entry Modal -->
    <Modal :show="showModal" title="Nova Entrada de Estoque" @close="showModal = false">
      <form @submit.prevent="saveEntry" class="space-y-4">
        <Select
          v-model="formData.productId"
          label="Produto"
          :options="productOptions"
          placeholder="Selecione um produto"
          :error="formErrors.productId"
          required
        />
        <Select
          v-model="formData.supplierId"
          label="Fornecedor"
          :options="supplierOptions"
          placeholder="Selecione um fornecedor"
          :error="formErrors.supplierId"
          required
        />
        <Input
          v-model="formData.quantity"
          label="Quantidade"
          type="number"
          min="1"
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
            Registrar Entrada
          </Button>
        </div>
      </form>
    </Modal>
  </div>
</template>
