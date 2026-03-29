<script setup>
import { ref, onMounted } from 'vue'
import { Plus, Pencil, Trash2, User, Shield, ShieldCheck, ShieldX } from 'lucide-vue-next'
import DataTable from '../components/ui/DataTable.vue'
import Modal from '../components/ui/Modal.vue'
import Button from '../components/ui/Button.vue'
import Input from '../components/ui/Input.vue'
import Select from '../components/ui/Select.vue'
import { getUsers, createUser, updateUser, deleteUser } from '../Services/userService'
import { mockData } from '../composables/useApi'
import { getSafeErrorMessage } from '../lib/safeErrorMessage'
import { useUiStore } from '../stores/ui'

const users = ref([])
const loading = ref(false)
const error = ref(null)
const showModal = ref(false)
const isEditing = ref(false)
const userToDelete = ref(null)
const showDeleteModal = ref(false)
const uiStore = useUiStore()

const formData = ref({
  id: null,
  name: '',
  email: '',
  role: '',
  status: 'Ativo',
  password: ''
})

const formErrors = ref({})

const columns = [
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'email', label: 'E-mail', sortable: true },
  { key: 'role', label: 'Função', sortable: true },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'actions', label: 'Ações' }
]

const roleOptions = [
  { value: 'Administrador', label: 'Administrador' },
  { value: 'Operador', label: 'Operador' },
  { value: 'Visualizador', label: 'Visualizador' }
]

const statusOptions = [
  { value: 'Ativo', label: 'Ativo' },
  { value: 'Inativo', label: 'Inativo' }
]

const getRoleIcon = (role) => {
  switch (role) {
    case 'Administrador': return ShieldCheck
    case 'Operador': return Shield
    default: return ShieldX
  }
}

const openCreateModal = () => {
  isEditing.value = false
  formData.value = {
    id: null,
    name: '',
    email: '',
    role: '',
    status: 'Ativo',
    password: ''
  }
  formErrors.value = {}
  showModal.value = true
}

const openEditModal = (user) => {
  isEditing.value = true
  formData.value = { ...user, password: '' }
  formErrors.value = {}
  showModal.value = true
}

const confirmDelete = (user) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

const validateForm = () => {
  const errors = {}
  if (!formData.value.name) errors.name = 'Nome é obrigatório'
  if (!formData.value.email) errors.email = 'E-mail é obrigatório'
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.email)) errors.email = 'E-mail inválido'
  if (!formData.value.role) errors.role = 'Selecione uma função'
  if (!isEditing.value && !formData.value.password) errors.password = 'Senha é obrigatória'
  else if (!isEditing.value && formData.value.password.length < 6) errors.password = 'Senha deve ter no mínimo 6 caracteres'
  
  formErrors.value = errors
  return Object.keys(errors).length === 0
}

const saveUser = async () => {
  if (!validateForm()) return

  const userData = {
    name: formData.value.name,
    email: formData.value.email,
    role: formData.value.role,
    status: formData.value.status
  }

  try {
    if (isEditing.value) {
      await updateUser(formData.value.id, userData)
    } else {
      await createUser(userData)
    }
    await loadUsers()
    showModal.value = false
  } catch (error) {
    console.error('Erro ao salvar usuário:', error)
  }
}

const deleteUserItem = async () => {
  try {
    await deleteUser(userToDelete.value.id)
    await loadUsers()
    showDeleteModal.value = false
    userToDelete.value = null
  } catch (error) {
    console.error('Erro ao deletar usuário:', error)
  }
}

const ensureArray = (value) => Array.isArray(value) ? value : (value?.data ?? [])

const loadUsers = async () => {
  loading.value = true
  error.value = null
  try {
    users.value = ensureArray(await getUsers())
  } catch (err) {
    console.error('Erro ao carregar usuários:', err)
    const safeMessage = getSafeErrorMessage(err, 'Nao foi possivel carregar usuarios agora.')
    error.value = `${safeMessage} Mostrando dados de exemplo.`
    users.value = mockData.users
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadUsers()
})

