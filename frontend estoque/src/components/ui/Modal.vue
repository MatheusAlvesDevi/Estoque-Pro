<script setup>
import { watch, onMounted, onUnmounted } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
  show: Boolean,
  title: String,
  size: {
    type: String,
    default: 'md'
  }
})

const emit = defineEmits(['close'])

const handleEscape = (e) => {
  if (e.key === 'Escape' && props.show) {
    emit('close')
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscape)
})

watch(() => props.show, (show) => {
  if (show) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})

const sizeClasses = {
  sm: 'max-w-md',
  md: 'max-w-lg',
  lg: 'max-w-2xl',
  xl: 'max-w-4xl'
}
</script>

<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
      >
        <div
          class="fixed inset-0 bg-black/50 backdrop-blur-sm"
          @click="emit('close')"
        ></div>
        <Transition name="slide">
          <div
            v-if="show"
            class="relative w-full rounded-xl border border-border bg-card shadow-xl"
            :class="sizeClasses[size]"
          >
            <div class="flex items-center justify-between border-b border-border p-4">
              <h2 class="text-lg font-semibold text-card-foreground">{{ title }}</h2>
              <button
                @click="emit('close')"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
              >
                <X class="h-5 w-5" />
              </button>
            </div>
            <div class="max-h-[calc(100vh-200px)] overflow-y-auto p-4">
              <slot></slot>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
