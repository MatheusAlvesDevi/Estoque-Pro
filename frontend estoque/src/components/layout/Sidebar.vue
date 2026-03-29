<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import {
  LayoutDashboard,
  Package,
  Truck,
  ArrowDownToLine,
  ArrowUpFromLine,
  Users,
  ChevronLeft,
  ChevronRight
} from 'lucide-vue-next'

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['toggle'])

const route = useRoute()

const menuItems = [
  { name: 'Dashboard', path: '/', icon: LayoutDashboard },
  { name: 'Produtos', path: '/products', icon: Package },
  { name: 'Fornecedores', path: '/suppliers', icon: Truck },
  { name: 'Entrada de Estoque', path: '/stock-entry', icon: ArrowDownToLine },
  { name: 'Saída de Estoque', path: '/stock-exit', icon: ArrowUpFromLine },
  { name: 'Usuários', path: '/users', icon: Users }
]

const isActive = (path) => {
  return route.path === path
}
</script>

<template>
  <aside
    class="fixed left-0 top-0 z-40 h-screen border-r border-border bg-card transition-all duration-300"
    :class="collapsed ? 'w-16' : 'w-64'"
  >
    <div class="flex h-16 items-center justify-between border-b border-border px-4">
      <div class="flex items-center gap-3" v-if="!collapsed">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
          <Package class="h-5 w-5 text-primary-foreground" />
        </div>
        <span class="font-semibold text-foreground">Estoque Pro</span>
      </div>
      <div v-else class="flex w-full justify-center">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
          <Package class="h-5 w-5 text-primary-foreground" />
        </div>
      </div>
    </div>

    <nav class="mt-4 px-2">
      <ul class="space-y-1">
        <li v-for="item in menuItems" :key="item.path">
          <router-link
            :to="item.path"
            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
            :class="[
              isActive(item.path)
                ? 'bg-primary text-primary-foreground'
                : 'text-muted-foreground hover:bg-muted hover:text-foreground'
            ]"
            :title="collapsed ? item.name : ''"
          >
            <component :is="item.icon" class="h-5 w-5 shrink-0" />
            <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
          </router-link>
        </li>
      </ul>
    </nav>

    <button
      @click="emit('toggle')"
      class="absolute -right-3 top-20 flex h-6 w-6 items-center justify-center rounded-full border border-border bg-card text-muted-foreground shadow-sm transition-colors hover:bg-muted hover:text-foreground"
    >
      <ChevronLeft v-if="!collapsed" class="h-4 w-4" />
      <ChevronRight v-else class="h-4 w-4" />
    </button>
  </aside>
</template>
