export default class AlbumDeleteController {
	constructor(view) {
		this.view = view;
		this.currentAlbum = null;

		this.view.search_album_input.addEventListener("input", async () => {
			const letters = this.view.search_album_input.value.trim();

			this.view.removeAlbumResultElements();

			if (letters === "") return;

			try {
				const res = await fetch(
					"http://localhost:8000/rest/api/v1/albums.php?action=search_album",
					{
						method: "POST",
						body: JSON.stringify({ letters })
					}
				);

				if (res.status === 401) {
					window.location.href = "../form/form.html";
					return;
				}

				if (!res.ok) {
					this.view.msg_box.textContent = "Erreur HTTP";
					return;
				}

				const data = await res.json();

				this.view.msg_box.textContent = data.message;

				if (!data.success) return;

				data.albums_result.forEach(album => {
					this.view.createAlbumResultElement(album.title).addEventListener("click", () => {
						this.currentAlbum = album;
						this.view.showConfirmSection(album.title);
					});
				});
			} catch (e) {
				this.view.msg_box.textContent = "Erreur serveur";
				console.error(e);
			}
		});

		this.view.confirm_delete_btn.addEventListener("click", async () => {
			if (!this.currentAlbum) return;

			try {
				const res = await fetch(
					"http://localhost:8000/rest/api/v1/albums.php?action=delete_album",
					{
						method: "POST",
						body: JSON.stringify({
							album_id: this.currentAlbum.id
						})
					}
				);

				if (res.status === 401) {
					window.location.href = "../form/form.html";
					return;
				}

				if (!res.ok) {
					this.view.msg_box.textContent = "Erreur HTTP";
					return;
				}

				const data = await res.json();

				this.view.msg_box.textContent = data.message;

				if (data.success) {
					this.currentAlbum = null;
					this.view.showSearchSection();
				}
			} catch (e) {
				this.view.msg_box.textContent = "Erreur serveur";
				console.error(e);
			}
		});

		this.view.cancel_btn.addEventListener("click", () => {
			this.currentAlbum = null;
			this.view.showSearchSection();
		});
	}
}