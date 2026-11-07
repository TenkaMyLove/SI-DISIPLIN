<template>
  <div class="space-y-3.5">
    <!-- Header: Compact without bottom border line to prevent sinking -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">Manajemen Divisi / Bagian</h1>
        <p class="text-xs sm:text-sm text-neutral-muted mt-0.5">
          Kelola master data divisi dan unit kerja Kementerian Hukum Kalimantan Selatan.
        </p>
      </div>

      <button
        @click="openAddModal"
        class="px-4 py-2 bg-navy text-white text-xs font-semibold hover:bg-navy-800 transition-colors flex items-center space-x-1.5 self-start sm:self-auto shadow-xs shrink-0"
      >
        <FolderPlus class="w-4 h-4" />
        <span>Tambah Divisi</span>
      </button>
    </div>

    <!-- Filter & Search Bar: Compact and seamless -->
    <div class="bg-white border border-neutral-border p-3 sm:px-4 sm:py-2.5 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-2xs">
      <div class="relative w-full sm:w-80">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari Nama atau Kode Divisi..."
          class="w-full text-xs border border-neutral-border pl-8 pr-3 py-1.5 bg-white focus:outline-none focus:border-navy"
          @input="handleSearch"
        />
        <Search class="w-3.5 h-3.5 text-neutral-400 absolute left-2.5 top-2.5" />
      </div>

      <div class="text-xs text-neutral-600 font-medium self-end sm:self-auto">
        Total: <strong class="text-navy">{{ divisiList.length }}</strong> Divisi / Bagian
      </div>
    </div>

    <!-- Skeleton Loader -->
    <div v-if="loading" class="space-y-4">
      <SkeletonLoader type="table" :rows="5" :cols="6" />
    </div>

    <!-- Divisi Table Card Container -->
    <div v-else class="bg-white border border-neutral-border shadow-sm flex flex-col">
      <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-250px)]">
        <table class="w-full text-left border-separate border-spacing-0 text-xs">
          <thead class="sticky top-0 z-20 bg-navy text-white">
            <tr class="bg-navy text-white">
              <th class="py-2.5 px-3 font-semibold text-center w-12 border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy text-white">No</th>
              <th class="py-2.5 px-3 font-semibold w-36 border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy text-white whitespace-nowrap">Kode</th>
              <th class="py-2.5 px-3 font-semibold border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy text-white">Nama Divisi / Bagian</th>
              <th class="py-2.5 px-3 font-semibold border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy text-white">Deskripsi</th>
              <th class="py-2.5 px-3 font-semibold text-center w-36 border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy text-white whitespace-nowrap">Jumlah Pegawai</th>
              <th class="py-2.5 px-3 font-semibold text-center w-28 border-b-2 border-navy-800 sticky top-0 z-20 bg-navy text-white whitespace-nowrap">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-border bg-white">
            <tr
              v-for="(d, index) in filteredDivisi"
              :key="d.id"
              class="hover:bg-neutral-50 transition-colors"
            >
              <td class="py-2.5 px-3 text-center text-neutral-500 border-b border-r border-neutral-border bg-inherit">
                {{ index + 1 }}
              </td>
              <td class="py-2.5 px-3 font-mono font-medium text-neutral-800 border-b border-r border-neutral-border whitespace-nowrap bg-inherit">
                <span v-if="d.kode" class="inline-block bg-neutral-100 text-neutral-700 px-2 py-0.5 border border-neutral-300 font-mono text-[11px] font-semibold">
                  {{ d.kode }}
                </span>
                <span v-else class="text-neutral-400 italic text-[11px]">-</span>
              </td>
              <td class="py-2.5 px-3 font-semibold text-navy border-b border-r border-neutral-border bg-inherit">
                {{ d.nama }}
              </td>
              <td class="py-2.5 px-3 text-neutral-600 border-b border-r border-neutral-border bg-inherit">
                {{ d.deskripsi || '-' }}
              </td>
              <td class="py-2.5 px-3 text-center border-b border-r border-neutral-border whitespace-nowrap bg-inherit">
                <span
                  class="inline-flex items-center justify-center px-2 py-0.5 text-[11px] font-semibold border whitespace-nowrap"
                  :class="d.pegawai_count > 0 ? 'bg-blue-50 text-blue-800 border-blue-300' : 'bg-neutral-100 text-neutral-600 border-neutral-300'"
                >
                  {{ d.pegawai_count }} pegawai
                </span>
              </td>
              <td class="py-2.5 px-3 text-center space-x-2 whitespace-nowrap border-b border-neutral-border bg-inherit">
                <button
                  @click="openEditModal(d)"
                  class="p-1 text-navy hover:text-blue-700 focus:outline-none"
                  title="Ubah Divisi"
                >
                  <Edit3 class="w-4 h-4 inline" />
                </button>
                <button
                  @click="confirmDelete(d)"
                  class="p-1 text-red-600 hover:text-red-800 focus:outline-none"
                  title="Hapus Divisi"
                >
                  <Trash2 class="w-4 h-4 inline" />
                </button>
              </td>
            </tr>
            <tr v-if="filteredDivisi.length === 0">
              <td colspan="6" class="py-8 text-center text-neutral-500 italic bg-inherit">
                Tidak ada data divisi / bagian yang ditemukan.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Tambah / Edit Divisi -->
    <Modal
      :show="showModal"
      :title="isEditing ? 'Ubah Data Divisi / Bagian' : 'Tambah Divisi / Bagian Baru'"
      @close="closeModal"
    >
      <form @submit.prevent="saveDivisi" class="space-y-4">
        <!-- Error Alert -->
        <div v-if="formError" class="p-3 bg-red-50 border border-red-300 text-red-700 text-xs">
          {{ formError }}
        </div>

        <div>
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
            Nama Divisi / Bagian <span class="text-red-600">*</span>
          </label>
          <input
            v-model="form.nama"
            type="text"
            required
            maxlength="100"
            placeholder="Contoh: Pelayanan Hukum"
            class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
            Kode Singkatan <span class="text-neutral-400 font-normal lowercase">(opsional)</span>
          </label>
          <input
            v-model="form.kode"
            type="text"
            maxlength="50"
            placeholder="Contoh: YANKUM"
            class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy font-mono"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
            Deskripsi / Keterangan <span class="text-neutral-400 font-normal lowercase">(opsional)</span>
          </label>
          <textarea
            v-model="form.deskripsi"
            rows="3"
            maxlength="255"
            placeholder="Keterangan tugas atau unit kerja..."
            class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy"
          ></textarea>
        </div>

        <div class="flex justify-end space-x-2 pt-3 border-t border-neutral-border">
          <button
            type="button"
            @click="closeModal"
            class="px-3 py-2 border border-neutral-border text-neutral-700 text-xs font-semibold hover:bg-neutral-100"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="saving"
            class="px-4 py-2 bg-navy text-white text-xs font-semibold hover:bg-navy-800 disabled:opacity-50"
          >
            {{ saving ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Simpan Divisi') }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Modal Konfirmasi Hapus Divisi -->
    <Modal
      :show="showDeleteModal"
      title="Konfirmasi Hapus Divisi"
      @close="showDeleteModal = false"
    >
      <div class="space-y-4">
        <p class="text-xs text-neutral-700 leading-relaxed">
          Apakah Anda yakin ingin menghapus divisi <strong>"{{ selectedToDelete?.nama }}"</strong>?
        </p>

        <div v-if="selectedToDelete?.pegawai_count > 0" class="p-3 bg-amber-50 border border-amber-300 text-amber-800 text-xs">
          <strong>Perhatian:</strong> Divisi ini masih memiliki <strong>{{ selectedToDelete.pegawai_count }} pegawai</strong> terdaftar. Divisi tidak dapat dihapus sebelum pegawai dipindahkan ke divisi lain.
        </div>

        <p v-else class="text-xs text-neutral-500">
          Tindakan ini tidak dapat dibatalkan setelah divisi dihapus dari sistem.
        </p>

        <div class="flex justify-end space-x-2 pt-3 border-t border-neutral-border">
          <button
            type="button"
            @click="showDeleteModal = false"
            class="px-3 py-2 border border-neutral-border text-neutral-700 text-xs font-semibold hover:bg-neutral-100"
          >
            Batal
          </button>
          <button
            type="button"
            :disabled="deleting || selectedToDelete?.pegawai_count > 0"
            @click="executeDelete"
            class="px-3 py-2 bg-red-700 text-white text-xs font-semibold hover:bg-red-800 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ deleting ? 'Menghapus...' : 'Ya, Hapus Divisi' }}
          </button>
        </div>
      </div>
    </Modal>

    <!-- Toast Notification -->
    <Toast
      :show="toast.show"
      :message="toast.message"
      :variant="toast.variant"
      @update:show="toast.show = $event"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import SkeletonLoader from '@/components/SkeletonLoader.vue'
import Modal from '@/components/Modal.vue'
import Toast from '@/components/Toast.vue'
import api from '@/composables/useApi'
import { FolderPlus, Search, Edit3, Trash2 } from 'lucide-vue-next'

const loading = ref(true)
const divisiList = ref([])
const searchQuery = ref('')

// Modal & Form State
const showModal = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const formError = ref('')
const form = reactive({
  id: null,
  nama: '',
  kode: '',
  deskripsi: '',
})

// Delete State
const showDeleteModal = ref(false)
const deleting = ref(false)
const selectedToDelete = ref(null)

// Toast
const toast = reactive({
  show: false,
  message: '',
  variant: 'success',
})

function notify(message, variant = 'success') {
  toast.message = message
  toast.variant = variant
  toast.show = true
}

let searchTimer = null
function handleSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    fetchDivisi()
  }, 300)
}

