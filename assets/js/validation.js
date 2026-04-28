/**
 * EduMatch — Client-side form validation (no HTML5 validation)
 * All forms use JS validation only, per ESPRIT rules.
 */
(function () {
  'use strict';

  /* ---------- helpers ---------- */
  function val(id) {
    var el = document.getElementById(id);
    return el ? el.value.trim() : '';
  }
  function err(id, msg) {
    var el = document.getElementById(id);
    if (el) el.textContent = msg;
  }
  function clear() {
    var divs = document.querySelectorAll('[id^="err-"]');
    for (var i = 0; i < divs.length; i++) divs[i].textContent = '';
  }

  /* --- Regex patterns --- */
  var RE_NAME   = /^[A-Za-zÀ-ÿ\s\-']+$/;          /* lettres, espaces, tirets, apostrophes */
  var RE_EMAIL  = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
  var RE_TEL_TN = /^[0-9]{8}$/;                     /* exactement 8 chiffres (Tunisie) */
  var RE_PWD_UP = /[A-Z]/;                           /* au moins 1 majuscule */
  var RE_PWD_LO = /[a-z]/;                           /* au moins 1 minuscule */
  var RE_PWD_DG = /[0-9]/;                           /* au moins 1 chiffre */
  var RE_PWD_SP = /[^A-Za-z0-9]/;                    /* au moins 1 caractère spécial */

  function isEmail(s) { return RE_EMAIL.test(s); }

  /* --- Validation nom / prenom --- */
  function checkName(value, fieldLabel) {
    if (!value) return fieldLabel + ' est obligatoire.';
    if (value.length < 2) return fieldLabel + ' doit contenir au moins 2 caractères.';
    if (value.length > 30) return fieldLabel + ' ne doit pas dépasser 30 caractères.';
    if (!RE_NAME.test(value)) return fieldLabel + ' ne doit contenir que des lettres.';
    return '';
  }

  /* --- Validation mot de passe sécurisé --- */
  function checkPassword(value) {
    if (!value) return 'Le mot de passe est obligatoire.';
    if (value.length < 8) return 'Minimum 8 caractères.';
    if (value.length > 50) return 'Maximum 50 caractères.';
    if (!RE_PWD_UP.test(value)) return 'Doit contenir au moins une majuscule (A-Z).';
    if (!RE_PWD_LO.test(value)) return 'Doit contenir au moins une minuscule (a-z).';
    if (!RE_PWD_DG.test(value)) return 'Doit contenir au moins un chiffre (0-9).';
    if (!RE_PWD_SP.test(value)) return 'Doit contenir au moins un caractère spécial (!@#...).';
    return '';
  }

  /* --- Validation téléphone tunisien (optionnel, mais si rempli => 8 chiffres) --- */
  function checkTel(value) {
    if (!value) return '';  /* optionnel */
    if (!RE_TEL_TN.test(value)) return 'Le numéro doit contenir exactement 8 chiffres.';
    return '';
  }

  /* --- Validation email --- */
  function checkEmail(value) {
    if (!value) return "L'email est obligatoire.";
    if (!isEmail(value)) return "Format d'email invalide.";
    return '';
  }

  /* ---------- Login form ---------- */
  var formLogin = document.getElementById('formLogin');
  if (formLogin) {
    formLogin.addEventListener('submit', function (e) {
      clear();
      var ok = true;
      if (!val('loginEmail')) { err('err-email', 'Email obligatoire.'); ok = false; }
      else if (!isEmail(val('loginEmail'))) { err('err-email', 'Email invalide.'); ok = false; }
      if (!val('loginPassword')) { err('err-password', 'Mot de passe obligatoire.'); ok = false; }
      if (!ok) e.preventDefault();
    });
  }

  /* ---------- Signup form ---------- */
  var formSignup = document.getElementById('formSignup');
  if (formSignup) {
    formSignup.addEventListener('submit', function (e) {
      clear();
      var ok = true, m;

      /* Nom */
      m = checkName(val('signupNom'), 'Le nom');
      if (m) { err('err-nom', m); ok = false; }

      /* Prénom */
      m = checkName(val('signupPrenom'), 'Le prénom');
      if (m) { err('err-prenom', m); ok = false; }

      /* Email */
      m = checkEmail(val('signupEmail'));
      if (m) { err('err-email', m); ok = false; }

      /* Téléphone (optionnel) */
      m = checkTel(val('signupTel'));
      if (m) { err('err-tel', m); ok = false; }

      /* Mot de passe */
      m = checkPassword(val('signupPassword'));
      if (m) { err('err-password', m); ok = false; }

      /* Confirmation */
      if (!val('signupConfirm')) { err('err-confirm', 'La confirmation est obligatoire.'); ok = false; }
      else if (val('signupConfirm') !== val('signupPassword')) { err('err-confirm', 'Les mots de passe ne correspondent pas.'); ok = false; }

      if (!ok) e.preventDefault();
    });
  }

  /* ---------- Forgot password form ---------- */
  var formForgot = document.getElementById('formForgot');
  if (formForgot) {
    formForgot.addEventListener('submit', function (e) {
      clear();
      var ok = true;
      if (!val('forgotEmail')) { err('err-email', 'Email obligatoire.'); ok = false; }
      else if (!isEmail(val('forgotEmail'))) { err('err-email', 'Email invalide.'); ok = false; }
      if (!ok) e.preventDefault();
    });
  }

  /* ---------- OTP form ---------- */
  var formOtp = document.getElementById('formOtp');
  if (formOtp) {
    formOtp.addEventListener('submit', function (e) {
      clear();
      var ok = true;
      if (!val('otpCode') || val('otpCode').length !== 6 || !/^\d{6}$/.test(val('otpCode'))) {
        err('err-otp', 'Code OTP à 6 chiffres requis.');
        ok = false;
      }
      if (!ok) e.preventDefault();
    });

    /* Auto-focus next OTP digit */
    var digits = formOtp.querySelectorAll('.otp-digit');
    if (digits.length) {
      for (var d = 0; d < digits.length; d++) {
        digits[d].addEventListener('input', function () {
          if (this.value.length === 1 && this.nextElementSibling && this.nextElementSibling.classList.contains('otp-digit')) {
            this.nextElementSibling.focus();
          }
          /* combine digits into hidden field */
          var code = '';
          for (var k = 0; k < digits.length; k++) code += digits[k].value;
          var hidden = document.getElementById('otpCode');
          if (hidden) hidden.value = code;
        });
        digits[d].addEventListener('keydown', function (ev) {
          if (ev.key === 'Backspace' && !this.value && this.previousElementSibling && this.previousElementSibling.classList.contains('otp-digit')) {
            this.previousElementSibling.focus();
          }
        });
      }
    }
  }

  /* ---------- Reset password form ---------- */
  var formReset = document.getElementById('formReset');
  if (formReset) {
    formReset.addEventListener('submit', function (e) {
      clear();
      var ok = true, m;
      m = checkPassword(val('resetPassword'));
      if (m) { err('err-password', m); ok = false; }
      if (!val('resetConfirm')) { err('err-confirm', 'La confirmation est obligatoire.'); ok = false; }
      else if (val('resetConfirm') !== val('resetPassword')) { err('err-confirm', 'Les mots de passe ne correspondent pas.'); ok = false; }
      if (!ok) e.preventDefault();
    });
  }

  /* ---------- Profile edit form ---------- */
  var formProfil = document.getElementById('formProfil');
  if (formProfil) {
    formProfil.addEventListener('submit', function (e) {
      clear();
      var ok = true, m;
      m = checkName(val('profilNom'), 'Le nom');
      if (m) { err('err-nom', m); ok = false; }
      m = checkName(val('profilPrenom'), 'Le prénom');
      if (m) { err('err-prenom', m); ok = false; }
      m = checkTel(val('profilTel'));
      if (m) { err('err-tel', m); ok = false; }
      if (!ok) e.preventDefault();
    });
  }

  /* ---------- Change password form ---------- */
  var formChangePwd = document.getElementById('formChangePwd');
  if (formChangePwd) {
    formChangePwd.addEventListener('submit', function (e) {
      clear();
      var ok = true, m;
      if (!val('currentPwd')) { err('err-current', 'Mot de passe actuel obligatoire.'); ok = false; }
      m = checkPassword(val('newPwd'));
      if (m) { err('err-new', m); ok = false; }
      if (!val('confirmPwd')) { err('err-confirm', 'La confirmation est obligatoire.'); ok = false; }
      else if (val('confirmPwd') !== val('newPwd')) { err('err-confirm', 'Les mots de passe ne correspondent pas.'); ok = false; }
      if (!ok) e.preventDefault();
    });
  }

  /* ---------- Admin Add User form ---------- */
  var formAddUser = document.getElementById('formAddUser');
  if (formAddUser) {
    formAddUser.addEventListener('submit', function (e) {
      clear();
      var ok = true, m;
      m = checkName(val('addNom'), 'Le nom');
      if (m) { err('err-nom', m); ok = false; }
      m = checkName(val('addPrenom'), 'Le prénom');
      if (m) { err('err-prenom', m); ok = false; }
      m = checkEmail(val('addEmail'));
      if (m) { err('err-email', m); ok = false; }
      m = checkTel(val('addTel'));
      if (m) { err('err-tel', m); ok = false; }
      m = checkPassword(val('addPassword'));
      if (m) { err('err-password', m); ok = false; }
      if (!ok) e.preventDefault();
    });
  }

  /* ---------- Admin Edit User form ---------- */
  var formEditUser = document.getElementById('formEditUser');
  if (formEditUser) {
    formEditUser.addEventListener('submit', function (e) {
      clear();
      var ok = true, m;
      m = checkName(val('editNom'), 'Le nom');
      if (m) { err('err-nom', m); ok = false; }
      m = checkName(val('editPrenom'), 'Le prénom');
      if (m) { err('err-prenom', m); ok = false; }
      m = checkEmail(val('editEmail'));
      if (m) { err('err-email', m); ok = false; }
      m = checkTel(val('editTel'));
      if (m) { err('err-tel', m); ok = false; }
      if (!ok) e.preventDefault();
    });
  }

  /* ---------- Delete confirmation ---------- */
  var deleteForms = document.querySelectorAll('.form-delete');
  for (var i = 0; i < deleteForms.length; i++) {
    deleteForms[i].addEventListener('submit', function (e) {
      if (!confirm('Voulez-vous vraiment supprimer cet utilisateur ?')) {
        e.preventDefault();
      }
    });
  }

  /* ---------- Toggle password visibility (eye icon) ---------- */
  var toggleBtns = document.querySelectorAll('.toggle-password');
  for (var t = 0; t < toggleBtns.length; t++) {
    toggleBtns[t].addEventListener('click', function () {
      var targetId = this.getAttribute('data-target');
      var input = document.getElementById(targetId);
      if (!input) return;
      var icon = this.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        /* Switch icon: fa-eye -> fa-eye-slash OR ti-eye -> ti-eye-off */
        if (icon.classList.contains('fa-eye')) {
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        } else if (icon.classList.contains('ti-eye')) {
          icon.classList.remove('ti-eye');
          icon.classList.add('ti-eye-off');
        }
      } else {
        input.type = 'password';
        if (icon.classList.contains('fa-eye-slash')) {
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        } else if (icon.classList.contains('ti-eye-off')) {
          icon.classList.remove('ti-eye-off');
          icon.classList.add('ti-eye');
        }
      }
    });
  }

})();
