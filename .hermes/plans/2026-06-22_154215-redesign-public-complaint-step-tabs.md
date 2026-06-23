# Redesign Public Complaint Step Tabs Implementation Plan

> **For Hermes:** Use subagent-driven-development skill to implement this plan task-by-task.

**Goal:** Redesain halaman utama complain menjadi alur per-tab/step-by-step seperti form tambah peserta/kontingen di DPS CI4, tanpa mengubah proses submit dan validasi backend yang sudah berjalan.

**Architecture:** Tetap pakai satu form public `#complaintForm` agar endpoint `POST /complaints` tidak berubah. UI dipecah menjadi Bootstrap-style tab panes + stepper, dengan JS kecil untuk tombol Lanjut/Kembali, validasi per step, counter langkah, dan status selesai. Existing search participant/contingent, add item, signature canvas, dan hidden fields tetap dipertahankan.

**Tech Stack:** CodeIgniter 4 view PHP, Bootstrap 5, vanilla JS, existing `complaint-form.js`, existing `signature-pad.js`, CSS di `complaint-theme.css`.

---

## Current Context

Project utama:
- `/Users/uphori1a/dps-complain`

Halaman yang akan diubah:
- `app/Views/complaints/form.php`
- `public/assets/js/complaint-form.js`
- `public/assets/css/complaint-theme.css`

File terkait yang harus dijaga:
- `public/assets/js/signature-pad.js`
- `app/Controllers/ComplaintController.php` atau controller submit terkait, kalau perlu cek rules validasi saja.
- `app/Views/layouts/public.php` sudah load Bootstrap bundle dan section scripts.

Referensi DPS CI4:
- `/Applications/XAMPP/xamppfiles/htdocs/dps-ci4/app/Views/kontingen/peserta/index.php:142-163`
- `/Applications/XAMPP/xamppfiles/htdocs/dps-ci4/app/Views/kontingen/peserta/index.php:165-269`
- `/Applications/XAMPP/xamppfiles/htdocs/dps-ci4/app/Views/kontingen/peserta/index.php:472-500`
- `/Applications/XAMPP/xamppfiles/htdocs/dps-ci4/public/assets/css/kontingen-theme.css:825-881`

Pola referensi DPS CI4:
- `.peserta-stepper`
- `.peserta-stepper-progress`
- `.peserta-stepper-counter`
- `.modal-tabs`
- `.peserta-step-tabs`
- `.step-chip`
- `.tab-content`
- `.tab-pane`
- `data-step-counter`
- update counter via event `shown.bs.tab`
- error badge per tab dari field `.is-invalid`

Halaman DPS Complain sekarang:
- Sudah punya urutan visual step card:
  1. Pilih Kejuaraan
  2. Daftar Item Complain
  3. Data Official + Tanda Tangan
  4. Submit bar
- Step 2-4 sekarang hanya dibungkus `#complaintDetails`, muncul setelah `event_id` dipilih.
- JS sekarang mengatur:
  - reveal detail setelah event dipilih
  - reset search saat event berubah
  - add/remove item complain
  - participant/contingent search
  - switch participant vs contingent berdasarkan `complaint_type`
- Signature pad sudah diperbaiki agar stabil di viewport laptop.

---

## UX Target

Bentuk baru halaman utama:

1. Header/hero tetap di atas.
2. Di bawah hero muncul stepper horizontal / tab pills seperti DPS CI4:
   - `1 Kejuaraan`
   - `2 Item Complain`
   - `3 Official`
   - `4 Review & Kirim`
3. User isi step 1 dulu.
4. Tombol `Lanjut ke Item Complain` aktif setelah kejuaraan dipilih.
5. Step 2 tampil daftar item complain; user bisa tambah item.
6. Step 3 tampil data official dan tanda tangan.
7. Step 4 tampil ringkasan singkat sebelum submit:
   - nama kejuaraan terpilih
   - jumlah item complain
   - nama official
   - status tanda tangan: sudah/belum
8. Navigasi:
   - Tombol `Kembali`
   - Tombol `Lanjut`
   - Tab bisa diklik, tapi pindah ke step berikutnya wajib lolos validasi step sebelumnya.
9. Jika validasi server gagal setelah submit, halaman tetap membuka step yang berisi error pertama.
10. Mobile: tab stepper boleh horizontal scroll, tidak pecah layout.

---

## Proposed Step Structure

### Step 1: Kejuaraan

Isi:
- Select `event_id`
- Helper text deadline complain
- Info: “Pilih kejuaraan dulu untuk membuka langkah berikutnya.”

