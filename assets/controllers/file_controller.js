import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    /** @type {HTMLInputElement} */
    this.element;

    this.element.addEventListener("change", this.checkFileSize);
  }

  checkFileSize() {
    /** @type {HTMLInputElement} */
    const fileInput = this;

    // Reset custom validity
    fileInput.setCustomValidity("");
    fileInput.reportValidity();

    const maxSize = parseInt(fileInput.dataset.maxSize);

    if (maxSize === undefined) return;

    const file = fileInput.files[0];

    const fileSize = file.size;

    if (fileSize > maxSize) {
      fileInput.setCustomValidity("This file is too large.");
      fileInput.reportValidity();
    }
  }
}
