import { startStimulusApp } from "@symfony/stimulus-bundle";

import HomepageTricks from "./controllers/homepage-tricks_controller.js";

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
app.register("homepage-tricks", HomepageTricks);
