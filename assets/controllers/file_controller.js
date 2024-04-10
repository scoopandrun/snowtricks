import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    this.element.addEventListener("dropzone:connect", this._onConnect);
    this.element.addEventListener("dropzone:change", this._onChange);
    this.element.addEventListener("dropzone:clear", this._onClear);
  }

  disconnect() {
    // You should always remove listeners when the controller is disconnected to avoid side-effects
    this.element.removeEventListener("dropzone:connect", this._onConnect);
    this.element.removeEventListener("dropzone:change", this._onChange);
    this.element.removeEventListener("dropzone:clear", this._onClear);
  }

  _onConnect(event) {
    // The dropzone was just created
  }

  _onChange(event) {
    // The dropzone just changed

    // Check file size and set custom validity

    /** @type {HTMLDivElement} */
    const wrapper = this;

    /** @type {HTMLInputElement} */
    const fileInput = wrapper.querySelector("input[type=file]");

    // Reset custom validity
    fileInput.setCustomValidity("");
    fileInput.reportValidity();
    this.removeAttribute("style");

    const maxSize = parseInt(fileInput.dataset.maxSize);

    if (maxSize === undefined) return;

    /** @type {File} */
    const file = event.detail;

    if (!file) return;

    const fileSize = file.size;

    if (fileSize > maxSize) {
      const messageSpan = document.createElement("span");
      messageSpan.style.color = "var(--bs-danger)";
      messageSpan.textContent = "   /!\\ This file is too large";

      this.querySelector(".dropzone-preview-filename").appendChild(messageSpan);

      // Prevent Symfony UX from hiding the input to allow for custom validity tooltip
      fileInput.style.display = "block";

      fileInput.setCustomValidity("This file is too large.");
      fileInput.reportValidity();
      wrapper.style.borderColor = "var(--bs-danger)";
    }
  }

  _onClear(event) {
    // The dropzone has just been cleared

    // Reset custom validity
    const fileInput = this.querySelector("input[type=file]");
    fileInput.setCustomValidity("");
    fileInput.reportValidity();
    this.removeAttribute("style");
  }
}
