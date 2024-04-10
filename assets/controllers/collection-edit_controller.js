import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    /** @type {HTMLDivElement} */
    const wrapper = this.element;

    this.index = this.element.childElementCount;

    this.addAddButton(wrapper);

    for (const child of wrapper.children) {
      if (child.tagName.toLowerCase() === "fieldset") {
        this.addRemoveButton(child);
        this.handleRadioButton(child);
      }
    }
  }

  /**
   * @param {HTMLFieldSetElement} collection
   */
  addAddButton(collection) {
    const addButton = document.createElement("button");
    addButton.classList.add("btn", "btn-secondary");
    addButton.setAttribute("type", "button");
    addButton.textContent = collection.dataset.addButtonText || "Add";
    addButton.addEventListener("click", this.addItem.bind(this));

    collection.appendChild(addButton);
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
    this.handleRadioButton(item);

    this.index++;

    e.currentTarget.before(item);
  }

  /**
   * @param {HTMLFieldSetElement} item
   */
  addRemoveButton(item) {
    const removeButton = document.createElement("button");
    removeButton.classList.add("btn", "btn-secondary", "btn-sm");
    removeButton.setAttribute("type", "button");
    removeButton.textContent =
      this.element.dataset.removeButtonText || "Remove";
    removeButton.addEventListener("click", () => item.remove());

    item.appendChild(removeButton);
  }

  /**
   *
   * @param {HTMLFieldSetElement} fieldset
   */
  handleRadioButton(fieldset) {
    /** @type {HTMLInputElement} */
    const radioButton = fieldset.querySelector("input[type=radio]");

    if (!radioButton) return;

    /** @type {HTMLDivElement} */
    const collection = this.element;

    radioButton.addEventListener("change", (e) => {
      const collectionButtons =
        collection.querySelectorAll("input[type=radio]");

      collectionButtons.forEach((/** @type {HTMLInputElement} */ button) => {
        if (button !== e.target) {
          button.checked = false;
        }
      });
    });
  }
}
