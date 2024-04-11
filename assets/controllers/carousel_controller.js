import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["media", "indicator"];

  initialize() {
    this.index = 0;
  }

  connect() {
    this.mediaTargets.forEach((element, index) => {
      element.addEventListener("click", (e) => {
        this.index = index;
        this.show();
      });
    });

    this.overlay.addEventListener("keydown", this._keyboardHandler.bind(this));

    this._addIndicators();
  }

  disconnect() {
    this.overlay.removeEventListener("keydown", this._keyboardHandler);
  }

  get overlay() {
    /** @type {HTMLDivElement} */
    const overlay = document.getElementById("carousel");

    return overlay;
  }

  /**
   * @param {MouseEvent} e
   */
  close(e) {
    this.overlay.classList.remove("carousel--visible");
    this.overlay.classList.add("carousel--hidden");

    /** @type {HTMLDivElement} */
    const captionDiv = this.overlay.querySelector(".carousel__caption");
    captionDiv.textContent = "";

    /** @type {HTMLDivElement} */
    const contentDiv = this.overlay.querySelector(".carousel__content");
    contentDiv.innerHTML = null;
  }

  show() {
    /** @type {HTMLButtonElement} */
    const button = this.mediaTargets[this.index];

    // Set content
    /** @type {"picture"|"video"} */
    const mediaType = button.dataset.type;
    const src =
      mediaType === "picture"
        ? button.firstElementChild.src
        : button.dataset.iframe;
    const content = this._makeContent(mediaType, src);

    /** @type {HTMLDivElement} */
    const contentDiv = this.overlay.querySelector(".carousel__content");
    contentDiv.innerHTML = null;
    contentDiv.appendChild(content);

    // Set caption
    const caption = button.dataset.caption;
    /** @type {HTMLDivElement} */
    const captionDiv = this.overlay.querySelector(".carousel__caption");
    captionDiv.textContent = caption;

    // Update indicators
    this.indicatorTargets.forEach(
      (/** @type {HTMLButtonElement} */ indicator, index) => {
        if (index === this.index) {
          indicator.classList.add("active");
        } else {
          indicator.classList.remove("active");
        }
      }
    );

    // Show overlay
    this.overlay.classList.remove("carousel--hidden");
    this.overlay.classList.add("carousel--visible");
  }

  prev() {
    this.index =
      this.index - 1 >= 0 ? this.index - 1 : this.mediaTargets.length - 1;
    this.show();
  }

  next() {
    this.index = this.index + 1 < this.mediaTargets.length ? this.index + 1 : 0;
    this.show();
  }

  /**
   * @param {"picture"|"video"} type
   * @param {string} src
   */
  _makeContent(type, src) {
    if (type === "picture") {
      const img = document.createElement("img");
      img.src = src;

      const container = document.createElement("div");
      container.classList.add("carousel__container");

      container.appendChild(img);

      return container;
    }

    if (type === "video") {
      const iframe = document.createRange().createContextualFragment(src);

      return iframe;
    }
  }

  /**
   * @param {KeyboardEvent} e
   */
  _keyboardHandler(e) {
    const key = e.key;

    switch (key) {
      case "Escape":
        this.close();
        break;

      case "ArrowLeft":
        this.prev();
        break;

      case "ArrowRight":
        this.next();
        break;

      default:
        break;
    }

    // Numbers
    if (parseInt(key) == key) {
      const keyIndex = parseInt(key) - 1;
      if (this.mediaTargets[keyIndex]) {
        this.index = keyIndex;
        this.show();
      }
    }
  }

  _addIndicators() {
    /** @type {HTMLDivElement} */
    const indicatorsDiv = this.overlay.querySelector(".carousel__indicators");

    /** @type {HTMLTemplateElement} */
    const template = indicatorsDiv.querySelector("template#indicator");

    this.mediaTargets.forEach((target, index) => {
      /** @type {HTMLButtonElement} */
      const indicator = template.content.firstElementChild.cloneNode(true);

      indicator.addEventListener("click", () => {
        this.index = index;
        this.show();
      });

      indicatorsDiv.appendChild(indicator);
    });
  }
}
