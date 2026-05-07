const UserModel = require('../models/User');

const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const PHONE_REGEX = /^\d+$/;

function hasOwn(payload, key) {
  return Object.prototype.hasOwnProperty.call(payload, key);
}

function normalizeString(value) {
  return typeof value === 'string' ? value.trim() : '';
}

function validateCreateUserPayload(payload = {}) {
  const cleaned = {
    email: normalizeString(payload.email).toLowerCase(),
    password: String(payload.password || ''),
    role: normalizeString(payload.role).toLowerCase(),
    status: normalizeString(payload.status || 'pending').toLowerCase(),
    nom: normalizeString(payload.nom),
    prenom: normalizeString(payload.prenom),
    telephone: normalizeString(payload.telephone),
    photo: normalizeString(payload.photo),
  };

  const errors = {};

  if (!EMAIL_REGEX.test(cleaned.email)) {
    errors.email = 'Please provide a valid email address.';
  }

  if (cleaned.password.length < 6) {
    errors.password = 'Password must be at least 6 characters long.';
  }

  if (!UserModel.roles.includes(cleaned.role)) {
    errors.role = 'Role must be student, teacher, or admin.';
  }

  if (!UserModel.statuses.includes(cleaned.status)) {
    errors.status = 'Status must be active, pending, or suspended.';
  }

  if (!cleaned.nom) {
    errors.nom = 'Nom is required.';
  }

  if (!cleaned.prenom) {
    errors.prenom = 'Prenom is required.';
  }

  if (!PHONE_REGEX.test(cleaned.telephone)) {
    errors.telephone = 'Telephone must contain only numeric characters.';
  }

  return {
    cleaned,
    errors,
  };
}

function validateUpdateUserPayload(payload = {}) {
  const cleaned = {};
  const errors = {};

  if (hasOwn(payload, 'email')) {
    cleaned.email = normalizeString(payload.email).toLowerCase();

    if (!EMAIL_REGEX.test(cleaned.email)) {
      errors.email = 'Please provide a valid email address.';
    }
  }

  if (hasOwn(payload, 'password')) {
    cleaned.password = String(payload.password || '');

    if (cleaned.password && cleaned.password.length < 6) {
      errors.password = 'Password must be at least 6 characters long.';
    }
  }

  if (hasOwn(payload, 'role')) {
    cleaned.role = normalizeString(payload.role).toLowerCase();

    if (!UserModel.roles.includes(cleaned.role)) {
      errors.role = 'Role must be student, teacher, or admin.';
    }
  }

  if (hasOwn(payload, 'status')) {
    cleaned.status = normalizeString(payload.status).toLowerCase();

    if (!UserModel.statuses.includes(cleaned.status)) {
      errors.status = 'Status must be active, pending, or suspended.';
    }
  }

  if (hasOwn(payload, 'nom')) {
    cleaned.nom = normalizeString(payload.nom);

    if (!cleaned.nom) {
      errors.nom = 'Nom cannot be empty.';
    }
  }

  if (hasOwn(payload, 'prenom')) {
    cleaned.prenom = normalizeString(payload.prenom);

    if (!cleaned.prenom) {
      errors.prenom = 'Prenom cannot be empty.';
    }
  }

  if (hasOwn(payload, 'telephone')) {
    cleaned.telephone = normalizeString(payload.telephone);

    if (!PHONE_REGEX.test(cleaned.telephone)) {
      errors.telephone = 'Telephone must contain only numeric characters.';
    }
  }

  if (hasOwn(payload, 'photo')) {
    cleaned.photo = normalizeString(payload.photo);
  }

  return {
    cleaned,
    errors,
  };
}

module.exports = {
  validateCreateUserPayload,
  validateUpdateUserPayload,
};
