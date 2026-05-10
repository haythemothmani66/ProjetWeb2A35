'use strict';

(() => {
  const state = {
    controllerUrl: '',
    devoirs: [],
  };

  const elements = {
    devoirForm: document.getElementById('devoirForm'),
    correctionForm: document.getElementById('correctionForm'),
    idDevoirSelect: document.getElementById('id_devoir'),
    statTotalDevoirs: document.getElementById('statTotalDevoirs'),
    statTotalCorrections: document.getElementById('statTotalCorrections'),
    toastContainer: document.getElementById('toastContainer'),
    submitDevoirBtn: document.getElementById('submitDevoirBtn'),
    submitCorrectionBtn: document.getElementById('submitCorrectionBtn'),
    dateSoumission: document.getElementById('date_soumission'),
    dateCorrection: document.getElementById('date_correction'),
  };

  const FIELD_RULES = {
    titre: {
      required: 'Le titre est requis.',
      minLength: 3,
      minLengthMessage: 'Le titre doit contenir au moins 3 caracteres.',
    },
    description: {
      required: 'La description est requise.',
      minLength: 10,
      minLengthMessage: 'La description doit contenir au moins 10 caracteres.',
    },
    file1: {
      required: 'Veuillez selectionner un fichier.',
    },
    date_soumission: {
      required: 'La date de soumission est requise.',
    },
    niveau_difficulte: {
      required: 'Le niveau de difficulte est requis.',
    },
    type_erreur_predominant: {
      required: "Le type d'erreur est requis.",
    },
    urgence: {
      required: "L'urgence est requise.",
    },
    temps_estime_resolution: {
      required: 'Le temps estime est requis.',
      min: 1,
      max: 480,
      rangeMessage: 'Le temps estime doit etre entre 1 et 480 minutes.',
    },
    progression_eleve: {
      required: 'La progression est requise.',
      min: 0,
      max: 100,
      rangeMessage: 'La progression doit etre entre 0 et 100%.',
    },
    mots_cles: {
      required: 'Les mots cles sont requis.',
    },
    id_devoir: {
      required: 'Veuillez selectionner un devoir.',
    },
    commentaire: {
      required: 'Le commentaire est requis.',
      minLength: 10,
      minLengthMessage: 'Le commentaire doit contenir au moins 10 caracteres.',
    },
    file2: {
      required: 'Veuillez selectionner le fichier corrige.',
    },
    date_correction: {
      required: 'La date de correction est requise.',
    },
    type_feedback: {
      required: 'Le type de feedback est requis.',
    },
    note_estimee: {
      required: 'La note est requise.',
      min: 0,
      max: 20,
      rangeMessage: 'La note doit etre entre 0 et 20.',
    },
    competences_evaluees: {
      required: 'Les competences sont requises.',
    },
    nombre_iterations: {
      required: "Le nombre d'iterations est requis.",
      min: 1,
      max: 10,
      rangeMessage: "Le nombre d'iterations doit etre entre 1 et 10.",
    },
    rapidite_correction: {
      required: 'La rapidite est requise.',
      min: 1,
      max: 480,
      rangeMessage: 'La rapidite doit etre entre 1 et 480 minutes.',
    },
    ton_feedback: {
      required: 'Le ton du feedback est requis.',
    },
  };

  function collectFormFields(form) {
    if (!form) {
      return [];
    }

    return Array.from(form.querySelectorAll('input, select, textarea')).filter(
      (field) => field.type !== 'hidden' && !field.disabled,
    );
  }

  function getFieldKey(field) {
    return field.id || field.name || '';
  }

  function isFieldEmpty(field) {
    if (field.type === 'file') {
      return !field.files || field.files.length === 0;
    }

    return String(field.value || '').trim() === '';
  }

  function getFieldMessageElement(field) {
    const key = getFieldKey(field);

    if (!key || !field.parentElement) {
      return null;
    }

    let messageElement = field.parentElement.querySelector(`.js-field-error[data-field="${key}"]`);

    if (!messageElement) {
      messageElement = document.createElement('div');
      messageElement.className = 'js-field-error text-danger small mt-1';
      messageElement.dataset.field = key;
      messageElement.style.display = 'none';
      field.insertAdjacentElement('afterend', messageElement);
    }

    return messageElement;
  }

  function clearFieldFeedback(field, markValid) {
    const messageElement = getFieldMessageElement(field);

    if (messageElement) {
      messageElement.textContent = '';
      messageElement.style.display = 'none';
    }

    field.classList.remove('is-invalid');

    if (markValid) {
      field.classList.add('is-valid');
    } else {
      field.classList.remove('is-valid');
    }
  }

  function setFieldError(field, message) {
    const messageElement = getFieldMessageElement(field);

    if (messageElement) {
      messageElement.textContent = message;
      messageElement.style.display = 'block';
    }

    field.classList.add('is-invalid');
    field.classList.remove('is-valid');
  }

  function resolveValidationError(field) {
    const key = getFieldKey(field);
    const rules = FIELD_RULES[key] || {};
    const value = String(field.value || '').trim();
    const hasRequiredOverride = Object.prototype.hasOwnProperty.call(field.dataset, 'required');
    const required = hasRequiredOverride
      ? ['1', 'true', 'yes'].includes(String(field.dataset.required || '').toLowerCase())
      : Boolean(rules.required);

    if (required && isFieldEmpty(field)) {
      return rules.required || 'Ce champ est requis.';
    }

    if (!isFieldEmpty(field)) {
      const minLength = Number(rules.minLength || 0);

      if (minLength > 0 && value.length < minLength) {
        return rules.minLengthMessage || `Minimum ${minLength} caracteres.`;
      }
    }

    if (field.type === 'number' && !isFieldEmpty(field)) {
      const numericValue = Number(value);

      if (Number.isNaN(numericValue)) {
        return 'Valeur numerique invalide.';
      }

      const min = rules.min !== undefined ? Number(rules.min) : Number.NEGATIVE_INFINITY;
      const max = rules.max !== undefined ? Number(rules.max) : Number.POSITIVE_INFINITY;

      if (numericValue < min || numericValue > max) {
        return rules.rangeMessage || `Valeur attendue entre ${min} et ${max}.`;
      }
    }

    return '';
  }

  function validateField(field, { force = false } = {}) {
    if (!field || field.disabled || field.type === 'hidden') {
      return true;
    }

    const touched = field.dataset.touched === '1';
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
      field.dataset.touched = '1';
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
      field.dataset.touched = '0';
      clearFieldFeedback(field, false);
    });
  }

  function bindRealtimeValidation(form) {
    const fields = collectFormFields(form);

    fields.forEach((field) => {
      const liveEvent = field.type === 'file' || field.tagName === 'SELECT' || field.type === 'date'
        ? 'change'
        : 'input';

      field.addEventListener(liveEvent, () => {
        field.dataset.touched = '1';
        validateField(field);
      });

      field.addEventListener('blur', () => {
        field.dataset.touched = '1';
        validateField(field);
      });
    });

    form.addEventListener('reset', () => {
      window.requestAnimationFrame(() => resetFormValidation(form));
    });
  }

  function getControllerUrl() {
    const normalized = window.location.pathname.replaceAll('\\', '/');
    const marker = '/view/backoffice/src/pages/backoffice/';
    const markerIndex = normalized.indexOf(marker);

    if (markerIndex !== -1) {
      const appBase = normalized.slice(0, markerIndex);
      return `${appBase}/controller/devoirs.php`;
    }

    return '/controller/devoirs.php';
  }

  function escapeHtml(value) {
    return String(value || '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#39;');
  }

  function showToast(message, variant) {
    const color = variant === 'danger' ? 'danger' : 'success';
    const icon = color === 'success' ? '✓' : '✕';
    const toastId = `toast-${Date.now()}`;

    const toastMarkup = `
      <div id="${toastId}" class="toast align-items-center text-bg-${color} border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 320px; font-size: 1rem; font-weight: 500;">
        <div class="d-flex">
          <div class="toast-body d-flex align-items-center gap-2">
            <span style="font-size: 1.4rem; font-weight: bold;">${icon}</span>
            <span>${escapeHtml(message)}</span>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    `;

    elements.toastContainer.insertAdjacentHTML('beforeend', toastMarkup);
    const toastElement = document.getElementById(toastId);
    const toast = bootstrap.Toast.getOrCreateInstance(toastElement, { delay: 5000 });

    toast.show();
    toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
  }

  function parseControllerResponse(raw) {
    const clean = (raw || '').trim();

    try {
      const json = JSON.parse(clean);
      return {
        success: Boolean(json.success),
        message: json.message || (json.success ? 'Operation succeeded.' : 'Operation failed.'),
      };
    } catch (error) {
      // non-JSON response
    }

    if (clean.includes('|')) {
      const [status, message] = clean.split('|');
      const statusNormalized = (status || '').toLowerCase();
      return {
        success: statusNormalized.includes('succ') || statusNormalized.includes('success'),
        message: message || clean,
      };
    }

    if (clean.toLowerCase() === 'succès' || clean.toLowerCase() === 'success') {
      return { success: true, message: 'Operation succeeded.' };
    }

    return {
      success: false,
      message: clean || 'Unknown server response.',
    };
  }

  function setButtonLoading(button, isLoading) {
    if (!button) {
      return;
    }

    if (!button.dataset.defaultText) {
      button.dataset.defaultText = button.innerHTML;
    }

    button.disabled = isLoading;
    if (isLoading) {
      button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
    } else {
      button.innerHTML = button.dataset.defaultText;
    }
  }

  function setSidebarActiveState() {
    const links = document.querySelectorAll('.navbar-nav .nav-link');

    links.forEach((link) => {
      const href = link.getAttribute('href') || '';
      const isSubmitLink = href.includes('pages/backoffice/submit_back.html');
      link.classList.toggle('active', isSubmitLink);
    });
  }

  async function fetchJson(url) {
    const response = await fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    const raw = await response.text();
    let payload = null;

    try {
      payload = JSON.parse(raw);
    } catch (error) {
      const jsonStart = raw.indexOf('{');
      const jsonEnd = raw.lastIndexOf('}');

      if (jsonStart !== -1 && jsonEnd !== -1 && jsonEnd > jsonStart) {
        try {
          payload = JSON.parse(raw.slice(jsonStart, jsonEnd + 1));
        } catch (innerError) {
          payload = null;
        }
      }
    }

    if (!payload) {
      throw new Error('Invalid JSON response from server.');
    }

    if (!response.ok || payload.success === false) {
      throw new Error(payload.message || 'Request failed.');
    }

    return payload;
  }

  function renderDevoirSelectOptions() {
    const selectedValue = String(
      elements.idDevoirSelect.value || elements.idDevoirSelect.dataset.selectedId || '',
    ).trim();

    if (!state.devoirs.length) {
      elements.idDevoirSelect.innerHTML = '<option value="">No devoir available</option>';
      if (elements.idDevoirSelect.dataset.touched === '1') {
        validateField(elements.idDevoirSelect, { force: true });
      }
      return;
    }

    const options = state.devoirs
      .map((devoir) => `<option value="${devoir.id_devoir}">${escapeHtml(devoir.titre)}</option>`)
      .join('');

    elements.idDevoirSelect.innerHTML = `<option value="">Select devoir</option>${options}`;

    if (selectedValue !== '') {
      const hasMatchingOption = state.devoirs.some(
        (devoir) => String(devoir.id_devoir) === selectedValue,
      );

      if (hasMatchingOption) {
        elements.idDevoirSelect.value = selectedValue;
        elements.idDevoirSelect.dataset.selectedId = '';
      }
    }

    if (elements.idDevoirSelect.dataset.touched === '1') {
      validateField(elements.idDevoirSelect, { force: true });
    }
  }

  async function refreshCountsAndDevoirs() {
    const devoirPayload = await fetchJson(`${state.controllerUrl}?action=listdevoirs`);
    const correctionPayload = await fetchJson(`${state.controllerUrl}?action=listcorrections`);

    state.devoirs = Array.isArray(devoirPayload.data) ? devoirPayload.data : [];

    elements.statTotalDevoirs.textContent = String(state.devoirs.length);
    elements.statTotalCorrections.textContent = String(
      Array.isArray(correctionPayload.data) ? correctionPayload.data.length : 0,
    );

    renderDevoirSelectOptions();
  }

  async function submitForm(formElement, action, buttonElement) {
    const { allValid, firstInvalid } = validateForm(formElement);

    if (!allValid) {
      showToast('Veuillez corriger les champs invalides avant de continuer.', 'danger');

      if (firstInvalid && typeof firstInvalid.focus === 'function') {
        firstInvalid.focus();
      }

      return;
    }

    setButtonLoading(buttonElement, true);

    try {
      const formData = new FormData(formElement);
      const response = await fetch(`${state.controllerUrl}?action=${action}`, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      const raw = await response.text();
      const parsed = parseControllerResponse(raw);

      if (!response.ok || !parsed.success) {
        throw new Error(parsed.message || 'Submission failed.');
      }

      showToast(parsed.message || 'Saved successfully.', 'success');
      formElement.reset();
      resetFormValidation(formElement);
      setTodayAsDefaultDate();
      await refreshCountsAndDevoirs();
    } catch (error) {
      showToast(error.message || 'Submission failed.', 'danger');
    } finally {
      setButtonLoading(buttonElement, false);
    }
  }

  function resolveFormAction(formElement, fallbackAction) {
    if (!formElement) {
      return fallbackAction;
    }

    const actionAttr = String(formElement.getAttribute('action') || '').trim();
    if (!actionAttr) {
      return fallbackAction;
    }

    try {
      const parsed = new URL(actionAttr, window.location.href);
      const actionParam = String(parsed.searchParams.get('action') || '').trim();
      if (actionParam) {
        return actionParam.toLowerCase();
      }
    } catch (error) {
      // Fallback regex parsing for unusual or malformed action URLs.
    }

    const match = actionAttr.match(/[?&]action=([^&]+)/i);
    if (match && match[1]) {
      try {
        return decodeURIComponent(match[1]).trim().toLowerCase() || fallbackAction;
      } catch (error) {
        return match[1].trim().toLowerCase() || fallbackAction;
      }
    }

    return fallbackAction;
  }

  function setTodayAsDefaultDate() {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const dateValue = `${yyyy}-${mm}-${dd}`;

    if (elements.dateSoumission && !elements.dateSoumission.value) {
      elements.dateSoumission.value = dateValue;
    }

    if (elements.dateCorrection && !elements.dateCorrection.value) {
      elements.dateCorrection.value = dateValue;
    }
  }

  function bindEvents() {
    bindRealtimeValidation(elements.devoirForm);
    bindRealtimeValidation(elements.correctionForm);

    elements.devoirForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const action = resolveFormAction(elements.devoirForm, 'submit');
      await submitForm(elements.devoirForm, action, elements.submitDevoirBtn);
    });

    elements.correctionForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const action = resolveFormAction(elements.correctionForm, 'correct');
      await submitForm(elements.correctionForm, action, elements.submitCorrectionBtn);
    });
  }

  async function init() {
    state.controllerUrl = getControllerUrl();
    setSidebarActiveState();
    bindEvents();
    setTodayAsDefaultDate();

    try {
      await refreshCountsAndDevoirs();
    } catch (error) {
      showToast(error.message || 'Unable to load devoir data.', 'danger');
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
