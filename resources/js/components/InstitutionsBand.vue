<template>
  <section class="inst-band" id="institutions">
    <div class="wrap">
      <div>
        <div class="eyebrow">FOR BANKS, FUNDS &amp; CORPORATES</div>
        <h2>The same network, at institutional scale.</h2>
        <p>Larger engagements — board-level advisory, product structuring, or ongoing certification — start with a scholar consultation but rarely end there. Where a consultation leads to a mandate, it's handled by our advisory team, not the self-serve checkout below.</p>
        <a href="#" class="btn btn-ghost-dark" style="margin-top:22px;">Request institutional access</a>
      </div>
      <ul class="inst-list">
        <li><span class="m">—</span>Certification, Sharia review, and audit engagements arising from a consultation are scoped and delivered by SRB directly.</li>
        <li><span class="m">—</span>Senior scholars are available at an institutional tier, priced per engagement rather than per session.</li>
        <li><span class="m">—</span>Every consultation includes a written summary, so nothing discussed needs to be repeated to your board or compliance team later.</li>
        <li><span class="m">—</span>Existing SRB clients can loop a familiar scholar into a first call at no extra cost.</li>
      </ul>
    </div>
  </section>
</template>

<style scoped>
.inst-band { background: var(--charcoal); color: var(--text-light); padding: 88px 0; }
.inst-band .wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
.inst-band h2 { font-size: 30px; color: var(--text-light); margin-top: 16px; }
.inst-band p { color: var(--text-muted-light); font-size: 15px; margin-top: 14px; }
.inst-list { list-style: none; padding: 0; margin: 26px 0 0; display: flex; flex-direction: column; gap: 12px; }
.inst-list li { display: flex; gap: 12px; font-size: 14px; color: var(--text-muted-light); align-items: flex-start; }
.inst-list li .m { color: var(--red-bright); flex-shrink: 0; margin-top: 3px; }

@media (max-width: 920px) { .inst-band .wrap { grid-template-columns: 1fr; } }
</style>
