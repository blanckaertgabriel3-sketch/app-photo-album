import FromView from "./FormView.js";
import FormController from "./FormController.js";

const form_view = new FromView();
new FormController(form_view);