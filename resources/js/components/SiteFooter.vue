<template>
  <footer>
    <div class="wrap">
      <div class="fmark">
        <svg width="16" height="16" class="star8"><use href="#star8"/></svg>
        Shariyah Review Bureau
      </div>
      <div>© {{ new Date().getFullYear() }} Shariyah Review Bureau. Template for internal review — not a live booking system.</div>
    </div>
  </footer>
</template>

<style scoped>
footer {
  background: var(--charcoal); color: var(--text-muted-light); padding: 48px 0 34px;
  font-size: 13px; border-top: 1px solid var(--line-dark);
}
footer .wrap { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px; }
.fmark { display: flex; align-items: center; gap: 9px; color: var(--text-light); font-family: 'Spectral', serif; font-size: 15px; }
</style>
