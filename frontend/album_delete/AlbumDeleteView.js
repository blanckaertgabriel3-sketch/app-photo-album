export default class AlbumDeleteView {
	constructor() {
		this.search_album_input = document.getElementById("search_album_input");
		this.album_search_result = document.getElementById("album_search_result");
		this.album_search_section = document.getElementById("album_search_section");
		this.album_confirm_section = document.getElementById("album_confirm_section");
		this.album_title_confirm = document.getElementById("album_title_confirm");
		this.confirm_delete_btn = document.getElementById("confirm_delete_btn");
		this.cancel_btn = document.getElementById("cancel_btn");
		this.msg_box = document.getElementById("msg_box");
	}

	showConfirmSection(albumTitle) {
		this.album_search_section.style.display = "none";
		this.album_confirm_section.style.display = "block";
		this.album_title_confirm.textContent = albumTitle;
	}

	showSearchSection() {
		this.album_confirm_section.style.display = "none";
		this.album_search_section.style.display = "block";
		this.search_album_input.value = "";
		this.removeAlbumResultElements();
	}

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
}