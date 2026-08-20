<template>
  <div>
    <div class="mb-8 max-w-2xl">
      <p class="admin-eyebrow">Overview</p>
      <h1 class="mt-3 text-[34px] leading-tight text-srb-ink">Directory that powers the public site</h1>
      <p class="mt-3 text-[15px] leading-relaxed text-srb-muted">
        Madhāhib and language filters, scholar roster, and booking records — the same signals shown on the consultations template.
      </p>
    </div>

    <div class="mb-3">
      <p class="font-mono text-[11px] tracking-[0.1em] uppercase text-srb-muted">Public directory snapshot</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div v-for="card in publicCards" :key="card.label" class="admin-stat">
        <div class="n">{{ card.value }}{{ card.suffix || '' }}</div>
        <div class="l">{{ card.label }}</div>
      </div>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-2">
      <section class="admin-panel p-6">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="admin-eyebrow">Madhhab filters</p>
            <h2 class="mt-2 text-xl text-srb-ink">Live filter chips</h2>
            <p class="mt-2 text-sm text-srb-muted">Active tags appear on the public scholar directory.</p>
          </div>
          <router-link :to="{ name: 'admin.madhahib' }" class="admin-btn admin-btn-ghost !px-3 !py-2 text-[12px]">Manage</router-link>
        </div>
        <div class="mt-5 flex flex-wrap gap-2">
          <span class="admin-chip admin-chip-active">All</span>
          <span
            v-for="item in activeMadhahib"
            :key="item.id"
            class="admin-chip"
          >{{ item.name }}</span>
          <p v-if="!activeMadhahib.length" class="text-sm text-srb-muted">No active Madhāhib yet.</p>
        </div>
      </section>

      <section class="admin-panel p-6">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="admin-eyebrow">Language filters</p>
            <h2 class="mt-2 text-xl text-srb-ink">Live filter chips</h2>
            <p class="mt-2 text-sm text-srb-muted">Active languages drive the second filter row.</p>
          </div>
          <router-link :to="{ name: 'admin.languages' }" class="admin-btn admin-btn-ghost !px-3 !py-2 text-[12px]">Manage</router-link>
        </div>
        <div class="mt-5 flex flex-wrap gap-2">
          <span class="admin-chip admin-chip-active">All</span>
          <span
            v-for="item in activeLanguages"
            :key="item.id"
            class="admin-chip"
          >{{ item.name }}</span>
          <p v-if="!activeLanguages.length" class="text-sm text-srb-muted">No active languages yet.</p>
        </div>
      </section>
    </div>

    <div class="mt-10 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div v-for="card in opsCards" :key="card.label" class="border border-srb-grey-300 bg-white px-5 py-5">
        <p class="font-mono text-[11px] uppercase tracking-wider text-srb-muted">{{ card.label }}</p>
        <p class="mt-3 font-mono text-[26px] text-srb-ink">{{ card.value }}</p>
      </div>
    </div>

    <div class="admin-panel mt-10">
      <div class="flex items-center justify-between border-b border-srb-grey-150 px-5 py-4">
        <h2 class="text-xl text-srb-ink">Recent bookings</h2>
        <router-link :to="{ name: 'admin.bookings' }" class="text-sm font-medium text-srb-red hover:text-srb-red-bright">View all</router-link>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-srb-grey-100 font-mono text-[11px] uppercase tracking-wider text-srb-muted">
            <tr>
              <th class="px-5 py-3 text-left font-medium">Attendee</th>
              <th class="px-5 py-3 text-left font-medium">Scholar</th>
              <th class="px-5 py-3 text-left font-medium">Status</th>
              <th class="px-5 py-3 text-left font-medium">Starts</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!recent.length">
              <td colspan="4" class="px-5 py-8 text-srb-muted">No bookings stored yet. Sync from the Bookings page or wait for Cal.com webhooks.</td>
            </tr>
            <tr v-for="row in recent" :key="row.id" class="border-t border-srb-grey-150">
              <td class="px-5 py-3">
                <div>{{ row.attendee_name || '—' }}</div>
                <div class="text-[12px] text-srb-muted">{{ row.attendee_email }}</div>
              </td>
              <td class="px-5 py-3">{{ row.scholar || '—' }}</td>
              <td class="px-5 py-3"><StatusBadge :value="row.status" /></td>
              <td class="px-5 py-3 whitespace-nowrap">{{ formatDate(row.starts_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from '../../bootstrap';
import StatusBadge from '../components/StatusBadge.vue';

const counts = ref({});
const publicDirectory = ref({});
const filters = ref({ madhahib: [], languages: [], countries: [] });
const recent = ref([]);

const publicCards = computed(() => [
  { label: 'Scholars shown', value: publicDirectory.value.scholars ?? '—' },
  { label: 'Countries', value: publicDirectory.value.countries ?? '—' },
  { label: 'Madhāhib', value: publicDirectory.value.madhahib ?? '—' },
  { label: 'Languages', value: publicDirectory.value.languages ?? '—', suffix: '+' },
]);

const opsCards = computed(() => [
  { label: 'Total scholars', value: counts.value.scholars ?? '—' },
  { label: 'Bookings stored', value: counts.value.bookings ?? '—' },
  { label: 'Madhāhib (all)', value: counts.value.madhahib ?? '—' },
  { label: 'Languages (all)', value: counts.value.languages ?? '—' },
]);

const activeMadhahib = computed(() => (filters.value.madhahib || []).filter((item) => item.is_active));
const activeLanguages = computed(() => (filters.value.languages || []).filter((item) => item.is_active));

function formatDate(value) {
  if (!value) return '—';
  return new Date(value).toLocaleString();
}

onMounted(async () => {
  const { data } = await axios.get('/api/admin/dashboard');
  counts.value = data.data?.counts ?? {};
  publicDirectory.value = data.data?.public_directory ?? {};
  filters.value = data.data?.filters ?? { madhahib: [], languages: [], countries: [] };
  recent.value = data.data?.recent_bookings ?? [];
});
</script>
