import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["grid", "fetchButton"];

  async fetch() {
    /** @type {HTMLButtonElement} */
    const fetchButton = this.fetchButtonTarget;

    /** @type {HTMLDivElement} */
    const grid = this.gridTarget;

    try {
      const nextPageNumber = fetchButton.dataset.nextPageNumber;
      const url = "/tricks-batch-" + nextPageNumber;

      fetchButton.setAttribute("disabled", true);
      fetchButton.textContent = "Loading...";

      const response = await fetch(url);

      if (!response.ok) {
        throw new Error("Error when fetching the tricks");
      }

      const html = await response.text();

      const fragment = document.createElement("div");
      fragment.innerHTML = html;
      const outerHTML = fragment.firstChild;

      grid.lastElementChild.after(...outerHTML.children);

      const newNextPageNumber = parseInt(outerHTML.dataset.nextPageNumber);

      if (newNextPageNumber) {
        fetchButton.dataset.nextPageNumber =
          parseInt(fetchButton.dataset.nextPageNumber) + 1;
      } else {
        fetchButton.parentElement.remove();
      }
    } catch (error) {
      console.error(error.message);
    } finally {
      fetchButton.removeAttribute("disabled");
      fetchButton.textContent = "Load more";
    }
  }
}
