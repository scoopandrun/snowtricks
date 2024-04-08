import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    /**
     * @type {HTMLButtonElement}
     */
    const button = this.element;

    const confirmationMessage =
      button.dataset.confirmation ||
      "Are you sure that you want to delete this?";

    button.addEventListener("click", (e) => {
      const deleteConfirmed = confirm(confirmationMessage);

      if (!deleteConfirmed) {
        e.preventDefault();
      }
    });
  }
}
