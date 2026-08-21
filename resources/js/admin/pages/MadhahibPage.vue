<template>
  <div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="admin-eyebrow">Filters</p>
        <h1 class="mt-3 text-[34px] text-srb-ink">Madhāhib</h1>
        <p class="mt-2 text-sm text-srb-muted">These tags appear as filter chips on the public scholar directory.</p>
      </div>
      <button type="button" class="admin-btn admin-btn-primary" @click="openCreate">
        Add madhhab
      </button>
    </div>

    <DataTable ref="table" endpoint="/api/admin/madhahib" :columns="columns">
      <template #cell-is_active="{ value }">
        <StatusBadge :value="Boolean(value)" />
      </template>
      <template #cell-actions="{ row }">
        <div class="flex gap-3">
          <button type="button" class="text-srb-red text-sm" @click="openEdit(row)">Edit</button>
          <button type="button" class="text-srb-muted text-sm hover:text-srb-red" @click="askDelete(row)">Delete</button>
        </div>
      </template>
    </DataTable>

    <FormDrawer :open="drawerOpen" :title="editing ? 'Edit madhhab' : 'Add madhhab'" eyebrow="Madhhab" @close="drawerOpen = false">
      <form class="space-y-4" @submit.prevent="save">
        <div>
          <label class="block text-[12px] mb-1.5 text-srb-muted">Name</label>
          <input v-model="form.name" required maxlength="120" class="admin-field">
        </div>
        <div>
          <label class="block text-[12px] mb-1.5 text-srb-muted">Slug</label>
          <input v-model="form.slug" maxlength="140" class="admin-field" placeholder="Generated from name if empty">
        </div>
        <div>
          <label class="block text-[12px] mb-1.5 text-srb-muted">Sort order</label>
          <input v-model.number="form.sort_order" type="number" min="0" class="admin-field">
        </div>
        <label class="flex items-center gap-2 text-sm">
          <input v-model="form.is_active" type="checkbox" class="accent-srb-red"> Active on public filters
        </label>
        <p v-if="formError" class="text-sm text-srb-red">{{ formError }}</p>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" class="admin-btn admin-btn-ghost" :disabled="saving" @click="drawerOpen = false">Cancel</button>
          <button type="submit" class="admin-btn admin-btn-primary" :disabled="saving">
            {{ saving ? 'Saving…' : 'Save' }}
          </button>
        </div>
      </form>
    </FormDrawer>

    <ConfirmModal
      :open="!!pendingDelete"
      title="Delete this madhhab?"
      message="Scholars using it will keep their other data; the tag will be removed."
      :busy="deleting"
      busy-label="Deleting…"
      @cancel="!deleting && (pendingDelete = null)"
      @confirm="destroy"
    />
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import axios from '../../bootstrap';
import DataTable from '../components/DataTable.vue';
import FormDrawer from '../components/FormDrawer.vue';
import ConfirmModal from '../components/ConfirmModal.vue';
import StatusBadge from '../components/StatusBadge.vue';

const table = ref(null);
const drawerOpen = ref(false);
const editing = ref(null);
const pendingDelete = ref(null);
const formError = ref('');
const saving = ref(false);
const deleting = ref(false);
const form = reactive({ name: '', slug: '', sort_order: 0, is_active: true });

const columns = [
  { key: 'name', label: 'Name' },
  { key: 'slug', label: 'Slug' },
  { key: 'sort_order', label: 'Order' },
  { key: 'scholars_count', label: 'Scholars', sortable: false },
  { key: 'is_active', label: 'Status' },
  { key: 'actions', label: '', sortable: false, class: 'text-right' },
];

function resetForm() {
  form.name = '';
  form.slug = '';
  form.sort_order = 0;
  form.is_active = true;
  formError.value = '';
}

function openCreate() {
  editing.value = null;
  resetForm();
  drawerOpen.value = true;
}

function openEdit(row) {
  editing.value = row;
  form.name = row.name;
  form.slug = row.slug;
  form.sort_order = row.sort_order;
  form.is_active = row.is_active;
  formError.value = '';
  drawerOpen.value = true;
}

function askDelete(row) {
  pendingDelete.value = row;
}

async function save() {
  if (saving.value) return;
  formError.value = '';
  saving.value = true;
  const payload = { ...form };

  try {
    if (editing.value) {
      await axios.put(`/api/admin/madhahib/${editing.value.id}`, payload);
    } else {
      await axios.post('/api/admin/madhahib', payload);
    }
    drawerOpen.value = false;
    table.value?.reload();
  } catch (e) {
    formError.value = Object.values(e.response?.data?.errors ?? {})[0]?.[0]
      || e.response?.data?.message
      || 'Unable to save.';
  } finally {
    saving.value = false;
  }
}

async function destroy() {
  if (!pendingDelete.value || deleting.value) return;
  deleting.value = true;
  try {
    await axios.delete(`/api/admin/madhahib/${pendingDelete.value.id}`);
    pendingDelete.value = null;
    table.value?.reload();
  } finally {
    deleting.value = false;
  }
}
</script>
