<template>
  <HeroSection />

  <ScrollReveal stagger>
    <HowItWorks />
  </ScrollReveal>

  <ScrollReveal direction="fade" :delay="80">
    <div class="divider-star"><svg width="14" height="14" class="star8"><use href="#star8"/></svg></div>
  </ScrollReveal>

  <ScrollReveal stagger>
    <ScholarDirectory />
  </ScrollReveal>

  <ScrollReveal direction="up">
    <InstitutionsBand />
  </ScrollReveal>

  <ScrollReveal stagger>
    <TrustSection />
  </ScrollReveal>
</template>

<script setup>
import HeroSection from '../components/HeroSection.vue';
import HowItWorks from '../components/HowItWorks.vue';
import ScholarDirectory from '../components/ScholarDirectory.vue';
import InstitutionsBand from '../components/InstitutionsBand.vue';
import TrustSection from '../components/TrustSection.vue';
import ScrollReveal from '../components/ScrollReveal.vue';
</script>
