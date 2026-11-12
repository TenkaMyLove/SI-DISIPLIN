export const WEEKDAYS = [
  { key: 'senin', label: 'Senin', defaultMasuk: '07:30', defaultPulang: '16:00', defaultFlex: false },
  { key: 'selasa', label: 'Selasa', defaultMasuk: '07:30', defaultPulang: '16:00', defaultFlex: true },
  { key: 'rabu', label: 'Rabu', defaultMasuk: '07:30', defaultPulang: '16:00', defaultFlex: true },
  { key: 'kamis', label: 'Kamis', defaultMasuk: '07:30', defaultPulang: '16:00', defaultFlex: true },
  { key: 'jumat', label: 'Jumat', defaultMasuk: '07:30', defaultPulang: '16:30', defaultFlex: false },
]

const JS_DAY_TO_KEY = {
  1: 'senin',
  2: 'selasa',
  3: 'rabu',
  4: 'kamis',
  5: 'jumat',
}

export function timeToMinutes(timeStr) {
  if (!timeStr) return 0
  const [h, m] = String(timeStr).split(':').map(Number)
  return (Number.isFinite(h) ? h : 0) * 60 + (Number.isFinite(m) ? m : 0)
}

export function minutesToTime(totalMinutes) {
  const clamped = Math.max(0, Math.min(1439, Math.round(totalMinutes)))
  const h = Math.floor(clamped / 60)
  const m = clamped % 60
  return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`
}

export function formatDurationMinutes(totalMinutes) {
  const m = Math.max(0, Math.round(totalMinutes || 0))
  const jam = Math.floor(m / 60)
  const menit = m % 60
  const parts = []
  if (jam > 0) parts.push(`${jam} jam`)
  if (menit > 0) parts.push(`${menit} menit`)
  return parts.join(' ') || '0 menit'
}

export function getScheduleForDate(jadwal, dateStr) {
  const fallback = {
    hari: 'Standar',
    masuk: '07:30',
    pulang: '16:00',
    is_flex: false,
    flex_masuk_mulai: '07:30',
    flex_masuk_akhir: '08:00',
    flex_pulang_mulai: '16:00',
    flex_pulang_akhir: '16:30',
    flex_toleransi_menit: 30,
  }

  if (!dateStr) return fallback

  const parts = dateStr.split('-').map(Number)
  if (parts.length !== 3) return fallback

  const dateObj = new Date(parts[0], parts[1] - 1, parts[2])
  const dayKey = JS_DAY_TO_KEY[dateObj.getDay()]

  if (dayKey && jadwal?.[dayKey]) {
    return jadwal[dayKey]
  }

  if (dayKey) {
    const meta = WEEKDAYS.find((d) => d.key === dayKey)
    if (meta) {
      return {
        ...fallback,
        hari: meta.label,
        masuk: meta.defaultMasuk,
        pulang: meta.defaultPulang,
        is_flex: meta.defaultFlex,
        flex_masuk_mulai: meta.defaultMasuk,
      }
    }
  }

  if (jadwal?.senin) {
    return {
      ...jadwal.senin,
      hari: 'Akhir Pekan',
      is_flex: false,
    }
  }

  return fallback
}

export function computeAttendanceDeficit({ status, jamMasuk, jamPulang, schedule }) {
  if (status === 'Tanpa Keterangan') {
    return 450 // 7.5 jam kerja
  }

  const masukMin = timeToMinutes(jamMasuk || schedule.masuk || '07:30')
  const pulangMin = timeToMinutes(jamPulang || schedule.pulang || '16:00')
  const stdMasukMin = timeToMinutes(schedule.masuk || '07:30')
  const stdPulangMin = timeToMinutes(schedule.pulang || '16:00')

  if (schedule.is_flex) {
    const stdDurationMin = Math.max(0, stdPulangMin - stdMasukMin)
    const flexMulaiMin = timeToMinutes(schedule.flex_masuk_mulai || schedule.masuk || '07:30')
    const flexAkhirMin = timeToMinutes(schedule.flex_masuk_akhir || minutesToTime(stdMasukMin + 30))

    const keterlambatanLuarFlexMin = Math.max(0, masukMin - flexAkhirMin)
    const effectiveMasukMin = Math.max(flexMulaiMin, Math.min(masukMin, flexAkhirMin))
    const targetPulangMin = effectiveMasukMin + stdDurationMin
    const kekuranganPulangMin = Math.max(0, targetPulangMin - pulangMin)

    return keterlambatanLuarFlexMin + kekuranganPulangMin
  }

  const selisihMasuk = Math.max(0, masukMin - stdMasukMin)
  const selisihPulang = Math.max(0, stdPulangMin - pulangMin)

  return selisihMasuk + selisihPulang
}
