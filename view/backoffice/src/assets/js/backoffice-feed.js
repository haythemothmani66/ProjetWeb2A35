"use strict";

(() => {
  const state = {
    controllerUrl: "",
    devoirs: [],
    corrections: [],
    search: "",
  };

  const elements = {
    searchInput: document.getElementById("searchInput"),
    clearFiltersBtn: document.getElementById("clearFiltersBtn"),
    refreshFeedBtn: document.getElementById("refreshFeedBtn"),
    devoirsTableBody: document.getElementById("devoirsTableBody"),
    correctionsTableBody: document.getElementById("correctionsTableBody"),
    devoirCountBadge: document.getElementById("devoirCountBadge"),
    correctionCountBadge: document.getElementById("correctionCountBadge"),
    statTotalDevoirs: document.getElementById("statTotalDevoirs"),
    statTotalCorrections: document.getElementById("statTotalCorrections"),
    detailsModalBody: document.getElementById("detailsModalBody"),
    detailsModalLabel: document.getElementById("detailsModalLabel"),
    editDevoirForm: document.getElementById("editDevoirForm"),
    editCorrectionForm: document.getElementById("editCorrectionForm"),
    toastContainer: document.getElementById("toastContainer"),
  };

  const detailsModal = new bootstrap.Modal(
    document.getElementById("detailsModal"),
  );
  const editDevoirModal = new bootstrap.Modal(
    document.getElementById("editDevoirModal"),
  );
  const editCorrectionModal = new bootstrap.Modal(
    document.getElementById("editCorrectionModal"),
  );

  const FIELD_RULES = {
    editTitre: {
      required: "Le titre est requis.",
      minLength: 3,
      minLengthMessage: "Le titre doit contenir au moins 3 caracteres.",
    },
    editDateSoumission: {
      required: "La date de soumission est requise.",
    },
    editDescription: {
      required: "La description est requise.",
      minLength: 10,
      minLengthMessage: "La description doit contenir au moins 10 caracteres.",
    },
    editNiveau: {
      required: "Le niveau de difficulte est requis.",
    },
    editTypeErreur: {
      required: "Le type d'erreur est requis.",
    },
    editTemps: {
      required: "Le temps estime est requis.",
      min: 1,
      max: 480,
      rangeMessage: "Le temps estime doit etre entre 1 et 480 minutes.",
    },
    editProgression: {
      required: "La progression est requise.",
      min: 0,
      max: 100,
      rangeMessage: "La progression doit etre entre 0 et 100%.",
    },
    editUrgence: {
      required: "L'urgence est requise.",
    },
    editMotsCles: {
      required: "Les mots cles sont requis.",
    },
    editDateCorrection: {
      required: "La date de correction est requise.",
    },
    editTypeFeedback: {
      required: "Le type de feedback est requis.",
    },
    editCommentaire: {
      required: "Le commentaire est requis.",
      minLength: 10,
      minLengthMessage: "Le commentaire doit contenir au moins 10 caracteres.",
    },
    editNote: {
      required: "La note est requise.",
      min: 0,
      max: 20,
      rangeMessage: "La note doit etre entre 0 et 20.",
    },
    editIterations: {
      required: "Le nombre d'iterations est requis.",
      min: 1,
      max: 10,
      rangeMessage: "Le nombre d'iterations doit etre entre 1 et 10.",
    },
    editRapidite: {
      required: "La rapidite est requise.",
      min: 1,
      max: 480,
      rangeMessage: "La rapidite doit etre entre 1 et 480 minutes.",
    },
    editTon: {
      required: "Le ton du feedback est requis.",
    },
    editCompetences: {
      required: "Les competences sont requises.",
    },
  };

  function collectFormFields(form) {
    if (!form) {
      return [];
    }

    return Array.from(form.querySelectorAll("input, select, textarea")).filter(
      (field) => field.type !== "hidden" && !field.disabled,
    );
  }

  function getFieldKey(field) {
    return field.id || field.name || "";
  }

  function isFieldEmpty(field) {
    if (field.type === "file") {
      return !field.files || field.files.length === 0;
    }

    return String(field.value || "").trim() === "";
  }

  function getFieldMessageElement(field) {
    const key = getFieldKey(field);

    if (!key || !field.parentElement) {
      return null;
    }

    let messageElement = field.parentElement.querySelector(
      `.js-field-error[data-field="${key}"]`,
    );

    if (!messageElement) {
      messageElement = document.createElement("div");
      messageElement.className = "js-field-error text-danger small mt-1";
      messageElement.dataset.field = key;
      messageElement.style.display = "none";
      field.insertAdjacentElement("afterend", messageElement);
    }

    return messageElement;
  }

  function clearFieldFeedback(field, markValid) {
    const messageElement = getFieldMessageElement(field);

    if (messageElement) {
      messageElement.textContent = "";
      messageElement.style.display = "none";
    }

    field.classList.remove("is-invalid");

    if (markValid) {
      field.classList.add("is-valid");
    } else {
      field.classList.remove("is-valid");
    }
  }

  function setFieldError(field, message) {
    const messageElement = getFieldMessageElement(field);

    if (messageElement) {
      messageElement.textContent = message;
      messageElement.style.display = "block";
    }

    field.classList.add("is-invalid");
    field.classList.remove("is-valid");
  }

  function resolveValidationError(field) {
    const key = getFieldKey(field);
    const rules = FIELD_RULES[key] || {};
    const value = String(field.value || "").trim();
    const required = field.required || Boolean(rules.required);

    if (required && isFieldEmpty(field)) {
      return rules.required || "Ce champ est requis.";
    }

    if (!isFieldEmpty(field)) {
      const minLength = Number(rules.minLength || field.minLength || 0);

      if (minLength > 0 && value.length < minLength) {
        return rules.minLengthMessage || `Minimum ${minLength} caracteres.`;
      }
    }

    if (field.type === "number" && !isFieldEmpty(field)) {
      const numericValue = Number(value);

      if (Number.isNaN(numericValue)) {
        return "Valeur numerique invalide.";
      }

      const min =
        rules.min !== undefined
          ? Number(rules.min)
          : field.min !== ""
            ? Number(field.min)
            : Number.NEGATIVE_INFINITY;

      const max =
        rules.max !== undefined
          ? Number(rules.max)
          : field.max !== ""
            ? Number(field.max)
            : Number.POSITIVE_INFINITY;

      if (numericValue < min || numericValue > max) {
        return rules.rangeMessage || `Valeur attendue entre ${min} et ${max}.`;
      }
    }

    return "";
  }

  function validateField(field, { force = false } = {}) {
    if (!field || field.disabled || field.type === "hidden") {
      return true;
    }

    const touched = field.dataset.touched === "1";
    const shouldDisplay = force || touched;
    const errorMessage = resolveValidationError(field);

    if (errorMessage) {
      if (shouldDisplay) {
        setFieldError(field, errorMessage);
      } else {
        clearFieldFeedback(field, false);
      }

      return false;
    }

    clearFieldFeedback(field, shouldDisplay && !isFieldEmpty(field));
    return true;
  }

  function validateForm(form) {
    const fields = collectFormFields(form);
    let firstInvalid = null;
    let allValid = true;

    fields.forEach((field) => {
      field.dataset.touched = "1";
      const fieldValid = validateField(field, { force: true });

      if (!fieldValid) {
        allValid = false;
        if (!firstInvalid) {
          firstInvalid = field;
        }
      }
    });

    return { allValid, firstInvalid };
  }

  function resetFormValidation(form) {
    collectFormFields(form).forEach((field) => {
      field.dataset.touched = "0";
      clearFieldFeedback(field, false);
    });
  }

  function bindRealtimeValidation(form) {
    const fields = collectFormFields(form);

    fields.forEach((field) => {
      const liveEvent =
        field.type === "file" ||
        field.tagName === "SELECT" ||
        field.type === "date"
          ? "change"
          : "input";

      field.addEventListener(liveEvent, () => {
        field.dataset.touched = "1";
        validateField(field);
      });

      field.addEventListener("blur", () => {
        field.dataset.touched = "1";
        validateField(field);
      });
    });

    form.addEventListener("reset", () => {
      window.requestAnimationFrame(() => resetFormValidation(form));
    });
  }

  function getControllerUrl() {
    const normalized = window.location.pathname.replaceAll("\\", "/");
    const marker = "/view/backoffice/src/pages/backoffice/";
    const markerIndex = normalized.indexOf(marker);

    if (markerIndex !== -1) {
      const appBase = normalized.slice(0, markerIndex);
      return `${appBase}/controller/devoirs_back.php`;
    }

    return "/controller/devoirs_back.php";
  }

  function escapeHtml(value) {
    return String(value || "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#39;");
  }

  function showToast(message, variant) {
    const color = variant === "danger" ? "danger" : "success";
    const toastId = `toast-${Date.now()}`;

    const toastMarkup = `
      <div id="${toastId}" class="toast align-items-center text-bg-${color} border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">${escapeHtml(message)}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    `;

    elements.toastContainer.insertAdjacentHTML("beforeend", toastMarkup);
    const toastElement = document.getElementById(toastId);
    const toast = bootstrap.Toast.getOrCreateInstance(toastElement, {
      delay: 2800,
    });

    toast.show();
    toastElement.addEventListener("hidden.bs.toast", () =>
      toastElement.remove(),
    );
  }

  function parseControllerResponse(raw) {
    const clean = (raw || "").trim();

    try {
      const json = JSON.parse(clean);
      return {
        success: Boolean(json.success),
        message:
          json.message ||
          (json.success ? "Operation succeeded." : "Operation failed."),
      };
    } catch (error) {
      // non-json response
    }

    if (clean.includes("|")) {
      const [status, message] = clean.split("|");
      const normalizedStatus = (status || "").toLowerCase();
      return {
        success:
          normalizedStatus.includes("succ") ||
          normalizedStatus.includes("success"),
        message: message || clean,
      };
    }

    if (clean.toLowerCase() === "succès" || clean.toLowerCase() === "success") {
      return { success: true, message: "Operation succeeded." };
    }

    return {
      success: false,
      message: clean || "Unknown server response.",
    };
  }

  async function fetchJson(url) {
    const response = await fetch(url, {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    const raw = await response.text();
    let payload = null;

    try {
      payload = JSON.parse(raw);
    } catch (error) {
      const jsonStart = raw.indexOf("{");
      const jsonEnd = raw.lastIndexOf("}");

      if (jsonStart !== -1 && jsonEnd !== -1 && jsonEnd > jsonStart) {
        try {
          payload = JSON.parse(raw.slice(jsonStart, jsonEnd + 1));
        } catch (innerError) {
          payload = null;
        }
      }
    }

    if (!payload) {
      throw new Error("Invalid JSON response from server.");
    }

    if (!response.ok || payload.success === false) {
      throw new Error(payload.message || "Request failed.");
    }

    return payload;
  }

  function setSidebarActiveState() {
    const links = document.querySelectorAll(".navbar-nav .nav-link");

    links.forEach((link) => {
      const href = link.getAttribute("href") || "";
      const isFeedLink = href.includes("pages/backoffice/feed_back.html");
      link.classList.toggle("active", isFeedLink);
    });
  }

  function urgenceBadge(urgence) {
    const normalized = String(urgence || "").toLowerCase();

    if (normalized === "urgente") {
      return '<span class="badge text-danger-emphasis bg-danger-subtle">Urgente</span>';
    }

    if (normalized === "moyenne") {
      return '<span class="badge text-warning-emphasis bg-warning-subtle">Moyenne</span>';
    }

    return '<span class="badge text-success-emphasis bg-success-subtle">Faible</span>';
  }

  function filterDevoirs() {
    if (!state.search) {
      return state.devoirs;
    }

    const query = state.search.toLowerCase();
    return state.devoirs.filter((devoir) => {
      const haystack = [
        devoir.id_devoir,
        devoir.titre,
        devoir.description,
        devoir.mots_cles,
        devoir.urgence,
        devoir.niveau_difficulte,
      ]
        .join(" ")
        .toLowerCase();

      return haystack.includes(query);
    });
  }

  function filterCorrections() {
    if (!state.search) {
      return state.corrections;
    }

    const query = state.search.toLowerCase();
    return state.corrections.filter((correction) => {
      const haystack = [
        correction.id_correction,
        correction.id_devoir,
        correction.devoir_titre,
        correction.commentaire,
        correction.type_feedback,
        correction.ton_feedback,
      ]
        .join(" ")
        .toLowerCase();

      return haystack.includes(query);
    });
  }

  function renderDevoirTable() {
    const rows = filterDevoirs();
    elements.devoirCountBadge.textContent = `${rows.length} devoir(s)`;

    if (!rows.length) {
      elements.devoirsTableBody.innerHTML =
        '<tr><td colspan="6" class="text-center py-5 text-secondary">No devoirs found.</td></tr>';
      return;
    }

    elements.devoirsTableBody.innerHTML = rows
      .map(
        (devoir) => `
        <tr>
          <td>${devoir.id_devoir}</td>
          <td>${escapeHtml(devoir.titre)}</td>
          <td>${escapeHtml(devoir.date_soumission)}</td>
          <td>${escapeHtml(devoir.niveau_difficulte)}</td>
          <td>${urgenceBadge(devoir.urgence)}</td>
          <td class="text-end">
            <div class="d-flex justify-content-end gap-2 flex-wrap">

            <!-- NOUVEAU BOUTON : Ajouter correction -->
              <a href="submit_back.html?add_correction_for=${devoir.id_devoir}&title=${encodeURIComponent(devoir.titre)}" 
                 class="btn btn-sm btn-success"
                 style="background: #10B981; border: none; border-radius: 0.5rem; padding: 0.3rem 0.8rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; text-decoration: none; color: white;">
                <i class="ti ti-plus"></i>
                Ajouter correction
              </a>


              <button class="btn btn-sm btn-white" data-type="devoir" data-action="view" data-id="${devoir.id_devoir}">View</button>
              <button class="btn btn-sm btn-white" data-type="devoir" data-action="edit" data-id="${devoir.id_devoir}">modifier</button>
              <button class="btn btn-sm btn-danger" data-type="devoir" data-action="delete" data-id="${devoir.id_devoir}">Delete</button>
            </div>
          </td>
        </tr>
      `,
      )
      .join("");
  }

  function renderCorrectionTable() {
    const rows = filterCorrections();
    elements.correctionCountBadge.textContent = `${rows.length} correction(s)`;

    if (!rows.length) {
      elements.correctionsTableBody.innerHTML =
        '<tr><td colspan="6" class="text-center py-5 text-secondary">No corrections found.</td></tr>';
      return;
    }

    elements.correctionsTableBody.innerHTML = rows
      .map(
        (correction) => `
        <tr>
          <td>${correction.id_correction}</td>
          <td>#${correction.id_devoir} - ${escapeHtml(correction.devoir_titre || "N/A")}</td>
          <td>${escapeHtml(correction.note_estimee)}</td>
          <td>${escapeHtml(correction.date_correction)}</td>
          <td>${escapeHtml(correction.type_feedback)}</td>
          <td class="text-end">
            <div class="d-flex justify-content-end gap-2 flex-wrap">
              <button class="btn btn-sm btn-white" data-type="correction" data-action="view" data-id="${correction.id_correction}">View</button>
              <button class="btn btn-sm btn-white" data-type="correction" data-action="edit" data-id="${correction.id_correction}">Edit</button>
              <button class="btn btn-sm btn-danger" data-type="correction" data-action="delete" data-id="${correction.id_correction}">Delete</button>
            </div>
          </td>
        </tr>
      `,
      )
      .join("");
  }

  function renderStats() {
    elements.statTotalDevoirs.textContent = String(state.devoirs.length);
    elements.statTotalCorrections.textContent = String(
      state.corrections.length,
    );
  }

  function renderAll() {
    renderStats();
    renderDevoirTable();
    renderCorrectionTable();
  }

  async function loadFeedData() {
    const [devoirPayload, correctionPayload] = await Promise.all([
      fetchJson(`${state.controllerUrl}?action=listdevoirs`),
      fetchJson(`${state.controllerUrl}?action=listcorrections`),
    ]);

    state.devoirs = Array.isArray(devoirPayload.data) ? devoirPayload.data : [];
    state.corrections = Array.isArray(correctionPayload.data)
      ? correctionPayload.data
      : [];
    renderAll();
  }

  function setDetailsHtml(title, rows) {
    elements.detailsModalLabel.textContent = title;
    elements.detailsModalBody.innerHTML = rows
      .map(
        (row) => `
          <div class="mb-2">
            <strong>${escapeHtml(row.label)}:</strong>
            <span>${escapeHtml(row.value)}</span>
          </div>
        `,
      )
      .join("");
    detailsModal.show();
  }

  async function openDevoirDetails(id) {
    const payload = await fetchJson(
      `${state.controllerUrl}?action=getdevoir&id=${encodeURIComponent(id)}`,
    );
    const devoir = payload.data;

    setDetailsHtml(`Devoir #${devoir.id_devoir}`, [
      { label: "Titre", value: devoir.titre },
      { label: "Description", value: devoir.description },
      { label: "Date soumission", value: devoir.date_soumission },
      { label: "Niveau", value: devoir.niveau_difficulte },
      { label: "Type erreur", value: devoir.type_erreur_predominant },
      { label: "Temps (min)", value: devoir.temps_estime_resolution },
      { label: "Progression (%)", value: devoir.progression_eleve },
      { label: "Mots cles", value: devoir.mots_cles },
      { label: "Urgence", value: devoir.urgence },
      { label: "Fichier", value: devoir.fichier || "N/A" },
    ]);
  }

  async function openCorrectionDetails(id) {
    const payload = await fetchJson(
      `${state.controllerUrl}?action=getcorrection&id=${encodeURIComponent(id)}`,
    );
    const correction = payload.data;

    setDetailsHtml(`Correction #${correction.id_correction}`, [
      { label: "ID devoir", value: correction.id_devoir },
      { label: "Commentaire", value: correction.commentaire },
      { label: "Date correction", value: correction.date_correction },
      { label: "Type feedback", value: correction.type_feedback },
      { label: "Note", value: correction.note_estimee },
      { label: "Competences", value: correction.competences_evaluees },
      { label: "Iterations", value: correction.nombre_iterations },
      {
        label: "Suggestions",
        value: correction.suggestions_personnalisees || "N/A",
      },
      {
        label: "Ressources",
        value: correction.ressources_recommandees || "N/A",
      },
      { label: "Rapidite (min)", value: correction.rapidite_correction },
      { label: "Ton", value: correction.ton_feedback },
      { label: "Fichier", value: correction.fichier_corrige || "N/A" },
    ]);
  }

  async function openDevoirEdit(id) {
    const payload = await fetchJson(
      `${state.controllerUrl}?action=getdevoir&id=${encodeURIComponent(id)}`,
    );
    const devoir = payload.data;

    document.getElementById("editDevoirId").value = devoir.id_devoir;
    document.getElementById("editTitre").value = devoir.titre || "";
    document.getElementById("editDescription").value = devoir.description || "";
    document.getElementById("editDateSoumission").value =
      devoir.date_soumission || "";
    document.getElementById("editNiveau").value =
      devoir.niveau_difficulte || "moyen";
    document.getElementById("editTypeErreur").value =
      devoir.type_erreur_predominant || "logique";
    document.getElementById("editTemps").value =
      devoir.temps_estime_resolution || 1;
    document.getElementById("editProgression").value =
      devoir.progression_eleve || 0;
    document.getElementById("editMotsCles").value = devoir.mots_cles || "";
    document.getElementById("editUrgence").value = devoir.urgence || "moyenne";

    resetFormValidation(elements.editDevoirForm);

    editDevoirModal.show();
  }

  async function openCorrectionEdit(id) {
    const payload = await fetchJson(
      `${state.controllerUrl}?action=getcorrection&id=${encodeURIComponent(id)}`,
    );
    const correction = payload.data;

    document.getElementById("editCorrectionId").value =
      correction.id_correction;
    document.getElementById("editCommentaire").value =
      correction.commentaire || "";
    document.getElementById("editDateCorrection").value =
      correction.date_correction || "";
    document.getElementById("editTypeFeedback").value =
      correction.type_feedback || "explicatif";
    document.getElementById("editNote").value = correction.note_estimee || 0;
    document.getElementById("editCompetences").value =
      correction.competences_evaluees || "";
    document.getElementById("editIterations").value =
      correction.nombre_iterations || 1;
    document.getElementById("editSuggestions").value =
      correction.suggestions_personnalisees || "";
    document.getElementById("editRessources").value =
      correction.ressources_recommandees || "";
    document.getElementById("editRapidite").value =
      correction.rapidite_correction || 1;
    document.getElementById("editTon").value =
      correction.ton_feedback || "neutre";

    resetFormValidation(elements.editCorrectionForm);

    editCorrectionModal.show();
  }

  async function deleteItem(type, id) {
    const label = type === "devoir" ? "devoir" : "correction";
    const confirmed = window.confirm(`Delete this ${label}?`);

    if (!confirmed) {
      return;
    }

    const action = type === "devoir" ? "delete" : "deletecorrection";
    const response = await fetch(
      `${state.controllerUrl}?action=${action}&id=${encodeURIComponent(id)}`,
    );
    const raw = await response.text();
    const parsed = parseControllerResponse(raw);

    if (!response.ok || !parsed.success) {
      throw new Error(parsed.message || `Unable to delete ${label}.`);
    }

    showToast(parsed.message || `${label} deleted.`, "success");
    await loadFeedData();
  }

  async function saveDevoirEdit(event) {
    event.preventDefault();

    const { allValid, firstInvalid } = validateForm(elements.editDevoirForm);

    if (!allValid) {
      showToast("Veuillez corriger les champs invalides du devoir.", "danger");

      if (firstInvalid && typeof firstInvalid.focus === "function") {
        firstInvalid.focus();
      }

      return;
    }

    const formData = new FormData(elements.editDevoirForm);
    const response = await fetch(`${state.controllerUrl}?action=updatedevoir`, {
      method: "POST",
      body: formData,
    });

    const raw = await response.text();
    const parsed = parseControllerResponse(raw);

    if (!response.ok || !parsed.success) {
      throw new Error(parsed.message || "Unable to update devoir.");
    }

    showToast(parsed.message || "Devoir updated successfully.", "success");
    editDevoirModal.hide();
    resetFormValidation(elements.editDevoirForm);
    await loadFeedData();
  }

  async function saveCorrectionEdit(event) {
    event.preventDefault();

    const { allValid, firstInvalid } = validateForm(
      elements.editCorrectionForm,
    );

    if (!allValid) {
      showToast(
        "Veuillez corriger les champs invalides de la correction.",
        "danger",
      );

      if (firstInvalid && typeof firstInvalid.focus === "function") {
        firstInvalid.focus();
      }

      return;
    }

    const formData = new FormData(elements.editCorrectionForm);
    const response = await fetch(
      `${state.controllerUrl}?action=updatecorrection`,
      {
        method: "POST",
        body: formData,
      },
    );

    const raw = await response.text();
    const parsed = parseControllerResponse(raw);

    if (!response.ok || !parsed.success) {
      throw new Error(parsed.message || "Unable to update correction.");
    }

    showToast(parsed.message || "Correction updated successfully.", "success");
    editCorrectionModal.hide();
    resetFormValidation(elements.editCorrectionForm);
    await loadFeedData();
  }

  async function handleTableActions(event) {
    const button = event.target.closest(
      "button[data-action][data-id][data-type]",
    );

    if (!button) {
      return;
    }

    const type = button.getAttribute("data-type");
    const action = button.getAttribute("data-action");
    const id = Number(button.getAttribute("data-id"));

    if (!type || !action || !id) {
      return;
    }

    try {
      if (type === "devoir" && action === "view") {
        await openDevoirDetails(id);
        return;
      }

      if (type === "devoir" && action === "edit") {
        await openDevoirEdit(id);
        return;
      }

      if (type === "devoir" && action === "delete") {
        await deleteItem("devoir", id);
        return;
      }

      if (type === "correction" && action === "view") {
        await openCorrectionDetails(id);
        return;
      }

      if (type === "correction" && action === "edit") {
        await openCorrectionEdit(id);
        return;
      }

      if (type === "correction" && action === "delete") {
        await deleteItem("correction", id);
      }
    } catch (error) {
      showToast(error.message || "Action failed.", "danger");
    }
  }

  function bindEvents() {
    let searchTimeout = null;

    bindRealtimeValidation(elements.editDevoirForm);
    bindRealtimeValidation(elements.editCorrectionForm);

    elements.searchInput.addEventListener("input", () => {
      if (searchTimeout) {
        clearTimeout(searchTimeout);
      }

      searchTimeout = setTimeout(() => {
        state.search = elements.searchInput.value.trim().toLowerCase();
        renderAll();
      }, 200);
    });

    elements.clearFiltersBtn.addEventListener("click", () => {
      state.search = "";
      elements.searchInput.value = "";
      renderAll();
    });

    elements.refreshFeedBtn.addEventListener("click", async () => {
      try {
        await loadFeedData();
        showToast("Feed refreshed.", "success");
      } catch (error) {
        showToast(error.message || "Unable to refresh feed.", "danger");
      }
    });

    elements.devoirsTableBody.addEventListener("click", handleTableActions);
    elements.correctionsTableBody.addEventListener("click", handleTableActions);

    elements.editDevoirForm.addEventListener("submit", async (event) => {
      try {
        await saveDevoirEdit(event);
      } catch (error) {
        showToast(error.message || "Unable to save devoir.", "danger");
      }
    });

    elements.editCorrectionForm.addEventListener("submit", async (event) => {
      try {
        await saveCorrectionEdit(event);
      } catch (error) {
        showToast(error.message || "Unable to save correction.", "danger");
      }
    });

    document
      .getElementById("editDevoirModal")
      .addEventListener("hidden.bs.modal", () => {
        resetFormValidation(elements.editDevoirForm);
      });

    document
      .getElementById("editCorrectionModal")
      .addEventListener("hidden.bs.modal", () => {
        resetFormValidation(elements.editCorrectionForm);
      });
  }

  async function init() {
    state.controllerUrl = getControllerUrl();
    setSidebarActiveState();
    bindEvents();

    try {
      await loadFeedData();
    } catch (error) {
      showToast(error.message || "Unable to load feed.", "danger");
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
