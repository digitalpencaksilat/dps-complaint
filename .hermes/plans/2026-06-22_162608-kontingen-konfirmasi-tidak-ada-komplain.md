# Kontingen Confirmation / Tidak Ada Komplain Implementation Plan

> **For Hermes:** Use subagent-driven-development skill to implement this plan task-by-task.

**Goal:** Menambah jalur publik “Tidak Ada Komplain” supaya official kontingen bisa mengonfirmasi data atlet sudah sesuai, lalu admin bisa melihat status konfirmasi per kontingen hasil sync.

**Architecture:** Simpan konfirmasi sebagai record terpisah dari tiket komplain, bukan sebagai `complaint_items` kosong. `complaint_reports` tetap khusus tiket komplain yang perlu diproses. Tambah tabel `contingent_confirmations` untuk bukti persetujuan per event+kontingen+official+signature, lalu dashboard baru/extended menampilkan semua kontingen hasil sync beserta badge “Tidak Ada Komplain” atau “Tidak Ada Konfirmasi”.

**Tech Stack:** CodeIgniter 4, PHP 8.2+, MySQL/MariaDB, Bootstrap 5, vanilla JS, existing search API `api/contingents/search`, existing signature canvas.

---

## Product Decision

Rekomendasi: jangan simpan “Tidak Ada Komplain” di `complaint_reports`.

Alasan:
- `complaint_reports` sekarang berarti tiket komplain yang punya lifecycle status: `baru`, `diproses`, `perlu_konfirmasi`, `selesai`, `ditolak`.
- “Tidak Ada Komplain” bukan tiket masalah. Tidak perlu SLA, status proses, detail item, atau admin action.
- Kalau dipaksa masuk `complaint_reports`, dashboard jumlah komplain akan tercampur dengan bukti persetujuan dan statistik jadi bias.
- Bukti persetujuan butuh uniqueness berbeda: satu kontingen per event sebaiknya punya satu konfirmasi aktif/terbaru.

Model terbaik:
- `complaint_reports`: tiket komplain.
- `complaint_items`: item detail komplain.
- `contingent_confirmations`: bukti kontingen tidak ada komplain.
- Admin “Data Komplain/Konfirmasi Kontingen”: query dari `contingents` hasil sync, left join `contingent_confirmations` terbaru.

---

## Current Context

Relevant existing files:

- Public form: `app/Views/complaints/form.php`
  - Step 1: Kejuaraan.
  - Step 2: Item Complain.
  - Step 3: Official + signature.
  - Step 4: Review & Kirim.
- Public JS: `public/assets/js/complaint-form.js`
  - Handles stepper, participant/contingent search, item add/remove, review summary.
- Submit service: `app/Services/ComplaintSubmissionService.php`
  - Validates event, official, signature, items.
  - Inserts `complaint_reports` and `complaint_items`.
- Search API: `api/contingents/search`
  - Already enough for searching contingent by selected event.
- Admin dashboard: `app/Controllers/Admin/ComplaintAdminController.php`, `app/Views/admin/complaints/index.php`
  - Shows complaint tickets only.
- Schema: `app/Database/Migrations/2026-06-21-000001_CreateComplaintSchema.php`
  - Has `contingents`, `participants`, `complaint_reports`, `complaint_items`.
  - No foreign keys, only indexes.

Important existing behavior:
- Normal sync upserts `contingents` by `event_id + source_contingent_id` and keeps local `contingents.id` stable if source ID same.
- Public form already has contingent search UI inside `missing_participant`; we can reuse pattern.

---

## Desired User Flow

### Public flow: Ada Komplain

1. User pilih kejuaraan.
2. Step 2 menampilkan pilihan awal:
   - “Ada Komplain”
   - “Tidak Ada Komplain”
3. User pilih “Ada Komplain”.
4. Daftar item complain muncul seperti sekarang.
5. User isi item, peserta/kontingen, keterangan.
6. User isi official + tanda tangan.
7. Review menampilkan mode “Ada Komplain” dan list item.
8. Submit membuat tiket komplain seperti sekarang.

### Public flow: Tidak Ada Komplain

