<template>
  <div class="overlay" :class="{ open: isOpen }" @click.self="close">
    <div class="modal" v-if="state.scholar">
      <!-- STEP 1: Profile & duration -->
      <template v-if="state.step === 1">
        <div class="modal-head">
          <button class="modal-close" @click="close">&times;</button>
          <div class="avatar">
            <img v-if="state.scholar.avatarUrl" :src="state.scholar.avatarUrl" :alt="state.scholar.name">
            <template v-else>{{ state.scholar.initials }}</template>
          </div>
          <div>
            <h3 class="mh" style="color:var(--text-light);margin-bottom:2px;">{{ state.scholar.name }}</h3>
            <div class="mono-sub" v-if="state.scholar.flag || state.scholar.country || state.scholar.madhhab">
              {{ [state.scholar.flag, state.scholar.country, state.scholar.madhhab].filter(Boolean).join(' · ') }}
            </div>
          </div>
        </div>
        <div class="modal-body">
          <StepTrack :step="1" />
          <p class="msub" style="margin-bottom:20px;" v-if="state.scholar.bio">{{ state.scholar.bio }}</p>
          <ul class="cred-list" v-if="state.scholar.credentials && state.scholar.credentials.length">
            <li v-for="c in state.scholar.credentials" :key="c">{{ c }}</li>
          </ul>
          <template v-if="state.scholar.tier === 'institutional'">
            <div class="consent" style="border-left-color:var(--red-deep);">
              Institutional-tier scholars are engaged per project. Continue to send a request — our advisory team will follow up with scope and pricing rather than instant checkout.
            </div>
            <div class="modal-actions">
              <button class="btn btn-ghost-light" @click="close">Cancel</button>
              <button class="btn btn-red" @click="close">Request access</button>
            </div>
          </template>
          <template v-else>
            <h4 style="font-size:14px;margin-bottom:12px;">Choose a session length</h4>
            <div class="pkg-grid">
              <button v-for="d in [30, 45, 60]" :key="d"
                      class="pkg" :class="{ sel: state.duration === d }"
                      @click="pickDuration(d)">
                <div class="dur">{{ d }} min</div>
                <div class="pr">${{ PRICING[d] }}</div>
              </button>
            </div>
            <div class="modal-actions">
              <button class="btn btn-ghost-light" @click="close">Cancel</button>
              <button class="btn btn-red" @click="goStep(2)">Continue to scheduling</button>
            </div>
          </template>
        </div>
      </template>

      <!-- STEP 2: Time selection -->
      <template v-if="state.step === 2">
        <div class="modal-head">
          <button class="modal-close" @click="close">&times;</button>
          <div class="avatar">
            <img v-if="state.scholar.avatarUrl" :src="state.scholar.avatarUrl" :alt="state.scholar.name">
            <template v-else>{{ state.scholar.initials }}</template>
          </div>
          <div>
            <h3 class="mh" style="color:var(--text-light);margin-bottom:2px;">Pick a time</h3>
            <div class="mono-sub">{{ state.scholar.name }} · {{ state.duration }} min<span v-if="scheduleLabel"> · {{ scheduleLabel }}</span></div>
          </div>
        </div>
        <div class="modal-body">
          <StepTrack :step="2" />
          <p v-if="!availableDays.length" class="msub">No upcoming times on this scholar's Cal.com schedule for a {{ state.duration }}-minute session.</p>
          <div v-for="day in availableDays" :key="day.key" class="day-block">
            <div class="dlabel">{{ day.label }}</div>
            <div class="slot-grid">
              <button v-for="t in day.slots" :key="t"
                      class="slot" :class="{ sel: state.day === day.key && state.time === t }"
                      @click="pickSlot(day.key, t)">{{ t }}</button>
            </div>
          </div>
          <div class="modal-actions">
            <button class="btn btn-ghost-light" @click="goStep(1)">Back</button>
            <button class="btn btn-red" :disabled="!state.time" :style="!state.time ? 'opacity:.5' : ''" @click="goStep(3)">Continue</button>
          </div>
        </div>
      </template>

      <!-- STEP 3: Details -->
      <template v-if="state.step === 3">
        <div class="modal-head">
          <button class="modal-close" @click="close">&times;</button>
          <div class="avatar">
            <img v-if="state.scholar.avatarUrl" :src="state.scholar.avatarUrl" :alt="state.scholar.name">
            <template v-else>{{ state.scholar.initials }}</template>
          </div>
          <div>
            <h3 class="mh" style="color:var(--text-light);margin-bottom:2px;">Your details</h3>
            <div class="mono-sub">{{ selectedDay?.label || state.day }}, {{ state.time }} · {{ state.duration }} min</div>
          </div>
        </div>
        <div class="modal-body">
          <StepTrack :step="3" />
          <div class="field-row">
            <div class="field"><label>Full name</label><input type="text" placeholder="Your name"></div>
            <div class="field"><label>Email</label><input type="email" placeholder="you@email.com"></div>
          </div>
          <div class="field-row">
            <div class="field"><label>Phone</label><input type="tel" placeholder="+ country code"></div>
            <div class="field"><label>Country</label><input type="text" placeholder="Your country"></div>
          </div>
          <div class="field"><label>What would you like to discuss?</label><textarea placeholder="A brief description helps the scholar prepare — e.g. a specific transaction, product, or ruling."></textarea></div>
          <div class="consent">
            <input type="checkbox" id="consentBox" checked>
            <label for="consentBox">I agree this session may be recorded and summarized for my own reference and SRB's records.</label>
          </div>
          <div class="modal-actions">
            <button class="btn btn-ghost-light" @click="goStep(2)">Back</button>
            <button class="btn btn-red" @click="goStep(4)">Continue to payment</button>
          </div>
        </div>
      </template>

      <!-- STEP 4: Payment -->
      <template v-if="state.step === 4">
        <div class="modal-head">
          <button class="modal-close" @click="close">&times;</button>
          <div class="avatar">
            <img v-if="state.scholar.avatarUrl" :src="state.scholar.avatarUrl" :alt="state.scholar.name">
            <template v-else>{{ state.scholar.initials }}</template>
          </div>
          <div>
            <h3 class="mh" style="color:var(--text-light);margin-bottom:2px;">Payment</h3>
            <div class="mono-sub">Secure checkout — mock, no real transaction</div>
          </div>
        </div>
        <div class="modal-body">
          <div class="field"><label>Card number</label><input type="text" placeholder="4242 4242 4242 4242"></div>
          <div class="field-row">
            <div class="field"><label>Expiry</label><input type="text" placeholder="MM / YY"></div>
            <div class="field"><label>CVC</label><input type="text" placeholder="123"></div>
          </div>
          <div style="margin:22px 0;">
            <div class="summary-line"><span>{{ state.scholar.name }}</span><span>{{ state.duration }} min</span></div>
            <div class="summary-line"><span>{{ selectedDay?.label || state.day }}, {{ state.time }}</span><span>{{ state.scholar.schedule?.timeZone?.replaceAll('_', ' ') || '—' }}</span></div>
            <div class="summary-line total"><span>Total</span><span>${{ PRICING[state.duration] }}</span></div>
          </div>
          <div class="modal-actions">
            <button class="btn btn-ghost-light" @click="goStep(3)">Back</button>
            <button class="btn btn-red" @click="goStep(5)">Confirm &amp; pay</button>
          </div>
        </div>
      </template>

      <!-- STEP 5: Confirmation -->
      <template v-if="state.step === 5">
        <div class="modal-head">
          <button class="modal-close" @click="close">&times;</button>
          <div class="avatar">
            <img v-if="state.scholar.avatarUrl" :src="state.scholar.avatarUrl" :alt="state.scholar.name">
            <template v-else>{{ state.scholar.initials }}</template>
          </div>
          <div>
            <h3 class="mh" style="color:var(--text-light);margin-bottom:2px;">Booking confirmed</h3>
            <div class="mono-sub">A calendar invite has been sent to your email</div>
          </div>
        </div>
        <div class="modal-body">
          <div class="confirm-box">
            <div class="ok">✓</div>
            <h4 style="font-size:17px;margin-bottom:6px;">You're booked with {{ state.scholar.name }}</h4>
            <p style="color:var(--text-muted-ink);font-size:14px;">{{ selectedDay?.label || state.day }} at {{ state.time }} · {{ state.duration }} minutes</p>
          </div>
          <div class="video-slot">Video call integration point — the session opens here at the scheduled time.</div>
          <div class="modal-actions"><button class="btn btn-ghost-light btn-block" @click="close">Done</button></div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { useBooking } from '../composables/useBooking';
