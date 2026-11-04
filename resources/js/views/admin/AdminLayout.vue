<template>
  <div class="min-h-screen flex flex-col bg-neutral-surface">
    <Navbar :full-width="true" />

    <div class="flex-1 flex w-full">
      <Sidebar class="flex-shrink-0" />

      <main class="flex-1 p-6 lg:p-8 min-w-0 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import Navbar from '@/components/Navbar.vue'
import Sidebar from '@/components/Sidebar.vue'
</script>
