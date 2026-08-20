<template>
  <div class="admin-panel">
    <div class="flex flex-col gap-3 border-b border-srb-grey-150 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="relative w-full sm:max-w-sm">
        <input
          v-model="searchInput"
          type="search"
          class="admin-field"
          :placeholder="searchPlaceholder"
          @input="onSearch"
        >
      </div>
      <div class="flex items-center gap-2 text-sm text-srb-muted">
        <span class="font-mono text-[11px] uppercase tracking-wider">Show</span>
        <select v-model.number="length" class="admin-field !w-auto !py-1.5" @change="reload(true)">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
      </div>
    </div>

    <div class="relative overflow-x-auto">
      <div v-if="loading" class="absolute inset-0 bg-white/70 flex items-center justify-center text-sm text-srb-muted">
        Loading…
      </div>
      <table class="min-w-full text-left text-sm">
        <thead class="bg-srb-grey-100 text-[11px] uppercase tracking-[0.08em] font-mono text-srb-muted">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-4 py-3 font-medium whitespace-nowrap"
              :class="column.class"
            >
              <button
                v-if="column.sortable !== false"
                type="button"
                class="inline-flex items-center gap-1 hover:text-srb-red"
                @click="sortBy(column.key)"
              >
                {{ column.label }}
                <span v-if="sort === column.key">{{ dir === 'asc' ? '↑' : '↓' }}</span>
              </button>
              <span v-else>{{ column.label }}</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!rows.length && !loading">
            <td :colspan="columns.length" class="px-4 py-10 text-center text-srb-muted">No matching records.</td>
          </tr>
          <tr v-for="(row, index) in rows" :key="row.id ?? index" class="border-t border-srb-grey-150 hover:bg-srb-grey-100/70">
            <td v-for="column in columns" :key="column.key" class="px-4 py-3 align-middle" :class="column.class">
              <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                {{ display(row[column.key]) }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex flex-col gap-3 border-t border-srb-grey-150 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
      <p class="font-mono text-[11px] text-srb-muted">
        Showing {{ meta.from }} to {{ meta.to }} of {{ recordsFiltered }}
      </p>
      <div class="flex items-center gap-1">
        <button type="button" class="px-3 py-1.5 text-sm border border-srb-grey-300 disabled:opacity-40" :disabled="page <= 1" @click="go(page - 1)">Prev</button>
        <span class="px-3 py-1.5 text-sm text-srb-muted">{{ page }} / {{ meta.last_page }}</span>
        <button type="button" class="px-3 py-1.5 text-sm border border-srb-grey-300 disabled:opacity-40" :disabled="page >= meta.last_page" @click="go(page + 1)">Next</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import axios from '../../bootstrap';

const props = defineProps({
  endpoint: { type: String, required: true },
  columns: { type: Array, required: true },
  searchPlaceholder: { type: String, default: 'Search…' },
  extraParams: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['loaded']);

const rows = ref([]);
const loading = ref(false);
const searchInput = ref('');
const search = ref('');
const length = ref(10);
const start = ref(0);
const sort = ref(props.columns.find((column) => column.sortable !== false)?.key ?? 'id');
const dir = ref('asc');
const draw = ref(0);
const recordsFiltered = ref(0);
const recordsTotal = ref(0);
const meta = reactive({ current_page: 1, last_page: 1, from: 0, to: 0 });
const page = ref(1);

let searchTimer;

function display(value) {
  if (value === null || value === undefined || value === '') {
    return '—';
  }
  return value;
}

function onSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    search.value = searchInput.value;
    reload(true);
  }, 350);
}

function sortBy(key) {
  if (sort.value === key) {
    dir.value = dir.value === 'asc' ? 'desc' : 'asc';
  } else {
    sort.value = key;
    dir.value = 'asc';
  }
  reload(true);
}

function go(next) {
  page.value = next;
  start.value = (next - 1) * length.value;
  reload();
}

async function reload(reset = false) {
  if (reset) {
    page.value = 1;
    start.value = 0;
  }

  loading.value = true;
  draw.value += 1;
  const currentDraw = draw.value;

  try {
    const { data } = await axios.get(props.endpoint, {
      params: {
        draw: currentDraw,
        start: start.value,
        length: length.value,
        search: search.value,
        sort: sort.value,
        dir: dir.value,
        ...props.extraParams,
      },
    });

    if (currentDraw !== draw.value) {
      return;
    }

    rows.value = data.data ?? [];
    recordsFiltered.value = data.recordsFiltered ?? 0;
    recordsTotal.value = data.recordsTotal ?? 0;
    Object.assign(meta, data.meta ?? meta);
    page.value = meta.current_page ?? 1;
    emit('loaded', data);
  } finally {
    if (currentDraw === draw.value) {
      loading.value = false;
    }
  }
}

onMounted(() => reload());
watch(() => props.endpoint, () => reload(true));

defineExpose({ reload });
</script>
