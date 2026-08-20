<template>
  <span
    class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium tracking-wide uppercase"
    :class="toneClass"
  >
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  value: { type: [String, Boolean], default: '' },
  onLabel: { type: String, default: 'Active' },
  offLabel: { type: String, default: 'Inactive' },
});

const label = computed(() => {
  if (typeof props.value === 'boolean') {
    return props.value ? props.onLabel : props.offLabel;
  }
  return props.value || '—';
});

const toneClass = computed(() => {
  const raw = typeof props.value === 'boolean'
    ? (props.value ? 'active' : 'inactive')
    : String(props.value || '').toLowerCase();

  if (['active', 'accepted', 'confirmed'].includes(raw)) {
    return 'bg-srb-red-soft text-srb-red-deep';
  }
  if (['cancelled', 'canceled', 'rejected', 'inactive'].includes(raw)) {
    return 'bg-srb-grey-150 text-srb-muted';
  }
  if (['pending', 'awaiting'].includes(raw)) {
    return 'bg-white text-srb-ink border border-srb-grey-300';
  }
  return 'bg-srb-grey-150 text-srb-ink';
});
</script>
