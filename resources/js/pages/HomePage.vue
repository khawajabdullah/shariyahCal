<template>
  <HeroSection />
  <HowItWorks />
  <div class="divider-star"><svg width="14" height="14" class="star8"><use href="#star8"/></svg></div>
  <ScholarDirectory />
  <InstitutionsBand />
  <TrustSection />
</template>

<script setup>
import HeroSection from '../components/HeroSection.vue';
import HowItWorks from '../components/HowItWorks.vue';
import ScholarDirectory from '../components/ScholarDirectory.vue';
import InstitutionsBand from '../components/InstitutionsBand.vue';
import TrustSection from '../components/TrustSection.vue';
</script>
