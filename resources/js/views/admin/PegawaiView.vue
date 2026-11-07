<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-neutral-border pb-5 gap-4">
      <div>
        <h1 class="text-2xl font-bold text-navy tracking-tight">Manajemen Data Pegawai</h1>
        <p class="text-sm text-neutral-muted mt-0.5">
          Kelola master data pegawai Kementerian Hukum Kalimantan Selatan.
        </p>
      </div>

      <button
        @click="openAddModal"
        class="px-4 py-2 bg-navy text-white text-xs font-semibold hover:bg-navy-800 transition-colors flex items-center space-x-1.5 self-start sm:self-auto"
      >
        <UserPlus class="w-4 h-4" />
        <span>Tambah Pegawai</span>
      </button>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white border border-neutral-border p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="relative w-full sm:w-80">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari NIP atau Nama Pegawai..."
          class="w-full text-xs border border-neutral-border pl-8 pr-3 py-2 bg-white focus:outline-none focus:border-navy"
          @input="handleSearch"
        />
        <Search class="w-3.5 h-3.5 text-neutral-400 absolute left-2.5 top-2.5" />
      </div>

      <div class="flex items-center space-x-2 w-full sm:w-auto">
        <label class="text-xs text-neutral-600 font-medium whitespace-nowrap">Filter Divisi / Bagian:</label>
        <div class="w-56">
          <CustomDropdown
            v-model="filterDivisi"
            :options="[
              { value: '', label: 'Semua Divisi / Bagian' },
              ...divisiList.map(d => ({ value: d, label: d }))
            ]"
            placeholder="Semua Divisi / Bagian"
            @change="fetchPegawai(1)"
          />
        </div>
      </div>
    </div>

    <!-- Skeleton Loader -->
    <div v-if="loading" class="space-y-4">
      <SkeletonLoader type="table" :rows="8" :cols="5" />
    </div>

    <!-- Pegawai Table Card Container -->
    <div v-else class="bg-white border border-neutral-border shadow-sm flex flex-col">
      <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-280px)] min-h-[350px]">
        <table class="w-full text-left border-collapse text-xs">
          <thead class="sticky top-0 z-10 bg-navy text-white">
            <tr class="bg-navy text-white">
              <th class="py-3 px-3 font-semibold text-center w-12 border-r border-navy-800 sticky top-0 bg-navy">No</th>
              <th class="py-3 px-3 font-semibold w-48 border-r border-navy-800 sticky top-0 bg-navy whitespace-nowrap">NIP (18 Digit)</th>
              <th class="py-3 px-3 font-semibold border-r border-navy-800 sticky top-0 bg-navy">Nama Lengkap</th>
              <th class="py-3 px-3 font-semibold w-52 border-r border-navy-800 sticky top-0 bg-navy">Divisi / Bagian</th>
              <th class="py-3 px-3 font-semibold text-center w-28 border-r border-navy-800 sticky top-0 bg-navy whitespace-nowrap">Catatan Absensi</th>
              <th class="py-3 px-3 font-semibold text-center w-28 sticky top-0 bg-navy whitespace-nowrap">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-border">
            <tr
              v-for="(p, index) in pegawaiList"
              :key="p.nip"
              class="hover:bg-neutral-50 transition-colors"
            >
              <td class="py-2.5 px-3 text-center text-neutral-500 border-r border-neutral-border">
                {{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}
              </td>
              <td class="py-2.5 px-3 font-mono font-medium text-neutral-800 border-r border-neutral-border whitespace-nowrap">
                {{ p.nip }}
              </td>
              <td class="py-2.5 px-3 font-semibold text-navy border-r border-neutral-border">
                {{ p.nama }}
              </td>
              <td class="py-2.5 px-3 text-neutral-700 border-r border-neutral-border">
                {{ p.divisi }}
              </td>
              <td class="py-2.5 px-3 text-center border-r border-neutral-border whitespace-nowrap">
                <span
                  class="inline-flex items-center justify-center px-2 py-0.5 text-[11px] font-semibold border whitespace-nowrap"
                  :class="p.absensi_count > 0 ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-neutral-100 text-neutral-600 border-neutral-300'"
                >
                  {{ p.absensi_count }} kali
                </span>
              </td>
              <td class="py-2.5 px-3 text-center space-x-2 whitespace-nowrap">
                <button
                  @click="openEditModal(p)"
                  class="p-1 text-navy hover:text-blue-700 focus:outline-none"
                  title="Ubah Pegawai"
                >
                  <Edit3 class="w-4 h-4 inline" />
                </button>
                <button
                  @click="confirmDelete(p)"
                  class="p-1 text-red-600 hover:text-red-800 focus:outline-none"
                  title="Hapus Pegawai"
                >
                  <Trash2 class="w-4 h-4 inline" />
                </button>
              </td>
            </tr>
            <tr v-if="pegawaiList.length === 0">
              <td colspan="6" class="py-10 text-center text-neutral-muted">
                Tidak ada data pegawai yang sesuai dengan filter pencarian.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination / Footer -->
      <div class="p-3.5 border-t border-neutral-border flex flex-col sm:flex-row items-center justify-between text-xs text-neutral-600 bg-white gap-2">
        <div>
          Menampilkan <span class="font-semibold text-navy">{{ pegawaiList.length > 0 ? (pagination.current_page - 1) * pagination.per_page + 1 : 0 }}</span> - <span class="font-semibold text-navy">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span> dari <span class="font-semibold text-navy">{{ pagination.total }}</span> data pegawai
        </div>
        <div v-if="pagination.total > pagination.per_page" class="flex items-center space-x-1">
          <button
            :disabled="pagination.current_page <= 1"
            @click="fetchPegawai(pagination.current_page - 1)"
            class="px-2.5 py-1 border border-neutral-border hover:bg-neutral-100 disabled:opacity-40"
          >
            Sebelumnya
          </button>
          <span class="px-2 font-medium">Hal {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
          <button
            :disabled="pagination.current_page >= pagination.last_page"
            @click="fetchPegawai(pagination.current_page + 1)"
            class="px-2.5 py-1 border border-neutral-border hover:bg-neutral-100 disabled:opacity-40"
          >
            Berikutnya
          </button>
        </div>
      </div>
    </div>

    <!-- Add / Edit Pegawai Modal -->
    <Modal
      :show="showModal"
      :title="isEditing ? 'Ubah Data Pegawai' : 'Tambah Pegawai Baru'"
      max-width="md"
      @close="closeModal"
    >
      <form @submit.prevent="savePegawai" class="space-y-4">
        <!-- Error Alert inside modal -->
        <div
          v-if="formError"
          class="p-3 bg-red-50 border border-red-300 text-red-800 text-xs font-medium"
        >
          {{ formError }}
        </div>

        <div>
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
            NIP (18 Digit Angka) <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.nip"
            type="text"
            maxlength="18"
            required
            :disabled="isEditing"
            placeholder="Contoh: 198503152010121001"
            class="w-full text-xs font-mono border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy disabled:bg-neutral-100 text-neutral-900"
          />
          <p class="text-[11px] text-neutral-muted mt-1">NIP harus berupa 18 digit angka dan bersifat unik.</p>
        </div>

        <div>
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
            Nama Lengkap & Gelar <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.nama"
            type="text"
            required
            placeholder="Contoh: Ahmad Fauzi, S.H."
            class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
            Divisi / Bagian <span class="text-red-500">*</span>
          </label>
          <CustomDropdown
            v-model="form.divisi"
            :options="divisiList"
            placeholder="-- Pilih Divisi / Bagian --"
          />
        </div>

        <div class="pt-4 border-t border-neutral-border flex justify-end space-x-2">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 border border-neutral-border text-xs font-medium text-neutral-700 hover:bg-neutral-100"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="saving"
            class="px-4 py-2 bg-navy text-white text-xs font-semibold hover:bg-navy-800 disabled:opacity-50"
          >
            {{ saving ? 'Menyimpan...' : 'Simpan Data' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Konfirmasi Hapus Pegawai"
      max-width="sm"
      @close="showDeleteModal = false"
    >
      <div class="space-y-3">
        <p class="text-xs text-neutral-700">
          Apakah Anda yakin ingin menghapus data pegawai:
        </p>
        <div class="p-3 bg-neutral-50 border border-neutral-border text-xs">
          <p class="font-bold text-navy">{{ selectedToDelete?.nama }}</p>
          <p class="font-mono text-neutral-600">NIP: {{ selectedToDelete?.nip }}</p>
          <p class="text-neutral-600">Divisi / Bagian: {{ selectedToDelete?.divisi }}</p>
        </div>
        <p class="text-[11px] text-amber-800">
          * Catatan: Penghapusan akan ditolak otomatis jika pegawai masih memiliki catatan absensi bermasalah.
        </p>

        <div class="pt-4 border-t border-neutral-border flex justify-end space-x-2">
          <button
            type="button"
            @click="showDeleteModal = false"
            class="px-3 py-2 border border-neutral-border text-xs font-medium text-neutral-700 hover:bg-neutral-100"
          >
            Batal
          </button>
          <button
            type="button"
            :disabled="deleting"
            @click="executeDelete"
            class="px-3 py-2 bg-red-700 text-white text-xs font-semibold hover:bg-red-800 disabled:opacity-50"
          >
            {{ deleting ? 'Menghapus...' : 'Ya, Hapus Pegawai' }}
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
import { ref, reactive, onMounted } from 'vue'
import SkeletonLoader from '@/components/SkeletonLoader.vue'
import Modal from '@/components/Modal.vue'
import CustomDropdown from '@/components/CustomDropdown.vue'
import Toast from '@/components/Toast.vue'
import api from '@/composables/useApi'
import { UserPlus, Search, Edit3, Trash2 } from 'lucide-vue-next'

const loading = ref(true)
const pegawaiList = ref([])
const searchQuery = ref('')
const filterDivisi = ref('')

const divisiList = [
  'Pelayanan Hukum',
  'P3H',
  'Tata Usaha dan Umum',
]

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

// Modal & Form State
const showModal = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const formError = ref('')
const form = reactive({
  nip: '',
  nama: '',
  divisi: '',
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
    fetchPegawai(1)
  }, 300)
}

async function fetchPegawai(page = 1) {
  loading.value = true
  try {
    const params = {
      page,
      per_page: pagination.per_page,
      q: searchQuery.value || undefined,
      divisi: filterDivisi.value || undefined,
    }
    const res = await api.get('/pegawai', { params })
    pegawaiList.value = res.data.data
    pagination.current_page = res.data.meta.current_page
    pagination.last_page = res.data.meta.last_page
    pagination.total = res.data.meta.total
  } catch (err) {
    console.error('Gagal mengambil data pegawai:', err)
  } finally {
    loading.value = false
  }
}

function openAddModal() {
  isEditing.value = false
  formError.value = ''
  form.nip = ''
  form.nama = ''
  form.divisi = ''
  showModal.value = true
}

function openEditModal(p) {
  isEditing.value = true
  formError.value = ''
  form.nip = p.nip
  form.nama = p.nama
  form.divisi = p.divisi
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

async function savePegawai() {
  formError.value = ''
  saving.value = true
  try {
    if (isEditing.value) {
      await api.put(`/pegawai/${form.nip}`, {
        nama: form.nama,
        divisi: form.divisi,
      })
      notify('Data pegawai berhasil diperbarui.')
    } else {
      await api.post('/pegawai', {
        nip: form.nip,
        nama: form.nama,
        divisi: form.divisi,
      })
      notify('Pegawai baru berhasil ditambahkan.')
    }
    showModal.value = false
    fetchPegawai(pagination.current_page)
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) {
      formError.value = Object.values(errors).flat().join(' ')
    } else {
      formError.value = err.response?.data?.message || 'Terjadi kesalahan saat menyimpan data.'
    }
  } finally {
    saving.value = false
  }
}

function confirmDelete(p) {
  selectedToDelete.value = p
  showDeleteModal.value = true
}

async function executeDelete() {
  if (!selectedToDelete.value) return
  deleting.value = true
  try {
    await api.delete(`/pegawai/${selectedToDelete.value.nip}`)
    notify('Data pegawai berhasil dihapus.')
    showDeleteModal.value = false
    fetchPegawai(pagination.current_page)
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal menghapus pegawai.'
    notify(msg, 'error')
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  fetchPegawai()
})
</script>
