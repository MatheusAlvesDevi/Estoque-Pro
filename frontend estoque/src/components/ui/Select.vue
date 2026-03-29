<script setup>
const props = defineProps({
  modelValue: [String, Number],
  label: String,
  options: Array,
  placeholder: String,
  error: String,
  required: Boolean,
  disabled: Boolean
})

const emit = defineEmits(['update:modelValue'])

const updateValue = (e) => {
  emit('update:modelValue', e.target.value)
}
</script>

<template>
  <div class="space-y-1.5">
    <label v-if="label" class="text-sm font-medium text-foreground">
      {{ label }}
      <span v-if="required" class="text-destructive">*</span>
    </label>
    <select
      :value="modelValue"
      :disabled="disabled"
      :required="required"
      @change="updateValue"
      class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
      :class="error ? 'border-destructive focus:ring-destructive' : ''"
    >
      <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
      <option
        v-for="option in options"
        :key="option.value"
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>
    <p v-if="error" class="text-xs text-destructive">{{ error }}</p>
  </div>
</template>