const filteredDivisi = computed(() => {
  if (!searchQuery.value) return divisiList.value
  const q = searchQuery.value.toLowerCase().trim()
  return divisiList.value.filter(d => 
    (d.nama && d.nama.toLowerCase().includes(q)) ||
    (d.kode && d.kode.toLowerCase().includes(q)) ||
    (d.deskripsi && d.deskripsi.toLowerCase().includes(q))
  )
})

async function fetchDivisi() {
  loading.value = true
  try {
    const res = await api.get('/divisi', {
      params: { q: searchQuery.value || undefined }
    })
    divisiList.value = res.data.data
  } catch (err) {
    console.error('Gagal memuat divisi:', err)
    notify('Gagal memuat daftar divisi.', 'error')
  } finally {
    loading.value = false
  }
}

function openAddModal() {
  isEditing.value = false
  form.id = null
  form.nama = ''
  form.kode = ''
  form.deskripsi = ''
  formError.value = ''
  showModal.value = true
}

function openEditModal(d) {
  isEditing.value = true
  form.id = d.id
  form.nama = d.nama
  form.kode = d.kode || ''
  form.deskripsi = d.deskripsi || ''
  formError.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  formError.value = ''
}

async function saveDivisi() {
  formError.value = ''
  saving.value = true

  try {
    const payload = {
      nama: form.nama.trim(),
      kode: form.kode ? form.kode.trim() : null,
      deskripsi: form.deskripsi ? form.deskripsi.trim() : null,
    }

    if (isEditing.value) {
      await api.put(`/divisi/${form.id}`, payload)
      notify('Data divisi berhasil diperbarui.')
    } else {
      await api.post('/divisi', payload)
      notify('Divisi baru berhasil ditambahkan.')
    }

    showModal.value = false
    fetchDivisi()
  } catch (err) {
    if (err.response?.status === 422 && err.response?.data?.errors) {
      const errors = err.response.data.errors
      formError.value = Object.values(errors).flat().join(' ')
    } else {
      formError.value = err.response?.data?.message || 'Terjadi kesalahan saat menyimpan divisi.'
    }
  } finally {
    saving.value = false
  }
}

function confirmDelete(d) {
  selectedToDelete.value = d
  showDeleteModal.value = true
}

async function executeDelete() {
  if (!selectedToDelete.value) return
  deleting.value = true
  try {
    await api.delete(`/divisi/${selectedToDelete.value.id}`)
    notify('Divisi berhasil dihapus.')
    showDeleteModal.value = false
    fetchDivisi()
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal menghapus divisi.'
    notify(msg, 'error')
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  fetchDivisi()
})
</script>
