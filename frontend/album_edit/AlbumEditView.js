export default class AlbumEditView {
	constructor() {
		// search
		this.search_album_input = document.getElementById("search_album_input");
		this.album_search_result = document.getElementById("album_search_result");
		this.album_search_section = document.getElementById("album_search_section");

		// edit form
		this.album_edit_section = document.getElementById("album_edit_section");
		this.selected_album_title = document.getElementById("selected_album_title");
		this.title_input = document.getElementById("title_input");
		this.description_input = document.getElementById("description_input");
		this.hashtag_input = document.getElementById("hashtag_input");
		this.add_hashtag_btn = document.getElementById("add_hashtag_btn");
		this.hashtag_result = document.getElementById("hashtag_result");
		this.messages_allowed_btn = document.getElementById("messages_allowed_btn");
		this.restriction_span = document.getElementById("restriction_span");
		this.restriction_public_btn = document.getElementById("restriction_public_btn");
		this.restriction_private_btn = document.getElementById("restriction_private_btn");
		this.restriction_restrict_btn = document.getElementById("restriction_restrict_btn");
		this.friend_input_search = document.getElementById("friend_input_search");
		this.friend_search_result = document.getElementById("friend_search_result");
		this.album_collaborators = document.getElementById("album_collaborators");
		this.photo_management = document.getElementById("photo_management");
		this.search_photo = document.getElementById("search_photo");
		this.selected_photos = document.getElementById("selected_photos");
		this.save_btn = document.getElementById("save_btn");
		this.msg_box = document.getElementById("msg_box");
	}

	showEditSection(albumTitle) {
		this.album_search_section.style.display = "none";
		this.album_edit_section.style.display = "block";
		this.selected_album_title.textContent = albumTitle;
	}

	updateRestrictionSpan(restriction) {
		this.restriction_span.textContent = restriction;
	}

	// album search results
	createAlbumResultElement(title) {
		const li = document.createElement("li");
		li.textContent = title;
		li.style.cursor = "pointer";
		this.album_search_result.appendChild(li);
		return li;
	}

	removeAlbumResultElements() {
		this.album_search_result.querySelectorAll("li").forEach(li => li.remove());
	}

	// photo search results
	createImgElement(srcValue) {
		const a = document.createElement("a");
		const img = document.createElement("img");

		img.className = "result_image";
		img.alt = "Photo";
		img.src = srcValue;

		this.photo_management.appendChild(a);
		a.appendChild(img);

		return a;
	}

	removeImgElement() {
		this.photo_management.querySelectorAll("a").forEach(a => a.remove());
	}

	createAlbumPhotoElement(imgSrc) {
		const photoContainer = document.createElement("div");
		const photoImg = document.createElement("img");
		const removeIcon = document.createElement("i");

		photoImg.className = "album_photo_image";
		photoImg.src = imgSrc;
		photoImg.alt = "Photo de l'album";

		removeIcon.className = "album_photo_remove fa-solid fa-circle-xmark";

		photoContainer.className = "album_photo_container";
		photoContainer.appendChild(photoImg);
		photoContainer.appendChild(removeIcon);
		this.selected_photos.appendChild(photoContainer);

		return { photoContainer, removeIcon };
	}

	// hashtags
	createHashtagElement(hashtag) {
		const li = document.createElement("li");
		const removeBtn = document.createElement("button");

		li.textContent = hashtag;
		removeBtn.textContent = "X";
		removeBtn.classList.add("hashtag_remove_btn");
		li.appendChild(removeBtn);
		this.hashtag_result.appendChild(li);

		return li;
	}

	clearHashtags() {
		this.hashtag_result.innerHTML = "";
	}

	// collaborators
	createFriendElement(username) {
		const li = document.createElement("li");

		li.textContent = username;
		li.style.cursor = "pointer";
		this.friend_search_result.appendChild(li);

		return li;
	}

	removeFriendElements() {
		this.friend_search_result.querySelectorAll("li").forEach(li => li.remove());
	}

	createCollaboratorsElement(username) {
		const li = document.createElement("li");
		const removeBtn = document.createElement("button");

		li.textContent = username;
		removeBtn.textContent = "X";
		removeBtn.classList.add("collaborator_remove_btn");
		li.appendChild(removeBtn);
		this.album_collaborators.appendChild(li);

		return removeBtn;
	}

	removeCollaboratorsElement(username) {
		this.album_collaborators.querySelectorAll("li").forEach(child => {
			if (child.firstChild.textContent === username) child.remove();
		});
	}

	clearCollaborators() {
		this.album_collaborators.innerHTML = "";
	}

	clearSelectedPhotos() {
		this.selected_photos.innerHTML = "";
	}
}