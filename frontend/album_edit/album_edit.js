import AlbumEditView from "./AlbumEditView.js";
import AlbumEditController from "./AlbumEditController.js";

import AlbumModel from "../album_management/AlbumModel.js";

const view = new AlbumEditView();
const model = new AlbumModel();
const controller = new AlbumEditController(view, model);
