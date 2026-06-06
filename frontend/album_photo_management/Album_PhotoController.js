export default class Album_PhotoController {
	constructor(album_photoView) {
		this.view = album_photoView;
		this.view.createPhotoBtn.addEventListener("click", () => {
			document.location.href="../photo_management/photo_management.html";
		})
		this.view.createAlbumBtn.addEventListener("click", () => {
			document.location.href="../album_management/album_management.html";
		})	
	}
}