Validasi client:
- `event_id` wajib terisi.

Behavior:
- Kalau event berubah, reset participant/contingent search seperti sekarang.
- Step 2 baru bisa dibuka kalau event sudah dipilih.

### Step 2: Item Complain

Isi:
- Existing `#complaintItems`
- Existing tombol `Tambah Item`
- Existing complaint type, participant/contingent search, description.

Validasi client:
- Minimal 1 item.
- Untuk type selain `missing_participant`: `participant_id` wajib.
- Untuk `missing_participant`: `contingent_id` wajib.
- Description minimal 10 karakter.

Behavior:
- Setelah tambah item, tetap di step 2.
- Kalau user klik lanjut tapi ada item invalid, fokus ke field pertama yang invalid.

### Step 3: Official & Tanda Tangan

Isi:
- `official_name`
- `official_phone`
- signature canvas
- hidden `signature_image`
- clear signature button

Validasi client:
- Nama official wajib.
- Nomor telepon wajib.
- Signature image wajib.

Behavior:
- Saat tab step 3 ditampilkan, trigger custom event agar signature canvas resize ulang.
- Jika belum ada event di `signature-pad.js`, dispatch `window.dispatchEvent(new Event('resize'))` cukup untuk memicu resize existing.

### Step 4: Review & Kirim

Isi:
- Summary card:
  - Event label dari selected option.
  - Total item complain.
  - Daftar ringkas item: jenis complain + label peserta/kontingen + potongan keterangan.
  - Official name/phone.
  - Signature status.
- Submit button `Simpan Complain`.
- Reminder: “Pastikan data benar. Tiket dibuat setelah form tersimpan.”

Validasi client:
- Semua step 1-3 valid sebelum submit.

---

## Files Likely To Change

### Modify: `app/Views/complaints/form.php`

Tujuan:
- Ubah markup `form-step-card` sequential menjadi tabbed layout.
- Pertahankan semua `name` attribute agar backend tidak berubah.
- Pertahankan IDs/classes yang dipakai JS sekarang:
  - `#complaintForm`
  - `[name="event_id"]`
  - `#complaintDetails` bisa diganti atau tetap sebagai wrapper, tapi kalau diganti JS harus update.
  - `#complaintItems`
  - `#addComplaintItem`
  - `.complaint-item`
  - `.complaint-type`
  - `.participant-search-box`
  - `.contingent-search-box`
  - `.participant-search`
  - `.contingent-search`
  - `.participant-id`
  - `.contingent-id`
  - `.description-helper`
  - `.complaint-description`
  - `#signatureCanvas`
  - `#signatureInput`
  - `#clearSignature`

Planned markup sections:
- Add `.complaint-stepper` near top of card body.
- Add `ul.nav.nav-tabs.modal-tabs.complaint-step-tabs` with 4 buttons.
- Add `.tab-content.complaint-step-content`.
- Move current kejuaraan card into `#complaint-step-event`.
- Move current item complain card into `#complaint-step-items`.
- Move official/signature into `#complaint-step-official`.
- Create new `#complaint-step-review`.
- Add footer controls in each pane or shared footer.

Acceptance:
- Existing form submission payload unchanged.
- Existing backend validation still receives same field names.

### Modify: `public/assets/js/complaint-form.js`

Tujuan:
- Preserve existing logic.
- Add wizard/tab controller.

New helpers:
- `const stepButtons = [...form.querySelectorAll('[data-complaint-step-target]')]`
- `const stepPanes = [...form.querySelectorAll('.complaint-step-pane')]`
- `const stepCounter = form.querySelector('[data-complaint-step-counter]')`
- `function getCurrentStepIndex()`
- `function showStep(index, options = {})`
- `function validateStep(index)`
- `function validatePreviousSteps(targetIndex)`
- `function updateStepState()`
- `function updateReviewSummary()`
- `function focusFirstInvalid(scope)`
- `function markFieldInvalid(field)` / prefer native `reportValidity()` where possible.

Rules:
- Step 1 validation: event select required.
- Step 2 validation: each item checks active required fields + description.
- Step 3 validation: official name/phone/signature required.
- Step 4 validation: run all before submit.

Important:
- Do not break participant/contingent search.
- Do not rebind old items multiple times.
- When event changes, reset searches and move/restrict to step 1 or allow next.
- When entering Step 3, run `window.dispatchEvent(new Event('resize'))` for signature canvas.
- On form submit, call all-step validation; prevent submit if fail.

