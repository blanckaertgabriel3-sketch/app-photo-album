export default class AlbumView {
	constructor() {
		// info_album
		this.title_input = document.getElementById("title_input");
		this.description_input = document.getElementById("description_input");
		this.hashtag_input = document.getElementById("hashtag_input");
		this.add_hashtag_btn = document.getElementById("add_hashtag_btn");
		this.hashtag_result = document.getElementById("hashtag_result");
		this.messages_allowed = document.getElementById("messages_allowed");
		this.messages_allowed_btn = document.getElementById("messages_allowed_btn");
		// restriction album
		this.restriction_span = document.getElementById("restriction_span");
		this.restriction_public_btn = document.getElementById("restriction_public_btn");
		this.restriction_private_btn = document.getElementById("restriction_private_btn");
		this.restriction_restrict_btn = document.getElementById("restriction_restrict_btn");

		this.friend_input_search = document.getElementById("friend_input_search");
		this.friend_search_result = document.getElementById("friend_search_result");
		this.album_collaborators = document.getElementById("album_collaborators");
		this.creation_date_span = document.getElementById("creation_date_span");
		// container_album_photos
		this.search_photo = document.getElementById("search_photo");
		this.photo_management = document.getElementById("photo_management");
		this.selected_photos = document.getElementById("selected_photos");
		
		this.create_btn = document.getElementById("create_btn");
		this.msg_box = document.getElementById("msg_box");

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
		const photoImg = document.createElement("img");
		const removeIcon = document.createElement("i");

		const photoInfoContainer = document.createElement("div");
		const photoTitle = document.createElement("p");
		const photoDescription = document.createElement("p");

		const photoTagsContainer = document.createElement("div");

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

		photoContainer.appendChild(photoImg);
		photoContainer.appendChild(removeIcon);
		photoContainer.appendChild(photoInfoContainer);
		photoContainer.appendChild(photoTagsContainer);

		this.selected_photos.appendChild(photoContainer);

		return { photoContainer, removeIcon };
	}
	createHashtagElement(hashtag) {
		const newLi = document.createElement("li");
		const removeBtn = document.createElement("button");

		newLi.textContent = hashtag;

		removeBtn.textContent = "X";
		removeBtn.classList.add("hashtag_remove_btn");

		newLi.appendChild(removeBtn);
		this.hashtag_result.appendChild(newLi);

		return newLi;
	}
	updateView(album_restriction) {
		restriction_span.textContent = album_restriction;
	}
	createFriendElement(username) {
		const newLi = document.createElement("li");
		newLi.textContent = username;
		this.friend_search_result.appendChild(newLi);

		return newLi;
	}
	removeFriendElement() {
		const childs = document.querySelectorAll("#friend_search_result > *").forEach(li => li.remove());
	}
	createCollaboratorsElement(username) {
		const newLi = document.createElement("li");
		const removeBtn = document.createElement("button");

		newLi.textContent = username;

		removeBtn.textContent = "X";
		removeBtn.classList.add("collaborator_remove_btn");

		newLi.appendChild(removeBtn);

		this.album_collaborators.appendChild(newLi);

		return removeBtn;
	}
	removeCollaboratorsElement(username) {
		const childs = document.querySelectorAll("#album_collaborators > *").forEach(child => {
			if(child.firstChild.textContent === username) {
				child.remove();
			}
		});
	}
}