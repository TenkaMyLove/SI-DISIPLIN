<template>
  <div class="min-h-screen bg-neutral-surface">
    <!-- Navbar: FIXED at the top of the viewport -->
    <Navbar :full-width="true" />

    <!-- Sidebar: FIXED on the left of the viewport below Navbar -->
    <Sidebar />

    <!-- Main Workspace: Offset by pl-64 (sidebar) and pt-20 (navbar) -->
    <main
      ref="mainContentRef"
      class="pt-20 pl-64 min-h-screen flex flex-col print:pt-0 print:pl-0 min-w-0 overflow-x-hidden"
    >
      <div class="flex-1 p-4 sm:p-5 lg:p-6 min-w-0 max-w-full flex flex-col">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import Sidebar from '@/components/Sidebar.vue'

const route = useRoute()
const mainContentRef = ref(null)

// Reset scroll to top whenever the admin route changes
watch(
  () => route.path,
  () => {
    window.scrollTo({ top: 0, behavior: 'instant' })
  }
)
</script>
