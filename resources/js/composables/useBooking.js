import { computed, reactive, ref } from 'vue';
import { useScholars } from './useScholars';

const isOpen = ref(false);
const state = reactive({
  scholar: null,
  step: 1,
  duration: 30,
  day: null,
  time: null,
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
    let slots = windowsForDay(schedule, date).flatMap((window) => slotsForWindow(window, duration));
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
      timeZone,
    });
  }

  return days;
}

export function useBooking() {
  const { findById, PRICING } = useScholars();

  function open(scholarOrId) {
    const s = scholarOrId && typeof scholarOrId === 'object'
      ? scholarOrId
      : findById(scholarOrId);
    if (!s) return;
    Object.assign(state, { scholar: s, step: 1, duration: 30, day: null, time: null });
    isOpen.value = true;
  }

  function close() {
    isOpen.value = false;
  }

  function goStep(n) {
    state.step = n;
  }

  function pickDuration(d) {
    if (state.duration !== d) {
      state.day = null;
      state.time = null;
    }
    state.duration = d;
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

  return {
    isOpen,
    state,
    open,
    close,
    goStep,
    pickDuration,
    pickSlot,
    availableDays,
    selectedDay,
    scheduleLabel,
    PRICING,
  };
}
