import { startStimulusApp } from "@symfony/stimulus-bundle";

import Homepage from "./controllers/homepage_controller.js";
import User from "./controllers/user_controller.js";
import TrickMedia from "./controllers/trick-media_controller.js";
import CollectionEdit from "./controllers/collection-edit_controller.js";

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
app.register("homepage", Homepage);
app.register("user", User);
app.register("trick-media", TrickMedia);
app.register("collection-edit", CollectionEdit);

document.addEventListener("turbo:load", () => addDeleteConfirmations());
document.addEventListener("turbo:frame-load", () => addDeleteConfirmations());

function addDeleteConfirmations() {
  document.querySelectorAll("[data-action='delete']").forEach((button) => {
    // Remove before adding to avoid duplicate listeners
    button.removeEventListener("click", confirmDelete);
    button.addEventListener("click", confirmDelete);
  });
}

function confirmDelete(e) {
  const deleteConfirmed = confirm("Are you sure that you want to delete this?");

  if (!deleteConfirmed) {
    e.preventDefault();
  }
}
