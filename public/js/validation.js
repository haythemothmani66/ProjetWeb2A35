document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    var forms = document.querySelectorAll('.needs-validation');

    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            var isValid = true;

            // Validation personnalisée pour chaque champ
            if (form.id === 'categorieForm') {
                isValid = validateCategorieForm(form);
            } else if (form.id === 'evenementForm') {
                isValid = validateEvenementForm(form);
            } else if (form.id === 'registerForm') {
                isValid = validateRegisterForm(form);
            }

            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
            }
        }, false);
    });

    function validateCategorieForm(form) {
        var nom = form.querySelector('#nom_categorie');
        var couleur = form.querySelector('#couleur');
        var isValid = true;

        if (nom.value.trim().length < 3) {
            nom.classList.add('is-invalid');
            document.getElementById('nom_categorie_error').textContent = 'Le nom doit contenir au moins 3 caractères.';
            isValid = false;
        } else {
            nom.classList.remove('is-invalid');
        }

        if (couleur && !/^#[a-fA-F0-9]{6}$/.test(couleur.value)) {
            couleur.classList.add('is-invalid');
            isValid = false;
        } else if (couleur) {
            couleur.classList.remove('is-invalid');
        }
        return isValid;
    }

    function validateEvenementForm(form) {
        var titre = form.querySelector('#titre');
        var dateDebut = form.querySelector('#date_debut');
        var dateFin = form.querySelector('#date_fin');
        var heureDebut = form.querySelector('#heure_debut');
        var heureFin = form.querySelector('#heure_fin');
        var capacite = form.querySelector('#capacite_max');
        var type = form.querySelector('#type_evenement');
        var lieu = form.querySelector('#lieu');
        var lien = form.querySelector('#lien_acces');
        var isValid = true;

        if (titre.value.trim().length < 5) {
            titre.classList.add('is-invalid');
            document.getElementById('titre_error').textContent = 'Le titre doit contenir au moins 5 caractères.';
            isValid = false;
        } else {
            titre.classList.remove('is-invalid');
        }

        if (dateDebut && dateDebut.value) {
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            var startDate = new Date(dateDebut.value + 'T00:00:00');
            if (startDate < today) {
                dateDebut.classList.add('is-invalid');
                var dateDebutError = document.getElementById('date_debut_error');
                if (dateDebutError) {
                    dateDebutError.textContent = "La date de début doit être supérieure ou égale à aujourd'hui.";
                }
                isValid = false;
            } else {
                dateDebut.classList.remove('is-invalid');
            }
        }

        if (dateDebut.value && dateFin.value) {
            var d1 = new Date(dateDebut.value);
            var d2 = new Date(dateFin.value);
            if (d2 < d1) {
                dateFin.classList.add('is-invalid');
                document.getElementById('date_fin_error').textContent = 'La date de fin ne peut pas être antérieure à la date de début.';
                isValid = false;
            } else {
                dateFin.classList.remove('is-invalid');
            }
        }

        if (heureDebut && heureFin && heureDebut.value && heureFin.value) {
            if (heureFin.value <= heureDebut.value) {
                heureFin.classList.add('is-invalid');
                var heureError = document.getElementById('heure_fin_error');
                if (heureError) {
                    heureError.textContent = "L'heure de fin doit etre superieure a l'heure de debut.";
                }
                isValid = false;
            } else {
                heureFin.classList.remove('is-invalid');
            }
        }

        if (!capacite.value || parseInt(capacite.value, 10) <= 0) {
            capacite.classList.add('is-invalid');
            document.getElementById('capacite_max_error').textContent = 'La capacité doit être supérieure à 0.';
            isValid = false;
        } else {
            capacite.classList.remove('is-invalid');
        }

        if (type && type.value === 'présentiel' && lieu && lieu.value.trim().length === 0) {
            lieu.classList.add('is-invalid');
            isValid = false;
        } else if (lieu) {
            lieu.classList.remove('is-invalid');
        }

        if (type && type.value === 'en ligne' && lien && lien.value.trim().length === 0) {
            lien.classList.add('is-invalid');
            isValid = false;
        } else if (lien) {
            lien.classList.remove('is-invalid');
        }

        return isValid;
    }

    function validateRegisterForm(form) {
        var nom = form.querySelector('#nom_participant');
        var email = form.querySelector('#email');
        var tel = form.querySelector('#telephone');
        var mode = form.querySelector('#mode_participation');
        var isValid = true;

        if (!nom.value || nom.value.trim().length < 3) {
            nom.classList.add('is-invalid');
            document.getElementById('nom_participant_error').textContent = 'Le nom doit contenir au moins 3 caractères.';
            isValid = false;
        } else {
            nom.classList.remove('is-invalid');
        }

        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value)) {
            email.classList.add('is-invalid');
            document.getElementById('email_error').textContent = 'Veuillez saisir une adresse email valide.';
            isValid = false;
        } else {
            email.classList.remove('is-invalid');
        }

        var telRegex = /^[0-9+\s]{8,20}$/;
        if (!telRegex.test(tel.value.replace(/\s/g, ''))) {
            tel.classList.add('is-invalid');
            document.getElementById('telephone_error').textContent = 'Le numéro de téléphone doit contenir au moins 8 chiffres.';
            isValid = false;
        } else {
            tel.classList.remove('is-invalid');
        }

        if (!mode.value) {
            mode.classList.add('is-invalid');
            document.getElementById('mode_participation_error').textContent = 'Veuillez choisir un mode de participation.';
            isValid = false;
        } else {
            mode.classList.remove('is-invalid');
        }

        return isValid;
    }
});
