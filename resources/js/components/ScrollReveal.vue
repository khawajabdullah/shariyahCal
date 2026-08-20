<template>
  <div
    ref="root"
    class="scroll-reveal"
    :class="[
      `scroll-reveal--${direction}`,
      { 'is-visible': visible, 'scroll-reveal--stagger': stagger },
    ]"
    :style="delayStyle"
  >
    <slot />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  direction: { type: String, default: 'up' },
  delay: { type: Number, default: 0 },
  threshold: { type: Number, default: 0.14 },
  stagger: { type: Boolean, default: false },
});

const root = ref(null);
const visible = ref(false);
let observer = null;

const delayStyle = computed(() =>
  props.delay ? { transitionDelay: `${props.delay}ms` } : null,
);

onMounted(() => {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    visible.value = true;
    return;
  }

  observer = new IntersectionObserver(
    ([entry]) => {
      visible.value = entry.isIntersecting;
    },
    { threshold: props.threshold, rootMargin: '0px 0px -6% 0px' },
  );

  if (root.value) observer.observe(root.value);
});

onUnmounted(() => observer?.disconnect());
</script>
