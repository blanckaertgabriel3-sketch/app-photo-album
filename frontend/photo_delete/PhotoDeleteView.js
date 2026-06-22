export default class PhotoDeleteView {
	constructor() {
		this.search_photo_input = document.getElementById("search_photo_input");
		this.photo_search_result = document.getElementById("photo_search_result");
		this.photo_search_section = document.getElementById("photo_search_section");
		this.photo_confirm_section = document.getElementById("photo_confirm_section");
		this.photo_preview = document.getElementById("photo_preview");
		this.photo_title_confirm = document.getElementById("photo_title_confirm");
		this.confirm_delete_btn = document.getElementById("confirm_delete_btn");
		this.cancel_btn = document.getElementById("cancel_btn");
		this.msg_box = document.getElementById("msg_box");
	}

	showConfirmSection(photo) {
		this.photo_search_section.style.display = "none";
		this.photo_confirm_section.style.display = "block";
		this.photo_title_confirm.textContent = photo.title;
		this.photo_preview.src = photo.file_directory;
	}

	showSearchSection() {
		this.photo_confirm_section.style.display = "none";
		this.photo_search_section.style.display = "block";
		this.search_photo_input.value = "";

		this.removePhotoResultElements();
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
}