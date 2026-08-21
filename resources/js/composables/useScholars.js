import { ref } from 'vue';
import axios from '../bootstrap';

const scholars = ref([]);
const filters = ref({ madhahib: [], languages: [] });
const loading = ref(false);
const loaded = ref(false);
const error = ref(null);
let loadPromise = null;

export function useScholars() {
  function load() {
    if (loadPromise) return loadPromise;

    loading.value = true;
    error.value = null;

    loadPromise = axios.get('/api/scholars')
      .then(({ data }) => {
        scholars.value = Array.isArray(data?.data) ? data.data : [];
        filters.value = {
          madhahib: Array.isArray(data?.filters?.madhahib) ? data.filters.madhahib : [],
          languages: Array.isArray(data?.filters?.languages) ? data.filters.languages : [],
        };
      })
      .catch((e) => {
        error.value = e.response?.data?.message || 'Unable to load scholars right now.';
        scholars.value = [];
        loadPromise = null;
      })
      .finally(() => {
        loading.value = false;
        loaded.value = true;
      });

    return loadPromise;
  }

  function findById(id) {
    return scholars.value.find((s) => s.id === id) ?? null;
  }

  return { scholars, filters, loading, loaded, error, load, findById };
}
