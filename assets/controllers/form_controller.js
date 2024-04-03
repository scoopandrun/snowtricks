import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    /** @type {HTMLFormElement} */
    const form = this.element;

    this.addPostSizeCheckOnSubmit(form);
  }

  /**
   * @param {HTMLFormElement} form
   */
  addPostSizeCheckOnSubmit(form) {
    const postMaxSizeBytes = form.dataset.postMaxSizeBytes;
    const postMaxSizeUnit = form.dataset.postMaxSizeUnit;

    if (!postMaxSizeBytes) return;

    form.addEventListener("submit", (e) => {
      /** @type {NodeListOf<HTMLInputElement>} */
      const fileInputs = form.querySelectorAll("input[type=file]");

      try {
        const totalFileSize = Array.from(fileInputs).reduce(
          (
            /** @type {number} */ prev,
            /** @type {HTMLInputElement} */ current
          ) => {
            return prev + current.files[0].size;
          },
          0
        );

        if (totalFileSize > postMaxSizeBytes) {
          e.preventDefault();

          const postSizeExceededMessage = `The total size of the pictures is too high (max ${postMaxSizeUnit}). Please remove some pictures or choose smaller ones.`;

          this.addFlash(postSizeExceededMessage, "danger");

          document.body.scrollTop = 0; // Safari
          document.documentElement.scrollTop = 0; // Chrome, Firefox, Opera
        }
      } catch (error) {
        console.error(error);
      }
    });
  }

  /**
   * @param {string} message
   * @param {"primary"|"secondary"|"light"|"success"|"warning"|"danger"|"info"} type
   */
  addFlash(message, type) {
    const alertDiv = document.createElement("div");
    alertDiv.classList.add("alert", "alert-dismissible", `alert-${type}`);

    const button = document.createElement("button");
    button.type = "button";
    button.classList.add("btn-close");
    button.dataset.bsDismiss = "alert";

    const messageSpan = document.createElement("span");
    messageSpan.textContent = message;

    alertDiv.appendChild(button);
    alertDiv.appendChild(messageSpan);

    document.getElementById("flashes").appendChild(alertDiv);
  }
}
