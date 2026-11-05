<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-neutral-border pb-5 gap-4">
      <div>
        <h1 class="text-2xl font-bold text-navy tracking-tight">Manajemen Absensi Bermasalah</h1>
        <p class="text-sm text-neutral-muted mt-0.5">
          Catat dan kelola pegawai yang tidak masuk kerja tanpa keterangan, terlambat, maupun pulang duluan.
        </p>
      </div>

      <button
        @click="openAddModal"
        class="px-4 py-2 bg-navy text-white text-xs font-semibold hover:bg-navy-800 transition-colors flex items-center space-x-1.5 self-start sm:self-auto"
      >
        <Plus class="w-4 h-4" />
        <span>Tambah Catatan Absensi</span>
      </button>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white border border-neutral-border p-4 grid grid-cols-1 sm:grid-cols-4 gap-4">
      <div class="relative sm:col-span-2">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari NIP atau Nama Pegawai..."
          class="w-full text-xs border border-neutral-border pl-8 pr-3 py-2 bg-white focus:outline-none focus:border-navy"
          @input="handleSearch"
        />
        <Search class="w-3.5 h-3.5 text-neutral-400 absolute left-2.5 top-2.5" />
      </div>

      <div>
        <select
          v-model="filterStatus"
          class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy"
          @change="fetchAbsensi(1)"
        >
          <option value="">Semua Status</option>
          <option value="Tanpa Keterangan">Tanpa Keterangan</option>
          <option value="Terlambat">Terlambat</option>
          <option value="Pulang Duluan">Pulang Duluan</option>
        </select>
      </div>

      <div>
        <input
          v-model="filterTanggal"
          type="date"
          class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy"
          @change="fetchAbsensi(1)"
        />
      </div>
    </div>

    <!-- Skeleton Loader -->
    <div v-if="loading" class="space-y-4">
      <SkeletonLoader type="table" :rows="8" :cols="8" />
    </div>

    <!-- Absensi Table -->
    <div v-else class="bg-white border border-neutral-border overflow-x-auto shadow-sm">
      <table class="w-full text-left border-collapse text-xs">
        <thead class="sticky top-0 z-10">
          <tr class="bg-navy text-white">
            <th class="py-3 px-3 font-semibold text-center w-12 border-r border-navy-800">No</th>
            <th class="py-3 px-3 font-semibold w-28 border-r border-navy-800">Tanggal</th>
            <th class="py-3 px-3 font-semibold w-44 border-r border-navy-800">NIP</th>
            <th class="py-3 px-3 font-semibold border-r border-navy-800">Nama Pegawai</th>
            <th class="py-3 px-2 font-semibold text-center w-28 border-r border-navy-800">Masuk - Pulang</th>
            <th class="py-3 px-3 font-semibold w-36 border-r border-navy-800">Status</th>
            <th class="py-3 px-3 font-semibold border-r border-navy-800">Alasan</th>
            <th class="py-3 px-3 font-semibold text-right w-32 border-r border-navy-800">Kekurangan</th>
            <th class="py-3 px-3 font-semibold text-center w-24">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-neutral-border">
          <tr
            v-for="(rec, index) in absensiList"
            :key="rec.id"
            class="hover:bg-neutral-50 transition-colors"
          >
            <td class="py-2.5 px-3 text-center text-neutral-500 border-r border-neutral-border">
              {{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}
            </td>
            <td class="py-2.5 px-3 font-medium text-neutral-800 border-r border-neutral-border whitespace-nowrap">
              {{ rec.periode_formatted }}
            </td>
            <td class="py-2.5 px-3 font-mono text-neutral-700 border-r border-neutral-border whitespace-nowrap">
              {{ rec.nip }}
            </td>
            <td class="py-2.5 px-3 font-semibold text-navy border-r border-neutral-border">
              {{ rec.pegawai?.nama }}
            </td>
            <td class="py-2.5 px-2 text-center text-neutral-800 border-r border-neutral-border whitespace-nowrap">
              {{ rec.jam_masuk?.substring(0, 5) }} &ndash; {{ rec.jam_pulang?.substring(0, 5) }}
            </td>
            <td class="py-2.5 px-3 border-r border-neutral-border">
              <span
                class="px-2 py-0.5 text-[11px] font-semibold border"
                :class="getStatusBadgeClass(rec.status)"
              >
                {{ rec.status }}
              </span>
            </td>
            <td class="py-2.5 px-3 text-neutral-700 border-r border-neutral-border">
              {{ rec.alasan || '-' }}
            </td>
            <td class="py-2.5 px-3 text-right font-medium text-red-700 border-r border-neutral-border whitespace-nowrap">
              {{ rec.durasi_formatted }}
              <span class="text-[10px] text-neutral-400 block">({{ rec.durasi_menit }} mnt)</span>
            </td>
            <td class="py-2.5 px-3 text-center space-x-2">
              <button
                @click="openEditModal(rec)"
                class="p-1 text-navy hover:text-blue-700 focus:outline-none"
                title="Ubah Catatan"
              >
                <Edit3 class="w-4 h-4 inline" />
              </button>
              <button
                @click="confirmDelete(rec)"
                class="p-1 text-red-600 hover:text-red-800 focus:outline-none"
                title="Hapus Catatan"
              >
                <Trash2 class="w-4 h-4 inline" />
              </button>
            </td>
          </tr>
          <tr v-if="absensiList.length === 0">
            <td colspan="9" class="py-10 text-center text-neutral-muted">
              Tidak ada catatan absensi yang sesuai filter.
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page" class="p-4 border-t border-neutral-border flex items-center justify-between text-xs text-neutral-600">
        <div>
          Menampilkan {{ (pagination.current_page - 1) * pagination.per_page + 1 }} - {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} dari {{ pagination.total }} catatan
        </div>
        <div class="flex items-center space-x-1">
          <button
            :disabled="pagination.current_page <= 1"
            @click="fetchAbsensi(pagination.current_page - 1)"
            class="px-2.5 py-1 border border-neutral-border hover:bg-neutral-100 disabled:opacity-40"
          >
            Sebelumnya
          </button>
          <span class="px-2 font-medium">Hal {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
          <button
            :disabled="pagination.current_page >= pagination.last_page"
            @click="fetchAbsensi(pagination.current_page + 1)"
            class="px-2.5 py-1 border border-neutral-border hover:bg-neutral-100 disabled:opacity-40"
          >
            Berikutnya
          </button>
        </div>
      </div>
    </div>

    <!-- Add / Edit Modal (ABS-1 to ABS-14) -->
    <Modal
      :show="showModal"
      :title="isEditing ? 'Ubah Catatan Absensi' : 'Tambah Catatan Absensi Bermasalah'"
      max-width="lg"
      @close="closeModal"
    >
      <form @submit.prevent="saveAbsensi" class="space-y-4">
        <!-- Error Alert -->
        <div
          v-if="formError"
          class="p-3 bg-red-50 border border-red-300 text-red-800 text-xs font-medium"
        >
          {{ formError }}
        </div>

        <!-- Autocomplete Pegawai (ABS-2: Nama and NIP dropdown searchable with data from pegawai table, selecting one fills other automatically) -->
        <div class="relative">
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
            Pegawai (Cari Nama atau NIP) <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <input
              v-model="pegawaiInputText"
              type="text"
              required
              placeholder="Ketik NIP atau Nama Pegawai..."
              class="w-full text-xs border border-neutral-border pl-8 pr-8 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
              @focus="showPegawaiDropdown = true"
              @input="handlePegawaiInput"
            />
            <Search class="w-3.5 h-3.5 text-neutral-400 absolute left-2.5 top-2.5" />
            <button
              v-if="form.nip"
              type="button"
              @click="clearSelectedPegawai"
              class="absolute right-2.5 top-2.5 text-neutral-400 hover:text-navy"
            >
              <X class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Dropdown Options -->
          <div
            v-if="showPegawaiDropdown"
            class="absolute z-20 w-full mt-1 bg-white border border-neutral-border shadow-lg max-h-48 overflow-y-auto divide-y divide-neutral-100"
          >
            <div
              v-for="p in filteredPegawai"
              :key="p.nip"
              @click="selectPegawai(p)"
              class="p-2.5 text-xs hover:bg-neutral-50 cursor-pointer flex justify-between items-center"
            >
              <div>
                <p class="font-semibold text-navy">{{ p.nama }}</p>
                <p class="text-[11px] font-mono text-neutral-500">NIP: {{ p.nip }} &bull; {{ p.divisi }}</p>
              </div>
              <Check v-if="form.nip === p.nip" class="w-4 h-4 text-navy" />
            </div>
            <div v-if="filteredPegawai.length === 0" class="p-3 text-xs text-neutral-muted text-center">
              Pegawai tidak ditemukan. Pastikan sudah didaftarkan pada Data Pegawai.
            </div>
          </div>
        </div>

        <!-- Periode (ABS-3: DD-MM-YYYY) -->
        <div>
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
            Tanggal (Periode) <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.periode"
            type="date"
            required
            class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
          />
        </div>

        <!-- Status Absensi (ABS-4) -->
        <div>
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
            Status Kehadiran Bermasalah <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.status"
            required
            class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900 font-medium"
            @change="triggerAutoCalculate"
          >
            <option value="Tanpa Keterangan">Tanpa Keterangan (Sanksi 7,5 Jam Kerja)</option>
            <option value="Terlambat">Terlambat</option>
            <option value="Pulang Duluan">Pulang Duluan</option>
          </select>
        </div>

        <!-- Jam Masuk & Jam Pulang (ABS-14: Mandatory on all statuses including Tanpa Keterangan) -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
              Jam Masuk <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.jam_masuk"
              type="time"
              required
              class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
              @change="triggerAutoCalculate"
            />
            <p class="text-[10px] text-neutral-muted mt-0.5">Standar: 08:00 WITA</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
              Jam Pulang <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.jam_pulang"
              type="time"
              required
              class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
              @change="triggerAutoCalculate"
            />
            <p class="text-[10px] text-neutral-muted mt-0.5">Standar: 16:00 WITA</p>
          </div>
        </div>

        <!-- Durasi Keterlambatan (ABS-5, ABS-6, ABS-9: auto-calculated, but Admin can edit manually) -->
        <div class="p-3 bg-neutral-50 border border-neutral-border space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider">
              Durasi Kekurangan Jam Kerja (Menit) <span class="text-red-500">*</span>
            </label>
            <span class="text-xs font-bold text-red-700">
              {{ computedDurasiHuman }}
            </span>
          </div>
          <div class="flex items-center space-x-2">
            <input
              v-model.number="form.durasi_menit"
              type="number"
              min="0"
              required
              class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
            />
            <button
              type="button"
              @click="recalculateDuration"
              class="px-2.5 py-2 border border-neutral-border bg-white text-[11px] font-medium text-navy hover:bg-neutral-100 whitespace-nowrap"
              title="Hitung ulang otomatis berdasarkan jam standar"
            >
              Hitung Ulang
            </button>
          </div>
          <p class="text-[11px] text-neutral-muted">
            * Dihitung otomatis oleh sistem namun dapat diubah secara manual oleh Admin bila diperlukan.
          </p>
        </div>

        <!-- Alasan (ABS-8: Free text typed manually) -->
        <div>
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
            Alasan Keterlambatan / Keterangan (Teks Bebas)
          </label>
          <textarea
            v-model="form.alasan"
            rows="2"
            placeholder="Ketik keterangan atau alasan izin bila ada (misal: Ban bocor, urusan keluarga mendesak, dll)..."
            class="w-full text-xs border border-neutral-border p-3 bg-white focus:outline-none focus:border-navy text-neutral-900"
          ></textarea>
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
            {{ saving ? 'Menyimpan...' : 'Simpan Catatan' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Konfirmasi Hapus Catatan Absensi"
      max-width="sm"
      @close="showDeleteModal = false"
    >
      <div class="space-y-3">
        <p class="text-xs text-neutral-700">
          Apakah Anda yakin ingin menghapus catatan absensi bermasalah ini?
        </p>
        <div class="p-3 bg-neutral-50 border border-neutral-border text-xs">
          <p class="font-bold text-navy">{{ selectedToDelete?.pegawai?.nama }}</p>
          <p class="text-neutral-600">Tanggal: {{ selectedToDelete?.periode_formatted }}</p>
          <p class="text-neutral-600">Status: {{ selectedToDelete?.status }} ({{ selectedToDelete?.durasi_formatted }})</p>
        </div>
        <p class="text-[11px] text-neutral-muted">
          Catatan yang dihapus akan otomatis dikeluarkan dari akumulasi rekapitulasi.
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
            {{ deleting ? 'Menghapus...' : 'Ya, Hapus' }}
          </button>
        </div>
      </div>
    </Modal>

    <!-- Toast -->
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
import { Plus, Search, Edit3, Trash2, X, Check } from 'lucide-vue-next'

const loading = ref(true)
const absensiList = ref([])
const pegawaiOptions = ref([])

const searchQuery = ref('')
const filterStatus = ref('')
const filterTanggal = ref('')

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

// Modal & Form State
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const saving = ref(false)
const formError = ref('')

const form = reactive({
  nip: '',
  periode: new Date().toISOString().substring(0, 10),
  jam_masuk: '08:00',
  jam_pulang: '16:00',
  status: 'Tanpa Keterangan',
  alasan: '',
  durasi_menit: 450,
})

// Autocomplete State
const pegawaiInputText = ref('')
const showPegawaiDropdown = ref(false)

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

const filteredPegawai = computed(() => {
  if (!pegawaiInputText.value) return pegawaiOptions.value
  const q = pegawaiInputText.value.toLowerCase()
  return pegawaiOptions.value.filter(
    (p) => p.nama.toLowerCase().includes(q) || p.nip.includes(q)
  )
})

const computedDurasiHuman = computed(() => {
  const m = Number(form.durasi_menit) || 0
  if (m <= 0) return '0 menit'
  const hari = Math.floor(m / 1440)
  const remM = m % 1440
  const jam = Math.floor(remM / 60)
  const menit = remM % 60

  const parts = []
  if (hari > 0) parts.push(`${hari} hari`)
  if (jam > 0) parts.push(`${jam} jam`)
  if (menit > 0) parts.push(`${menit} menit`)
  return parts.join(' ') || '0 menit'
})

function getStatusBadgeClass(status) {
  switch (status) {
    case 'Tanpa Keterangan':
      return 'bg-red-50 text-red-800 border-red-300'
    case 'Terlambat':
      return 'bg-amber-50 text-amber-800 border-amber-300'
    case 'Pulang Duluan':
      return 'bg-blue-50 text-blue-800 border-blue-300'
    default:
      return 'bg-neutral-100 text-neutral-800 border-neutral-300'
  }
}

let searchTimer = null
function handleSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    fetchAbsensi(1)
  }, 300)
}

async function fetchPegawaiOptions() {
  try {
    const res = await api.get('/pegawai/options')
    pegawaiOptions.value = res.data.data
  } catch (err) {
    console.error('Gagal mengambil options pegawai:', err)
  }
}

async function fetchAbsensi(page = 1) {
  loading.value = true
  try {
    const params = {
      page,
      per_page: pagination.per_page,
      q: searchQuery.value || undefined,
      status: filterStatus.value || undefined,
      tanggal: filterTanggal.value || undefined,
    }
    const res = await api.get('/absensi', { params })
    absensiList.value = res.data.data
    pagination.current_page = res.data.meta.current_page
    pagination.last_page = res.data.meta.last_page
    pagination.total = res.data.meta.total
  } catch (err) {
    console.error('Gagal memuat absensi:', err)
  } finally {
    loading.value = false
  }
}

function handlePegawaiInput() {
  showPegawaiDropdown.value = true
}

function selectPegawai(p) {
  form.nip = p.nip
  pegawaiInputText.value = `${p.nama} (${p.nip})`
  showPegawaiDropdown.value = false
}

function clearSelectedPegawai() {
  form.nip = ''
  pegawaiInputText.value = ''
}

function triggerAutoCalculate() {
  recalculateDuration()
}

function recalculateDuration() {
  if (form.status === 'Tanpa Keterangan') {
    form.durasi_menit = 450 // 7.5 jam
    return
  }

  // Calculate against standard 08:00 - 16:00
  const [masukH, masukM] = (form.jam_masuk || '08:00').split(':').map(Number)
  const [pulangH, pulangM] = (form.jam_pulang || '16:00').split(':').map(Number)

  const masukTotalMin = masukH * 60 + masukM
  const pulangTotalMin = pulangH * 60 + pulangM

  const stdMasukTotalMin = 8 * 60 // 08:00
  const stdPulangTotalMin = 16 * 60 // 16:00

  const selisihMasuk = Math.max(0, masukTotalMin - stdMasukTotalMin)
  const selisihPulang = Math.max(0, stdPulangTotalMin - pulangTotalMin)

  form.durasi_menit = selisihMasuk + selisihPulang
}

function openAddModal() {
  isEditing.value = false
  editingId.value = null
  formError.value = ''
  form.nip = ''
  pegawaiInputText.value = ''
  form.periode = new Date().toISOString().substring(0, 10)
  form.jam_masuk = '08:00'
  form.jam_pulang = '16:00'
  form.status = 'Tanpa Keterangan'
  form.alasan = ''
  form.durasi_menit = 450
  showModal.value = true
}

function openEditModal(rec) {
  isEditing.value = true
  editingId.value = rec.id
  formError.value = ''
  form.nip = rec.nip
  pegawaiInputText.value = `${rec.pegawai?.nama || ''} (${rec.nip})`
  form.periode = rec.periode
  form.jam_masuk = rec.jam_masuk?.substring(0, 5) || '08:00'
  form.jam_pulang = rec.jam_pulang?.substring(0, 5) || '16:00'
  form.status = rec.status
  form.alasan = rec.alasan || ''
  form.durasi_menit = rec.durasi_menit
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  showPegawaiDropdown.value = false
}

async function saveAbsensi() {
  formError.value = ''

  if (!form.nip) {
    formError.value = 'Pegawai wajib dipilih dari daftar pencarian.'
    return
  }

  saving.value = true
  try {
    const payload = {
      nip: form.nip,
      periode: form.periode,
      jam_masuk: form.jam_masuk,
      jam_pulang: form.jam_pulang,
      status: form.status,
      alasan: form.alasan,
      durasi_menit: form.durasi_menit,
    }

    if (isEditing.value) {
      await api.put(`/absensi/${editingId.value}`, payload)
      notify('Catatan absensi bermasalah berhasil diperbarui.')
    } else {
      await api.post('/absensi', payload)
      notify('Catatan absensi bermasalah berhasil ditambahkan.')
    }

    showModal.value = false
    fetchAbsensi(pagination.current_page)
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) {
      formError.value = Object.values(errors).flat().join(' ')
    } else {
      formError.value = err.response?.data?.message || 'Terjadi kesalahan saat menyimpan catatan.'
    }
  } finally {
    saving.value = false
  }
}

function confirmDelete(rec) {
  selectedToDelete.value = rec
  showDeleteModal.value = true
}

async function executeDelete() {
  if (!selectedToDelete.value) return
  deleting.value = true
  try {
    await api.delete(`/absensi/${selectedToDelete.value.id}`)
    notify('Catatan absensi berhasil dihapus.')
    showDeleteModal.value = false
    fetchAbsensi(pagination.current_page)
  } catch (err) {
    notify(err.response?.data?.message || 'Gagal menghapus catatan.', 'error')
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  fetchPegawaiOptions()
  fetchAbsensi()
})
</script>
