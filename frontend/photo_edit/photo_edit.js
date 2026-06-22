import PhotoEditView from "./PhotoEditView.js";
import PhotoEditController from "./PhotoEditController.js";
import AlbumModel from "../album_management/AlbumModel.js";

const view = new PhotoEditView();
const model = new AlbumModel();
const controller = new PhotoEditController(view, model);
