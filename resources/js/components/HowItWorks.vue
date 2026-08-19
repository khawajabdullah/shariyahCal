<template>
  <section id="how">
    <div class="wrap">
      <div class="section-head">
        <div class="eyebrow">THE PROCESS</div>
        <h2>Four steps, one sitting.</h2>
        <p>No proposals, no engagement letters. Choose a scholar, pick a time, and join the call — the way you'd book any other appointment.</p>
      </div>
      <div class="steps">
        <div v-for="(s, i) in steps" :key="i" class="step">
          <div class="num">{{ String(i + 1).padStart(2, '0') }}</div>
          <h3>{{ s.title }}</h3>
          <p>{{ s.desc }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const steps = [
  { title: 'Choose your scholar', desc: 'Filter by madhhab, language, country, or specialization until you find the right fit.' },
  { title: 'Pick a time', desc: "See real availability and reserve a slot in the scholar's own calendar." },
  { title: 'Confirm & pay', desc: "Secure checkout. You'll get a calendar invite and a joining link by email." },
  { title: 'Meet on video', desc: 'Join directly on the platform. A written summary follows the call, for your records.' },
];
</script>

<style scoped>
section { padding: 88px 0; }
.steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; }
.step { border-top: 2px solid var(--red); padding-top: 18px; }
.step .num { font-family: 'IBM Plex Mono', monospace; color: var(--red-deep); font-size: 13px; }
.step h3 { font-size: 18px; margin: 10px 0 8px; color: var(--text-ink); }
.step p { font-size: 14px; color: var(--text-muted-ink); margin: 0; }

@media (max-width: 920px) { .steps { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 560px) { .steps { grid-template-columns: 1fr; } }
</style>
