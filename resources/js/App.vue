<template>
  <SvgDefs />
  <SiteHeader />
  <router-view />
  <SiteFooter />
  <BookingModal />
</template>

<script setup>
import { onMounted } from 'vue';
import SvgDefs from './components/SvgDefs.vue';
import SiteHeader from './components/SiteHeader.vue';
import SiteFooter from './components/SiteFooter.vue';
import BookingModal from './components/BookingModal.vue';
import { useScholars } from './composables/useScholars';

const { load } = useScholars();
onMounted(() => load());
</script>
