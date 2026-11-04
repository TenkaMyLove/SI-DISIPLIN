<template>
  <div class="w-full space-y-3" aria-busy="true" aria-label="Memuat data...">
    <!-- Table Skeleton Mode -->
    <div v-if="type === 'table'" class="w-full border border-neutral-border bg-white rounded-none divide-y divide-neutral-border">
      <div class="bg-navy p-3 flex space-x-4">
        <div v-for="c in cols" :key="'th-'+c" class="skeleton h-4" :style="{ width: getColWidth(c) }"></div>
      </div>
      <div v-for="r in rows" :key="'tr-'+r" class="p-4 flex items-center space-x-4">
        <div v-for="c in cols" :key="'td-'+r+'-'+c" class="skeleton h-4" :style="{ width: getColWidth(c) }"></div>
      </div>
    </div>

    <!-- Stat Box Skeleton Mode -->
    <div v-else-if="type === 'stat'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="s in (rows || 4)" :key="'stat-'+s" class="border border-neutral-border bg-white p-4 space-y-2">
        <div class="skeleton h-3 w-24"></div>
        <div class="skeleton h-7 w-32"></div>
        <div class="skeleton h-3 w-40"></div>
      </div>
    </div>

    <!-- Form Skeleton Mode -->
    <div v-else-if="type === 'form'" class="border border-neutral-border bg-white p-6 space-y-5">
      <div v-for="f in rows" :key="'form-'+f" class="space-y-2">
        <div class="skeleton h-4 w-28"></div>
        <div class="skeleton h-10 w-full"></div>
      </div>
    </div>

    <!-- Generic Block Skeleton Mode -->
    <div v-else class="space-y-2">
      <div v-for="r in rows" :key="'line-'+r" class="skeleton h-4 w-full" :style="{ width: r === rows ? '60%' : '100%' }"></div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  type: {
    type: String,
    default: 'table', // 'table' | 'stat' | 'form' | 'lines'
  },
  rows: {
    type: Number,
    default: 5,
  },
  cols: {
    type: Number,
    default: 6,
  },
})

function getColWidth(index) {
  const widths = ['8%', '15%', '22%', '18%', '12%', '15%', '10%']
  return widths[(index - 1) % widths.length] || '14%'
}
</script>
