<template>
  <router-view />
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuth } from '@/composables/useAuth'

const { checkAuth } = useAuth()

onMounted(() => {
  checkAuth()
})
</script>
