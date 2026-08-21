<template>
  <div>
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <p class="admin-eyebrow">Roster</p>
        <h1 class="mt-3 text-[34px] text-srb-ink">Scholars</h1>
        <p class="mt-2 text-sm text-srb-muted">Synced from Cal.com without duplicates. Assign Madhāhib, languages, API keys, and session prices.</p>
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
      <template #cell-has_cal_api_key="{ value }">
        <StatusBadge :value="Boolean(value)" />
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
        <h3 class="text-srb-ink text-lg font-medium">{{ name }}</h3>
        <p class="text-srb-muted text-sm">{{ email }}</p>
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
        <div>
          <label class="block text-[12px] mb-1.5 text-srb-muted">Cal.com API key</label>
          <input v-model="form.cal_api_key" type="password" autocomplete="off" class="admin-field" :placeholder="hasApiKey ? '•••••••• (leave blank to keep)' : 'cal_live_…'">
          <p class="mt-1 text-[11px] text-srb-muted">Used only to sync this scholar’s event types. Stored encrypted.</p>
        </div>
        <label class="flex items-center gap-2 text-sm">
          <input v-model="form.is_active" type="checkbox" class="accent-srb-red"> Visible on public directory
        </label>

        <div class="border-t border-srb-grey-300 pt-4">
          <div class="mb-3 flex items-center justify-between gap-3">
            <div>
              <h4 class="text-srb-ink text-sm font-medium">Session lengths (event types)</h4>
              <p class="text-[11px] text-srb-muted">Sync from Cal.com, then set the price shown on the public site.</p>
            </div>
            <button type="button" class="admin-btn admin-btn-ghost text-sm" :disabled="saving || syncingEventTypes || !!savingEventTypeId || !canSyncEventTypes" @click="syncEventTypes">
              {{ syncingEventTypes ? 'Syncing…' : 'Sync event types' }}
            </button>
          </div>
          <p v-if="eventTypeMessage" class="mb-3 text-sm text-srb-muted">{{ eventTypeMessage }}</p>
          <div v-if="!eventTypes.length" class="text-sm text-srb-muted">No event types yet. Save an API key and sync.</div>
          <div v-else class="space-y-3">
            <div v-for="item in eventTypes" :key="item.id" class="border border-srb-grey-300 p-3">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <div class="text-sm font-medium text-srb-ink">{{ item.title }}</div>
                  <div class="font-mono text-[11px] text-srb-muted">{{ item.length_in_minutes }} min · {{ item.slug }}{{ item.is_hidden ? ' · hidden' : '' }}</div>
                </div>
                <label class="flex items-center gap-1.5 text-xs text-srb-muted">
                  <input v-model="item.is_active" type="checkbox" class="accent-srb-red" @change="saveEventType(item)">
                  Active
                </label>
              </div>
              <div class="mt-3 grid grid-cols-[1fr_88px_auto] gap-2 items-end">
                <div>
                  <label class="block text-[11px] mb-1 text-srb-muted">Price</label>
                  <input v-model.number="item.price" type="number" min="0" step="0.01" class="admin-field" placeholder="0.00">
                </div>
                <div>
                  <label class="block text-[11px] mb-1 text-srb-muted">Currency</label>
                  <input v-model="item.currency" maxlength="3" class="admin-field uppercase" placeholder="usd">
                </div>
                <button
                  type="button"
                  class="admin-btn admin-btn-primary"
                  :disabled="saving || savingEventTypeId === item.id || syncingEventTypes"
                  @click="saveEventType(item)"
                >
                  {{ savingEventTypeId === item.id ? 'Saving…' : 'Save' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <p v-if="formError" class="text-sm text-srb-red">{{ formError }}</p>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" class="admin-btn admin-btn-ghost" :disabled="saving || syncingEventTypes || !!savingEventTypeId" @click="drawerOpen = false">Cancel</button>
          <button type="submit" class="admin-btn admin-btn-primary" :disabled="saving || syncingEventTypes || !!savingEventTypeId">
            {{ saving ? 'Saving…' : 'Save' }}
          </button>
        </div>
      </form>
    </FormDrawer>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
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
const saving = ref(false);
const madhahib = ref([]);
const languages = ref([]);
const name = ref('');
const email = ref('');
const hasApiKey = ref(false);
const eventTypes = ref([]);
const syncingEventTypes = ref(false);
const savingEventTypeId = ref(null);
const eventTypeMessage = ref('');
const form = reactive({
  country: '',
  flag: '',
  madhhab_id: null,
  language_ids: [],
  tier: 'standard',
  is_active: true,
  cal_api_key: '',
});

const columns = [
  { key: 'name', label: 'Scholar' },
  { key: 'email', label: 'Email' },
  { key: 'country', label: 'Country' },
  { key: 'madhhab', label: 'Madhhab', sortable: false },
  { key: 'languages', label: 'Languages', sortable: false },
  { key: 'has_cal_api_key', label: 'API key', sortable: false },
  { key: 'tier', label: 'Tier' },
  { key: 'is_active', label: 'Status' },
  { key: 'actions', label: '', sortable: false, class: 'text-right' },
];

const canSyncEventTypes = computed(() => hasApiKey.value || Boolean(form.cal_api_key?.trim()));

onMounted(async () => {
  const [m, l] = await Promise.all([
    axios.get('/api/admin/madhahib/options'),
    axios.get('/api/admin/languages/options'),
  ]);
  madhahib.value = m.data.data ?? [];
  languages.value = l.data.data ?? [];
});

async function openEdit(row) {
  editing.value = row;
  name.value = row.name;
  email.value = row.email || '';
  form.country = row.country || '';
  form.flag = row.flag || '';
  form.madhhab_id = row.madhhab_id;
  form.language_ids = row.language_ids || (row.languages || []).map((item) => item.id);
  form.tier = row.tier || 'standard';
  form.is_active = row.is_active;
  form.cal_api_key = '';
  hasApiKey.value = Boolean(row.has_cal_api_key);
  formError.value = '';
  eventTypeMessage.value = '';
  drawerOpen.value = true;

  try {
    const { data } = await axios.get(`/api/admin/scholars/${row.id}`);
    const detail = data.data ?? {};
    hasApiKey.value = Boolean(detail.has_cal_api_key);
    eventTypes.value = (detail.event_types || []).map(normalizeEventType);
  } catch {
    eventTypes.value = [];
  }
}

function normalizeEventType(item) {
  return {
    ...item,
    price: item.price ?? null,
    currency: item.currency || 'usd',
    is_active: item.is_active !== false,
  };
}

async function save() {
  if (saving.value) return;
  formError.value = '';
  saving.value = true;
  try {
    const payload = {
      country: form.country,
      flag: form.flag,
      madhhab_id: form.madhhab_id,
      language_ids: form.language_ids,
      tier: form.tier,
      is_active: form.is_active,
    };
    if (form.cal_api_key.trim()) {
      payload.cal_api_key = form.cal_api_key.trim();
    }
    const { data } = await axios.put(`/api/admin/scholars/${editing.value.id}`, payload);
    const detail = data.data ?? {};
    hasApiKey.value = Boolean(detail.has_cal_api_key);
    form.cal_api_key = '';
    if (detail.event_types) {
      eventTypes.value = detail.event_types.map(normalizeEventType);
    }
    editing.value = { ...editing.value, ...detail };
    table.value?.reload();
  } catch (e) {
    formError.value = Object.values(e.response?.data?.errors ?? {})[0]?.[0]
      || e.response?.data?.message
      || 'Unable to save.';
  } finally {
    saving.value = false;
  }
}

async function syncEventTypes() {
  if (!editing.value) return;
  eventTypeMessage.value = '';
  syncingEventTypes.value = true;
  try {
    if (form.cal_api_key.trim()) {
      await axios.put(`/api/admin/scholars/${editing.value.id}`, {
        cal_api_key: form.cal_api_key.trim(),
      });
      form.cal_api_key = '';
      hasApiKey.value = true;
    }
    const { data } = await axios.post(`/api/admin/scholars/${editing.value.id}/event-types/sync`);
    eventTypes.value = (data.data?.event_types || []).map(normalizeEventType);
    const sync = data.data?.sync || {};
    eventTypeMessage.value = `Synced ${sync.total ?? 0} event types (${sync.created ?? 0} new, ${sync.updated ?? 0} updated).`;
    table.value?.reload();
  } catch (e) {
    eventTypeMessage.value = e.response?.data?.message || 'Event type sync failed.';
  } finally {
    syncingEventTypes.value = false;
  }
}

async function saveEventType(item) {
  if (!editing.value || savingEventTypeId.value) return;
  eventTypeMessage.value = '';
  savingEventTypeId.value = item.id;
  try {
    const { data } = await axios.put(`/api/admin/scholars/${editing.value.id}/event-types/${item.id}`, {
      price: item.price,
      currency: item.currency || 'usd',
      is_active: item.is_active,
    });
    const updated = normalizeEventType(data.data ?? item);
    eventTypes.value = eventTypes.value.map((row) => (row.id === updated.id ? updated : row));
    eventTypeMessage.value = `Saved price for ${updated.title}.`;
  } catch (e) {
    eventTypeMessage.value = Object.values(e.response?.data?.errors ?? {})[0]?.[0]
      || e.response?.data?.message
      || 'Unable to save event type.';
  } finally {
    savingEventTypeId.value = null;
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
