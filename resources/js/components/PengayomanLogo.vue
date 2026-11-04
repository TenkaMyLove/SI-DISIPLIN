<template>
  <img
    src="/images/logo-pengayoman.png"
    alt="Logo Pengayoman Kementerian Hukum"
    :style="{ width: formattedSize, height: formattedSize }"
    class="object-contain flex-shrink-0 inline-block"
  />
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: {
    type: [Number, String],
    default: 44,
  },
})

const formattedSize = computed(() => {
  return typeof props.size === 'number' ? `${props.size}px` : props.size
})
</script>
