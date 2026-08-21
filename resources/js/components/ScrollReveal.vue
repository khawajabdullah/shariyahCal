<template>
  <div
    ref="root"
    class="scroll-reveal"
    :class="[
      `scroll-reveal--${direction}`,
      { 'is-visible': visible, 'is-settled': settled, 'scroll-reveal--stagger': stagger },
    ]"
    :style="delayStyle"
    @transitionend="onTransitionEnd"
  >
    <slot />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  direction: { type: String, default: 'up' },
  delay: { type: Number, default: 0 },
  threshold: { type: Number, default: 0.14 },
  stagger: { type: Boolean, default: false },
});

const root = ref(null);
const visible = ref(false);
const settled = ref(false);
let observer = null;
let settleTimer = null;

const delayStyle = computed(() =>
  props.delay ? { transitionDelay: `${props.delay}ms` } : null,
);

function onTransitionEnd(e) {
  if (e.target !== root.value) return;
  if (visible.value) settled.value = true;
}

watch(visible, (isVisible) => {
  clearTimeout(settleTimer);
  if (!isVisible) {
    settled.value = false;
    return;
  }
  // Fallback if transitionend doesn't fire (e.g. display:none children)
  settleTimer = setTimeout(() => {
    settled.value = true;
  }, 900);
});

onMounted(() => {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    visible.value = true;
    settled.value = true;
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

onUnmounted(() => {
  observer?.disconnect();
  clearTimeout(settleTimer);
});
</script>
