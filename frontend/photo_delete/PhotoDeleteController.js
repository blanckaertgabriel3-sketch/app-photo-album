export default class PhotoDeleteController {
	constructor(view) {
		this.view = view;
		this.currentPhoto = null;

		this.view.search_photo_input.addEventListener("input", async () => {
			const letters = this.view.search_photo_input.value.trim();

			this.view.removePhotoResultElements();

			if (letters === "") return;

			try {
				const res = await fetch("http://localhost:8000/rest/api/v1/photos.php?action=search", {
					method: "POST",
					body: JSON.stringify({ letters })
				});

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

				data.photos_result.forEach(photo => {
					this.view.createPhotoResultElement(photo).addEventListener("click", () => {
						this.currentPhoto = photo;
						this.view.showConfirmSection(photo);
					});
				});
			} catch (e) {
				this.view.msg_box.textContent = "Erreur serveur";
				console.error(e);
			}
		});

		this.view.confirm_delete_btn.addEventListener("click", async () => {
			if (!this.currentPhoto) return;

			try {
				const res = await fetch("http://localhost:8000/rest/api/v1/photos.php?action=delete_photo", {
					method: "POST",
					body: JSON.stringify({
						photo_id: this.currentPhoto.id
					})
				});

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
					this.currentPhoto = null;
					this.view.showSearchSection();
				}
			} catch (e) {
				this.view.msg_box.textContent = "Erreur serveur";
				console.error(e);
			}
		});

		this.view.cancel_btn.addEventListener("click", () => {
			this.currentPhoto = null;
			this.view.showSearchSection();
		});
	}
}