### Modify: `public/assets/css/complaint-theme.css`

Tujuan:
- Tambah style stepper mirip DPS CI4.
- Keep readable CSS, jangan minify tambahan baru.

New classes:
- `.complaint-stepper`
- `.complaint-stepper-progress`
- `.complaint-stepper-counter`
- `.complaint-step-tabs`
- `.complaint-step-tabs .nav-link`
- `.complaint-step-tabs .nav-link.active`
- `.step-chip`
- `.complaint-step-pane`
- `.complaint-step-actions`
- `.complaint-review-card`
- `.complaint-review-list`
- `.step-complete` / `.step-locked` optional

Base from DPS CI4 reference:
- Copy adapted CSS from `kontingen-theme.css:825-881`.
- Change prefix from `peserta-` to `complaint-`.
- Use existing variables in complaint app, e.g. `--brand-primary` if available.

### Optional Inspect: `app/Controllers/ComplaintController.php`

Tujuan:
- Only inspect validation rules and old input behavior.
- Avoid backend change unless needed.

Possible need:
- If server validation error does not add `.is-invalid` classes in view, view may need error-aware classes and badges.

---

## Step-by-Step Implementation Plan

### Task 1: Snapshot current behavior

**Objective:** Verify current form works before UI refactor.

**Files:** No change.

Steps:
1. Run:
   `php -l app/Views/complaints/form.php`
2. Run:
   `node --check public/assets/js/complaint-form.js`
3. Open:
   `http://127.0.0.1:8090/complaints`
4. Verify event select shows, choosing event reveals details.
5. Verify participant search still responds for selected event.
6. Verify signature canvas can draw.

Expected:
- No syntax errors.
- Current page still usable.

### Task 2: Refactor form markup to tab panes

**Objective:** Move existing sections into four tab panes while preserving field names and IDs.

**Modify:** `app/Views/complaints/form.php`

Steps:
1. Keep hero and form opening unchanged.
2. Replace outer sequential `form-step-card` layout with `.complaint-stepper` + `.tab-content`.
3. Put event select in pane 1.
4. Put `#complaintItems` and `#addComplaintItem` in pane 2.
5. Put official fields and signature canvas in pane 3.
6. Add new pane 4 summary placeholders with data attributes:
   - `[data-review-event]`
   - `[data-review-total-items]`
   - `[data-review-items]`
   - `[data-review-official]`
   - `[data-review-phone]`
   - `[data-review-signature]`
7. Add buttons with data attributes:
   - `[data-step-prev]`
   - `[data-step-next]`
   - submit only in pane 4.

Acceptance:
- `name="event_id"`, `name="items[...]"`, `name="official_name"`, `name="official_phone"`, `name="signature_image"` unchanged.
- Page renders without PHP syntax error.

### Task 3: Add complaint stepper CSS

**Objective:** Make tabs look like DPS CI4 stepper and work on mobile.

**Modify:** `public/assets/css/complaint-theme.css`

Steps:
1. Add `.complaint-stepper` block adapted from `.peserta-stepper`.
2. Add `.complaint-step-tabs` nav style.
3. Add `.step-chip` style if not already global.
4. Add active state using brand red.
5. Add mobile horizontal scroll:
   - `overflow-x:auto`
   - `flex-wrap:nowrap`
   - `min-width:max-content` on nav links if needed.
6. Add review card style.

Acceptance:
- Desktop: tabs fit horizontally.
- Mobile/laptop narrow: tabs scroll horizontally, not wrapping badly.

### Task 4: Add wizard controller JS

**Objective:** Enable next/back/tab navigation and per-step validation.

**Modify:** `public/assets/js/complaint-form.js`

Steps:
1. Keep existing functions intact.
2. Add step element queries after existing DOM constants.
3. Add `showStep(index)` to toggle active classes on buttons/panes.
4. Add counter update text: `1 / 4`, `2 / 4`, etc.
5. Add `validateStep(index)`.
6. Add click listeners for step buttons:
   - allow previous step always
   - block future step if previous invalid
7. Add click listeners for next/prev buttons.
8. Add submit listener to validate all steps before native submit.
9. Add `updateReviewSummary()` and call it before showing step 4.
10. Dispatch `resize` when showing official step.

Acceptance:
- User can complete flow using Next/Back.
- Direct click step 4 blocked until required prior steps valid.
- Review updates live or when entering step 4.
- Signature canvas still works after navigating to step 3.

### Task 5: Preserve server validation errors and old input

