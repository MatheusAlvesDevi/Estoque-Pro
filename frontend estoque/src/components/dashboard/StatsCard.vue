<script setup>
import { TrendingUp, TrendingDown } from 'lucide-vue-next'

const props = defineProps({
  title: String,
  value: [Number, String],
  icon: [Object, Function, Array],
  color: String,
  change: String,
  trend: String
})
</script>

<template>
  <div class="rounded-xl border border-border bg-card p-6 transition-shadow hover:shadow-md">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-muted-foreground">{{ title }}</p>
        <p class="mt-2 text-3xl font-bold text-card-foreground">{{ value }}</p>
        <div class="mt-2 flex items-center gap-1">
          <TrendingUp v-if="trend === 'up'" class="h-4 w-4 text-success" />
          <TrendingDown v-else class="h-4 w-4 text-destructive" />
          <span
            class="text-sm font-medium"
            :class="trend === 'up' ? 'text-success' : 'text-destructive'"
          >
            {{ change }}
          </span>
          <span class="text-sm text-muted-foreground">vs mês anterior</span>
        </div>
      </div>
      <div
        class="flex h-12 w-12 items-center justify-center rounded-lg"
        :class="color"
      >
        <component :is="icon" class="h-6 w-6 text-white" />
      </div>
    </div>
  </div>
</template>
