import HomeView from "./HomeView.js";
import HomeController from "./HomeController.js";

const home_view = new HomeView();
new HomeController(home_view);