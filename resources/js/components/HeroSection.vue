<template>
  <section class="hero" :class="{ 'hero--entered': entered }">
    <div class="wrap">
      <div class="hero-grid">
        <div class="hero-copy">
          <div class="eyebrow">A NEW WAY TO REACH OUR SCHOLARS</div>
          <h1>Speak with a Sharia scholar, <em>on your schedule</em> — not theirs.</h1>
          <p class="lead">Book a private video consultation with a scholar matched to your madhhab, language, and question. The same scholars who advise the banks, funds, and regulators you already trust.</p>
          <div class="hero-ctas">
            <router-link to="/#directory" class="btn btn-red">Browse scholars</router-link>
            <router-link to="/#how" class="btn btn-ghost-dark">How it works</router-link>
          </div>
          <div class="stat-strip">
            <div class="stat"><div class="n">{{ stats.scholars }}</div><div class="l">Scholars shown</div></div>
            <div class="stat"><div class="n">{{ stats.countries }}</div><div class="l">Countries</div></div>
            <div class="stat"><div class="n">{{ stats.madhahib }}</div><div class="l">Madhāhib</div></div>
            <div class="stat"><div class="n">{{ stats.languages }}+</div><div class="l">Languages</div></div>
          </div>
        </div>

        <div class="constellation" aria-hidden="true">
          <svg viewBox="0 0 100 100" preserveAspectRatio="xMidYMid meet">
            <line
              v-for="node in constellationNodes"
              :key="'line-' + node.label"
              x1="50"
              y1="50"
              :x2="node.x"
              :y2="node.y"
              stroke="rgba(165,0,52,0.2)"
              stroke-width="0.3"
            />
          </svg>
          <div
            v-for="(node, i) in constellationNodes"
            :key="node.label"
            class="node"
            :style="{ left: node.x + '%', top: node.y + '%', transitionDelay: (0.35 + i * 0.08) + 's' }"
          >
            <div class="dot"></div>
            <div class="lbl">{{ node.label }}</div>
          </div>
          <div class="node big" style="left:50%;top:50%">
            <div class="dot"></div>
            <div class="lbl">SRB Network</div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { useScholars } from '../composables/useScholars';
import { getConstellationNodes, CONSTELLATION_COUNTRIES } from '../utils/constellation';
import { computeHeroStats } from '../utils/heroStats';

const { scholars, filters } = useScholars();
const entered = ref(false);
const constellationNodes = getConstellationNodes(CONSTELLATION_COUNTRIES);

onMounted(() => {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    entered.value = true;
    return;
  }
  requestAnimationFrame(() => {
    entered.value = true;
  });
});

const stats = computed(() => computeHeroStats(scholars.value, filters.value));
</script>

<style scoped>
.hero {
  position: relative;
  background: radial-gradient(ellipse 900px 500px at 78% -10%, rgba(165, 0, 52, 0.18), transparent 60%), var(--charcoal);
  color: var(--text-light);
  overflow: hidden;
  padding: 104px 0 90px;
}
.hero .wrap { position: relative; z-index: 2; }
.hero-grid { display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 56px; align-items: center; }

.hero-copy {
  opacity: 0;
  transform: translateY(32px);
  transition: opacity 0.85s cubic-bezier(0.22, 1, 0.36, 1), transform 0.85s cubic-bezier(0.22, 1, 0.36, 1);
}
.hero--entered .hero-copy {
  opacity: 1;
  transform: translateY(0);
}

.hero h1 { font-size: 52px; line-height: 1.08; color: var(--text-light); margin: 22px 0 22px; max-width: 640px; }
.hero h1 em { color: var(--red-bright); font-style: italic; }
.hero .lead { font-size: 17px; color: var(--text-muted-light); max-width: 480px; margin-bottom: 34px; }
.hero-ctas { display: flex; gap: 14px; align-items: center; margin-bottom: 56px; }
.stat-strip { display: flex; gap: 0; border-top: 1px solid var(--line-dark); padding-top: 22px; max-width: 620px; }
.stat { flex: 1; padding-right: 20px; }
.stat .n { font-family: 'IBM Plex Mono', monospace; font-size: 23px; color: var(--red-bright); }
.stat .l { font-size: 11.5px; color: var(--text-muted-light); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 4px; }

.constellation { position: relative; height: 440px; }
.constellation svg { position: absolute; inset: 0; width: 100%; height: 100%; }
.node {
  position: absolute;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  transform: translate(-50%, -50%) scale(0.6);
  opacity: 0;
  transition: opacity 0.6s cubic-bezier(0.22, 1, 0.36, 1), transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}
.hero--entered .node {
  opacity: 1;
  transform: translate(-50%, -50%) scale(1);
}
.hero--entered .node.big {
  transition-delay: 0.2s;
}
.node .dot {
  width: 9px; height: 9px; border-radius: 50%; background: var(--red);
  box-shadow: 0 0 0 4px rgba(165, 0, 52, 0.16);
}
.node.big .dot { width: 13px; height: 13px; background: var(--red-bright); box-shadow: 0 0 0 7px rgba(196, 31, 82, 0.18); }
.node .lbl {
  font-family: 'IBM Plex Mono', monospace; font-size: 10.5px; color: var(--text-muted-light); white-space: nowrap;
}
.node.big .lbl { color: var(--text-light); }

@media (max-width: 920px) {
  .hero-grid { grid-template-columns: 1fr; }
  .constellation { display: none; }
  .hero h1 { font-size: 38px; }
}
@media (max-width: 560px) {
  .stat-strip { flex-wrap: wrap; gap: 16px; }
}
@media (prefers-reduced-motion: reduce) {
  .hero-copy,
  .node {
    opacity: 1;
    transform: none;
    transition: none;
  }
}
</style>
