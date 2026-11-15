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

const DAY_LABELS = {
  0: 'Minggu',
  1: 'Senin',
  2: 'Selasa',
  3: 'Rabu',
  4: 'Kamis',
  5: 'Jumat',
  6: 'Sabtu',
}

export function getWitaDayIndex(date = new Date()) {
  const parts = new Intl.DateTimeFormat('en-US', {
    timeZone: 'Asia/Makassar',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).formatToParts(date)

  const getPart = (type) => Number(parts.find((part) => part.type === type)?.value)
  const witaDate = new Date(getPart('year'), getPart('month') - 1, getPart('day'))
  return witaDate.getDay()
}

export function getTodayWorkingHours(settings, date = new Date()) {
  const dayIndex = getWitaDayIndex(date)
  const dayLabel = DAY_LABELS[dayIndex] || 'Senin'
  const dayKey = JS_DAY_TO_KEY[dayIndex]

  if (!dayKey) {
    return {
      isHoliday: true,
      dayLabel,
      hoursText: 'Libur',
      subtitleText: `WITA \u2022 ${dayLabel}`,
    }
  }

  const daySchedule = settings?.jadwal?.[dayKey]
  const fallbackMeta = WEEKDAYS.find((day) => day.key === dayKey)
  const masuk = daySchedule?.masuk || fallbackMeta?.defaultMasuk || settings?.jam_masuk_standar || '07:30'
  const pulang = daySchedule?.pulang || fallbackMeta?.defaultPulang || settings?.jam_pulang_standar || '16:00'

  return {
    isHoliday: false,
    dayLabel,
    hoursText: `${masuk} \u2013 ${pulang}`,
    subtitleText: `WITA \u2022 ${dayLabel}`,
  }
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

export const STATUS_HANYA_ABSEN = 'Hanya Absen Masuk / Pulang'

export const ABSENSI_STATUS_OPTIONS = [
  { value: 'Tanpa Keterangan', label: 'Tanpa Keterangan' },
  { value: 'Terlambat', label: 'Terlambat' },
  { value: 'Pulang Duluan', label: 'Pulang Duluan' },
  { value: STATUS_HANYA_ABSEN, label: STATUS_HANYA_ABSEN },
]

export function getStatusBadgeClass(status) {
  switch (status) {
    case 'Tanpa Keterangan':
      return 'bg-red-50 text-red-800 border-red-300'
    case 'Terlambat':
      return 'bg-amber-50 text-amber-800 border-amber-300'
    case 'Pulang Duluan':
      return 'bg-blue-50 text-blue-800 border-blue-300'
    case STATUS_HANYA_ABSEN:
      return 'bg-purple-50 text-purple-800 border-purple-300'
    default:
      return 'bg-neutral-100 text-neutral-800 border-neutral-300'
  }
}

export function computeAttendanceDeficit({ status, jamMasuk, jamPulang, schedule }) {
  if (status === 'Tanpa Keterangan') {
    return 450 // 7.5 jam kerja
  }

  if (status === STATUS_HANYA_ABSEN) {
    return 90
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