1. User pilih kejuaraan.
2. Step 2 pilih “Tidak Ada Komplain”.
3. Form item complain disembunyikan.
4. Field “Cari Kontingen” muncul.
5. User pilih satu kontingen hasil sync.
6. User isi official + nomor telepon + tanda tangan.
7. Review menampilkan:
   - Mode: Tidak Ada Komplain.
   - Kontingen: nama kontingen.
   - Official + nomor telepon.
   - Pernyataan: official menyatakan data atlet kontingen sudah sesuai dengan data kejuaraan.
8. Submit menyimpan `contingent_confirmations`.
9. Success page bisa tetap memakai ticket-like code atau halaman success baru untuk kode konfirmasi.

### Admin flow

1. Admin masuk halaman baru, misal `admin/complaints/contingents` atau tab “Konfirmasi Kontingen”.
2. Admin filter event.
3. Sistem menampilkan semua kontingen dari tabel `contingents` hasil sync untuk event itu.
4. Kolom:
   - No
   - Kejuaraan
   - Kontingen
   - Badge Konfirmasi
   - Nama Official
   - Nomor Telepon Official
   - Waktu Konfirmasi
   - Aksi/detail
5. Badge:
   - “Tidak Ada Komplain” hijau kalau ada record konfirmasi untuk kontingen+event.
   - “Tidak Ada Konfirmasi” abu-abu kalau belum ada.
6. Optional detail menampilkan signature dan statement snapshot.

---

## Data Model Draft

### New table: `contingent_confirmations`

Fields:

- `id` BIGINT unsigned PK auto increment
- `event_id` BIGINT unsigned, indexed
- `contingent_id` BIGINT unsigned, indexed
- `confirmation_code` VARCHAR(50), unique
- `official_name` VARCHAR(255)
- `official_phone` VARCHAR(50)
- `signature_image` LONGTEXT nullable
- `signature_hash` VARCHAR(64) nullable
- `contingent_snapshot` TEXT nullable
- `statement` TEXT nullable
- `confirmed_at` DATETIME
- `created_at` DATETIME nullable
- `updated_at` DATETIME nullable

Indexes:

- `confirmation_code` unique
- `event_id`
- `contingent_id`
- `confirmed_at`
- unique `event_id + contingent_id` if one confirmation per kontingen should be enforced.

Recommendation for duplicate submissions:
- Enforce unique `event_id + contingent_id`.
- If same kontingen submits again, update existing confirmation with latest official/signature, or reject with message “Kontingen ini sudah melakukan konfirmasi.”
- For audit simplicity MVP: reject duplicate. Later can add history table.

### Why snapshot needed

Store `contingent_snapshot` when confirmation submitted so proof remains stable even if sync updates contingent name later.

---

## Routes Draft

Modify `app/Config/Routes.php`:

Public:
- Keep `POST complaints` same route, service decides by `submission_mode`.
- Or add separate `POST complaints/confirmations`.

Recommendation MVP: keep same route `POST complaints`.
Reason: same form, same rate limit, same signature. Less UI duplication.

Admin:
- `GET admin/complaints/contingents` → `Admin\ComplaintAdminController::contingents`
- Optional detail: `GET admin/complaints/contingents/(:num)` → `Admin\ComplaintAdminController::contingentConfirmation/$1`

---

## Functional Requirements

### FR1: Public form mode selector

Acceptance criteria:
- Step 2 shows two cards/radio options:
  - “Ada Komplain”
  - “Tidak Ada Komplain”
- Default should be “Ada Komplain” for backward compatibility.
- Choosing “Ada Komplain” shows existing item complain UI.
- Choosing “Tidak Ada Komplain” hides item complain UI and shows contingent search.
- Only visible branch fields are required.

### FR2: Submit “Ada Komplain” unchanged

Acceptance criteria:
- Existing complaint submit still creates `complaint_reports` and `complaint_items`.
- Existing validation still rejects empty item/description.
- Existing success page still works.
- Existing tests keep passing.

### FR3: Submit “Tidak Ada Komplain”

Acceptance criteria:
- Requires event, contingent, official name, official phone, signature.
- Does not require `items` or description.
- Creates one row in `contingent_confirmations`.
- Stores `contingent_snapshot` JSON.
- Stores `signature_hash` and `confirmed_at`.
- Rejects invalid contingent not belonging to selected event.
- Duplicate event+contingent gets clear message.
- If event+contingent already confirmed, submit is rejected with clear message that confirmation was already input.
- If contingent has active complaint status `baru`, `diproses`, or `perlu_konfirmasi`, submit is rejected with alert explaining active complaint must be resolved first.

