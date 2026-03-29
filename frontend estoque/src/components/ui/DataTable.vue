<script setup>
import { ref, computed, watch } from 'vue'
import { Search, ChevronUp, ChevronDown, ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
  columns: Array,
  data: Array,
  externalSearchQuery: {
    type: String,
    default: ''
  },
  searchable: {
    type: Boolean,
    default: true
  },
  itemsPerPage: {
    type: Number,
    default: 10
  }
})

const emit = defineEmits(['row-click'])

const searchQuery = ref('')
const sortKey = ref('')
const sortOrder = ref('asc')
const currentPage = ref(1)

const filteredData = computed(() => {
  let result = [...props.data]

  // Applies client-side text filtering across configured columns.
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(item => {
      return props.columns.some(col => {
        const value = item[col.key]
        return value && String(value).toLowerCase().includes(query)
      })
    })
  }

  // Applies deterministic client-side sorting for the selected column.
  if (sortKey.value) {
    result.sort((a, b) => {
      const aVal = a[sortKey.value]
      const bVal = b[sortKey.value]
      
      if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1
      if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1
      return 0
    })
  }

  return result
})

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * props.itemsPerPage
  const end = start + props.itemsPerPage
  return filteredData.value.slice(start, end)
})

const totalPages = computed(() => Math.ceil(filteredData.value.length / props.itemsPerPage))

const toggleSort = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

watch(searchQuery, () => {
  currentPage.value = 1
})

watch(
  () => props.externalSearchQuery,
  (value) => {
    searchQuery.value = value || ''
  },
  { immediate: true }
)
</script>

<template>
  <div class="rounded-xl border border-border bg-card">
    <!-- Search -->
    <div v-if="searchable" class="border-b border-border p-4">
      <div class="relative max-w-sm">
        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Buscar..."
          class="h-10 w-full rounded-lg border border-input bg-background pl-9 pr-4 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
        />
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b border-border bg-muted/50">
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-3 text-left text-sm font-semibold text-card-foreground"
              :class="col.sortable ? 'cursor-pointer select-none hover:bg-muted' : ''"
              @click="col.sortable && toggleSort(col.key)"
            >
              <div class="flex items-center gap-2">
                {{ col.label }}
                <template v-if="col.sortable && sortKey === col.key">
                  <ChevronUp v-if="sortOrder === 'asc'" class="h-4 w-4" />
                  <ChevronDown v-else class="h-4 w-4" />
                </template>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border">
          <tr
            v-for="(item, index) in paginatedData"
            :key="index"
            class="transition-colors hover:bg-muted/50"
            @click="emit('row-click', item)"
          >
            <td
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-3 text-sm text-card-foreground"
            >
              <slot :name="`cell-${col.key}`" :item="item" :value="item[col.key]">
                {{ item[col.key] }}
              </slot>
            </td>
          </tr>
          <tr v-if="paginatedData.length === 0">
            <td :colspan="columns.length" class="px-4 py-8 text-center text-muted-foreground">
              Nenhum registro encontrado
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between border-t border-border px-4 py-3">
      <p class="text-sm text-muted-foreground">
        Mostrando {{ paginatedData.length }} de {{ filteredData.length }} registros
      </p>
      <div class="flex items-center gap-2">
        <button
          @click="currentPage--"
          :disabled="currentPage === 1"
          class="flex h-8 w-8 items-center justify-center rounded-lg border border-border text-muted-foreground transition-colors hover:bg-muted hover:text-foreground disabled:cursor-not-allowed disabled:opacity-50"
        >
          <ChevronLeft class="h-4 w-4" />
        </button>
        <span class="text-sm text-card-foreground">{{ currentPage }} / {{ totalPages || 1 }}</span>
        <button
          @click="currentPage++"
          :disabled="currentPage >= totalPages"
          class="flex h-8 w-8 items-center justify-center rounded-lg border border-border text-muted-foreground transition-colors hover:bg-muted hover:text-foreground disabled:cursor-not-allowed disabled:opacity-50"
        >
          <ChevronRight class="h-4 w-4" />
        </button>
      </div>
    </div>
  </div>
</template>