</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between rounded-xl border border-border bg-card/70 px-4 py-3 backdrop-blur-sm">
      <div>
        <p class="text-sm font-medium text-foreground">Usuarios</p>
        <p class="text-xs text-muted-foreground">Gerencie os usuarios do sistema</p>
      </div>
      <Button @click="openCreateModal">
        <Plus class="h-4 w-4" />
        Novo Usuário
      </Button>
    </div>

    <div v-if="error" class="rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
      {{ error }}
    </div>

    <!-- Stats -->
    <div class="grid gap-4 sm:grid-cols-3">
      <div class="rounded-xl border border-border bg-card p-4">
        <p class="text-sm text-muted-foreground">Total de Usuários</p>
        <p class="mt-1 text-2xl font-bold text-card-foreground">{{ users.length }}</p>
      </div>
      <div class="rounded-xl border border-border bg-card p-4">
        <p class="text-sm text-muted-foreground">Usuários Ativos</p>
        <p class="mt-1 text-2xl font-bold text-success">{{ users.filter(u => u.status === 'Ativo').length }}</p>
      </div>
      <div class="rounded-xl border border-border bg-card p-4">
        <p class="text-sm text-muted-foreground">Administradores</p>
        <p class="mt-1 text-2xl font-bold text-primary">{{ users.filter(u => u.role === 'Administrador').length }}</p>
      </div>
    </div>

    <!-- Table -->
    <DataTable :columns="columns" :data="users" :externalSearchQuery="uiStore.globalSearch">
      <template #cell-name="{ item }">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10">
            <User class="h-4 w-4 text-primary" />
          </div>
          <span class="font-medium">{{ item.name }}</span>
        </div>
      </template>
      <template #cell-email="{ item }">
        <span class="text-muted-foreground">{{ item.email }}</span>
      </template>
      <template #cell-role="{ item }">
        <div class="flex items-center gap-2">
          <component :is="getRoleIcon(item.role)" class="h-4 w-4 text-muted-foreground" />
          <span>{{ item.role }}</span>
        </div>
      </template>
      <template #cell-status="{ item }">
        <span
          class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
          :class="item.status === 'Ativo' 
            ? 'bg-success/10 text-success' 
            : 'bg-muted text-muted-foreground'"
        >
          {{ item.status }}
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
            :disabled="item.role === 'Administrador' && users.filter(u => u.role === 'Administrador').length === 1"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive disabled:cursor-not-allowed disabled:opacity-50"
          >
            <Trash2 class="h-4 w-4" />
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Create/Edit Modal -->
    <Modal :show="showModal" :title="isEditing ? 'Editar Usuário' : 'Novo Usuário'" @close="showModal = false">
      <form @submit.prevent="saveUser" class="space-y-4">
        <Input
          v-model="formData.name"
          label="Nome"
          placeholder="Nome completo"
          :error="formErrors.name"
          required
        />
        <Input
          v-model="formData.email"
          label="E-mail"
          type="email"
          placeholder="email@exemplo.com"
          :error="formErrors.email"
          required
        />
        <div class="grid gap-4 sm:grid-cols-2">
          <Select
            v-model="formData.role"
            label="Função"
            :options="roleOptions"
            placeholder="Selecione uma função"
            :error="formErrors.role"
            required
          />
          <Select
            v-model="formData.status"
            label="Status"
            :options="statusOptions"
            required
          />
        </div>
        <Input
          v-model="formData.password"
          label="Senha"
          type="password"
          :placeholder="isEditing ? 'Deixe em branco para manter a atual' : 'Mínimo 6 caracteres'"
          :error="formErrors.password"
          :required="!isEditing"
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
          Tem certeza que deseja excluir o usuário <strong class="text-foreground">{{ userToDelete?.name }}</strong>?
        </p>
        <p class="text-sm text-destructive">Esta ação não pode ser desfeita.</p>
        <div class="flex justify-end gap-2 pt-2">
          <Button variant="outline" @click="showDeleteModal = false">
            Cancelar
          </Button>
          <Button variant="destructive" @click="deleteUserItem">
            Excluir
          </Button>
        </div>
      </div>
    </Modal>
  </div>
</template>
