export default class view_album_management {
	constructor() {
		this.msg_box = document.getElementById("msg_box");
		this.create_btn = document.getElementById("create_btn");
		this.title_input = document.getElementById("title_input");
		this.photo_management = document.getElementById("photo_management");
		this.selected_photos = document.getElementById("selected_photos");
		this.search_photo = document.getElementById("search_photo");
		this.newA;

	}
	createImgElement(srcValue) {
		this.newA = document.createElement("a");
		const newImg = document.createElement("img");

		const attributeClass = document.createAttribute("class");
		const attributeAlt = document.createAttribute("alt");
		const attributeSrc = document.createAttribute("src");

		attributeClass.value = "result_image";
		attributeAlt.value = "Image crée par l'utilisateur";
		attributeSrc.value = srcValue;

		newImg.setAttributeNode(attributeClass);
		newImg.setAttributeNode(attributeAlt);
		newImg.setAttributeNode(attributeSrc);

		this.photo_management.appendChild(this.newA);
		this.newA.appendChild(newImg);
	}
	removeImgElement() {
		document.querySelectorAll(".result_image").forEach(img => img.remove());
		this.photo_management.querySelectorAll("a").forEach(a => a.remove());
	}
	createAlbumPhotoElement(imgSrc) {
		const photoContainer = document.createElement("div");
		const orderIcon = document.createElement("i");
		const photoImg = document.createElement("img");
		const removeIcon = document.createElement("i");

		const photoInfoContainer = document.createElement("div");
		const photoTitle = document.createElement("p");
		const photoDescription = document.createElement("p");

		const photoTagsContainer = document.createElement("div");

		orderIcon.classList.add("album_photo_order");
		orderIcon.classList.add("fa-solid");
		orderIcon.classList.add("fa-equals");

		photoImg.classList.add("album_photo_image");
		photoImg.src = imgSrc;
		photoImg.alt = "Photo de l'album";

		removeIcon.classList.add("album_photo_remove");
		removeIcon.classList.add("fa-solid");
		removeIcon.classList.add("fa-circle-xmark");

		photoContainer.classList.add("album_photo_container");

		photoInfoContainer.classList.add("album_photo_info");

		photoTitle.classList.add("album_photo_title");
		photoTitle.textContent = "Titre photo exemple - 00/00/0000";

		photoDescription.classList.add("album_photo_description");
		photoDescription.textContent = "Description de la photo";

		photoTagsContainer.classList.add("album_photo_tags");

		photoInfoContainer.appendChild(photoTitle);
		photoInfoContainer.appendChild(photoDescription);

		photoContainer.appendChild(orderIcon);
		photoContainer.appendChild(photoImg);
		photoContainer.appendChild(removeIcon);
		photoContainer.appendChild(photoInfoContainer);
		photoContainer.appendChild(photoTagsContainer);

		this.selected_photos.appendChild(photoContainer);

		return photoContainer;
	}

}