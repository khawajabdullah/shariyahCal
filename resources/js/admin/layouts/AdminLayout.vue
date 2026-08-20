<template>
  <div class="admin-app min-h-screen lg:grid lg:grid-cols-[272px_1fr]">
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-30 bg-srb-charcoal/45 lg:hidden"
      @click="sidebarOpen = false"
    />

    <aside
      class="fixed inset-y-0 left-0 z-40 flex w-[272px] flex-col overflow-hidden border-r border-[rgba(165,0,52,0.24)] bg-srb-charcoal text-srb-light transition-transform lg:sticky lg:top-0 lg:h-dvh lg:self-start lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <div class="shrink-0 border-b border-white/10 px-6 py-6">
        <div class="flex items-center gap-2.5 text-white">
          <svg width="15" height="15" class="text-srb-red-bright shrink-0" viewBox="0 0 100 100" aria-hidden="true">
            <path fill="currentColor" d="M50 4 C52 22 62 32 50 50 C62 68 52 78 50 96 C48 78 38 68 50 50 C38 32 48 22 50 4Z"/>
            <path fill="currentColor" d="M4 50 C22 48 32 38 50 50 C68 38 78 48 96 50 C78 52 68 62 50 50 C32 62 22 52 4 50Z"/>
          </svg>
          <div>
            <p class="font-serif text-[16px] leading-tight">Shariyah Review Bureau</p>
            <p class="mt-1 font-mono text-[9.5px] tracking-[0.16em] uppercase text-srb-muted-light">Admin console</p>
          </div>
        </div>
      </div>

      <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto px-3 py-5">
        <router-link
          v-for="item in nav"
          :key="item.label"
          :to="item.to"
          custom
          v-slot="{ href, navigate, isActive, isExactActive }"
        >
          <a
            :href="href"
            class="block border-l-2 border-transparent px-3 py-2.5 text-[13.5px] text-srb-muted-light transition-colors hover:bg-white/5 hover:text-white"
            :class="(item.exact ? isExactActive : isActive) ? '!border-srb-red-bright !bg-white/[0.08] !text-white' : ''"
            @click="(e) => { navigate(e); sidebarOpen = false }"
          >
            {{ item.label }}
          </a>
        </router-link>
      </nav>

      <div class="shrink-0 border-t border-white/10 px-6 py-5">
        <p class="truncate text-sm text-white">{{ user?.name }}</p>
        <p class="truncate text-[12px] text-srb-muted-light">{{ user?.email }}</p>
        <button type="button" class="mt-4 text-[12.5px] font-medium text-srb-red-bright hover:text-white" @click="onLogout">
          Sign out
        </button>
      </div>
    </aside>

    <div class="min-w-0">
      <header class="sticky top-0 z-20 flex items-center justify-between gap-4 border-b border-srb-grey-300 bg-white/92 px-4 py-3.5 backdrop-blur lg:px-8">
        <button type="button" class="admin-btn admin-btn-ghost !px-3 !py-1.5 lg:hidden" @click="sidebarOpen = true">
          Menu
        </button>
        <p class="font-mono text-[11px] tracking-[0.12em] uppercase text-srb-muted">{{ title }}</p>
        <div class="hidden text-sm text-srb-muted sm:block">SRB Scholar Consultations</div>
      </header>
      <main class="px-4 py-8 lg:px-8">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';

const route = useRoute();
const router = useRouter();
const { user, logout } = useAuth();
const sidebarOpen = ref(false);

const nav = [
  { to: { name: 'admin.dashboard' }, label: 'Overview', exact: true },
  { to: { name: 'admin.madhahib' }, label: 'Madhāhib', exact: false },
  { to: { name: 'admin.languages' }, label: 'Languages', exact: false },
  { to: { name: 'admin.scholars' }, label: 'Scholars', exact: false },
  { to: { name: 'admin.bookings' }, label: 'Bookings', exact: false },
  { to: { name: 'admin.settings' }, label: 'Settings', exact: false },
];

const titles = {
  'admin.dashboard': 'Overview',
  'admin.madhahib': 'Madhāhib',
  'admin.languages': 'Languages',
  'admin.scholars': 'Scholars',
  'admin.bookings': 'Bookings',
  'admin.settings': 'Settings',
};

const title = computed(() => titles[route.name] ?? 'Dashboard');

async function onLogout() {
  await logout();
  router.push({ name: 'admin.login' });
}
</script>