### FR4: Review step adapts by mode

Acceptance criteria:
- Mode “Ada Komplain”: review shows item list as now.
- Mode “Tidak Ada Komplain”: review shows selected kontingen and approval statement.
- Submit button label changes if possible:
  - “Simpan Complain” for complaint mode.
  - “Simpan Konfirmasi” for no-complaint mode.

### FR5: Admin contingent recap page

Acceptance criteria:
- Page lists all `contingents` for selected event.
- Each row has badge:
  - green “Tidak Ada Komplain” if confirmed.
  - gray “Tidak Ada Konfirmasi” if not confirmed.
- Shows official name/phone only when confirmed.
- Filter event available; if no event selected, either show latest/active event or prompt select event.
- DataTables export works with existing admin export helper.

### FR6: Admin contingent confirmation recap exports

Acceptance criteria:
- New page “Konfirmasi Kontingen” exists and is separate from Dashboard Complain.
- Page has filter controls like Rekap Complain: event, confirmation badge/status, contingent search.
- Page has DataTables with print and Excel export using existing `window.initAdminExportTable` pattern.
- Print/export includes DPS watermark/logo like other admin report pages.
- Print/export title: `REKAP KONFIRMASI KONTINGEN`.
- Export columns include: No, Kejuaraan, Kontingen, Badge Konfirmasi, Official, Nomor Telepon, Waktu Konfirmasi.

### FR7: Sync safety interaction

Acceptance criteria:
- Normal sync adding new kontingen makes new row appear with badge “Tidak Ada Konfirmasi”.
- Existing confirmed kontingen keeps badge “Tidak Ada Komplain” because `contingent_id` remains stable for normal upsert.
- If fresh sync allowed, confirmation relation can break. Add guard separately or include in implementation: block `fresh=true` when complaint reports or confirmations exist.

---

## Non-Functional Requirements

- Public submit remains rate-limited by IP using existing `RateLimitService`.
- Search API should not expose records without event_id.
- No sensitive DB credentials printed.
- Signature storage follows existing pattern.
- Admin page scoped by event; never mix event data.
- Views stay readable, no compressed one-line markup.

---

## Implementation Tasks

### Task 1: Add migration for `contingent_confirmations`

**Objective:** Create storage for “Tidak Ada Komplain” proof separate from complaint tickets.

**Files:**
- Create: `app/Database/Migrations/YYYY-MM-DD-HHMMSS_CreateContingentConfirmations.php`

**Steps:**
1. Create migration with fields listed in Data Model Draft.
2. Add keys: primary id, unique confirmation_code, key event_id, key contingent_id, key confirmed_at.
3. Add unique key `event_id, contingent_id` for MVP duplicate prevention.
4. Run: `php spark migrate` on dev DB if environment ready.
5. Verify schema via `php spark migrate:status` or DB inspection.

**Rollback:** migration `down()` drops `contingent_confirmations`.

### Task 2: Create `ContingentConfirmationModel`

**Objective:** Add model for confirmation CRUD and admin recap query.

**Files:**
- Create: `app/Models/ContingentConfirmationModel.php`

**Implementation notes:**
- `$table = 'contingent_confirmations'`
- `$useTimestamps = true`
- allowed fields:
  - `event_id`, `contingent_id`, `confirmation_code`, `official_name`, `official_phone`, `signature_image`, `signature_hash`, `contingent_snapshot`, `statement`, `confirmed_at`

Add helper method later if useful:
- `findByEventContingent(int $eventId, int $contingentId): ?array`
- `latestByEvent(int $eventId): array` keyed by contingent_id, if needed.

### Task 3: Add confirmation submit service

**Objective:** Keep complaint submit clean and route no-complaint mode to confirmation storage.

**Files:**
- Modify: `app/Services/ComplaintSubmissionService.php`
- Or create: `app/Services/ContingentConfirmationService.php` (recommended)

**Recommended design:**
- Create `ContingentConfirmationService` for confirmation only.
- In `ComplaintController::submit()`, inspect `submission_mode`:
  - `complaint` → existing `ComplaintSubmissionService`.
  - `no_complaint` → new `ContingentConfirmationService`.

**Validation:**
- event exists and complaint still open via `EventModel::isComplaintOpen($event)`.
- `official_name`, `official_phone`, `signature_image` required.
- `contingent_id` belongs to event.
- duplicate event+contingent rejected.

