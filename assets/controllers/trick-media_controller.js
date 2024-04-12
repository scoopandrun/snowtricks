import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["toggleCollection"];

  /**
   * @param {MouseEvent} e
   */
  toggleCollection(e) {
    /** @type {HTMLButtonElement} */
    const toggleButton = this.toggleCollectionTarget;

    const collection = document.getElementById("trick-media");

    collection.classList.toggle("d-none");

    const collectionIsHidden = collection.classList.contains("d-none");

    toggleButton.textContent = collectionIsHidden ? "Show media" : "Hide media";
  }
}
