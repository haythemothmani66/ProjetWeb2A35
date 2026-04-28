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
  function isEmail(s) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(s);
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
      var ok = true;
      if (!val('signupNom')) { err('err-nom', 'Nom obligatoire.'); ok = false; }
      if (!val('signupPrenom')) { err('err-prenom', 'Prénom obligatoire.'); ok = false; }
      if (!val('signupEmail')) { err('err-email', 'Email obligatoire.'); ok = false; }
      else if (!isEmail(val('signupEmail'))) { err('err-email', 'Email invalide.'); ok = false; }
      if (val('signupPassword').length < 6) { err('err-password', 'Minimum 6 caractères.'); ok = false; }
      if (val('signupConfirm') !== val('signupPassword')) { err('err-confirm', 'Les mots de passe ne correspondent pas.'); ok = false; }
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
      var ok = true;
      if (val('resetPassword').length < 6) { err('err-password', 'Minimum 6 caractères.'); ok = false; }
      if (val('resetConfirm') !== val('resetPassword')) { err('err-confirm', 'Les mots de passe ne correspondent pas.'); ok = false; }
      if (!ok) e.preventDefault();
    });
  }

  /* ---------- Profile edit form ---------- */
  var formProfil = document.getElementById('formProfil');
  if (formProfil) {
    formProfil.addEventListener('submit', function (e) {
      clear();
      var ok = true;
      if (!val('profilNom')) { err('err-nom', 'Nom obligatoire.'); ok = false; }
      if (!val('profilPrenom')) { err('err-prenom', 'Prénom obligatoire.'); ok = false; }
      if (!ok) e.preventDefault();
    });
  }

  /* ---------- Change password form ---------- */
  var formChangePwd = document.getElementById('formChangePwd');
  if (formChangePwd) {
    formChangePwd.addEventListener('submit', function (e) {
      clear();
      var ok = true;
      if (!val('currentPwd')) { err('err-current', 'Mot de passe actuel obligatoire.'); ok = false; }
      if (val('newPwd').length < 6) { err('err-new', 'Minimum 6 caractères.'); ok = false; }
      if (val('confirmPwd') !== val('newPwd')) { err('err-confirm', 'Les mots de passe ne correspondent pas.'); ok = false; }
      if (!ok) e.preventDefault();
    });
  }

  /* ---------- Admin Add User form ---------- */
  var formAddUser = document.getElementById('formAddUser');
  if (formAddUser) {
    formAddUser.addEventListener('submit', function (e) {
      clear();
      var ok = true;
      if (!val('addNom')) { err('err-nom', 'Nom obligatoire.'); ok = false; }
      if (!val('addPrenom')) { err('err-prenom', 'Prénom obligatoire.'); ok = false; }
      if (!val('addEmail')) { err('err-email', 'Email obligatoire.'); ok = false; }
      else if (!isEmail(val('addEmail'))) { err('err-email', 'Email invalide.'); ok = false; }
      if (val('addPassword').length < 6) { err('err-password', 'Minimum 6 caractères.'); ok = false; }
      if (!ok) e.preventDefault();
    });
  }

  /* ---------- Admin Edit User form ---------- */
  var formEditUser = document.getElementById('formEditUser');
  if (formEditUser) {
    formEditUser.addEventListener('submit', function (e) {
      clear();
      var ok = true;
      if (!val('editNom')) { err('err-nom', 'Nom obligatoire.'); ok = false; }
      if (!val('editPrenom')) { err('err-prenom', 'Prénom obligatoire.'); ok = false; }
      if (!val('editEmail')) { err('err-email', 'Email obligatoire.'); ok = false; }
      else if (!isEmail(val('editEmail'))) { err('err-email', 'Email invalide.'); ok = false; }
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
