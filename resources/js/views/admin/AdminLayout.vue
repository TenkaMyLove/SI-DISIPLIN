<template>
  <div class="h-screen flex flex-col bg-neutral-surface overflow-hidden print:h-auto print:overflow-visible">
    <!-- Header / Navbar: Anchored firmly at the top -->
    <Navbar :full-width="true" class="flex-shrink-0 z-30" />

    <!-- Main Workspace: Anchored Sidebar on left, Independent scrollable content on right -->
    <div class="flex-1 flex w-full overflow-hidden print:overflow-visible">
      <Sidebar class="flex-shrink-0" />

      <main
        ref="mainContentRef"
        class="flex-1 p-6 lg:p-8 min-w-0 overflow-y-auto print:overflow-visible print:p-0"
      >
        <router-view />
      </main>
    </div>
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
    if (mainContentRef.value) {
      mainContentRef.value.scrollTop = 0
    }
  }
)
</script>
