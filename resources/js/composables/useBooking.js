import { computed, reactive, ref } from 'vue';
import axios from '../bootstrap';
import { useScholars } from './useScholars';

const isOpen = ref(false);
const submitting = ref(false);
const submitError = ref('');
const confirmation = ref(null);
const state = reactive({
  scholar: null,
  step: 1,
  eventTypeId: null,
  duration: null,
  price: null,
  currency: 'usd',
  day: null,
  time: null,
  attendee: {
    name: '',
    email: '',
    phone: '',
    country: '',
    notes: '',
    consent: true,
  },
});

function weekdayInZone(date, timeZone) {
  return new Intl.DateTimeFormat('en-US', { weekday: 'long', timeZone }).format(date);
}

function isoDateInZone(date, timeZone) {
  return new Intl.DateTimeFormat('en-CA', {
    timeZone,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).format(date);
}

function formatDayLabel(date, timeZone) {
  return new Intl.DateTimeFormat('en-GB', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    timeZone,
  }).format(date);
}

function toMinutes(hhmm) {
  const [h, m] = String(hhmm || '00:00').split(':').map(Number);
  return (h || 0) * 60 + (m || 0);
}

function fromMinutes(mins) {
  const h = Math.floor(mins / 60);
  const m = mins % 60;
  return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
}

function nowMinutesInZone(timeZone) {
  const parts = new Intl.DateTimeFormat('en-GB', {
    timeZone,
    hour: '2-digit',
    minute: '2-digit',
    hourCycle: 'h23',
  }).formatToParts(new Date());
  const hour = Number(parts.find((p) => p.type === 'hour')?.value || 0);
  const minute = Number(parts.find((p) => p.type === 'minute')?.value || 0);
  return hour * 60 + minute;
}

function windowsForDay(schedule, date) {
  const timeZone = schedule.timeZone || 'UTC';
  const iso = isoDateInZone(date, timeZone);
  const overrides = (schedule.overrides || []).filter((item) => item.date === iso && item.startTime && item.endTime);

  if (overrides.length) {
    return overrides;
  }

  const dayName = weekdayInZone(date, timeZone);
  return (schedule.availability || []).filter((window) => (window.days || []).includes(dayName));
}

function slotsForWindow(window, duration) {
  const start = toMinutes(window.startTime);
  const end = toMinutes(window.endTime);
  const slots = [];
  for (let t = start; t + duration <= end; t += duration) {
    slots.push(fromMinutes(t));
  }
  return slots;
}

function formatWindowRange(window) {
  if (!window?.startTime || !window?.endTime) return '';
  return `${window.startTime}-${window.endTime}`;
}

function formatMoney(amount, currency = 'usd') {
  if (amount == null || Number.isNaN(Number(amount))) return '—';
  try {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: String(currency || 'usd').toUpperCase(),
      maximumFractionDigits: 0,
    }).format(Number(amount));
  } catch {
    return `$${amount}`;
  }
}

export function availableDaysFor(scholar, duration, lookAhead = 14, maxDays = 5) {
  const schedule = scholar?.schedule;
  if (!schedule || !duration) return [];

  const timeZone = schedule.timeZone || 'UTC';
  const todayIso = isoDateInZone(new Date(), timeZone);
  const days = [];

  for (let i = 0; i < lookAhead && days.length < maxDays; i++) {
    const date = new Date();
    date.setDate(date.getDate() + i);
    const iso = isoDateInZone(date, timeZone);
    const windows = windowsForDay(schedule, date);
    let slots = windows.flatMap((window) => slotsForWindow(window, duration));
    slots = [...new Set(slots)].sort();

    if (iso === todayIso) {
      const now = nowMinutesInZone(timeZone);
      slots = slots.filter((time) => toMinutes(time) > now);
    }

    if (!slots.length) continue;

    days.push({
      key: iso,
      label: formatDayLabel(date, timeZone),
      slots,
      windows: windows.map(formatWindowRange).filter(Boolean),
      timeZone,
    });
  }

  return days;
}

function resetAttendee() {
  Object.assign(state.attendee, {
    name: '',
    email: '',
    phone: '',
    country: '',
    notes: '',
    consent: true,
  });
}

export function useBooking() {
  const { findById } = useScholars();

  const eventTypes = computed(() => {
    const list = state.scholar?.eventTypes;
    return Array.isArray(list) ? list.filter((item) => item?.price != null) : [];
  });

  const selectedEventType = computed(() =>
    eventTypes.value.find((item) => item.id === state.eventTypeId) || null,
  );

  function open(scholarOrId) {
    const s = scholarOrId && typeof scholarOrId === 'object'
      ? scholarOrId
      : findById(scholarOrId);
    if (!s) return;

    const first = Array.isArray(s.eventTypes)
      ? s.eventTypes.find((item) => item?.price != null)
      : null;

    Object.assign(state, {
      scholar: s,
      step: 1,
      eventTypeId: first?.id ?? null,
      duration: first?.lengthInMinutes ?? null,
      price: first?.price ?? null,
      currency: first?.currency || 'usd',
      day: null,
      time: null,
    });
    resetAttendee();
    submitError.value = '';
    confirmation.value = null;
    isOpen.value = true;
  }

  function close() {
    if (submitting.value) return;
    isOpen.value = false;
  }

  function goStep(n) {
    if (submitting.value) return;
    state.step = n;
  }

  function pickEventType(eventType) {
    if (!eventType) return;
    if (state.eventTypeId !== eventType.id) {
      state.day = null;
      state.time = null;
    }
    state.eventTypeId = eventType.id;
    state.duration = eventType.lengthInMinutes;
    state.price = eventType.price;
    state.currency = eventType.currency || 'usd';
  }

  function pickSlot(day, time) {
    state.day = day;
    state.time = time;
  }

  const availableDays = computed(() => availableDaysFor(state.scholar, state.duration));
  const selectedDay = computed(() => availableDays.value.find((day) => day.key === state.day) || null);
  const scheduleLabel = computed(() => {
    const schedule = state.scholar?.schedule;
    if (!schedule) return '';
    return [schedule.name, schedule.timeZone?.replaceAll('_', ' ')].filter(Boolean).join(' · ');
  });
  const priceLabel = computed(() => formatMoney(state.price, state.currency));

  async function confirmBooking() {
    if (submitting.value || !state.scholar || !state.eventTypeId || !state.day || !state.time) return;

    submitting.value = true;
    submitError.value = '';

    try {
      const timeZone = state.scholar.schedule?.timeZone || selectedDay.value?.timeZone || 'UTC';
      const { data } = await axios.post('/api/bookings', {
        scholar_id: state.scholar.id,
        event_type_id: state.eventTypeId,
        start: `${state.day}T${state.time}:00`,
        attendee_name: state.attendee.name,
        attendee_email: state.attendee.email,
        attendee_phone: state.attendee.phone || null,
        attendee_timezone: timeZone,
        notes: state.attendee.notes || null,
      });
      confirmation.value = data.data ?? null;
      state.step = 5;
    } catch (e) {
      submitError.value = Object.values(e.response?.data?.errors ?? {})[0]?.[0]
        || e.response?.data?.message
        || 'Unable to create booking.';
    } finally {
      submitting.value = false;
    }
  }

  return {
    isOpen,
    state,
    open,
    close,
    goStep,
    pickEventType,
    pickSlot,
    availableDays,
    selectedDay,
    selectedEventType,
    scheduleLabel,
    eventTypes,
    priceLabel,
    formatMoney,
    submitting,
    submitError,
    confirmation,
    confirmBooking,
  };
}
