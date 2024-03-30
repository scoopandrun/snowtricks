import { startStimulusApp } from "@symfony/stimulus-bundle";

import HomepageController from "./controllers/homepage_controller.js";
import UserController from "./controllers/user_controller.js";
import TrickMediaController from "./controllers/trick-media_controller.js";
import CollectionEditController from "./controllers/collection-edit_controller.js";
import FileController from "./controllers/file_controller.js";
import FormController from "./controllers/form_controller.js";

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
app.register("homepage", HomepageController);
app.register("user", UserController);
app.register("trick-media", TrickMediaController);
app.register("collection-edit", CollectionEditController);
app.register("file", FileController);
app.register("form", FormController);

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
