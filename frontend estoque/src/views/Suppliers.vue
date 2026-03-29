<script setup>
import { ref, onMounted } from 'vue'
import { Plus, Pencil, Trash2, Building2, Mail, Phone } from 'lucide-vue-next'
import DataTable from '../components/ui/DataTable.vue'
import Modal from '../components/ui/Modal.vue'
import Button from '../components/ui/Button.vue'
import Input from '../components/ui/Input.vue'
import { getSuppliers, createSupplier, updateSupplier, deleteSupplier } from '../Services/supplierService'
import { mockData } from '../composables/useApi'
import { getSafeErrorMessage } from '../lib/safeErrorMessage'
import { useUiStore } from '../stores/ui'

const suppliers = ref([])
const loading = ref(false)
const error = ref(null)
const showModal = ref(false)
const isEditing = ref(false)
const supplierToDelete = ref(null)
const showDeleteModal = ref(false)
const uiStore = useUiStore()

const formData = ref({
  id: null,
  name: '',
  cnpj: '',
  tel: '',
  email: '',
  phone: '',
  address: ''
})

const formErrors = ref({})

const columns = [
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'cnpj', label: 'CNPJ', sortable: true },
  { key: 'tel', label: 'Telefone', sortable: true },
  { key: 'email', label: 'E-mail', sortable: true },
  { key: 'actions', label: 'Ações' }
]

const openCreateModal = () => {
  isEditing.value = false
  formData.value = {
    id: null,
    name: '',
    cnpj: '',
    tel: '',
    email: '',
    phone: '',
    address: ''
  }
  formErrors.value = {}
  showModal.value = true
}

const openEditModal = (supplier) => {
  isEditing.value = true
  formData.value = {
    ...supplier,
    cnpj: supplier.cnpj ?? supplier.CNPJ ?? '',
    tel: supplier.tel ?? supplier.phone ?? ''
  }
  formErrors.value = {}
  showModal.value = true
}

const confirmDelete = (supplier) => {
  supplierToDelete.value = supplier
  showDeleteModal.value = true
}

const validateForm = () => {
  const errors = {}
  if (!formData.value.name) errors.name = 'Nome é obrigatório'
  if (!formData.value.cnpj) errors.cnpj = 'CNPJ é obrigatório'
  if (!formData.value.tel) errors.tel = 'Telefone é obrigatório'
  if (!formData.value.email) errors.email = 'E-mail é obrigatório'
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.email)) errors.email = 'E-mail inválido'
  
  formErrors.value = errors
  return Object.keys(errors).length === 0
}

const saveSupplier = async () => {
  if (!validateForm()) return

  try {
    if (isEditing.value) {
      await updateSupplier(formData.value.id, formData.value)
    } else {
      await createSupplier(formData.value)
    }
    await loadSuppliers()
    showModal.value = false
  } catch (error) {
    console.error('Erro ao salvar fornecedor:', error)
  }
}

const deleteSupplierItem = async () => {
  try {
    await deleteSupplier(supplierToDelete.value.id)
    await loadSuppliers()
    showDeleteModal.value = false
    supplierToDelete.value = null
  } catch (error) {
    console.error('Erro ao deletar fornecedor:', error)
  }
}

const ensureArray = (value) => Array.isArray(value) ? value : (value?.data ?? [])

const normalizeSupplier = (supplier = {}) => ({
  id: supplier.id ?? '-',
  name: supplier.name ?? supplier.nome ?? '-',
  cnpj: supplier.cnpj ?? supplier.CNPJ ?? '-',
  tel: supplier.tel ?? supplier.phone ?? supplier.telefone ?? '-',
  email: supplier.email ?? '-',
  phone: supplier.tel ?? supplier.phone ?? supplier.telefone ?? '-',
  address: supplier.address ?? supplier.endereco ?? '-'
})

const loadSuppliers = async () => {
  loading.value = true
  error.value = null
  try {
    suppliers.value = ensureArray(await getSuppliers()).map(normalizeSupplier)
  } catch (err) {
    console.error('Erro ao carregar fornecedores:', err)
    const safeMessage = getSafeErrorMessage(err, 'Nao foi possivel carregar fornecedores agora.')
    error.value = `${safeMessage} Mostrando dados de exemplo.`
    suppliers.value = ensureArray(mockData.suppliers).map(normalizeSupplier)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadSuppliers()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between rounded-xl border border-border bg-card/70 px-4 py-3 backdrop-blur-sm">
      <div>
        <p class="text-sm font-medium text-foreground">Fornecedores</p>
        <p class="text-xs text-muted-foreground">Gerencie os fornecedores cadastrados no sistema</p>
      </div>
      <Button @click="openCreateModal">
        <Plus class="h-4 w-4" />
        Novo Fornecedor
      </Button>
    </div>

    <div v-if="error" class="rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
      {{ error }}
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-8">
      <div class="text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
        <p class="mt-2 text-sm text-muted-foreground">Carregando fornecedores...</p>
      </div>
    </div>

    <!-- Table -->
    <DataTable v-else :columns="columns" :data="suppliers" :externalSearchQuery="uiStore.globalSearch">
      <template #cell-name="{ item }">
        <div class="flex items-center gap-2">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
            <Building2 class="h-4 w-4 text-primary" />
          </div>
          <span class="font-medium">{{ item.name }}</span>
        </div>
      </template>
      <template #cell-cnpj="{ item }">
        <span class="font-mono text-xs text-muted-foreground">{{ item.cnpj }}</span>
      </template>
      <template #cell-email="{ item }">
        <div class="flex items-center gap-2 text-muted-foreground">
          <Mail class="h-4 w-4" />
          {{ item.email }}
        </div>
      </template>
      <template #cell-tel="{ item }">
        <div class="flex items-center gap-2 text-muted-foreground">
          <Phone class="h-4 w-4" />
          {{ item.tel }}
        </div>
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
    <Modal :show="showModal" :title="isEditing ? 'Editar Fornecedor' : 'Novo Fornecedor'" @close="showModal = false">
      <form @submit.prevent="saveSupplier" class="space-y-4">
        <Input
          v-model="formData.name"
          label="Nome"
          placeholder="Nome do fornecedor"
          :error="formErrors.name"
          required
        />
        <div class="grid gap-4 sm:grid-cols-2">
          <Input
            v-model="formData.cnpj"
            label="CNPJ"
            placeholder="00.000.000/0000-00"
            :error="formErrors.cnpj"
            required
          />
          <Input
            v-model="formData.tel"
            label="Telefone"
            placeholder="(11) 99999-0001"
            :error="formErrors.tel"
            required
          />
        </div>
        <div class="grid gap-4 sm:grid-cols-1">
          <Input
            v-model="formData.email"
            label="E-mail"
            type="email"
            placeholder="email@exemplo.com"
            :error="formErrors.email"
            required
          />
        </div>
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
          Tem certeza que deseja excluir o fornecedor <strong class="text-foreground">{{ supplierToDelete?.name }}</strong>?
        </p>
        <p class="text-sm text-destructive">Esta ação não pode ser desfeita.</p>
        <div class="flex justify-end gap-2 pt-2">
          <Button variant="outline" @click="showDeleteModal = false">
            Cancelar
          </Button>
          <Button variant="destructive" @click="deleteSupplierItem">
            Excluir
          </Button>
        </div>
      </div>
    </Modal>
  </div>
</template>
