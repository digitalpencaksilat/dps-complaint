(() => {
  const form = document.getElementById("complaintForm");
  if (!form) return;

  const eventSelect = form.querySelector('[name="event_id"]');
  const complaintDetails = document.getElementById("complaintDetails");
  const itemsBox = document.getElementById("complaintItems");
  const addButton = document.getElementById("addComplaintItem");
  const participantUrl =
    form.dataset.participantUrl || "/api/participants/search";
  const contingentUrl = form.dataset.contingentUrl || "/api/contingents/search";
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

  function updateDescriptionHelper(item) {
    const type = item.querySelector(".complaint-type")?.value || "name_error";
    const config = helpers[type] || helpers.name_error;

    item.querySelector(".description-helper").innerHTML =
      `<i class="fas fa-lightbulb me-1"></i>${escapeHtml(config.text)}`;
    item.querySelector(".complaint-description").placeholder =
      config.placeholder;
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

  function refreshStepVisibility() {
    complaintDetails?.classList.toggle("d-none", !eventSelect?.value);
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

  function bindItem(item) {
    item.querySelector(".complaint-type")?.addEventListener("change", () => {
      resetSearches(item);
      toggleType(item);
    });

    item
      .querySelector(".participant-search")
      ?.addEventListener("input", async (event) => {
        item.querySelector(".participant-id").value = "";
        item.querySelector(".participant-results").innerHTML =
          renderParticipant(await search(participantUrl, event.target.value));
      });

    item
      .querySelector(".contingent-search")
      ?.addEventListener("input", async (event) => {
        item.querySelector(".contingent-id").value = "";
        item.querySelector(".contingent-results").innerHTML = renderContingent(
          await search(contingentUrl, event.target.value),
        );
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
      }),
    );

    item.querySelector(".remove-item")?.addEventListener("click", () => {
      item.remove();
      renumberItems();
    });

    toggleType(item);
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
  });

  eventSelect?.addEventListener("change", () => {
    itemsBox.querySelectorAll(".complaint-item").forEach(resetSearches);
    refreshStepVisibility();
  });

  itemsBox.querySelectorAll(".complaint-item").forEach(bindItem);
  renumberItems();
  refreshStepVisibility();
})();
