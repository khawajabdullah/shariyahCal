<template>
  <template v-if="isAdminShell">
    <router-view />
  </template>
  <template v-else>
    <SvgDefs />
    <SiteHeader />
    <router-view />
    <SiteFooter />
    <BookingModal />
  </template>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import SvgDefs from './components/SvgDefs.vue';
import SiteHeader from './components/SiteHeader.vue';
import SiteFooter from './components/SiteFooter.vue';
import BookingModal from './components/BookingModal.vue';
import { useScholars } from './composables/useScholars';

const route = useRoute();

const isAdminShell = computed(() => {
  if (route.matched.some((record) => record.meta.adminShell)) {
    return true;
  }

  const path = route.path || window.location.pathname;

  return path === '/login' || path.startsWith('/admin');
});

const { load } = useScholars();

watch(
  isAdminShell,
  (admin) => {
    if (!admin) {
      load();
    }
  },
  { immediate: true },
);
</script>
