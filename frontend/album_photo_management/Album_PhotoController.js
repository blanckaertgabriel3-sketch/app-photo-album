export default class Album_PhotoController {
	constructor(album_photoView) {
		this.view = album_photoView;
		
		this.view.createAlbumBtn.addEventListener("click", () => {
			document.location.href = "../album_management/album_management.html";
		});
		this.view.editAlbumBtn.addEventListener("click", () => {
			document.location.href = "../album_edit/album_edit.html";
		});
		this.view.deleteAlbumBtn.addEventListener("click", () => {
			document.location.href = "../album_delete/album_delete.html";
		});
		this.view.createPhotoBtn.addEventListener("click", () => {
			document.location.href = "../photo_management/photo_management.html";
		});
		this.view.editPhotoBtn.addEventListener("click", () => {
			document.location.href = "../photo_edit/photo_edit.html";
		});
		this.view.deletePhotoBtn.addEventListener("click", () => {
			document.location.href = "../photo_delete/photo_delete.html";
		});
	}
}