**Confirmation code format:**
- `CONF-<EVENTSLUG12>-<ymd>-<random6>`
- Example: `CONF-JAKARTAOPEN-260622-A1B2C3`

### Task 4: Update public controller routing logic

**Objective:** Submit same form to correct service and redirect to success.

**Files:**
- Modify: `app/Controllers/ComplaintController.php`
- Modify: `app/Views/complaints/success.php` if wording needs adapt.

**Steps:**
1. Read `$mode = $this->request->getPost('submission_mode') ?: 'complaint';`
2. If `no_complaint`, call `ContingentConfirmationService::submit()`.
3. Redirect to success page with code and type query, e.g. `/complaints/success/{code}?type=confirmation`.
4. Update success view to show “Konfirmasi berhasil tersimpan” when `type=confirmation`.

### Task 5: Update public form markup

**Objective:** Add mode choice and no-complaint contingent search to Step 2.

**Files:**
- Modify: `app/Views/complaints/form.php`

**Step 2 structure:**
- Hidden/default input or radio group: `submission_mode` values:
  - `complaint`
  - `no_complaint`
- Existing complaint item block wrapped in e.g. `[data-mode-panel="complaint"]`.
- New no-complaint panel `[data-mode-panel="no_complaint"]` containing:
  - contingent search input `name="confirmation_contingent_label"`
  - hidden `name="confirmation_contingent_id"`
  - results container.
  - explanatory text: “Dengan memilih Tidak Ada Komplain, official menyatakan data atlet kontingen sudah sesuai dengan data kejuaraan.”

**Old input support:**
- Preserve `old('submission_mode')`.
- Preserve `old('confirmation_contingent_label')` and `old('confirmation_contingent_id')`.

### Task 6: Update public JS mode handling

**Objective:** Step validation, search, review summary adapt by mode.

**Files:**
- Modify: `public/assets/js/complaint-form.js`

**Key changes:**
1. Add `getSubmissionMode()`.
2. Toggle panels on radio change.
3. Disable/enable required fields by mode:
   - Complaint mode: item fields required as existing.
   - No-complaint mode: item fields not required; confirmation contingent label/id required.
4. Add search binding for no-complaint contingent search using existing `contingentUrl`.
5. Update `getStepFields()` so hidden/inactive mode fields do not validate.
6. Update `updateReviewSummary()`:
   - Complaint mode: current item list.
   - No-complaint mode: selected kontingen + statement.
7. Update `data-review-total-items` label to not show `0 item` in no-complaint mode; maybe show “Tidak Ada Komplain”.
8. On event change, reset both complaint item searches and confirmation contingent search.

### Task 7: Add CSS for mode selector and confirmation panel

**Objective:** Make new UI clear and consistent.

**Files:**
- Modify: `public/assets/css/complaint-theme.css`

**Classes:**
- `.complaint-mode-options`
- `.complaint-mode-card`
- `.complaint-mode-card.active`
- `.confirmation-panel`
- `.confirmation-statement`

Acceptance:
- Looks good desktop/mobile.
- Active choice visually obvious.
- Green accent for “Tidak Ada Komplain”, red accent for “Ada Komplain” acceptable.

### Task 8: Add admin contingent confirmation recap route/controller

**Objective:** Admin can see per-contingent confirmation status.

**Files:**
- Modify: `app/Config/Routes.php`
- Modify: `app/Controllers/Admin/ComplaintAdminController.php`
- Create/Modify view: `app/Views/admin/complaints/contingents.php`

**Controller method:** `contingents()`

Data query approach:
- Require/filter `event_id`.
- Get events list.
- Get contingents for event: `(new ContingentModel())->where('event_id', $eventId)->orderBy('name')->findAll()`.
- Get confirmations for event and key by `contingent_id`.
- Build rows:
  - event_name
  - contingent_name
  - confirmation_status: confirmed/unconfirmed
  - official_name
  - official_phone
  - confirmed_at
  - confirmation_code

**Acceptance:**
- If event not selected, show message “Pilih kejuaraan untuk melihat rekap konfirmasi kontingen.”
- If event selected but no contingents synced, show “Belum ada kontingen hasil sync.”
- Filter `confirmation_status` supports: all, confirmed, unconfirmed.
- Filter `contingent` searches contingent name.
- Export/print buttons available via existing admin export helper.
- Watermark uses `assets/img/dps-logo.png` and `Powered by <strong>Digital Pencak Silat</strong>`.

