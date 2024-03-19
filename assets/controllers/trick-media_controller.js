import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["media"];

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
  }

  get overlay() {
    /** @type {HTMLDivElement} */
    const overlay = document.getElementById("media-overlay");

    return overlay;
  }

  /**
   * @param {MouseEvent} e
   */
  close(e) {
    const overlay = this.overlay;

    overlay.classList.remove("media-overlay--visible");
    overlay.classList.add("media-overlay--hidden");

    /** @type {HTMLDivElement} */
    const captionDiv = overlay.querySelector(".media-overlay__caption");
    captionDiv.textContent = "";

    /** @type {HTMLDivElement} */
    const contentDiv = overlay.querySelector(".media-overlay__content");
    contentDiv.innerHTML = null;
  }

  show() {
    /** @type {HTMLButtonElement} */
    const button = this.mediaTargets[this.index];

    /** @type {"picture"|"video"} */
    const mediaType = button.dataset.type;
    const caption = button.dataset.caption;
    const src =
      mediaType === "picture"
        ? button.firstElementChild.src
        : button.dataset.iframe;
    const content = this.makeContent(mediaType, src);

    const overlay = this.overlay;
    overlay.classList.remove("media-overlay--hidden");
    overlay.classList.add("media-overlay--visible");

    /** @type {HTMLDivElement} */
    const captionDiv = overlay.querySelector(".media-overlay__caption");
    captionDiv.textContent = caption;

    /** @type {HTMLDivElement} */
    const contentDiv = overlay.querySelector(".media-overlay__content");
    contentDiv.innerHTML = null;
    contentDiv.appendChild(content);
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
  makeContent(type, src) {
    if (type === "picture") {
      const img = document.createElement("img");
      img.src = src;

      const container = document.createElement("div");
      container.classList.add("media-overlay__container");

      container.appendChild(img);

      return container;
    }

    if (type === "video") {
      const iframe = document.createRange().createContextualFragment(src);

      return iframe;
    }
  }
}
