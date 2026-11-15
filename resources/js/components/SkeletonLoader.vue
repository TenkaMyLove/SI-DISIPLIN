<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'table', // 'table' | 'stat' | 'form' | 'pengaturan' | 'lines'
  },
  rows: {
    type: Number,
    default: 5,
  },
  cols: {
    type: Number,
    default: 6,
  },
  compact: {
    type: Boolean,
    default: false,
  },
  showSubtitle: {
    type: Boolean,
    default: false,
  },
  showIcon: {
    type: Boolean,
    default: false,
  },
  hasSectionHeader: {
    type: Boolean,
    default: false,
  },
  hasPagination: {
    type: Boolean,
    default: false,
  },
})

const statGridClass = computed(() => {
  const count = props.cols || props.rows || 3
  if (count === 2) return 'grid-cols-1 sm:grid-cols-2'
  if (count === 3) return 'grid-cols-1 sm:grid-cols-3'
  return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4'
})

function getColWidth(index) {
  const widths = ['6%', '14%', '22%', '18%', '12%', '14%', '10%', '12%', '12%']
  return widths[(index - 1) % widths.length] || '14%'
}
</script>

<template>
  <div class="w-full" aria-busy="true" aria-label="Memuat data...">
    <!-- Table Skeleton Mode -->
    <div
      v-if="type === 'table'"
      class="w-full bg-white border border-neutral-border shadow-sm flex flex-col"
      :class="hasSectionHeader ? 'p-4 sm:p-5' : ''"
    >
      <div
        v-if="hasSectionHeader"
        class="flex items-center justify-between border-b border-neutral-border pb-3 mb-3 gap-4"
      >
        <div class="space-y-1.5">
          <div class="skeleton h-4 w-56 max-w-full"></div>
          <div class="skeleton h-3 w-72 max-w-full"></div>
        </div>
        <div class="skeleton h-3.5 w-32 shrink-0"></div>
      </div>

      <div class="w-full overflow-hidden">
        <div class="bg-navy px-3 py-2.5 flex items-center gap-3 border-b-2 border-navy-800">
          <div
            v-for="c in cols"
            :key="'th-' + c"
            class="h-3.5 rounded bg-white/25 animate-pulse"
            :style="{ width: getColWidth(c) }"
          ></div>
        </div>
        <div class="divide-y divide-neutral-border bg-white">
          <div
            v-for="r in rows"
            :key="'tr-' + r"
            class="px-3 py-2.5 flex items-center gap-3"
          >
            <div
              v-for="c in cols"
              :key="'td-' + r + '-' + c"
              class="skeleton h-3.5"
              :style="{ width: getColWidth(c) }"
            ></div>
          </div>
        </div>
      </div>

      <div
        v-if="hasPagination"
        class="p-3.5 border-t border-neutral-border flex flex-col sm:flex-row items-center justify-between gap-2 bg-white"
      >
        <div class="skeleton h-3.5 w-52"></div>
        <div class="flex items-center space-x-1">
          <div class="skeleton h-6 w-20"></div>
          <div class="skeleton h-3.5 w-24"></div>
          <div class="skeleton h-6 w-20"></div>
        </div>
      </div>
    </div>

    <!-- Stat Box Skeleton Mode -->
    <div v-else-if="type === 'stat'" class="grid gap-3" :class="statGridClass">
      <div
        v-for="s in (rows || 3)"
        :key="'stat-' + s"
        class="border border-neutral-border bg-white shadow-2xs"
        :class="compact ? 'p-3 space-y-1.5' : 'p-4 space-y-2'"
      >
        <div class="flex items-center justify-between">
          <div class="skeleton h-3 w-28"></div>
          <div v-if="showIcon" class="skeleton h-4 w-4"></div>
        </div>
        <div class="skeleton" :class="compact ? 'h-6 w-24' : 'h-7 w-28'"></div>
        <div v-if="showSubtitle" class="skeleton h-3 w-40 max-w-full"></div>
      </div>
    </div>

    <!-- Pengaturan / Form Skeleton Mode -->
    <div
      v-else-if="type === 'pengaturan' || type === 'form'"
      class="bg-white border border-neutral-border p-6 shadow-sm space-y-6"
    >
      <div class="border-b border-neutral-border pb-3 space-y-1.5">
        <div class="skeleton h-4 w-56 max-w-full"></div>
        <div class="skeleton h-3 w-80 max-w-full"></div>
      </div>

      <div class="space-y-4">
        <div
          v-for="d in 5"
          :key="'day-' + d"
          class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-stretch"
        >
          <div class="p-4 bg-neutral-50 border border-neutral-border space-y-3">
            <div class="flex items-center justify-between gap-2">
              <div class="skeleton h-4 w-16"></div>
              <div class="skeleton h-4 w-32"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <div class="skeleton h-3 w-20"></div>
                <div class="skeleton h-8 w-full"></div>
              </div>
              <div class="space-y-1.5">
                <div class="skeleton h-3 w-20"></div>
                <div class="skeleton h-8 w-full"></div>
              </div>
            </div>
          </div>

          <div
            v-if="d >= 2 && d <= 4"
            class="p-4 bg-navy-50/40 border border-navy-200/70 space-y-3"
          >
            <div class="flex items-center justify-between gap-2">
              <div class="skeleton h-3.5 w-48 max-w-full"></div>
              <div class="skeleton h-4 w-12"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div v-for="f in 4" :key="'flex-' + d + '-' + f" class="space-y-1.5">
                <div class="skeleton h-3 w-28"></div>
                <div class="skeleton h-8 w-full"></div>
              </div>
            </div>
            <div class="pt-2.5 border-t border-navy-200/50">
              <div class="skeleton h-3 w-full"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="pt-4 border-t border-neutral-border flex justify-end">
        <div class="skeleton h-9 w-52"></div>
      </div>
    </div>

    <!-- Generic Block Skeleton Mode -->
    <div v-else class="space-y-2">
      <div
        v-for="r in rows"
        :key="'line-' + r"
        class="skeleton h-4 w-full"
        :style="{ width: r === rows ? '60%' : '100%' }"
      ></div>
    </div>
  </div>
</template>
