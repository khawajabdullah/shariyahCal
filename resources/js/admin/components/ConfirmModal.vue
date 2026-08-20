<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <button class="absolute inset-0 bg-srb-charcoal/40" type="button" aria-label="Close" @click="$emit('cancel')" />
    <div class="relative w-full max-w-md bg-white p-7 border border-srb-grey-300">
      <p class="admin-eyebrow">Please confirm</p>
      <h3 class="mt-2 text-[22px] text-srb-ink">{{ title }}</h3>
      <p class="mt-3 text-sm text-srb-muted">{{ message }}</p>
      <div class="mt-6 flex justify-end gap-3">
        <button type="button" class="admin-btn admin-btn-ghost" @click="$emit('cancel')">
          Cancel
        </button>
        <button type="button" class="admin-btn admin-btn-primary" @click="$emit('confirm')">
          {{ confirmLabel }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: 'Delete this record?' },
  message: { type: String, default: 'This action cannot be undone.' },
  confirmLabel: { type: String, default: 'Delete' },
});

defineEmits(['confirm', 'cancel']);
</script>
