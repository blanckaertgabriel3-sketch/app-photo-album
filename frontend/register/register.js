import RegisterView from "./RegisterView.js";
import RegisterController from "./RegisterController.js";

const register_view = new RegisterView();
new RegisterController(register_view);