import StepTrack from './StepTrack.vue';

const { isOpen, state, close, goStep, pickDuration, pickSlot, availableDays, selectedDay, scheduleLabel, PRICING } = useBooking();
</script>

<style scoped>
.overlay {
  position: fixed; inset: 0; background: rgba(63, 65, 68, 0.6); backdrop-filter: blur(3px);
  display: none; align-items: flex-start; justify-content: center; z-index: 100;
  padding: 48px 20px; overflow-y: auto;
}
.overlay.open { display: flex; }
.modal {
  background: var(--white); max-width: 720px; width: 100%;
  box-shadow: 0 40px 80px -30px rgba(0, 0, 0, 0.5);
  position: relative; margin-bottom: 48px;
}
.modal-head {
  background: var(--charcoal); color: var(--text-light); padding: 30px 34px;
  display: flex; gap: 18px; align-items: center; position: relative;
}
.modal-close {
  position: absolute; top: 18px; right: 18px; background: none; border: none; color: var(--text-muted-light);
  font-size: 22px; line-height: 1; padding: 6px;
}
.modal-close:hover { color: var(--text-light); }
.modal-body { padding: 34px; }
.avatar {
  width: 56px; height: 56px; border-radius: 50%;
  background: linear-gradient(150deg, var(--grey-700), var(--grey-900));
  color: #fff; font-family: 'Spectral', serif; font-weight: 600; font-size: 19px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  overflow: hidden;
}
.avatar img { width: 100%; height: 100%; object-fit: cover; }
.mono-sub { font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; color: var(--text-muted-light); }
.mh { font-size: 20px; margin-bottom: 6px; }
.msub { color: var(--text-muted-ink); font-size: 13.5px; margin-bottom: 22px; }
.cred-list { list-style: none; padding: 0; margin: 0 0 22px; display: flex; flex-direction: column; gap: 8px; }
.cred-list li { font-size: 13.5px; color: var(--text-muted-ink); padding-left: 16px; position: relative; }
.cred-list li::before { content: "—"; position: absolute; left: 0; color: var(--red-deep); }
.pkg-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 26px; }
.pkg { border: 1px solid var(--grey-300); padding: 16px 14px; text-align: left; background: var(--white); }
.pkg .dur { font-family: 'IBM Plex Mono', monospace; font-size: 13px; color: var(--text-muted-ink); }
.pkg .pr { font-family: 'Spectral', serif; font-size: 20px; margin-top: 6px; }
.pkg.sel { border-color: var(--red); background: var(--red-soft); box-shadow: inset 0 0 0 1px var(--red); }
.day-block { margin-bottom: 18px; }
.day-block .dlabel { font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; color: var(--text-muted-ink); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px; }
.slot-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 8px; }
.slot { border: 1px solid var(--grey-300); padding: 10px 8px; font-size: 12.5px; text-align: center; background: var(--white); }
.slot:hover { border-color: var(--red); }
.slot.sel { background: var(--red); color: #fff; border-color: var(--red); }
.field { margin-bottom: 16px; }
.field label { display: block; font-size: 12.5px; color: var(--text-muted-ink); margin-bottom: 6px; font-weight: 600; }
.field input, .field textarea, .field select {
  width: 100%; padding: 11px 13px; border: 1px solid var(--grey-300); background: var(--white);
  font-family: 'Work Sans', sans-serif; font-size: 14px; color: var(--text-ink);
}
.field textarea { resize: vertical; min-height: 80px; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.consent {
  display: flex; gap: 10px; align-items: flex-start; font-size: 12.5px; color: var(--text-muted-ink);
  background: var(--grey-100); padding: 14px 16px; margin-bottom: 22px; border-left: 2px solid var(--red);
}
.consent input { margin-top: 2px; }
.summary-line { display: flex; justify-content: space-between; font-size: 13.5px; padding: 9px 0; border-bottom: 1px solid var(--grey-300); }
.summary-line.total { font-weight: 600; font-size: 15px; border-bottom: none; padding-top: 14px; }
.modal-actions { display: flex; gap: 12px; margin-top: 26px; }
.modal-actions .btn { flex: 1; }
.confirm-box { text-align: center; padding: 20px 0 6px; }
.confirm-box .ok {
  width: 56px; height: 56px; border-radius: 50%; background: var(--red); color: #fff;
  display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 26px;
}
.video-slot {
  border: 1px dashed var(--grey-300); padding: 40px 20px; text-align: center; color: var(--text-muted-ink);
  font-size: 13px; margin-top: 22px; background: var(--grey-100);
}

@media (max-width: 920px) {
  .pkg-grid, .slot-grid, .field-row { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 560px) {
  .pkg-grid, .slot-grid, .field-row { grid-template-columns: 1fr; }
}
</style>
