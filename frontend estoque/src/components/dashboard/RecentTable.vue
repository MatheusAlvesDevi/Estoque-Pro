<script setup>
import { ArrowDownToLine, ArrowUpFromLine, ChevronRight } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps({
  title: String,
  items: Array,
  type: String
})

const safeItems = computed(() => (Array.isArray(props.items) ? props.items.filter(Boolean) : []))

const formatDate = (date) => {
  if (!date) return '-'
  const parsed = new Date(date)
  if (Number.isNaN(parsed.getTime())) return '-'
  return parsed.toLocaleDateString('pt-BR')
}

</script>

<template>
  <div class="rounded-xl border border-border bg-card">
    <div class="flex items-center justify-between border-b border-border p-4">
      <div class="flex items-center gap-2">
        <ArrowDownToLine v-if="type === 'entry'" class="h-5 w-5 text-success" />
        <ArrowUpFromLine v-else class="h-5 w-5 text-warning" />
        <h3 class="font-semibold text-card-foreground">{{ title }}</h3>
      </div>
      <router-link
        :to="type === 'entry' ? '/stock-entry' : '/stock-exit'"
        class="flex items-center gap-1 text-sm font-medium text-primary hover:underline"
      >
        Ver todos
        <ChevronRight class="h-4 w-4" />
      </router-link>
    </div>
    <div class="divide-y divide-border">
      <div
        v-for="item in safeItems"
        :key="item.id"
        class="flex items-center justify-between p-4 transition-colors hover:bg-muted/50"
      >
        <div>
          <p class="font-medium text-card-foreground">{{ item.name }}</p>
          <p class="text-sm text-muted-foreground">
            Registrado por {{ item.userName || item.user_id || 'Usuario' }}
          </p>
        </div>
        <div class="text-right">
          <p class="font-semibold" :class="type === 'entry' ? 'text-success' : 'text-warning'">
            {{ type === 'entry' ? '+' : '-' }}{{ item.quantity ?? 0 }} un.
          </p>
          <p class="text-sm text-muted-foreground">{{ formatDate(item.date) }}</p>
        </div>
      </div>
      <div v-if="safeItems.length === 0" class="p-8 text-center text-muted-foreground">
        Nenhum registro encontrado
      </div>
    </div>
  </div>
</template>
