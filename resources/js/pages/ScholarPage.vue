<template>
  <section style="padding:88px 0;">
    <div class="wrap" v-if="loading || !loaded">
      <p>Loading scholar…</p>
    </div>
    <div class="wrap" v-else-if="scholar">
      <router-link to="/#directory" class="btn btn-ghost-light btn-sm" style="margin-bottom:32px;">← Back to directory</router-link>
      <div class="profile-grid">
        <div>
          <div class="avatar-lg">
            <img v-if="scholar.avatarUrl" :src="scholar.avatarUrl" :alt="scholar.name">
            <template v-else>{{ scholar.initials }}</template>
          </div>
        </div>
        <div>
          <div class="eyebrow">SCHOLAR PROFILE</div>
          <h1 style="font-size:36px;margin-top:14px;">{{ scholar.name }}</h1>
          <div class="meta" v-if="scholar.flag || scholar.country || scholar.madhhab">
            {{ [scholar.flag, scholar.country, scholar.madhhab].filter(Boolean).join(' · ') }}
          </div>
          <div class="tagrow" v-if="scholar.madhhab || (scholar.languages && scholar.languages.length)">
            <span v-if="scholar.madhhab" class="tag madhhab">{{ scholar.madhhab }}</span>
            <span v-for="l in scholar.languages" :key="l" class="tag">{{ l }}</span>
          </div>
          <p class="bio" v-if="scholar.bio">{{ scholar.bio }}</p>
          <ul class="cred-list" v-if="scholar.credentials && scholar.credentials.length">
            <li v-for="c in scholar.credentials" :key="c">{{ c }}</li>
          </ul>
          <template v-if="scholar.specialties && scholar.specialties.length">
            <h4 style="font-size:14px;margin:24px 0 10px;">Specializations</h4>
            <p class="specs">{{ scholar.specialties.join(' · ') }}</p>
          </template>
          <button class="btn btn-red" style="margin-top:24px;" @click="openBooking(scholar)">
            {{ scholar.tier === 'institutional' ? 'Request access' : 'Book a consultation' }}
          </button>
        </div>
      </div>
    </div>
    <div class="wrap" v-else>
      <p>Scholar not found. <router-link to="/">Return home</router-link></p>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useScholars } from '../composables/useScholars';
import { useBooking } from '../composables/useBooking';

const route = useRoute();
const { findById, loading, loaded, load } = useScholars();
const { open: openBooking } = useBooking();

onMounted(() => {
  load();
});

const scholar = computed(() => findById(route.params.id));
</script>

<style scoped>
.profile-grid { display: grid; grid-template-columns: auto 1fr; gap: 40px; align-items: start; }
.avatar-lg {
  width: 96px; height: 96px; border-radius: 50%;
  background: linear-gradient(150deg, var(--grey-700), var(--grey-900));
  color: #fff; font-family: 'Spectral', serif; font-weight: 600; font-size: 32px;
  display: flex; align-items: center; justify-content: center;
  overflow: hidden;
}
.avatar-lg img { width: 100%; height: 100%; object-fit: cover; }
.meta { font-family: 'IBM Plex Mono', monospace; font-size: 13px; color: var(--text-muted-ink); margin-top: 8px; }
.tagrow { display: flex; flex-wrap: wrap; gap: 6px; margin: 14px 0; }
.tag { font-size: 10.5px; padding: 4px 9px; border: 1px solid var(--grey-300); color: var(--text-muted-ink); letter-spacing: 0.02em; }
.tag.madhhab { border-color: var(--red); color: var(--red-deep); }
.bio { font-size: 15px; color: var(--text-muted-ink); margin: 16px 0; max-width: 560px; white-space: pre-line; }
.cred-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 6px; }
.cred-list li { font-size: 13.5px; color: var(--text-muted-ink); padding-left: 16px; position: relative; }
.cred-list li::before { content: "—"; position: absolute; left: 0; color: var(--red-deep); }
.specs { font-size: 14px; color: var(--text-muted-ink); }

@media (max-width: 560px) {
  .profile-grid { grid-template-columns: 1fr; }
}
</style>