### Task 8B: Add print and Excel endpoints for confirmation recap

**Objective:** Match Rekap Complain functions for confirmation data.

**Files:**
- Modify: `app/Config/Routes.php`
- Modify: `app/Controllers/Admin/ComplaintAdminController.php`
- Create: `app/Views/admin/complaints/contingents_print.php`
- Create: `app/Views/admin/complaints/contingents_excel.php`

**Routes:**
- `GET admin/complaints/contingents/print` → `ComplaintAdminController::contingentsPrint`
- `GET admin/complaints/contingents/excel` → `ComplaintAdminController::contingentsExcel`

**Controller extraction:**
- Create private `confirmationFilters(): array`.
- Create private `confirmationRows(array $filters): array`.
- Reuse both for normal page, print, and excel.

**Acceptance:**
- Print page displays title `REKAP KONFIRMASI KONTINGEN`, generated date, filters, and watermark.
- Excel endpoint downloads `.xls` with same row data.
- Normal DataTables export still works on main page.

### Task 8C: Add public confirmation pre-check endpoint

**Objective:** Show immediate alert when selected kontingen already confirmed or has active complaint.

**Files:**
- Modify: `app/Config/Routes.php`
- Create: `app/Controllers/Api/ContingentConfirmationStatusController.php`

**Route:**
- `GET api/contingents/confirmation-status?event_id=...&contingent_id=...`

**Response shape:**
- `{ "ok": true, "can_confirm": true, "status": "available", "message": "" }`
- Already confirmed: `{ "can_confirm": false, "status": "already_confirmed", "message": "Kontingen ini sudah menginput konfirmasi Tidak Ada Komplain." }`
- Active complaint: `{ "can_confirm": false, "status": "active_complaint", "message": "Kontingen ini masih memiliki komplain aktif. Selesaikan komplain terlebih dahulu sebelum konfirmasi Tidak Ada Komplain." }`

**Important:**
- Endpoint only helps UX. `ContingentConfirmationService` remains source of truth and repeats same validation server-side.

### Task 9: Add admin navigation link

**Objective:** Page discoverable.

**Files:**
- Modify: `app/Views/layouts/admin.php`

Add nav item near Dashboard/Rekap:
- Label: “Konfirmasi Kontingen”
- URL: `admin/complaints/contingents`

### Task 10: Add helper labels/badges

**Objective:** Avoid scattered strings.

**Files:**
- Modify: `app/Helpers/complaint_helper.php`

Add functions:
- `confirmation_badge_label(?array $confirmation): string`
  - confirmed → `Tidak Ada Komplain`
  - null → `Tidak Ada Konfirmasi`
- `confirmation_badge_class(?array $confirmation): string`
  - confirmed → `bg-success`
  - null → `bg-secondary`

### Task 11: Protect fresh sync if confirmations exist

**Objective:** Keep confirmation relation safe.

**Files:**
- Modify: `app/Services/ParticipantSyncService.php`
- Modify: `app/Commands/SyncParticipants.php` if force flag desired.

MVP guard:
- If `$fresh === true`, count complaint reports and contingent confirmations for event.
- If either > 0, throw `RuntimeException('Fresh sync ditolak karena sudah ada data complain/konfirmasi kontingen.')`.

Web controller already catches? Current `EventAdminController::sync()` does not catch RuntimeException. Add try/catch and flash error.

Acceptance:
- Normal Sync still works.
- `?fresh=1` after confirmation exists shows error and does not delete data.

### Task 12: Tests / verification

**Objective:** Prove both flows work.

Test commands:
- `php -l app/Services/ContingentConfirmationService.php`
- `php -l app/Models/ContingentConfirmationModel.php`
- `php -l app/Controllers/ComplaintController.php`
- `php -l app/Controllers/Admin/ComplaintAdminController.php`
- `php spark routes` if available.
- `composer test`.

Manual verification:
1. Run normal participant sync for test event.
2. Public form: submit “Ada Komplain”; confirm existing ticket created.
3. Public form: submit “Tidak Ada Komplain”; confirm row in `contingent_confirmations`.
4. Admin page: selected event shows confirmed kontingen green and others gray.
5. Sync normal after adding kontingen: new kontingen appears gray.
6. Try `admin/events/{id}/sync?fresh=1` after confirmation exists: blocked.

