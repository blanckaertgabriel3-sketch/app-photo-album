import AlbumView from "./AlbumView.js";
import AlbumModel from "./AlbumModel.js";
import AlbumController from "./AlbumController.js";

const album_view = new AlbumView();
const album_model = new AlbumModel();
const album_controller = new AlbumController(album_view, album_model);