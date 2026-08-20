<template>
  <div>
    <div class="mb-8 max-w-2xl">
      <p class="admin-eyebrow">Account</p>
      <h1 class="mt-3 text-[34px] leading-tight text-srb-ink">Settings</h1>
      <p class="mt-3 text-[15px] leading-relaxed text-srb-muted">
        Update your admin profile. Changing your password signs you out of every session so you can sign in again.
      </p>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
      <section class="admin-panel p-6 sm:p-8">
        <p class="admin-eyebrow">Profile</p>
        <h2 class="mt-2 text-xl text-srb-ink">Name and email</h2>
        <form class="mt-6 space-y-4" @submit.prevent="saveProfile">
          <div>
            <label class="mb-1.5 block text-[12px] font-medium text-srb-muted">Name</label>
            <input v-model="profile.name" type="text" required maxlength="120" autocomplete="name" class="admin-field">
          </div>
          <div>
            <label class="mb-1.5 block text-[12px] font-medium text-srb-muted">Email</label>
            <input v-model="profile.email" type="email" required maxlength="255" autocomplete="email" class="admin-field">
          </div>
          <p v-if="profileSuccess" class="text-sm text-emerald-700">{{ profileSuccess }}</p>
          <p v-if="profileError" class="text-sm text-srb-red">{{ profileError }}</p>
          <div class="pt-2">
            <button type="submit" class="admin-btn admin-btn-primary" :disabled="savingProfile">
              {{ savingProfile ? 'Saving…' : 'Save profile' }}
            </button>
          </div>
        </form>
      </section>

      <section class="admin-panel p-6 sm:p-8">
        <p class="admin-eyebrow">Security</p>
        <h2 class="mt-2 text-xl text-srb-ink">Change password</h2>
        <p class="mt-2 text-sm text-srb-muted">You will be signed out everywhere after a successful update.</p>
        <form class="mt-6 space-y-4" @submit.prevent="savePassword">
          <PasswordField v-model="password.current_password" label="Current password" autocomplete="current-password" />
          <PasswordField v-model="password.password" label="New password" autocomplete="new-password" />
          <PasswordField v-model="password.password_confirmation" label="Confirm new password" autocomplete="new-password" />
          <p class="text-[12px] text-srb-muted">Use at least 8 characters, including a letter and a number.</p>
          <p v-if="passwordError" class="text-sm text-srb-red">{{ passwordError }}</p>
          <div class="pt-2">
            <button type="submit" class="admin-btn admin-btn-dark" :disabled="savingPassword">
              {{ savingPassword ? 'Updating…' : 'Update password' }}
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from '../../bootstrap';
import { useAuth } from '../composables/useAuth';
import PasswordField from '../components/PasswordField.vue';

const router = useRouter();
const { user, clearSession } = useAuth();

const savingProfile = ref(false);
const savingPassword = ref(false);
const profileSuccess = ref('');
const profileError = ref('');
const passwordError = ref('');

const profile = reactive({
  name: '',
  email: '',
});

const password = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});

onMounted(() => {
  profile.name = user.value?.name || '';
  profile.email = user.value?.email || '';
});

async function saveProfile() {
  savingProfile.value = true;
  profileSuccess.value = '';
  profileError.value = '';

  try {
    const { data } = await axios.put('/api/admin/profile', { ...profile });
    user.value = data.data;
    profile.name = data.data.name;
    profile.email = data.data.email;
    profileSuccess.value = 'Profile updated.';
  } catch (e) {
    profileError.value = Object.values(e.response?.data?.errors ?? {})[0]?.[0]
      || e.response?.data?.message
      || 'Unable to update profile.';
  } finally {
    savingProfile.value = false;
  }
}

async function savePassword() {
  savingPassword.value = true;
  passwordError.value = '';

  if (password.password !== password.password_confirmation) {
    passwordError.value = 'The new password confirmation does not match.';
    savingPassword.value = false;
    return;
  }

  try {
    await axios.put('/api/admin/profile/password', { ...password });
    clearSession();
    router.replace({ name: 'admin.login', query: { passwordUpdated: '1' } });
  } catch (e) {
    passwordError.value = Object.values(e.response?.data?.errors ?? {})[0]?.[0]
      || e.response?.data?.message
      || 'Unable to update password.';
  } finally {
    savingPassword.value = false;
  }
}
</script>
