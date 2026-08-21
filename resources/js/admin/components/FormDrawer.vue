<template>
  <div v-if="open" class="fixed inset-0 z-50 flex justify-end">
    <div class="absolute inset-0 bg-srb-charcoal/40" aria-hidden="true" />
    <aside class="relative h-full w-full max-w-md bg-white shadow-2xl overflow-y-auto">
      <div class="border-b border-srb-grey-150 px-6 py-5">
        <p class="admin-eyebrow">{{ eyebrow }}</p>
        <h2 class="mt-2 text-[26px] text-srb-ink">{{ title }}</h2>
      </div>
      <div class="px-6 py-6">
        <slot />
      </div>
    </aside>
  </div>
</template>

<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, required: true },
  eyebrow: { type: String, default: 'Record' },
});

defineEmits(['close']);
</script>
