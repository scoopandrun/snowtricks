import { startStimulusApp } from "@symfony/stimulus-bundle";
import feather from "feather-icons";

import HomepageTricks from "./controllers/homepage-tricks_controller.js";

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
app.register("homepage-tricks", HomepageTricks);

document.addEventListener("turbo:load", () => feather.replace());
document.addEventListener("turbo:load", () => addDeleteConfirmations());
document.addEventListener("turbo:frame-load", () => addDeleteConfirmations());

function addDeleteConfirmations() {
  document.querySelectorAll("[data-action='delete']").forEach((button) => {
    // Remove before adding to avoid duplicate listeners
    button.removeEventListener("click", addDeleteConfirmation);
    button.addEventListener("click", addDeleteConfirmation);
  });
}

function addDeleteConfirmation(e) {
  const deleteConfirmed = confirm("Are you sure that you want to delete this?");

  if (!deleteConfirmed) {
    e.preventDefault();
  }
}
