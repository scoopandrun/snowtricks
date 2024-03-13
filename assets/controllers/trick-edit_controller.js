import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    this.index = this.element.childElementCount;

    const addButton = document.createElement("button");
    addButton.classList.add("btn", "btn-secondary");
    addButton.setAttribute("type", "button");
    addButton.textContent = "Add a picture";
    addButton.addEventListener("click", this.addItem.bind(this));

    /** @type {HTMLDivElement} */
    const wrapper = this.element;

    for (const fieldset of wrapper.children) {
      this.addRemoveButton(fieldset);
    }

    this.element.appendChild(addButton);
  }

  /**
   * @param {MouseEvent} e
   */
  addItem(e) {
    e.preventDefault();

    /** @type {HTMLFieldSetElement} */
    const item = document
      .createRange()
      .createContextualFragment(
        this.element.dataset.prototype.replaceAll("__name__", this.index)
      ).firstElementChild;

    this.addRemoveButton(item);

    this.index++;

    e.currentTarget.before(item);
  }

  /**
   * @param {HTMLFieldSetElement} item
   */
  addRemoveButton(item) {
    const removeButton = document.createElement("button");
    removeButton.classList.add("btn", "btn-secondary");
    removeButton.setAttribute("type", "button");
    removeButton.textContent = "Remove";
    removeButton.addEventListener("click", () => item.remove());

    item.appendChild(removeButton);
  }
}
