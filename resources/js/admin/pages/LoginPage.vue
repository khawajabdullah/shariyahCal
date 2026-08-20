<template>
  <div class="admin-app admin-login-bg flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-[460px]">
      <div class="mb-8 text-center sm:text-left">
        <div class="inline-flex items-center gap-2 text-[#f5f5f6]">
          <svg width="16" height="16" class="text-srb-red-bright" viewBox="0 0 100 100" aria-hidden="true">
            <path fill="currentColor" d="M50 4 C52 22 62 32 50 50 C62 68 52 78 50 96 C48 78 38 68 50 50 C38 32 48 22 50 4Z"/>
            <path fill="currentColor" d="M4 50 C22 48 32 38 50 50 C68 38 78 48 96 50 C78 52 68 62 50 50 C32 62 22 52 4 50Z"/>
          </svg>
          <span class="font-serif text-[17px]">Shariyah Review Bureau</span>
        </div>
        <p class="mt-1 font-mono text-[10px] tracking-[0.16em] uppercase text-srb-muted-light">Scholar consultations · Admin</p>
      </div>

      <div class="admin-panel border-srb-grey-300 bg-white p-8 sm:p-10 shadow-[0_24px_60px_-28px_rgba(0,0,0,0.55)]">
        <p class="admin-eyebrow">Secure access</p>
        <h1 class="mt-3 text-[34px] leading-[1.1] text-srb-ink">Sign in to the console.</h1>
        <p class="mt-3 text-sm leading-relaxed text-srb-muted">
          Manage Madhāhib and language filters, sync the scholar roster from Cal.com, and review booking records.
        </p>

        <form class="mt-8 space-y-4" @submit.prevent="submit">
          <div>
            <label class="mb-1.5 block text-[12px] font-medium text-srb-muted">Email</label>
            <input v-model="form.email" type="email" required autocomplete="username" maxlength="255" class="admin-field">
          </div>
          <PasswordField v-model="form.password" label="Password" autocomplete="current-password" />
          <label class="flex items-center gap-2 text-sm text-srb-muted">
            <input v-model="form.remember" type="checkbox" class="accent-srb-red">
            Keep me signed in
          </label>
          <p v-if="route.query.passwordUpdated" class="text-sm text-emerald-700">
            Your password was updated. Sign in with your new password.
          </p>
          <p v-if="error" class="text-sm text-srb-red">{{ error }}</p>
          <button type="submit" class="admin-btn admin-btn-primary w-full" :disabled="submitting">
            {{ submitting ? 'Signing in…' : 'Sign in' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';
import PasswordField from '../components/PasswordField.vue';

const { login } = useAuth();
const router = useRouter();
const route = useRoute();
const submitting = ref(false);
const error = ref('');
const form = reactive({
  email: '',
  password: '',
  remember: true,
});

async function submit() {
  submitting.value = true;
  error.value = '';

  try {
    await login(form);
    router.replace(route.query.redirect || { name: 'admin.dashboard' });
  } catch (e) {
    error.value = e.response?.data?.message
      || e.response?.data?.errors?.email?.[0]
      || 'Unable to sign in.';
  } finally {
    submitting.value = false;
  }
}
</script>
