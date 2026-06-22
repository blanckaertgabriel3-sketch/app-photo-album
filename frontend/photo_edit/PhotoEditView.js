export default class PhotoEditView {
	constructor() {
		this.search_photo_input = document.getElementById("search_photo_input");
		this.photo_search_result = document.getElementById("photo_search_result");
		this.photo_search_section = document.getElementById("photo_search_section");

		this.photo_edit_section = document.getElementById("photo_edit_section");
		this.selected_photo_title = document.getElementById("selected_photo_title");
		this.photo_preview = document.getElementById("photo_preview");
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
		this.save_btn = document.getElementById("save_btn");
		this.msg_box = document.getElementById("msg_box");
	}

	showEditSection(photo) {
		this.photo_search_section.style.display = "none";
		this.photo_edit_section.style.display = "block";
		this.selected_photo_title.textContent = photo.title;
		this.photo_preview.src = photo.file_directory;
	}

	updateRestrictionSpan(restriction) {
		this.restriction_span.textContent = restriction;
	}

	createPhotoResultElement(photo) {
		const div = document.createElement("div");

		div.style.display = "inline-block";
		div.style.margin = "5px";
		div.style.cursor = "pointer";

		const img = document.createElement("img");

		img.src = photo.file_directory;
		img.alt = photo.title;
		img.style.width = "100px";
		img.style.height = "80px";
		img.style.objectFit = "cover";

		const p = document.createElement("p");

		p.textContent = photo.title;

		div.appendChild(img);
		div.appendChild(p);

		this.photo_search_result.appendChild(div);

		return div;
	}

	removePhotoResultElements() {
		this.photo_search_result.innerHTML = "";
	}

	createHashtagElement(name) {
		const li = document.createElement("li");
		const removeBtn = document.createElement("button");

		li.textContent = name;
		removeBtn.textContent = "X";
		removeBtn.classList.add("hashtag_remove_btn");

		li.appendChild(removeBtn);
		this.hashtag_result.appendChild(li);

		return li;
	}

	clearHashtags() {
		this.hashtag_result.innerHTML = "";
	}
}