<template>
  <div class="step-track">
    <div v-for="i in 4" :key="i" class="seg" :class="{ done: i <= step }"></div>
  </div>
</template>

<script setup>
defineProps({ step: { type: Number, required: true } });
</script>

<style scoped>
.step-track { display: flex; gap: 6px; margin-bottom: 26px; }
.seg { flex: 1; height: 3px; background: var(--grey-300); }
.seg.done { background: var(--red); }
</style>
