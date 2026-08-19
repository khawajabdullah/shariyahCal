<template>
  <header class="site-header">
    <div class="navrow">
      <router-link to="/" class="brand">
        <svg class="mark star8" width="20" height="20"><use href="#star8"/></svg>
        <div class="name">Shariyah Review Bureau<small>SCHOLAR CONSULTATIONS</small></div>
      </router-link>
      <nav class="links">
        <router-link to="/#directory">Scholars</router-link>
        <router-link to="/#how">How it works</router-link>
        <router-link to="/#institutions">For institutions</router-link>
        <router-link to="/#trust">Accreditation</router-link>
      </nav>
      <router-link to="/#directory" class="btn btn-red">Book a scholar</router-link>
    </div>
  </header>
</template>

<style scoped>
.site-header {
  position: sticky; top: 0; z-index: 40;
  background: rgba(38, 40, 44, 0.94);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(165, 0, 52, 0.24);
}
.navrow {
  display: flex; align-items: center; justify-content: space-between;
  padding: 18px 32px; max-width: 1180px; margin: 0 auto;
}
.brand { display: flex; align-items: center; gap: 10px; color: var(--text-light); }
.brand .mark { color: var(--red-bright); }
.brand .name { font-family: 'Spectral', serif; font-size: 17px; font-weight: 600; letter-spacing: 0.01em; }
.brand .name small {
  display: block; font-family: 'IBM Plex Mono', monospace; font-size: 9.5px;
  letter-spacing: 0.14em; color: var(--text-muted-light); font-weight: 400; margin-top: 1px;
}
nav.links { display: flex; gap: 32px; align-items: center; }
nav.links a { font-size: 13.5px; color: var(--text-muted-light); transition: color .15s ease; }
nav.links a:hover { color: var(--text-light); }

@media (max-width: 920px) {
  nav.links { display: none; }
}
</style>
