<template>
  <section class="directory" id="directory">
    <div class="wrap">
      <div class="section-head">
        <div class="eyebrow">OUR SCHOLARS</div>
        <h2>Find a scholar by madhhab, language, or specialization.</h2>
        <p>Every scholar on this platform is independently vetted by SRB and, in most cases, AAOIFI-certified. Filter the live roster by madhhab or language.</p>
      </div>

      <div class="filters" v-if="madhahib.length || languages.length">
        <template v-if="madhahib.length">
          <span class="flabel">Madhhab</span>
          <div class="chip-group">
            <button class="chip" :class="{ active: activeMadhhab === 'All' }" @click="activeMadhhab = 'All'">All</button>
            <button v-for="m in madhahib" :key="m" class="chip" :class="{ active: activeMadhhab === m }" @click="activeMadhhab = m">{{ m }}</button>
          </div>
        </template>
        <template v-if="languages.length">
          <span class="flabel">Language</span>
          <div class="chip-group">
            <button class="chip" :class="{ active: activeLang === 'All' }" @click="activeLang = 'All'">All</button>
            <button v-for="l in languages" :key="l" class="chip" :class="{ active: activeLang === l }" @click="activeLang = l">{{ l }}</button>
          </div>
        </template>
      </div>

      <p v-if="loading" class="status">Loading scholars…</p>
      <p v-else-if="error" class="status">{{ error }}</p>
      <div v-else class="scholar-grid">
        <template v-if="filtered.length">
          <div v-for="s in filtered" :key="s.id" class="scard" :class="{ 'tier-institutional': s.tier === 'institutional' }">
            <svg class="corner star8" width="16" height="16"><use href="#star8"/></svg>
            <div class="avatar" :class="{ 'avatar-inst': s.tier === 'institutional' }">
              <img v-if="s.avatarUrl" :src="s.avatarUrl" :alt="s.name">
              <template v-else>{{ s.initials }}</template>
            </div>
            <h3>{{ s.name }}</h3>
            <div class="country" v-if="s.flag || s.country">{{ [s.flag, s.country].filter(Boolean).join(' · ') }}</div>
            <div class="tagrow" v-if="s.madhhab || (s.languages && s.languages.length)">
              <span v-if="s.madhhab" class="tag madhhab">{{ s.madhhab }}</span>
              <span v-for="l in (s.languages || []).slice(0, 3)" :key="l" class="tag">{{ l }}</span>
            </div>
            <div class="specialties" v-if="s.specialties && s.specialties.length">{{ s.specialties.join(' · ') }}</div>
            <p class="specialties" v-else-if="s.bio">{{ s.bio }}</p>
            <div class="pricerow">
              <div class="price">
                <template v-if="s.tier === 'institutional'">
                  By engagement<br>
                  <small>Request access</small>
                </template>
                <template v-else-if="startingPrice(s)">
                  {{ startingPrice(s).label }}<br>
                  <small>from · {{ startingPrice(s).minutes }} min</small>
                </template>
                <template v-else>
                  Pricing soon<br>
                  <small>sessions syncing</small>
                </template>
              </div>
            </div>
            <button class="view-btn" @click="openBooking(s)">
              {{ s.tier === 'institutional' ? 'View profile' : 'View profile & book' }}
            </button>
          </div>
        </template>
        <p v-else class="status">No scholars match those filters — try widening your search.</p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useScholars } from '../composables/useScholars';
import { useBooking } from '../composables/useBooking';

const { scholars, filters, loading, error, load } = useScholars();
const { open: openBooking, formatMoney } = useBooking();

const activeMadhhab = ref('All');
const activeLang = ref('All');

onMounted(() => {
  load();
});

function startingPrice(scholar) {
  const priced = (scholar.eventTypes || [])
    .filter((item) => item?.price != null)
    .sort((a, b) => Number(a.price) - Number(b.price));
  const first = priced[0];
  if (!first) return null;
  return {
    label: formatMoney(first.price, first.currency),
    minutes: first.lengthInMinutes,
  };
}
const madhahib = computed(() => filters.value.madhahib.length
  ? filters.value.madhahib
  : [...new Set(scholars.value.map(s => s.madhhab).filter(Boolean))]);
const languages = computed(() => filters.value.languages.length
  ? filters.value.languages
  : [...new Set(scholars.value.flatMap(s => s.languages || []))].sort());

