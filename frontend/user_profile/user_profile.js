import User_Profile_View from "./User_Profile_View.js";
import User_Profile_Controller from "./User_Profile_Controller.js";

const view = new User_Profile_View();
new User_Profile_Controller(view);