**Objective:** If server returns validation error, open the correct tab and show badges/counts.

**Modify:** `app/Views/complaints/form.php`, `public/assets/js/complaint-form.js`

Steps:
1. Inspect controller/session validation shape.
2. Add error class rendering only if existing app already passes errors. If not available, skip deep server-error badge and rely on browser validation for first pass.
3. Add JS startup logic:
   - if `event_id` old exists but official fields missing, open first incomplete step.
   - if `signatureInput` empty after old submit, open official step.
4. Add optional data attr on form: `data-initial-step` from PHP if errors can be mapped.

Acceptance:
- After failed submit, user does not land on blank/wrong step.
- Old item values remain visible.

### Task 6: Manual QA browser pass

**Objective:** Prove full redesigned flow works.

Commands:
- `php -l app/Views/complaints/form.php`
- `node --check public/assets/js/complaint-form.js`
- `php spark routes | head -20`

Browser checks:
1. Open `/complaints`.
2. Confirm tab labels:
   - Kejuaraan
   - Item Complain
   - Official
   - Review & Kirim
3. Click Next without event: blocked with validation.
4. Select event: Next works to Step 2.
5. Add item: new item appears and numbering correct.
6. Switch complaint type to `Tidak Ada Peserta`: participant search hidden, contingent search visible.
7. Step 3: draw signature using mouse; hidden input filled.
8. Step 4: summary shows correct event, item count, official, signature status.
9. Submit with incomplete field: blocked and moves/focuses to invalid step.
10. Mobile width/laptop viewport: tabs remain usable.

Expected:
- No browser console errors.
- No broken signature canvas.
- Existing submit endpoint unchanged.

---

## Risks & Mitigations

### Risk: Bootstrap tab JS conflicts with custom validation

Mitigation:
- Do not rely fully on `data-bs-toggle="tab"` for wizard blocking.
- Use custom click handler and `showStep()` class toggling if blocking becomes hard.
- Or intercept `show.bs.tab` and call `event.preventDefault()` when prior step invalid.

### Risk: Signature canvas size becomes wrong inside hidden tab

Mitigation:
- Trigger `window.dispatchEvent(new Event('resize'))` after Step 3 becomes visible.
- Existing `signature-pad.js` already listens to resize/load/pageshow/mouseenter/touchstart.

### Risk: Hidden required fields block browser validation in inactive tabs

Mitigation:
- Because all panes are still inside one form, native required fields in hidden panes can block submit but may focus invisible fields.
- On Next/Submit, custom validation should show the pane containing invalid fields before calling `reportValidity()`.
- If needed, temporarily avoid `reportValidity()` on hidden panes and manually mark fields.

### Risk: Existing participant search reset behavior too aggressive

Mitigation:
- Preserve current event-change reset only.
- Do not reset when switching tabs.

### Risk: User wants tab UI but still simple public flow

Mitigation:
- Keep fields same, only reorganize layout.
- No new DB fields.
- No autosave/draft for MVP.

---

## Out of Scope for First Implementation

- Autosave draft.
- Upload file evidence/bukti.
- Admin-side changes.
- Backend workflow/status changes.
- Multi-page form route.
- Database schema changes.

---

## Verification Before Commit/Push

Run:

```bash
php -l app/Views/complaints/form.php
node --check public/assets/js/complaint-form.js
node --check public/assets/js/signature-pad.js
php spark routes | head -20
```

Manual browser:
- `/complaints`
- laptop viewport
- mobile viewport
- draw signature after navigating tabs
- submit validation flow

Commit message suggestion:

```bash
git add app/Views/complaints/form.php public/assets/js/complaint-form.js public/assets/css/complaint-theme.css
git commit -m "feat: redesign form complain jadi step tabs"
git push origin main
```

---

## Open Questions

1. Step 4 review cukup ringkasan data, atau mau ada checkbox konfirmasi “Saya menyatakan data benar” sebelum submit?
2. Tab boleh diklik bebas ke step sebelumnya/berikutnya, atau harus selalu pakai tombol Lanjut/Kembali?
3. Mau label step memakai istilah:
   - `Kejuaraan`, `Item Complain`, `Official`, `Review & Kirim`
   atau
   - `Pilih Event`, `Data Complain`, `Data Official`, `Konfirmasi`?
4. Mau tetap satu halaman card besar, atau dibuat seperti modal wizard DPS CI4? Rekomendasi: tetap satu halaman card besar karena public form lebih nyaman di mobile.
