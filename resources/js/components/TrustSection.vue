<template>
  <section id="trust">
    <div class="wrap">
      <div class="section-head">
        <div class="eyebrow">WHY IT'S RELIABLE</div>
        <h2>Credentials you can check, not just claim.</h2>
      </div>
      <div class="trust-grid">
        <div v-for="t in cards" :key="t.title" class="tcard">
          <h4>{{ t.title }}</h4>
          <p>{{ t.desc }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const cards = [
  { title: 'Independently accredited', desc: 'SRB is licensed by the Central Bank of Bahrain. Scholars are listed with their qualifications, current board seats, and AAOIFI certifications where held.' },
  { title: 'A record of every call', desc: 'Each session closes with a written, AI-assisted summary sent to you — useful if a ruling needs to be referenced again, by you or your compliance team.' },
  { title: 'Matched, not assigned', desc: "You choose the scholar, the madhhab, and the language. If a topic needs a different specialist, we'll say so before you pay." },
];
</script>

<style scoped>
section { padding: 88px 0; }
.trust-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; }
.tcard { padding: 26px; border: 1px solid var(--grey-300); }
.tcard h4 { font-size: 15.5px; margin-bottom: 9px; }
.tcard p { font-size: 13.5px; color: var(--text-muted-ink); margin: 0; }

@media (max-width: 920px) { .trust-grid { grid-template-columns: 1fr; } }
</style>
