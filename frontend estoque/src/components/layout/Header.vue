<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Sun, Moon, Bell, Menu, Search, User, LogOut } from 'lucide-vue-next'
import { useAuthStore } from '../../stores/auth'
import { useUiStore } from '../../stores/ui'
import { getEntries } from '../../Services/entryProductService'
import { getExits } from '../../Services/exitProductService'
import { getUsers } from '../../Services/userService'

const props = defineProps({
  isDark: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['toggleTheme', 'toggleSidebar'])

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const uiStore = useUiStore()
const showUserMenu = ref(false)
const showNotifications = ref(false)
const notifications = ref([])
const unseenCount = ref(0)

const currentPageTitle = computed(() => route.meta?.title || 'Pagina')

const LAST_SEEN_NOTIFICATIONS_KEY = 'notifications_last_seen_at'
let notificationsIntervalId = null

const handleLogout = () => {
  authStore.logout()
  router.push({ name: 'Login' })
}

const displayName = () => {
  return authStore.user?.name || authStore.user?.email || 'Usuário'
}

const displayEmail = () => {
  return authStore.user?.email || 'user@email.com'
}

const ensureArray = (value) => {
  if (Array.isArray(value)) return value
  if (Array.isArray(value?.data)) return value.data
  if (Array.isArray(value?.data?.data)) return value.data.data
  return []
}

const parseDateTime = (item = {}) => {
  const raw =
    item?.date ||
    item?.data_de_entrada ||
    item?.data_de_saida ||
    item?.created_at ||
    item?.createdAt ||
    null

  if (!raw) return 0
  const parsed = new Date(raw).getTime()
  return Number.isFinite(parsed) ? parsed : 0
}

const resolveUserName = (item = {}) => {
  return (
    item?.userName ||
    item?.user_name ||
    item?.nome_usuario ||
    item?.registeredByName ||
    item?.registered_by_name ||
    item?.registrado_por ||
    item?.user?.name ||
    item?.user?.nome ||
    null
  )
}

const resolveProductName = (item = {}) => {
  return (
    item?.productName ||
    item?.name ||
    item?.nome ||
    item?.product?.name ||
    item?.product?.nome ||
    'Produto'
  )
}

const formatNotificationTime = (timestamp) => {
  if (!timestamp) return '-'
  return new Date(timestamp).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const rebuildNotifications = async () => {
  try {
    const [entriesResp, exitsResp, usersResp] = await Promise.all([
      getEntries().catch(() => []),
      getExits().catch(() => []),
      getUsers().catch(() => [])
    ])

    const usersMap = new Map(
      ensureArray(usersResp)
        .map((user) => ({
          id: user?.id ?? user?.user_id ?? user?.id_user ?? user?.usuario_id ?? null,
          name: user?.name || user?.nome || user?.username || user?.user_name || ''
        }))
        .filter((user) => user.id != null)
        .map((user) => [String(user.id), user.name])
    )

    const resolveNotificationUserName = (item = {}) => {
      const fallbackName = resolveUserName(item)
      if (fallbackName) return fallbackName

      const userId =
        item?.userId ??
        item?.user_id ??
        item?.id_user ??
        item?.users_id ??
        item?.registered_by ??
        item?.registeredBy ??
        item?.usuario_id ??
        item?.user?.id

      if (userId == null) return 'Sistema'
      return usersMap.get(String(userId)) || `Usuario #${userId}`
    }

    const entriesList = ensureArray(entriesResp).map((item) => {
      const timestamp = parseDateTime(item)
      return {
        id: `entry-${item?.id ?? timestamp}`,
        timestamp,
        type: 'Entrada',
        title: 'Nova entrada registrada',
        description: `${resolveProductName(item)} • +${item?.quantity ?? 0} un. • ${resolveNotificationUserName(item)}`,
        when: formatNotificationTime(timestamp)
      }
    })

    const exitsList = ensureArray(exitsResp).map((item) => {
      const timestamp = parseDateTime(item)
      return {
        id: `exit-${item?.id ?? timestamp}`,
        timestamp,
        type: 'Saida',
        title: 'Nova saida registrada',
        description: `${resolveProductName(item)} • -${item?.quantity ?? 0} un. • ${resolveNotificationUserName(item)}`,
        when: formatNotificationTime(timestamp)
      }
    })

    notifications.value = [...entriesList, ...exitsList]
      .sort((a, b) => b.timestamp - a.timestamp)
      .slice(0, 12)

    const savedSeen = Number.parseInt(localStorage.getItem(LAST_SEEN_NOTIFICATIONS_KEY) || '', 10)
    if (!Number.isFinite(savedSeen)) {
      localStorage.setItem(LAST_SEEN_NOTIFICATIONS_KEY, String(Date.now()))
      unseenCount.value = 0
      return
    }

    unseenCount.value = notifications.value.filter((item) => item.timestamp > savedSeen).length
  } catch (error) {
    console.error('Erro ao carregar notificacoes:', error)
  }
}

const markNotificationsAsSeen = () => {
  localStorage.setItem(LAST_SEEN_NOTIFICATIONS_KEY, String(Date.now()))
  unseenCount.value = 0
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value) {
    markNotificationsAsSeen()
  }
}

onMounted(async () => {
  await rebuildNotifications()
  notificationsIntervalId = setInterval(rebuildNotifications, 30000)
})

onBeforeUnmount(() => {
  if (notificationsIntervalId) {
    clearInterval(notificationsIntervalId)
    notificationsIntervalId = null
  }
})
</script>

<template>
  <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-border bg-card/80 px-6 backdrop-blur-sm">
    <div class="flex h-10 items-center gap-3">
      <button
        @click="emit('toggleSidebar')"
        class="flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground lg:hidden"
      >
        <Menu class="h-5 w-5" />
      </button>

      <p class="text-base font-semibold leading-none text-foreground md:text-lg">
        {{ currentPageTitle }}
      </p>
    </div>

    <div class="absolute left-1/2 hidden w-80 -translate-x-1/2 md:block lg:w-96">
      <div class="relative">
        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
        <input
          v-model="uiStore.globalSearch"
          type="text"
          placeholder="Buscar..."
          class="h-10 w-full rounded-xl border border-input bg-background pl-9 pr-4 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
        />
      </div>
    </div>

    <div class="flex items-center gap-3">
      <button
        @click="emit('toggleTheme')"
        class="flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
        :title="isDark ? 'Modo claro' : 'Modo escuro'"
      >
        <Moon v-if="!isDark" class="h-5 w-5" />
        <Sun v-else class="h-5 w-5" />
      </button>

      <div class="relative">
        <button
          @click="toggleNotifications"
          class="relative flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
          title="Notificacoes"
        >
          <Bell class="h-5 w-5" />
          <span
            v-if="unseenCount > 0"
            class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-destructive px-1.5 py-0.5 text-[10px] font-semibold text-white"
          >
            {{ unseenCount > 9 ? '9+' : unseenCount }}
          </span>
        </button>

        <div
          v-if="showNotifications"
          class="absolute right-0 top-11 z-40 w-[340px] overflow-hidden rounded-xl border border-border bg-card shadow-xl"
        >
          <div class="border-b border-border px-4 py-3">
            <p class="text-sm font-semibold text-foreground">Notificacoes</p>
            <p class="text-xs text-muted-foreground">Entradas e saidas recentes</p>
          </div>

          <div class="max-h-80 overflow-y-auto">
            <div
              v-for="item in notifications"
              :key="item.id"
              class="border-b border-border px-4 py-3 last:border-b-0"
            >
              <p class="text-sm font-medium text-foreground">{{ item.title }}</p>
              <p class="mt-0.5 text-xs text-muted-foreground">{{ item.description }}</p>
              <p class="mt-1 text-[11px] text-muted-foreground">{{ item.when }}</p>
            </div>

            <div v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-muted-foreground">
              Nenhuma notificacao encontrada.
            </div>
          </div>
        </div>
      </div>

      <div class="relative ml-2 border-l border-border pl-4">
        <button
          @click="showUserMenu = !showUserMenu"
          class="flex items-center gap-3 rounded-lg p-1 transition-colors hover:bg-muted"
        >
          <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary">
            <User class="h-4 w-4 text-primary-foreground" />
          </div>
          <div class="hidden text-left sm:block">
            <p class="text-sm font-medium text-foreground">{{ displayName() }}</p>
            <p class="text-xs text-muted-foreground">{{ displayEmail() }}</p>
          </div>
        </button>

        <!-- User Menu Dropdown -->
        <div
          v-if="showUserMenu"
          class="absolute right-0 top-12 w-48 rounded-lg border border-border bg-card shadow-lg"
        >
          <div class="border-b border-border px-4 py-3">
            <p class="text-sm font-medium text-foreground">{{ displayName() }}</p>
            <p class="text-xs text-muted-foreground">{{ displayEmail() }}</p>
          </div>
          <button
            @click="handleLogout"
            class="flex w-full items-center gap-2 rounded-none px-4 py-2 text-sm text-destructive transition-colors hover:bg-destructive/10"
          >
            <LogOut class="h-4 w-4" />
            Sair
          </button>
        </div>
      </div>
    </div>
  </header>
</template>
