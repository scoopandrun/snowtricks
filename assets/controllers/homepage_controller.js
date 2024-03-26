import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["grid", "fetchButton"];

  connect() {
    /** @type {HTMLButtonElement} */
    const fetchButton = this.fetchButtonTarget;

    fetchButton.removeAttribute("hidden");
  }

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

      const outerHTML = document
        .createRange()
        .createContextualFragment(html).firstElementChild;

      grid.lastElementChild.after(...outerHTML.children);

      grid.querySelectorAll("[data-action='delete']").forEach((button) => {
        button.addEventListener("click", (e) => {
          const deleteConfirmed = confirm(
            "Are you sure that you want to delete this?"
          );

          if (!deleteConfirmed) {
            e.preventDefault();
          }
        });
      });

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
