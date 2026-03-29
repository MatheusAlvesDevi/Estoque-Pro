<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Plus, Pencil, Trash2, AlertTriangle } from 'lucide-vue-next'
import DataTable from '../components/ui/DataTable.vue'
import Modal from '../components/ui/Modal.vue'
import Button from '../components/ui/Button.vue'
import Input from '../components/ui/Input.vue'
import Select from '../components/ui/Select.vue'
import { getProducts, createProduct, updateProduct, deleteProduct } from '../Services/productService'
import { getSuppliers } from '../Services/supplierService'
import { mockData } from '../composables/useApi'
import { getSafeErrorMessage } from '../lib/safeErrorMessage'
import { useUiStore } from '../stores/ui'

const products = ref([])
const suppliers = ref([])
const loading = ref(false)
const error = ref(null)
const showModal = ref(false)
const isEditing = ref(false)
const productToDelete = ref(null)
const showDeleteModal = ref(false)
const showSuccessPopup = ref(false)
const successMessage = ref('')
let successPopupTimer = null
const uiStore = useUiStore()

const formData = ref({
  id: null,
  name: '',
  code: '',
  price: '',
  quantity: '',
  minimumstock: '',
  supplier_id: ''
})

const formErrors = ref({})

const columns = [
  { key: 'code', label: 'Código', sortable: true },
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'price', label: 'Preço', sortable: true },
  { key: 'quantity', label: 'Quantidade', sortable: true },
  { key: 'minimumstock', label: 'Estoque Mínimo', sortable: true },
  { key: 'actions', label: 'Ações' }
]

