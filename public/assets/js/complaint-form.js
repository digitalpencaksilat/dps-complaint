(() => {
  const form = document.getElementById("complaintForm");
  if (!form) return;

  const eventSelect = form.querySelector('[name="event_id"]');
  const complaintDetails = document.getElementById("complaintDetails");
  const itemsBox = document.getElementById("complaintItems");
  const addButton = document.getElementById("addComplaintItem");
  const stepButtons = [...form.querySelectorAll("[data-complaint-step-target]")];
  const stepPanes = [...form.querySelectorAll("[data-complaint-step]")];
  const stepCounter = form.querySelector("[data-complaint-step-counter]");
  const modeRadios = [...form.querySelectorAll('[name="submission_mode"]')];
  const modePanels = [...form.querySelectorAll("[data-mode-panel]")];
  const confirmationInput = form.querySelector(".confirmation-contingent-search");
  const confirmationIdInput = form.querySelector(".confirmation-contingent-id");
  const confirmationResults = form.querySelector(".confirmation-contingent-results");
  const confirmationMessage = form.querySelector("[data-confirmation-status-message]");
  const submitButton = form.querySelector("[data-submit-button]");
  const submitNote = form.querySelector("[data-submit-note]");
  const participantUrl =
    form.dataset.participantUrl || "/api/participants/search";
  const contingentUrl = form.dataset.contingentUrl || "/api/contingents/search";
  const confirmationStatusUrl =
    form.dataset.confirmationStatusUrl || "/api/contingents/confirmation-status";
  const complaintTypeLabels = {
    name_error: "Kesalahan Nama",
    gender_error: "Kesalahan Jenis Kelamin",
    category_error: "Kesalahan Kategori Yang Diikuti",
    missing_participant: "Tidak Ada Peserta",
  };
  const helpers = {
    name_error: {
      text: "Contoh: Nama di bagan tertulis “Ahmad Fauzi”, seharusnya “Achmad Fauzi” sesuai data kontingen.",
      placeholder:
        "Tulis nama yang salah dan nama yang benar. Sertakan ejaan lengkap.",
    },
    gender_error: {
      text: "Contoh: Peserta tercatat Putra, seharusnya Putri sesuai data pendaftaran.",
      placeholder:
        "Tulis jenis kelamin yang tampil sekarang dan koreksi yang benar.",
    },
    category_error: {
      text: "Contoh: Peserta masuk Usia Dini - Putra kelas B, seharusnya Pra Remaja - Putra kelas C.",
      placeholder:
        "Tulis kategori/kelas yang salah dan kategori/kelas yang benar.",
    },
    missing_participant: {
      text: "Contoh: Kontingen Setia Hati memiliki peserta bernama Budi Santoso, tetapi tidak muncul di daftar peserta/bagan.",
      placeholder:
        "Tulis nama peserta yang belum muncul, nomor/kategori lomba, dan data pendukung lain.",
    },
  };
  let currentStep = 0;
  let confirmationCanSubmit = true;

  const escapeHtml = (value) =>
    String(value ?? "").replace(
      /[&<>'"]/g,
      (char) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          "'": "&#039;",
          '"': "&quot;",
        })[char],
    );

  const getSubmissionMode = () =>
    form.querySelector('[name="submission_mode"]:checked')?.value || "complaint";

  async function search(url, q) {
    const eventId = eventSelect?.value || "";
    if (!eventId || q.trim().length < 2) return [];

    const separator = url.includes("?") ? "&" : "?";
    const response = await fetch(
      `${url}${separator}event_id=${encodeURIComponent(eventId)}&q=${encodeURIComponent(q.trim())}`,
      { headers: { Accept: "application/json" } },
    );

    return response.ok ? response.json() : [];
  }

  async function getConfirmationStatus(contingentId) {
    const eventId = eventSelect?.value || "";
    if (!eventId || !contingentId) return null;

    const separator = confirmationStatusUrl.includes("?") ? "&" : "?";
    const response = await fetch(
      `${confirmationStatusUrl}${separator}event_id=${encodeURIComponent(eventId)}&contingent_id=${encodeURIComponent(contingentId)}`,
      { headers: { Accept: "application/json" } },
    );

    return response.ok ? response.json() : null;
  }

  function renderParticipant(rows) {
    if (!rows.length)
      return '<div class="empty-result">Peserta tidak ditemukan.</div>';

    return rows
      .map((row) => {
        const id = escapeHtml(row.id);
        const fullName = escapeHtml(row.full_name);
        const contingentName = escapeHtml(row.contingent_name || "-");
        const ageCategory = escapeHtml(row.age_category || "-");
        const gender = escapeHtml(row.gender || "-");
        const competitionCategory = escapeHtml(row.competition_category || "-");
        const classOrArtName = escapeHtml(row.class_or_art_name || "-");

        return `<button type="button" class="result-card" data-id="${id}" data-label="${fullName}"><strong>${fullName}</strong><span>Kontingen: ${contingentName}</span><span>${ageCategory} - ${gender}</span><span>${competitionCategory} / ${classOrArtName}</span></button>`;
      })
      .join("");
  }

  function renderContingent(rows) {
    if (!rows.length)
      return '<div class="empty-result">Kontingen tidak ditemukan.</div>';

    return rows
      .map((row) => {
        const id = escapeHtml(row.id);
        const name = escapeHtml(row.name);

        return `<button type="button" class="result-card" data-id="${id}" data-label="${name}"><strong>${name}</strong></button>`;
      })
      .join("");
  }

  function setConfirmationMessage(message, state = "") {
    if (!confirmationMessage) return;
    confirmationMessage.textContent = message || "";
    confirmationMessage.classList.toggle("text-danger", state === "error");
    confirmationMessage.classList.toggle("text-success", state === "success");
    confirmationMessage.classList.toggle("text-muted", !state);
  }

  function updateDescriptionHelper(item) {
    const type = item.querySelector(".complaint-type")?.value || "name_error";
    const config = helpers[type] || helpers.name_error;

    item.querySelector(".description-helper").innerHTML =
      `<i class="fas fa-lightbulb me-1"></i>${escapeHtml(config.text)}`;
    item.querySelector(".complaint-description").placeholder = config.placeholder;
  }

  function toggleType(item) {
    const missing =
      item.querySelector(".complaint-type")?.value === "missing_participant";

    item
      .querySelector(".participant-search-box")
      ?.classList.toggle("d-none", missing);
    item
      .querySelector(".contingent-search-box")
      ?.classList.toggle("d-none", !missing);
    item
      .querySelector(".participant-search")
      ?.toggleAttribute("required", !missing);
    item
      .querySelector(".participant-id")
      ?.toggleAttribute("required", !missing);
    item
      .querySelector(".contingent-search")
      ?.toggleAttribute("required", missing);
    item.querySelector(".contingent-id")?.toggleAttribute("required", missing);
    updateDescriptionHelper(item);
  }

  function updateModeState() {
    const mode = getSubmissionMode();
    modePanels.forEach((panel) => {
      const active = panel.dataset.modePanel === mode;
      panel.classList.toggle("d-none", !active);
      panel.querySelectorAll("input, select, textarea").forEach((field) => {
        field.disabled = !active;
      });
    });

    form.querySelectorAll(".complaint-mode-card").forEach((card) => {
      card.classList.toggle(
        "active",
        card.querySelector('input[type="radio"]')?.checked || false,
      );
    });

    confirmationInput?.toggleAttribute("required", mode === "no_complaint");
    confirmationIdInput?.toggleAttribute("required", mode === "no_complaint");

    itemsBox?.querySelectorAll(".complaint-item").forEach(toggleType);

    if (submitButton) {
      submitButton.textContent =
        mode === "no_complaint" ? "Simpan Konfirmasi" : "Simpan Complain";
    }
    if (submitNote) {
      submitNote.textContent =
        mode === "no_complaint"
          ? "Konfirmasi Tidak Ada Complain dibuat setelah form lengkap dan tersimpan."
          : "Tiket complain dibuat setelah form lengkap dan tersimpan.";
    }

    updateReviewSummary();
    updateStepState();
  }

  function refreshStepVisibility() {
    complaintDetails?.classList.toggle("complaint-step-locked", !eventSelect?.value);
    updateStepState();
  }

  function renumberItems() {
    const items = [...itemsBox.querySelectorAll(".complaint-item")];

    items.forEach((item, index) => {
      item.dataset.index = index;
      item.querySelector(".item-count").textContent = `Complain #${index + 1}`;
      item.querySelector(".complaint-type").name =
        `items[${index}][complaint_type]`;
      item.querySelector(".participant-search").name =
        `items[${index}][participant_label]`;
      item.querySelector(".participant-id").name =
        `items[${index}][participant_id]`;
      item.querySelector(".contingent-search").name =
        `items[${index}][contingent_label]`;
      item.querySelector(".contingent-id").name =
        `items[${index}][contingent_id]`;
      item.querySelector("textarea").name = `items[${index}][description]`;
      item
        .querySelector(".remove-item")
        .classList.toggle("d-none", items.length === 1);
    });

    updateReviewSummary();
  }

  function resetSearches(item) {
    item.querySelectorAll(".search-input").forEach((input) => {
      input.value = "";
    });
    item.querySelectorAll('input[type="hidden"]').forEach((input) => {
      input.value = "";
    });
    item.querySelectorAll(".search-results").forEach((result) => {
      result.innerHTML = "";
    });
  }

  function resetConfirmationSearch() {
    if (confirmationInput) confirmationInput.value = "";
    if (confirmationIdInput) confirmationIdInput.value = "";
    if (confirmationResults) confirmationResults.innerHTML = "";
    confirmationCanSubmit = true;
    setConfirmationMessage("");
  }

  function bindItem(item) {
    item.querySelector(".complaint-type")?.addEventListener("change", () => {
      resetSearches(item);
      toggleType(item);
      updateReviewSummary();
    });

    item
      .querySelector(".participant-search")
      ?.addEventListener("input", async (event) => {
        item.querySelector(".participant-id").value = "";
        item.querySelector(".participant-results").innerHTML =
          renderParticipant(await search(participantUrl, event.target.value));
        updateReviewSummary();
      });

    item
      .querySelector(".contingent-search")
      ?.addEventListener("input", async (event) => {
        item.querySelector(".contingent-id").value = "";
        item.querySelector(".contingent-results").innerHTML = renderContingent(
          await search(contingentUrl, event.target.value),
        );
        updateReviewSummary();
      });

    item.querySelector(".complaint-description")?.addEventListener("input", () => {
      updateReviewSummary();
    });

    item.querySelectorAll(".search-results").forEach((result) =>
      result.addEventListener("click", (event) => {
        const card = event.target.closest(".result-card");
        if (!card) return;

        const wrapper = card.closest(".entity-search");
        wrapper.querySelector(".search-input").value = card.dataset.label || "";
        wrapper.querySelector('input[type="hidden"]').value =
          card.dataset.id || "";
        result.innerHTML = "";
        updateReviewSummary();
      }),
    );

    item.querySelector(".remove-item")?.addEventListener("click", () => {
      item.remove();
      renumberItems();
      updateStepState();
    });

    toggleType(item);
  }

  function getStepPane(index) {
    return stepPanes.find(
      (pane) => Number(pane.dataset.complaintStep || 0) === index,
    );
  }

  function getStepFields(index) {
    const pane = getStepPane(index);
    if (!pane) return [];

    return [
      ...pane.querySelectorAll("input, select, textarea"),
    ].filter((field) => {
      if (field.disabled) return false;
      if (field.type === "button" || field.type === "submit") return false;
      return !field.closest(".d-none");
    });
  }

  function focusFirstInvalid(index) {
    const fields = getStepFields(index);
    const invalid = fields.find((field) => !field.checkValidity());
    if (!invalid) return;

    invalid.focus({ preventScroll: true });
    invalid.scrollIntoView({ behavior: "smooth", block: "center" });
    invalid.reportValidity();
  }

  function validateStep(index, showError = false) {
    const fields = getStepFields(index);
    const valid = fields.every((field) => field.checkValidity());

    if (!valid && showError) focusFirstInvalid(index);
    if (valid && showError && index === 1 && getSubmissionMode() === "no_complaint" && !confirmationCanSubmit) {
      confirmationMessage?.scrollIntoView({ behavior: "smooth", block: "center" });
      return false;
    }
    return valid;
  }

  function validateUntil(targetIndex, showError = false) {
    for (let index = 0; index < targetIndex; index += 1) {
      if (!validateStep(index, false)) {
        if (showError) showStep(index);
        if (showError) window.setTimeout(() => focusFirstInvalid(index), 60);
        return false;
      }
    }

    return true;
  }

  function updateReviewSummary() {
    const mode = getSubmissionMode();
    const selectedOption = eventSelect?.selectedOptions?.[0];
    const eventText = selectedOption?.value ? selectedOption.textContent.trim() : "-";
    const items = [...itemsBox.querySelectorAll(".complaint-item")];
    const officialName = form.querySelector('[name="official_name"]')?.value.trim();
    const officialPhone = form.querySelector('[name="official_phone"]')?.value.trim();
    const signatureValue = form.querySelector("#signatureInput")?.value;

    const setText = (selector, value) => {
      const target = form.querySelector(selector);
      if (target) target.textContent = value;
    };

    setText("[data-review-event]", eventText);
    setText(
      "[data-review-total-items]",
      mode === "no_complaint" ? "Tidak Ada Complain" : `${items.length} item`,
    );
    setText("[data-review-official]", officialName || "-");
    setText("[data-review-phone]", officialPhone || "-");
    setText("[data-review-signature]", signatureValue ? "Sudah ada" : "Belum ada");

    const list = form.querySelector("[data-review-items]");
    if (!list) return;

    if (mode === "no_complaint") {
      const contingent = confirmationInput?.value.trim() || "Data belum dipilih";
      list.innerHTML = `<div class="complaint-review-item confirmation-review-item"><span>Konfirmasi Kontingen</span><strong>${escapeHtml(contingent)}</strong><small>Tidak Ada Complain</small><p>Saya menyatakan data atlet kontingen sudah sesuai dengan data kejuaraan.</p></div>`;
      return;
    }

    if (!items.length) {
      list.innerHTML = '<div class="empty-result">Belum ada item complain.</div>';
      return;
    }

    list.innerHTML = items
      .map((item, index) => {
        const type = item.querySelector(".complaint-type")?.value || "name_error";
        const missing = type === "missing_participant";
        const label = missing
          ? item.querySelector(".contingent-search")?.value.trim()
          : item.querySelector(".participant-search")?.value.trim();
        const description =
          item.querySelector(".complaint-description")?.value.trim() || "-";
        const shortDescription =
          description.length > 130
            ? `${description.slice(0, 130).trim()}...`
            : description;

        return `<div class="complaint-review-item"><span>Complain #${index + 1}</span><strong>${escapeHtml(complaintTypeLabels[type] || type)}</strong><small>${escapeHtml(label || "Data belum dipilih")}</small><p>${escapeHtml(shortDescription)}</p></div>`;
      })
      .join("");
  }

  function updateStepState() {
    stepButtons.forEach((button, index) => {
      const isActive = index === currentStep;
      const isDone = index < currentStep && validateStep(index, false);
      const isLocked = index > 0 && !validateUntil(index, false);

      button.classList.toggle("active", isActive);
      button.classList.toggle("step-complete", isDone);
      button.classList.toggle("step-locked", isLocked);
      button.setAttribute("aria-selected", isActive ? "true" : "false");
    });

    if (stepCounter) stepCounter.textContent = `${currentStep + 1} / ${stepPanes.length}`;
  }

  function showStep(index, options = {}) {
    if (index < 0 || index >= stepPanes.length) return;
    if (options.validate && !validateUntil(index, true)) return;

    currentStep = index;

    stepPanes.forEach((pane) => {
      const active = Number(pane.dataset.complaintStep || 0) === index;
      pane.classList.toggle("show", active);
      pane.classList.toggle("active", active);
    });

    updateReviewSummary();
    updateStepState();

    if (index === 2) {
      window.setTimeout(() => window.dispatchEvent(new Event("resize")), 80);
    }

    form.scrollIntoView({ behavior: "smooth", block: "start" });
  }

  addButton?.addEventListener("click", () => {
    const template = itemsBox.querySelector(".complaint-item");
    const clone = template.cloneNode(true);

    clone.querySelector(".complaint-type").value = "name_error";
    clone.querySelector("textarea").value = "";
    resetSearches(clone);
    itemsBox.appendChild(clone);
    bindItem(clone);
    renumberItems();
    updateModeState();
  });

  confirmationInput?.addEventListener("input", async (event) => {
    if (confirmationIdInput) confirmationIdInput.value = "";
    confirmationCanSubmit = true;
    setConfirmationMessage("");
    if (confirmationResults) {
      confirmationResults.innerHTML = renderContingent(
        await search(contingentUrl, event.target.value),
      );
    }
    updateReviewSummary();
  });

  confirmationResults?.addEventListener("click", async (event) => {
    const card = event.target.closest(".result-card");
    if (!card) return;

    confirmationInput.value = card.dataset.label || "";
    confirmationIdInput.value = card.dataset.id || "";
    confirmationResults.innerHTML = "";
    setConfirmationMessage("Mengecek status konfirmasi...", "");

    const status = await getConfirmationStatus(card.dataset.id || "");
    confirmationCanSubmit = Boolean(status?.can_confirm);
    if (confirmationCanSubmit) {
      setConfirmationMessage("Kontingen bisa melakukan konfirmasi Tidak Ada Complain.", "success");
    } else {
      setConfirmationMessage(status?.message || "Kontingen belum bisa melakukan konfirmasi.", "error");
    }
    updateReviewSummary();
    updateStepState();
  });

  eventSelect?.addEventListener("change", () => {
    itemsBox.querySelectorAll(".complaint-item").forEach(resetSearches);
    resetConfirmationSearch();
    refreshStepVisibility();
    updateReviewSummary();
  });

  modeRadios.forEach((radio) => {
    radio.addEventListener("change", updateModeState);
  });

  form.querySelector('[name="official_name"]')?.addEventListener("input", updateReviewSummary);
  form.querySelector('[name="official_phone"]')?.addEventListener("input", updateReviewSummary);
  form.querySelector("#clearSignature")?.addEventListener("click", () => {
    window.setTimeout(updateReviewSummary, 50);
  });
  form.querySelector("#signatureCanvas")?.addEventListener("pointerup", () => {
    window.setTimeout(updateReviewSummary, 50);
  });

  stepButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const target = Number(button.dataset.complaintStepTarget || 0);
      if (target <= currentStep) {
        showStep(target);
        return;
      }

      showStep(target, { validate: true });
    });
  });

  form.querySelectorAll("[data-step-next]").forEach((button) => {
    button.addEventListener("click", () => {
      if (!validateStep(currentStep, true)) return;
      showStep(currentStep + 1, { validate: true });
    });
  });

  form.querySelectorAll("[data-step-prev]").forEach((button) => {
    button.addEventListener("click", () => showStep(currentStep - 1));
  });

  form.addEventListener("submit", (event) => {
    for (let index = 0; index < stepPanes.length - 1; index += 1) {
      if (!validateStep(index, false)) {
        event.preventDefault();
        showStep(index);
        window.setTimeout(() => focusFirstInvalid(index), 60);
        return;
      }
    }

    if (getSubmissionMode() === "no_complaint" && !confirmationCanSubmit) {
      event.preventDefault();
      showStep(1);
      confirmationMessage?.scrollIntoView({ behavior: "smooth", block: "center" });
    }
  });

  itemsBox.querySelectorAll(".complaint-item").forEach(bindItem);
  renumberItems();
  updateModeState();
  refreshStepVisibility();
  updateReviewSummary();
  showStep(eventSelect?.value ? 1 : 0);
})();
