import axios from 'axios';
import { sanitizeForm } from './utils/sanitize';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';

axios.interceptors.request.use((config) => {
  if (config.data && typeof config.data === 'object' && !(config.data instanceof FormData)) {
    config.data = sanitizeForm(config.data);
  }

  if (config.params && typeof config.params === 'object') {
    config.params = sanitizeForm(config.params);
  }

  return config;
});

axios.interceptors.response.use(
  (response) => response,
  async (error) => {
    const original = error.config;

    if (error.response?.status === 419 && original && !original._csrfRetry) {
      original._csrfRetry = true;
      await axios.get('/sanctum/csrf-cookie');
      return axios(original);
    }

    return Promise.reject(error);
  },
);

export default axios;
