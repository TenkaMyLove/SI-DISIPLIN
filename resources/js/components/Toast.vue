<template>
  <div
    v-if="visible"
    class="fixed bottom-5 right-5 z-50 flex items-center p-4 space-x-3 border shadow-md text-sm transition-all"
    :class="variantClasses"
    role="alert"
  >
    <CheckCircle v-if="variant === 'success'" class="w-5 h-5 flex-shrink-0" />
    <AlertCircle v-else-if="variant === 'error'" class="w-5 h-5 flex-shrink-0" />
    <Info v-else class="w-5 h-5 flex-shrink-0" />

    <div class="font-medium">
      {{ message }}
    </div>

    <button
      type="button"
      class="ml-auto -mx-1.5 -my-1.5 p-1.5 inline-flex h-7 w-7 text-current opacity-70 hover:opacity-100 focus:outline-none"
      @click="visible = false"
      aria-label="Tutup"
    >
      <X class="w-4 h-4" />
    </button>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { CheckCircle, AlertCircle, Info, X } from 'lucide-vue-next'

const props = defineProps({
  show: Boolean,
  message: String,
  variant: {
    type: String,
    default: 'success', // 'success' | 'error' | 'info'
  },
  duration: {
    type: Number,
    default: 4000,
  },
})

const emit = defineEmits(['update:show'])

const visible = ref(props.show)
let timer = null

watch(() => props.show, (newVal) => {
  visible.value = newVal
  if (newVal && props.duration > 0) {
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => {
      visible.value = false
      emit('update:show', false)
    }, props.duration)
  }
})

watch(visible, (newVal) => {
  emit('update:show', newVal)
})

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'success':
      return 'bg-white text-emerald-900 border-emerald-500'
    case 'error':
      return 'bg-white text-red-900 border-red-500'
    default:
      return 'bg-white text-navy border-navy'
  }
})
</script>
