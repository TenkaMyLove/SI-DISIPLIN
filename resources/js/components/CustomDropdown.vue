<template>
  <div ref="dropdownRef" class="relative inline-block w-full text-xs">
    <!-- Optional label -->
    <label v-if="label" class="block font-semibold uppercase tracking-wider text-neutral-700 mb-1 text-[11px]">
      {{ label }} <span v-if="required" class="text-red-600">*</span>
    </label>

    <!-- Trigger button (Styled exactly like Figma reference) -->
    <button
      type="button"
      @click="toggle"
      :disabled="disabled"
      class="w-full bg-white border border-neutral-300 rounded-lg px-3 py-2 text-left flex items-center justify-between text-xs transition-all hover:border-neutral-400 focus:outline-none focus:ring-1 focus:ring-navy disabled:opacity-50 disabled:cursor-not-allowed shadow-2xs"
      :class="[
        isOpen ? 'border-navy ring-1 ring-navy' : '',
        triggerClass
      ]"
      aria-haspopup="listbox"
      :aria-expanded="isOpen"
    >
      <span class="truncate pr-2 font-medium" :class="selectedItem ? 'text-neutral-900' : 'text-neutral-400'">
        {{ selectedItem ? selectedItem.label : (placeholder || 'Pilih...') }}
      </span>

      <div class="flex items-center space-x-1 shrink-0">
        <!-- Clear button (if clearable and has value) -->
        <button
          v-if="clearable && modelValue"
          type="button"
          @click.stop="clearValue"
          class="w-5 h-5 text-neutral-400 hover:text-navy hover:bg-neutral-100 rounded flex items-center justify-center transition-colors"
          title="Reset pilihan"
        >
          <X class="w-3 h-3" />
        </button>

        <!-- Enclosed rounded chevron button (as shown in Figma mockup) -->
        <span
          class="w-6 h-6 bg-neutral-100 rounded-md text-neutral-600 hover:text-navy flex-shrink-0 flex items-center justify-center transition-colors"
        >
          <ChevronUp v-if="isOpen" class="w-3.5 h-3.5" />
          <ChevronDown v-else class="w-3.5 h-3.5" />
        </span>
      </div>
    </button>

    <!-- Floating Dropdown Menu (Detached rounded card with soft shadow) -->
    <transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div
        v-if="isOpen"
        class="absolute z-50 w-full mt-1.5 bg-white rounded-lg border border-neutral-200 shadow-xl overflow-hidden py-1 max-h-64 flex flex-col"
        :class="menuClass"
        role="listbox"
      >
        <!-- Search Box (Auto-complete search input) -->
        <div v-if="searchable" class="p-2 border-b border-neutral-100 bg-neutral-50/80 sticky top-0 z-10">
          <div class="relative">
            <input
              ref="searchInputRef"
              v-model="searchQuery"
              type="text"
              :placeholder="searchPlaceholder || 'Cari...'"
              class="w-full text-xs border border-neutral-300 rounded-md pl-7 pr-6 py-1.5 bg-white focus:outline-none focus:border-navy focus:ring-1 focus:ring-navy placeholder:text-neutral-400"
              @click.stop
              @keydown.stop="handleSearchKeydown"
            />
            <Search class="w-3.5 h-3.5 text-neutral-400 absolute left-2 top-2 pointer-events-none" />
            <button
              v-if="searchQuery"
              type="button"
              @click.stop="searchQuery = ''"
              class="w-4 h-4 absolute right-2 top-2 text-neutral-400 hover:text-navy flex items-center justify-center transition-colors"
              title="Bersihkan teks"
            >
              <X class="w-3 h-3" />
            </button>
          </div>
        </div>

        <!-- Options List -->
        <div class="overflow-y-auto max-h-52 divide-y divide-neutral-50">
          <div
            v-for="opt in filteredOptions"
            :key="opt.value"
            @click="select(opt.value)"
            class="px-3 py-2 text-xs cursor-pointer flex items-center space-x-2.5 transition-colors select-none"
            :class="opt.value === modelValue ? 'bg-neutral-100 text-navy font-semibold' : 'text-neutral-700 hover:bg-neutral-50'"
            role="option"
            :aria-selected="opt.value === modelValue"
          >
            <!-- Square Check Indicator (exactly as shown in Figma reference) -->
            <span
              class="w-4 h-4 rounded border flex items-center justify-center flex-shrink-0 transition-colors"
              :class="opt.value === modelValue ? 'border-navy bg-navy text-white' : 'border-neutral-300 bg-white'"
            >
              <Check v-if="opt.value === modelValue" class="w-2.5 h-2.5 stroke-[3]" />
            </span>

            <span class="truncate">{{ opt.label }}</span>
          </div>

          <div v-if="filteredOptions.length === 0" class="px-3 py-3 text-xs text-neutral-400 text-center">
            Pilihan tidak ditemukan
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { ChevronDown, ChevronUp, Check, Search, X } from 'lucide-vue-next'

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    default: '',
  },
  options: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Pilih...',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  required: {
    type: Boolean,
    default: false,
  },
  triggerClass: {
    type: String,
    default: '',
  },
  menuClass: {
    type: String,
    default: '',
  },
  searchable: {
    type: Boolean,
    default: false,
  },
  searchPlaceholder: {
    type: String,
    default: 'Cari...',
  },
  clearable: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue', 'change'])

const isOpen = ref(false)
const dropdownRef = ref(null)
const searchInputRef = ref(null)
const searchQuery = ref('')

// Normalize options to [{ value: '', label: '' }]
const normalizedOptions = computed(() => {
  return props.options.map((opt) => {
    if (typeof opt === 'string' || typeof opt === 'number') {
      return { value: opt, label: String(opt) }
    }
    return {
      value: opt.value ?? '',
      label: opt.label ?? String(opt.value ?? ''),
    }
  })
})

const filteredOptions = computed(() => {
  if (!props.searchable || !searchQuery.value.trim()) {
    return normalizedOptions.value
  }
  const q = searchQuery.value.toLowerCase().trim()
  return normalizedOptions.value.filter((opt) => {
    const labelMatch = opt.label && String(opt.label).toLowerCase().includes(q)
    const valMatch = opt.value && String(opt.value).toLowerCase().includes(q)
    return labelMatch || valMatch
  })
})

const selectedItem = computed(() => {
  return normalizedOptions.value.find((opt) => opt.value === props.modelValue)
})

watch(isOpen, (open) => {
  if (open) {
    searchQuery.value = ''
    if (props.searchable) {
      nextTick(() => {
        searchInputRef.value?.focus()
      })
    }
  }
})

function toggle() {
  if (props.disabled) return
  isOpen.value = !isOpen.value
}

function close() {
  isOpen.value = false
  searchQuery.value = ''
}

function select(val) {
  emit('update:modelValue', val)
  emit('change', val)
  close()
}

function clearValue() {
  select('')
}

function handleSearchKeydown(e) {
  if (e.key === 'Enter') {
    e.preventDefault()
    if (filteredOptions.value.length > 0) {
      select(filteredOptions.value[0].value)
    }
  } else if (e.key === 'Escape') {
    close()
  }
}

// Click outside handler
function handleClickOutside(event) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    close()
  }
}

// Escape key handler
function handleKeydown(event) {
  if (event.key === 'Escape' && isOpen.value) {
    close()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})
</script>