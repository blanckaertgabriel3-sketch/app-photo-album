const albumPhotoManagement = document.getElementById("album_photo_management");

const albumManagement = document.getElementById("album_management");
const photoManagement = document.getElementById("photo_management");

const createAlbumBtn = document.getElementById("create_album");
const editAlbumBtn = document.getElementById("edit_album");
const deleteAlbumBtn = document.getElementById("delete_album");

const createPhotoBtn = document.getElementById("create_photo");
const editPhotoBtn = document.getElementById("edit_photo");
const deletePhotoBtn = document.getElementById("delete_photo");

const errorBox = document.getElementById("msg_box");

createPhotoBtn.addEventListener("click", () => {
	document.location.href="../photo_management/photo_management.html";
})
createAlbumBtn.addEventListener("click", () => {
	document.location.href="../album_management/album_management.html";
})