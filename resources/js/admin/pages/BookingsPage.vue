<template>
  <div>
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <p class="admin-eyebrow">Records</p>
        <h1 class="mt-3 text-[34px] text-srb-ink">Bookings</h1>
        <p class="mt-2 text-sm text-srb-muted">Stored from Cal.com webhooks and manual sync. Unique on booking UID.</p>
      </div>
      <button type="button" class="admin-btn admin-btn-dark" :disabled="syncing" @click="sync">
        {{ syncing ? 'Syncing…' : 'Sync bookings' }}
      </button>
    </div>
    <p v-if="syncMessage" class="mb-4 text-sm text-srb-muted">{{ syncMessage }}</p>

    <DataTable ref="table" endpoint="/api/admin/bookings" :columns="columns">
      <template #cell-attendee_name="{ row }">
        <div>{{ row.attendee_name || '—' }}</div>
        <div class="text-[12px] text-srb-muted">{{ row.attendee_email }}</div>
      </template>
      <template #cell-scholar="{ row }">
        {{ row.scholar?.name || '—' }}
      </template>
      <template #cell-status="{ value }">
        <StatusBadge :value="value" />
      </template>
      <template #cell-starts_at="{ value }">
        {{ value ? new Date(value).toLocaleString() : '—' }}
      </template>
      <template #cell-amount="{ row }">
        <span v-if="row.amount">{{ row.currency }} {{ row.amount }}</span>
        <span v-else>—</span>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from '../../bootstrap';
import DataTable from '../components/DataTable.vue';
import StatusBadge from '../components/StatusBadge.vue';

const table = ref(null);
const syncing = ref(false);
const syncMessage = ref('');

const columns = [
  { key: 'attendee_name', label: 'Attendee' },
  { key: 'scholar', label: 'Scholar', sortable: false },
  { key: 'status', label: 'Status' },
  { key: 'starts_at', label: 'Starts' },
  { key: 'duration_minutes', label: 'Mins' },
  { key: 'amount', label: 'Amount' },
];

async function sync() {
  syncing.value = true;
  syncMessage.value = '';
  try {
    const { data } = await axios.post('/api/admin/bookings/sync');
    syncMessage.value = `Synced ${data.data.total} bookings (${data.data.created} new, ${data.data.updated} updated).`;
    table.value?.reload();
  } catch (e) {
    syncMessage.value = e.response?.data?.message || 'Sync failed.';
  } finally {
    syncing.value = false;
  }
}
</script>
