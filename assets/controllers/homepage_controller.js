import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["grid", "fetchButton"];

  connect() {
    /** @type {HTMLButtonElement} */
    const fetchButton = this.fetchButtonTarget;

    fetchButton.removeAttribute("hidden");
  }
}
