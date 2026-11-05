<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
  >
    <!-- Solid dark backdrop -->
    <div
      class="fixed inset-0 bg-neutral-900 bg-opacity-60 transition-opacity"
      @click="$emit('close')"
    ></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
      <div
        class="relative transform bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full border border-neutral-border overflow-visible"
        :class="maxWidthClass"
        @click.stop
      >
        <!-- Modal Header in Solid Navy -->
        <div class="bg-navy px-6 py-4 flex items-center justify-between text-white border-b-2 border-accent">
          <h3 id="modal-title" class="text-base font-semibold leading-6 text-white tracking-wide">
            {{ title }}
          </h3>
          <button
            type="button"
            class="text-neutral-300 hover:text-white p-1 focus:outline-none"
            @click="$emit('close')"
            aria-label="Tutup"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Body -->
        <div class="bg-white px-6 py-5">
          <slot></slot>
        </div>

        <!-- Modal Footer -->
        <div v-if="$slots.footer" class="bg-neutral-50 px-6 py-3 flex flex-row-reverse gap-3 border-t border-neutral-border">
          <slot name="footer"></slot>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
  maxWidth: {
    type: String,
    default: 'md', // sm, md, lg, xl, 2xl
  },
})

defineEmits(['close'])

const maxWidthClass = computed(() => {
  switch (props.maxWidth) {
    case 'sm': return 'sm:max-w-sm'
    case 'md': return 'sm:max-w-md'
    case 'lg': return 'sm:max-w-lg'
    case 'xl': return 'sm:max-w-xl'
    case '2xl': return 'sm:max-w-2xl'
    case '3xl': return 'sm:max-w-3xl'
    default: return 'sm:max-w-md'
  }
})
</script>
