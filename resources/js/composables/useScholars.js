import { ref } from 'vue';
import axios from 'axios';
import { PRICING } from '../data/scholars';

const scholars = ref([]);
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

  return { scholars, loading, loaded, error, load, findById, PRICING };
}
