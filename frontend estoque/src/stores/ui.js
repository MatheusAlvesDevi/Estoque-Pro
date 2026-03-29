import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUiStore = defineStore('ui', () => {
  const globalSearch = ref('')

  const setGlobalSearch = (value) => {
    globalSearch.value = value || ''
  }

  return {
    globalSearch,
    setGlobalSearch
  }
})
