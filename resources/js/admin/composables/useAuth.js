import { ref } from 'vue';
import axios from '../../bootstrap';

const user = ref(null);
const loaded = ref(false);
let pending = null;

export function useAuth() {
  async function csrf() {
    await axios.get('/sanctum/csrf-cookie');
  }

  async function fetchUser() {
    try {
      const { data } = await axios.get('/api/user');
      user.value = data.data ?? null;
    } catch {
      user.value = null;
    } finally {
      loaded.value = true;
    }

    return user.value;
  }

  function ensure() {
    if (loaded.value) {
      return Promise.resolve(user.value);
    }

    if (!pending) {
      pending = fetchUser().finally(() => {
        pending = null;
      });
    }

    return pending;
  }

  async function login(payload) {
    await csrf();
    const { data } = await axios.post('/api/login', payload);
    await csrf();
    user.value = data.data;
    loaded.value = true;
    return user.value;
  }

  async function logout() {
    try {
      await axios.post('/api/logout');
    } finally {
      clearSession();
    }
  }

  function clearSession() {
    user.value = null;
    loaded.value = true;
  }

  return { user, loaded, ensure, login, logout, fetchUser, clearSession };
}