---

## Files Likely To Change

Create:
- `app/Database/Migrations/YYYY-MM-DD-HHMMSS_CreateContingentConfirmations.php`
- `app/Models/ContingentConfirmationModel.php`
- `app/Services/ContingentConfirmationService.php`
- `app/Views/admin/complaints/contingents.php`
- `app/Views/admin/complaints/contingents_print.php`
- `app/Views/admin/complaints/contingents_excel.php`
- `app/Controllers/Api/ContingentConfirmationStatusController.php`

Modify:
- `app/Config/Routes.php`
- `app/Controllers/ComplaintController.php`
- `app/Controllers/Admin/ComplaintAdminController.php`
- `app/Controllers/Admin/EventAdminController.php`
- `app/Services/ParticipantSyncService.php`
- `app/Views/complaints/form.php`
- `app/Views/complaints/success.php`
- `app/Views/layouts/admin.php`
- `app/Helpers/complaint_helper.php`
- `public/assets/js/complaint-form.js`
- `public/assets/css/complaint-theme.css`

Optional modify:
- `app/Commands/SyncParticipants.php`
- `app/Views/admin/complaints/report.php` if confirmation should be exportable with complaint recap later.

---

## Risks / Tradeoffs

1. Duplicate confirmation by same kontingen.
   - MVP reject duplicate.
   - Later can allow resubmit and keep history.

2. “Tidak Ada Komplain” after previously submitting complaint.
   - Product decision needed: should one kontingen be allowed to confirm no complaint if same kontingen has open complaint ticket?
   - Recommended: allow only if no active complaint for that kontingen? Hard to determine because complaint can be per participant or missing participant. MVP can allow but admin sees both complaint dashboard and confirmation recap separately.

3. Existing success page wording.
   - If same success page used, wording must support ticket and confirmation code.

4. Fresh sync orphan risk.
   - Must add guard in same implementation if confirmation table added.

5. Admin page performance.
   - Event-level contingents likely manageable. If thousands, add pagination/DataTables server side later. MVP use DataTables client like current dashboard.

---

## Confirmed Decisions

1. Duplicate confirmation:
   - Jika kontingen sudah submit “Tidak Ada Komplain”, submit ulang ditolak.
   - Public form harus memberi tanda/alert bahwa kontingen tersebut sudah pernah menginput konfirmasi.
   - Service tetap wajib enforce unique `event_id + contingent_id`, bukan hanya validasi JS.

2. Conflict with active complaint:
   - Jika kontingen masih punya komplain aktif, submit “Tidak Ada Komplain” diblokir.
   - Public form/service memberi alert alasan: kontingen masih punya komplain aktif sehingga belum bisa konfirmasi data sudah sesuai.
   - Status aktif yang diblokir: `baru`, `diproses`, `perlu_konfirmasi`.
   - Status selesai/ditolak tidak memblokir.

3. Admin location:
   - Buat halaman baru “Konfirmasi Kontingen”.
   - Jangan gabung ke Dashboard Complain agar statistik tiket komplain tetap bersih.
   - Halaman baru harus punya fungsi seperti Rekap Complain: filter, DataTables, print/export Excel, watermark, dan tampilan siap cetak.

4. Wording public:
   - UI option: “Tidak Ada Komplain”.
   - Statement: “Saya menyatakan data atlet kontingen sudah sesuai dengan data kejuaraan.”

---

## Recommended MVP Scope

Build now:
- New table + model + service.
- Public form mode selector.
- No-complaint contingent search.
- Submit confirmation.
- Admin recap page with badges.
- Fresh sync guard.

Skip for later:
- Confirmation history versioning.
- PDF proof download.
- WhatsApp notification.
- Server-side DataTables.
- Complex conflict detection between complaints and confirmations.

---

## Final Recommendation

Simpan “Tidak Ada Komplain” di tabel baru `contingent_confirmations`, bukan di `complaint_reports`. Halaman admin harus berbasis `contingents` hasil sync, lalu left join confirmation. Dengan begitu kontingen yang belum pernah submit tetap muncul sebagai “Tidak Ada Konfirmasi”, dan kontingen yang sudah submit muncul hijau “Tidak Ada Komplain” beserta official dan nomor telepon.