const isLowStock = (product) => {
  return product.quantity <= product.minimumstock
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const openCreateModal = () => {
  isEditing.value = false
  formData.value = {
    id: null,
    name: '',
    code: '',
    price: '',
    quantity: '',
    minimumstock: '',
    supplier_id: ''
  }
  formErrors.value = {}
  showModal.value = true
}

const openEditModal = (product) => {
  isEditing.value = true
  formData.value = {
    ...product,
    supplier_id: product.supplier_id ? String(product.supplier_id) : ''
  }
  formErrors.value = {}
  showModal.value = true
}

const confirmDelete = (product) => {
  productToDelete.value = product
  showDeleteModal.value = true
}

const validateForm = () => {
  const errors = {}
  if (!formData.value.name) errors.name = 'Nome é obrigatório'
  if (!formData.value.code) errors.code = 'Código é obrigatório'
  if (!formData.value.price || formData.value.price <= 0) errors.price = 'Preço deve ser maior que zero'
  if (formData.value.quantity === '' || formData.value.quantity < 0) errors.quantity = 'Quantidade inválida'
  if (formData.value.minimumstock === '' || formData.value.minimumstock < 0) errors.minimumstock = 'Estoque mínimo inválido'
  if (!formData.value.supplier_id) errors.supplier_id = 'Fornecedor é obrigatório'
  
  formErrors.value = errors
  return Object.keys(errors).length === 0
}

const saveProduct = async () => {
  if (!validateForm()) return

  // Persisted entities keep id server-owned; strip it from create/update payloads.
  const formDataCopy = { ...formData.value }
  delete formDataCopy.id
  const productData = {
    ...formDataCopy,
    price: parseFloat(formData.value.price),
    quantity: parseInt(formData.value.quantity),
    minimumstock: parseInt(formData.value.minimumstock),
    supplier_id: parseInt(formData.value.supplier_id)
  }

  try {
    error.value = null
    if (isEditing.value) {
      await updateProduct(formData.value.id, productData)
      showModal.value = false
    } else {
      await createProduct(productData)
      showModal.value = false
      successMessage.value = `Produto ${formData.value.name} adicionado com sucesso!`
      showSuccessPopup.value = true
      if (successPopupTimer) {
        clearTimeout(successPopupTimer)
      }
      successPopupTimer = setTimeout(() => {
        showSuccessPopup.value = false
      }, 2500)
    }
    await loadProducts()
  } catch (err) {
    console.error('Erro ao salvar produto:', err)
    error.value = getSafeErrorMessage(err, 'Nao foi possivel salvar o produto.')
  }
}

const deleteProductItem = async () => {
  try {
    await deleteProduct(productToDelete.value.id)
    await loadProducts()
    showDeleteModal.value = false
    productToDelete.value = null
  } catch (error) {
    console.error('Erro ao deletar produto:', error)
  }
}


const ensureArray = (value) => Array.isArray(value) ? value : (value?.data ?? [])

const loadSuppliers = async () => {
  try {
    suppliers.value = ensureArray(await getSuppliers())
  } catch (err) {
    console.error('Erro ao carregar fornecedores para produtos:', err)
    suppliers.value = ensureArray(mockData.suppliers)
  }
}

const supplierOptions = computed(() => {
  return suppliers.value
    .map((supplier) => ({
      value: String(supplier.id ?? supplier.supplier_id ?? ''),
      label: supplier.name ?? supplier.nome ?? `Fornecedor ${supplier.id ?? ''}`
    }))
    .filter((option) => option.value)
})

const normalizeProduct = (product) => {
  return {
    id: product.id ?? '-',
    code: product.code ?? '-',
    name: product.name ?? '-',
    price: product.price ?? '-',
    quantity: product.quantity ?? '-',
    minimumstock: product.minimumstock ?? product.minStock ?? product.min_stock ?? '-',
    supplier_id: product.supplier_id ?? product.supplier?.id ?? '',
    supplier_name: product.supplier?.name ?? product.supplier_name ?? product.fornecedor?.name ?? '-',
  }
}

const loadProducts = async () => {
  loading.value = true
  error.value = null
  try {
    products.value = ensureArray(await getProducts()).map(normalizeProduct)
  } catch (err) {
    console.error('Erro ao carregar produtos:', err)
    const safeMessage = getSafeErrorMessage(err, 'Nao foi possivel carregar produtos agora.')
    error.value = `${safeMessage} Mostrando dados de exemplo.`
    products.value = mockData.products.map(normalizeProduct)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadSuppliers()
  loadProducts()
})

onBeforeUnmount(() => {
  if (successPopupTimer) {
    clearTimeout(successPopupTimer)
  }
})
</script>

<template>
  <div class="space-y-6">
    <div
      v-if="showSuccessPopup"
      class="fixed right-6 top-6 z-[80] w-[280px] rounded-xl border border-success/30 bg-card p-4 shadow-lg"
    >
      <div class="flex flex-col items-center gap-2 text-center">
        <img src="/product-added-success.svg" alt="Produto adicionado com sucesso" class="h-28 w-auto" />
        <p class="text-sm font-medium text-success">{{ successMessage }}</p>
      </div>
    </div>

    <!-- Header -->
    <div class="flex items-center justify-between rounded-xl border border-border bg-card/70 px-4 py-3 backdrop-blur-sm">
      <div>
        <p class="text-sm font-medium text-foreground">Produtos</p>
        <p class="text-xs text-muted-foreground">Gerencie o catalogo de produtos do sistema</p>
      </div>
      <Button @click="openCreateModal">
        <Plus class="h-4 w-4" />
        Novo Produto
      </Button>
    </div>

    <div v-if="error" class="rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
      {{ error }}
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-8">
      <div class="text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
        <p class="mt-2 text-sm text-muted-foreground">Carregando produtos...</p>
      </div>
    </div>

    <!-- Table -->
    <DataTable v-else :columns="columns" :data="products" :externalSearchQuery="uiStore.globalSearch">
      <template #cell-code="{ item }">
        <span class="font-mono text-xs">{{ item.code }}</span>
      </template>
      <template #cell-name="{ item }">
        <div class="flex items-center gap-2">
          <span>{{ item.name }}</span>
          <span
            v-if="isLowStock(item)"
            class="inline-flex items-center gap-1 rounded-full bg-destructive/10 px-2 py-0.5 text-xs font-medium text-destructive"
          >
            <AlertTriangle class="h-3 w-3" />
            Baixo
          </span>
        </div>
      </template>
      <template #cell-price="{ item }">
        <span class="font-medium">{{ formatCurrency(item.price) }}</span>
      </template>
      <template #cell-quantity="{ item }">
        <span
          class="font-medium"
          :class="isLowStock(item) ? 'text-destructive' : 'text-foreground'"
        >
          {{ item.quantity }}
        </span>
      </template>
      <template #cell-actions="{ item }">
        <div class="flex items-center gap-1">
          <button
            @click.stop="openEditModal(item)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
          >
            <Pencil class="h-4 w-4" />
          </button>
          <button
            @click.stop="confirmDelete(item)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
          >
            <Trash2 class="h-4 w-4" />
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Create/Edit Modal -->
    <Modal :show="showModal" :title="isEditing ? 'Editar Produto' : 'Novo Produto'" @close="showModal = false">
      <form @submit.prevent="saveProduct" class="space-y-4">
        <Input
          v-model="formData.name"
          label="Nome"
          placeholder="Nome do produto"
          :error="formErrors.name"
          required
        />
        <Input
          v-model="formData.code"
          label="Código"
          placeholder="Código único"
          :error="formErrors.code"
          required
        />
        <div class="grid gap-4 sm:grid-cols-3">
          <Input
            v-model="formData.price"
            label="Preço"
            type="number"
            step="0.01"
            placeholder="0.00"
            :error="formErrors.price"
            required
          />
          <Input
            v-model="formData.quantity"
            label="Quantidade"
            type="number"
            placeholder="0"
            :error="formErrors.quantity"
            required
          />
          <Input
            v-model="formData.minimumstock"
            label="Estoque Mínimo"
            type="number"
            placeholder="0"
            :error="formErrors.minimumstock"
            required
          />
        </div>
        <Select
          v-model="formData.supplier_id"
          label="Fornecedor"
          :options="supplierOptions"
          placeholder="Selecione um fornecedor"
          :error="formErrors.supplier_id"
          required
        />
        <div class="flex justify-end gap-2 pt-4">
          <Button variant="outline" type="button" @click="showModal = false">
            Cancelar
          </Button>
          <Button type="submit">
            {{ isEditing ? 'Salvar' : 'Criar' }}
          </Button>
        </div>
      </form>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal :show="showDeleteModal" title="Confirmar Exclusão" size="sm" @close="showDeleteModal = false">
      <div class="space-y-4">
        <p class="text-muted-foreground">
          Tem certeza que deseja excluir o produto <strong class="text-foreground">{{ productToDelete?.name }}</strong>?
        </p>
        <p class="text-sm text-destructive">Esta ação não pode ser desfeita.</p>
        <div class="flex justify-end gap-2 pt-2">
          <Button variant="outline" @click="showDeleteModal = false">
            Cancelar
          </Button>
          <Button variant="destructive" @click="deleteProductItem">
            Excluir
          </Button>
        </div>
      </div>
    </Modal>
  </div>
</template>
