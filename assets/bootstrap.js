import { startStimulusApp } from "@symfony/stimulus-bundle";

import HomepageController from "./controllers/homepage_controller.js";
import UserController from "./controllers/user_controller.js";
import TrickMediaController from "./controllers/trick-media_controller.js";
import CollectionEditController from "./controllers/collection-edit_controller.js";
import FileController from "./controllers/file_controller.js";
import FormController from "./controllers/form_controller.js";
import DeleteButtonController from "./controllers/delete-button_controller.js";
import TrickSearchController from "./controllers/trick-search_controller.js";

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
app.register("homepage", HomepageController);
app.register("user", UserController);
app.register("trick-media", TrickMediaController);
app.register("collection-edit", CollectionEditController);
app.register("file", FileController);
app.register("form", FormController);
app.register("delete-button", DeleteButtonController);
app.register("trick-search", TrickSearchController);
