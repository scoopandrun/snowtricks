import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  difficulties = ["Easy", "Intermediate", "Advanced", "Expert", "Pro"];

  update() {
    /** @type {HTMLInputElement} */
    const slider = this.element;

    /** @type {HTMLSpanElement} */
    const nameSpan = slider.nextElementSibling;

    const difficultyName = this.difficulties[slider.value - 1];

    nameSpan.textContent = difficultyName;

    slider.style.setProperty(
      "--difficulty",
      `var(--difficulty-${difficultyName.toLowerCase()})`
    );
  }
}
