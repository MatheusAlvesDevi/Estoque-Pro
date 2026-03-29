<script setup>
import { ref, onMounted, provide, onErrorCaptured, computed } from 'vue'
import { useRoute } from 'vue-router'
import Sidebar from './components/layout/Sidebar.vue'
import Header from './components/layout/Header.vue'
import { getSafeErrorMessage } from './lib/safeErrorMessage'

const isDark = ref(false)
const sidebarCollapsed = ref(false)
const runtimeError = ref('')
const route = useRoute()

const isLoginPage = computed(() => route.name === 'Login')

const toggleTheme = () => {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

onMounted(() => {
  const savedTheme = localStorage.getItem('theme')

  // Default to dark theme when no preference is stored to preserve visual consistency.
  if (savedTheme === 'dark' || !savedTheme) {
    isDark.value = true
    document.documentElement.classList.add('dark')
  } else {
    isDark.value = false
    document.documentElement.classList.remove('dark')
  }
})

provide('theme', { isDark, toggleTheme })

onErrorCaptured((err, instance, info) => {
  console.error('Erro de runtime capturado no App:', err, info, instance)
  runtimeError.value = getSafeErrorMessage(err, 'Erro inesperado ao renderizar componentes.')
  return false
})
</script>

<template>
  <div v-if="isLoginPage" class="min-h-screen bg-background">
    <div v-if="runtimeError" class="mb-4 rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
      Erro de renderização: {{ runtimeError }}
    </div>
    <router-view />
  </div>

  <div v-else class="relative flex h-screen overflow-hidden bg-background">
    <div class="pointer-events-none absolute -left-28 -top-28 h-72 w-72 rounded-full bg-blue-500/12 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 h-80 w-80 rounded-full bg-cyan-400/10 blur-3xl"></div>

    <Sidebar :collapsed="sidebarCollapsed" @toggle="toggleSidebar" />
    <div class="relative z-10 flex h-screen flex-1 flex-col" :class="sidebarCollapsed ? 'ml-16' : 'ml-64'">
      <Header :isDark="isDark" @toggleTheme="toggleTheme" @toggleSidebar="toggleSidebar" />
      <main class="flex-1 overflow-y-auto p-6 pb-10">
        <div v-if="runtimeError" class="mb-4 rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
          Erro de renderização: {{ runtimeError }}
        </div>
        <router-view />
      </main>
    </div>
  </div>
</template>
