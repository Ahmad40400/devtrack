// ============================================
// Main JavaScript
// ============================================

document.addEventListener("DOMContentLoaded", function () {
  // Auto dismiss alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert:not(.alert-permanent)");
  alerts.forEach((alert) => {
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });

  // Confirm delete
  const deleteButtons = document.querySelectorAll(".delete-confirm");
  deleteButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      if (!confirm("Are you sure you want to delete this item?")) {
        e.preventDefault();
      }
    });
  });

  // Tooltips
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]'),
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Form validation
  const forms = document.querySelectorAll(".needs-validation");
  forms.forEach((form) => {
    form.addEventListener("submit", function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add("was-validated");
    });
  });

  // Auto close dropdowns on click outside
  document.addEventListener("click", function (event) {
    const dropdowns = document.querySelectorAll(".dropdown-menu");
    dropdowns.forEach((dropdown) => {
      if (
        !dropdown.contains(event.target) &&
        !dropdown.previousElementSibling?.contains(event.target)
      ) {
        dropdown.classList.remove("show");
      }
    });
  });
});

// Toast notification function
function showToast(message, type = "success") {
  const toastContainer = document.querySelector(".toast-container");
  if (!toastContainer) {
    const container = document.createElement("div");
    container.className = "toast-container";
    document.body.appendChild(container);
  }

  const toast = document.createElement("div");
  toast.className = `toast align-items-center text-white bg-${type} border-0`;
  toast.role = "alert";
  toast.ariaLive = "assertive";
  toast.ariaAtomic = "true";

  toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

  document.querySelector(".toast-container").appendChild(toast);
  const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
  bsToast.show();

  toast.addEventListener("hidden.bs.toast", function () {
    toast.remove();
  });
}

// Loading spinner
function showLoading() {
  const overlay = document.createElement("div");
  overlay.className = "spinner-overlay";
  overlay.id = "loadingOverlay";
  overlay.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading...</p>
        </div>
    `;
  document.body.appendChild(overlay);
}

function hideLoading() {
  const overlay = document.getElementById("loadingOverlay");
  if (overlay) {
    overlay.remove();
  }
}

// Copy to clipboard
function copyToClipboard(text) {
  navigator.clipboard
    .writeText(text)
    .then(() => {
      showToast("Copied to clipboard!");
    })
    .catch(() => {
      // Fallback
      const textarea = document.createElement("textarea");
      textarea.value = text;
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand("copy");
      textarea.remove();
      showToast("Copied to clipboard!");
    });
}

// Handle AJAX form submissions
function submitFormAjax(form, url, method = "POST") {
  const formData = new FormData(form);
  showLoading();

  fetch(url, {
    method: method,
    body: formData,
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      hideLoading();
      if (data.success) {
        showToast(data.message || "Success!");
        if (data.redirect) {
          setTimeout(() => {
            window.location.href = data.redirect;
          }, 1500);
        }
      } else {
        showToast(data.message || "Error occurred!", "danger");
      }
    })
    .catch((error) => {
      hideLoading();
      showToast("An error occurred. Please try again.", "danger");
      console.error("Error:", error);
    });
}
