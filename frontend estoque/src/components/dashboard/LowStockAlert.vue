<script setup>
import { AlertTriangle, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
  products: Array
})
</script>

<template>
  <div class="rounded-xl border border-warning/30 bg-warning/10 p-4">
    <div class="flex items-start gap-3">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-warning">
        <AlertTriangle class="h-5 w-5 text-white" />
      </div>
      <div class="flex-1">
        <h3 class="font-semibold text-foreground">Alerta de Estoque Baixo</h3>
        <p class="mt-1 text-sm text-muted-foreground">
          {{ products.length }} produto(s) estão com estoque abaixo do mínimo
        </p>
        <div class="mt-3 flex flex-wrap gap-2">
          <router-link
            v-for="product in products.slice(0, 5)"
            :key="product.id"
            to="/products"
            class="inline-flex items-center gap-1 rounded-lg bg-card px-3 py-1.5 text-sm font-medium text-foreground transition-colors hover:bg-muted"
          >
            {{ product.name }}
            <span class="text-destructive">({{ product.quantity }})</span>
          </router-link>
          <router-link
            v-if="products.length > 5"
            to="/products"
            class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
          >
            Ver todos
            <ChevronRight class="h-4 w-4" />
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>
