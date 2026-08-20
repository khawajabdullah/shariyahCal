<template>
  <div>
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <p class="admin-eyebrow">Roster</p>
        <h1 class="mt-3 text-[34px] text-srb-ink">Scholars</h1>
        <p class="mt-2 text-sm text-srb-muted">Synced from Cal.com without duplicates. Assign Madhāhib and languages for public filters.</p>
      </div>
      <button type="button" class="admin-btn admin-btn-dark" :disabled="syncing" @click="sync">
        {{ syncing ? 'Syncing…' : 'Sync from Cal.com' }}
      </button>
    </div>
    <p v-if="syncMessage" class="mb-4 text-sm text-srb-muted">{{ syncMessage }}</p>

    <DataTable ref="table" endpoint="/api/admin/scholars" :columns="columns">
      <template #cell-name="{ row }">
        <div class="font-medium text-srb-ink">{{ row.name }}</div>
        <div class="font-mono text-[11px] text-srb-muted">{{ row.cal_username }}</div>
      </template>
      <template #cell-madhhab="{ row }">
        {{ row.madhhab?.name || '—' }}
      </template>
      <template #cell-languages="{ row }">
        {{ (row.languages || []).map((item) => item.name).join(', ') || '—' }}
      </template>
      <template #cell-is_active="{ value }">
        <StatusBadge :value="Boolean(value)" />
      </template>
      <template #cell-actions="{ row }">
        <button type="button" class="text-srb-red text-sm" @click="openEdit(row)">Edit</button>
      </template>
    </DataTable>

    <FormDrawer :open="drawerOpen" title="Edit scholar" eyebrow="Scholar" @close="drawerOpen = false">
      <form class="space-y-4" @submit.prevent="save">
        <div>
          <label class="block text-[12px] mb-1.5 text-srb-muted">Name</label>
          <input v-model="form.name" required maxlength="180" class="admin-field">
        </div>
        <div>
          <label class="block text-[12px] mb-1.5 text-srb-muted">Cal.com email</label>
          <input :value="form.email" readonly class="admin-field bg-srb-grey-100 text-srb-muted">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-[12px] mb-1.5 text-srb-muted">Country</label>
            <input v-model="form.country" maxlength="80" class="admin-field">
          </div>
          <div>
            <label class="block text-[12px] mb-1.5 text-srb-muted">Flag</label>
            <input v-model="form.flag" maxlength="8" class="admin-field" inputmode="text">
          </div>
        </div>
        <div>
          <label class="block text-[12px] mb-1.5 text-srb-muted">Madhhab</label>
          <select v-model="form.madhhab_id" class="admin-field">
            <option :value="null">Unassigned</option>
            <option v-for="item in madhahib" :key="item.id" :value="item.id">{{ item.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-[12px] mb-1.5 text-srb-muted">Languages</label>
          <div class="flex flex-wrap gap-2">
            <label v-for="item in languages" :key="item.id" class="flex items-center gap-1.5 text-sm border border-srb-grey-300 px-2 py-1">
              <input v-model="form.language_ids" type="checkbox" :value="item.id" class="accent-srb-red">
              {{ item.name }}
            </label>
          </div>
        </div>
        <div>
          <label class="block text-[12px] mb-1.5 text-srb-muted">Tier</label>
          <select v-model="form.tier" class="admin-field">
            <option value="standard">Standard</option>
            <option value="institutional">Institutional</option>
          </select>
        </div>
        <label class="flex items-center gap-2 text-sm">
          <input v-model="form.is_active" type="checkbox" class="accent-srb-red"> Visible on public directory
        </label>
        <p v-if="formError" class="text-sm text-srb-red">{{ formError }}</p>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" class="admin-btn admin-btn-ghost" @click="drawerOpen = false">Cancel</button>
          <button type="submit" class="admin-btn admin-btn-primary">Save</button>
        </div>
      </form>
    </FormDrawer>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import axios from '../../bootstrap';
import DataTable from '../components/DataTable.vue';
import FormDrawer from '../components/FormDrawer.vue';
import StatusBadge from '../components/StatusBadge.vue';

const table = ref(null);
const drawerOpen = ref(false);
const editing = ref(null);
const syncing = ref(false);
const syncMessage = ref('');
const formError = ref('');
const madhahib = ref([]);
const languages = ref([]);
const form = reactive({
  name: '',
  email: '',
  country: '',
  flag: '',
  madhhab_id: null,
  language_ids: [],
  tier: 'standard',
  is_active: true,
});

const columns = [
  { key: 'name', label: 'Scholar' },
  { key: 'email', label: 'Email' },
  { key: 'country', label: 'Country' },
  { key: 'madhhab', label: 'Madhhab', sortable: false },
  { key: 'languages', label: 'Languages', sortable: false },
  { key: 'tier', label: 'Tier' },
  { key: 'is_active', label: 'Status' },
  { key: 'actions', label: '', sortable: false, class: 'text-right' },
];

onMounted(async () => {
  const [m, l] = await Promise.all([
    axios.get('/api/admin/madhahib/options'),
    axios.get('/api/admin/languages/options'),
  ]);
  madhahib.value = m.data.data ?? [];
  languages.value = l.data.data ?? [];
});

function openEdit(row) {
  editing.value = row;
  form.name = row.name;
  form.email = row.email || '';
  form.country = row.country || '';
  form.flag = row.flag || '';
  form.madhhab_id = row.madhhab_id;
  form.language_ids = row.language_ids || (row.languages || []).map((item) => item.id);
  form.tier = row.tier || 'standard';
  form.is_active = row.is_active;
  formError.value = '';
  drawerOpen.value = true;
}

async function save() {
  formError.value = '';
  try {
    await axios.put(`/api/admin/scholars/${editing.value.id}`, { ...form });
    drawerOpen.value = false;
    table.value?.reload();
  } catch (e) {
    formError.value = Object.values(e.response?.data?.errors ?? {})[0]?.[0]
      || e.response?.data?.message
      || 'Unable to save.';
  }
}

async function sync() {
  syncing.value = true;
  syncMessage.value = '';
  try {
    const { data } = await axios.post('/api/admin/scholars/sync');
    syncMessage.value = `Synced ${data.data.total} scholars (${data.data.created} new, ${data.data.updated} updated).`;
    table.value?.reload();
  } catch (e) {
    syncMessage.value = e.response?.data?.message || 'Sync failed.';
  } finally {
    syncing.value = false;
  }
}
</script>
