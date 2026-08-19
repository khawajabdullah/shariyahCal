<template>
  <section class="hero">
    <div class="wrap">
      <div class="hero-grid">
        <div>
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
        <div class="constellation" aria-hidden="true" ref="constellationEl"></div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { useScholars } from '../composables/useScholars';

const { scholars, load } = useScholars();
const constellationEl = ref(null);

onMounted(() => {
  load();
});

const stats = computed(() => {
  const list = scholars.value;
  return {
    scholars: list.length,
    countries: new Set(list.map(s => s.country).filter(Boolean)).size,
    madhahib: new Set(list.map(s => s.madhhab).filter(Boolean)).size,
    languages: new Set(list.flatMap(s => s.languages || [])).size,
  };
});

function drawConstellation(list) {
  const el = constellationEl.value;
  if (!el) return;

  el.innerHTML = '';
  const countries = [...new Set(list.map(s => s.country).filter(Boolean))];
  const labels = countries.length ? countries : list.map(s => s.name);
  const cx = 50, cy = 50;
  let svg = '<svg viewBox="0 0 100 100" preserveAspectRatio="xMidYMid meet">';
  const positions = [];
  labels.forEach((c, i) => {
    const angle = labels.length ? (i / labels.length) * Math.PI * 2 - Math.PI / 2 : 0;
    const r = 34 + (i % 3) * 4;
    const x = cx + Math.cos(angle) * r;
    const y = cy + Math.sin(angle) * r;
    positions.push({ x, y, c });
    svg += `<line x1="${cx}" y1="${cy}" x2="${x}" y2="${y}" stroke="rgba(165,0,52,0.2)" stroke-width="0.3"/>`;
  });
  svg += '</svg>';
  el.innerHTML = svg;
  positions.forEach(p => {
    const node = document.createElement('div');
    node.className = 'node';
    node.style.left = p.x + '%';
    node.style.top = p.y + '%';
    node.innerHTML = '<div class="dot"></div><div class="lbl"></div>';
    node.querySelector('.lbl').textContent = p.c;
    el.appendChild(node);
  });
  const center = document.createElement('div');
  center.className = 'node big';
  center.style.left = '50%';
  center.style.top = '50%';
  center.innerHTML = '<div class="dot"></div><div class="lbl">SRB Network</div>';
  el.appendChild(center);
}

watch(scholars, (list) => drawConstellation(list), { immediate: true });
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
.hero h1 { font-size: 52px; line-height: 1.08; color: var(--text-light); margin: 22px 0 22px; max-width: 640px; }
.hero h1 em { color: var(--red-bright); font-style: italic; }
.hero .lead { font-size: 17px; color: var(--text-muted-light); max-width: 480px; margin-bottom: 34px; }
.hero-ctas { display: flex; gap: 14px; align-items: center; margin-bottom: 56px; }
.stat-strip { display: flex; gap: 0; border-top: 1px solid var(--line-dark); padding-top: 22px; max-width: 620px; }
.stat { flex: 1; padding-right: 20px; }
.stat .n { font-family: 'IBM Plex Mono', monospace; font-size: 23px; color: var(--red-bright); }
.stat .l { font-size: 11.5px; color: var(--text-muted-light); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 4px; }

.constellation { position: relative; height: 440px; }
.constellation :deep(svg) { position: absolute; inset: 0; width: 100%; height: 100%; }
.constellation :deep(.node) {
  position: absolute; display: flex; flex-direction: column; align-items: center; gap: 6px;
  transform: translate(-50%, -50%);
}
.constellation :deep(.node .dot) {
  width: 9px; height: 9px; border-radius: 50%; background: var(--red);
  box-shadow: 0 0 0 4px rgba(165, 0, 52, 0.16);
}
.constellation :deep(.node.big .dot) { width: 13px; height: 13px; background: var(--red-bright); box-shadow: 0 0 0 7px rgba(196, 31, 82, 0.18); }
.constellation :deep(.node .lbl) {
  font-family: 'IBM Plex Mono', monospace; font-size: 10.5px; color: var(--text-muted-light); white-space: nowrap;
}
.constellation :deep(.node.big .lbl) { color: var(--text-light); }

@media (max-width: 920px) {
  .hero-grid { grid-template-columns: 1fr; }
  .constellation { display: none; }
  .hero h1 { font-size: 38px; }
}
@media (max-width: 560px) {
  .stat-strip { flex-wrap: wrap; gap: 16px; }
}
</style>
