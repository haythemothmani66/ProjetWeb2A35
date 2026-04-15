// ========================================
// ProjetWeb2A35 - JavaScript
// ========================================

document.addEventListener("DOMContentLoaded", function () {
  // Confirmation for delete buttons
  const deleteButtons = document.querySelectorAll('a[onclick*="confirm"]');
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      if (!confirm("Êtes-vous sûr de vouloir supprimer cet élément ?")) {
        e.preventDefault();
      }
    });
  });

  // Simple form validation
  const forms = document.querySelectorAll(".form");
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const requiredInputs = form.querySelectorAll("[required]");
      let isValid = true;

      requiredInputs.forEach((input) => {
        if (
          input.type === "text" ||
          input.type === "textarea" ||
          input.type === "date" ||
          input.tagName === "SELECT"
        ) {
          if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = "#e74c3c";
          } else {
            input.style.borderColor = "#bdc3c7";
          }
        }
      });

      if (!isValid) {
        e.preventDefault();
        alert("Veuillez remplir tous les champs requis.");
      }
    });
  });

  // Clear input error border on focus
  const inputs = document.querySelectorAll(
    ".form input, .form textarea, .form select",
  );
  inputs.forEach((input) => {
    input.addEventListener("focus", function () {
      this.style.borderColor = "#bdc3c7";
    });
  });
});