const filtered = computed(() => scholars.value.filter(s => {
  const mOk = activeMadhhab.value === 'All' || s.madhhab === activeMadhhab.value;
  const lOk = activeLang.value === 'All' || (s.languages || []).includes(activeLang.value);
  return mOk && lOk;
}));
</script>

<style scoped>
.directory { background: var(--grey-100); padding: 88px 0; }
.filters { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 36px; align-items: center; }
.flabel { font-family: 'IBM Plex Mono', monospace; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted-ink); margin-right: 6px; }
.chip-group { display: flex; gap: 8px; flex-wrap: wrap; margin-right: 18px; }
.chip { font-size: 12.5px; padding: 8px 15px; border-radius: 20px; border: 1px solid var(--grey-300); background: var(--white); color: var(--text-muted-ink); transition: all .15s ease; }
.chip:hover { border-color: var(--red); }
.chip.active { background: var(--charcoal); color: var(--text-light); border-color: var(--charcoal); }
.status { color: var(--text-muted-ink); font-size: 14px; }

.scholar-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; }
.scard {
  background: var(--white); border: 1px solid var(--grey-300);
  padding: 26px 22px 22px; position: relative;
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  display: flex; flex-direction: column;
}
.scard:hover { transform: translateY(-3px); box-shadow: 0 14px 30px -18px rgba(63, 65, 68, 0.35); border-color: var(--red); }
.scard .corner { position: absolute; top: 14px; right: 14px; color: var(--red); opacity: 0.5; }
.scard.tier-institutional { background: var(--charcoal); color: var(--text-light); border-color: var(--charcoal); }
.scard.tier-institutional .corner { color: var(--red-bright); }
.avatar {
  width: 56px; height: 56px; border-radius: 50%;
  background: linear-gradient(150deg, var(--grey-700), var(--grey-900));
  color: #fff; font-family: 'Spectral', serif; font-weight: 600; font-size: 19px;
  display: flex; align-items: center; justify-content: center; margin-bottom: 16px;
  overflow: hidden;
}
.avatar img { width: 100%; height: 100%; object-fit: cover; }
.avatar-inst { background: linear-gradient(150deg, var(--red), var(--red-deep)); }
.scard h3 { font-size: 17px; line-height: 1.25; color: inherit; }
.country { font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: var(--text-muted-ink); margin-top: 5px; letter-spacing: 0.03em; }
.tier-institutional .country { color: var(--text-muted-light); }
.tagrow { display: flex; flex-wrap: wrap; gap: 6px; margin: 14px 0; }
.tag { font-size: 10.5px; padding: 4px 9px; border: 1px solid var(--grey-300); color: var(--text-muted-ink); letter-spacing: 0.02em; }
.tag.madhhab { border-color: var(--red); color: var(--red-deep); }
.tier-institutional .tag { border-color: rgba(242, 242, 242, 0.25); color: var(--text-muted-light); }
.tier-institutional .tag.madhhab { border-color: var(--red-bright); color: var(--red-bright); }
.specialties { font-size: 13px; color: var(--text-muted-ink); margin: 14px 0 16px; flex: 1; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }
.tier-institutional .specialties { color: var(--text-muted-light); }
.pricerow {
  display: flex; align-items: baseline; justify-content: space-between;
  border-top: 1px solid var(--grey-300); padding-top: 14px; margin-top: auto;
}
.tier-institutional .pricerow { border-color: rgba(242, 242, 242, 0.16); }
.price { font-family: 'IBM Plex Mono', monospace; font-size: 15px; }
.price small { font-size: 10.5px; color: var(--text-muted-ink); }
.view-btn {
  margin-top: 14px; width: 100%; padding: 11px; font-size: 12.5px; font-weight: 600;
  border: 1px solid var(--charcoal); background: transparent; color: var(--charcoal);
  transition: all .15s ease;
}
.view-btn:hover { background: var(--charcoal); color: var(--text-light); }
.tier-institutional .view-btn { border-color: var(--red-bright); color: var(--red-bright); }
.tier-institutional .view-btn:hover { background: var(--red-bright); color: var(--charcoal); }

@media (max-width: 920px) { .scholar-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 560px) { .scholar-grid { grid-template-columns: 1fr; } }
</style>
