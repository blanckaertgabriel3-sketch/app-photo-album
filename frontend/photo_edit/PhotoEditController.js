export default class PhotoEditController {
	constructor(view, model) {
		this.view = view;
		this.model = model;
		this.currentPhoto = null;
		this.photo_hashtags = [];
		this.restriction = "private";

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
						this._loadPhoto(photo);
					});
				});
			} catch (e) {
				this.view.msg_box.textContent = "Erreur serveur";
				console.error(e);
			}
		});

		this.view.add_hashtag_btn.addEventListener("click", () => {
			const name = this.view.hashtag_input.value.trim();

			if (name === "") {
				this.view.msg_box.textContent = "Hashtag vide";
				return;
			}

			if (this.photo_hashtags.includes(name)) {
				this.view.msg_box.textContent = "Hashtag déjà présent";
				return;
			}

			this.photo_hashtags.push(name);
			this.view.createHashtagElement(name);
			this.view.hashtag_input.value = "";
		});

		this.view.hashtag_result.addEventListener("click", event => {
			if (!event.target.classList.contains("hashtag_remove_btn")) return;

			const li = event.target.parentElement;
			const name = li.firstChild.textContent.trim();

			li.remove();
			this.photo_hashtags.splice(this.photo_hashtags.indexOf(name), 1);
		});

		this.view.messages_allowed_btn.addEventListener("click", () => {
			this.view.messages_allowed_btn.textContent =
				this.model.swapValue(this.view.messages_allowed_btn.textContent);
		});

		this.view.restriction_public_btn.addEventListener("click", () => {
			this.restriction = "public";
			this.view.updateRestrictionSpan(this.restriction);
		});

		this.view.restriction_private_btn.addEventListener("click", () => {
			this.restriction = "private";
			this.view.updateRestrictionSpan(this.restriction);
		});

		this.view.restriction_restrict_btn.addEventListener("click", () => {
			this.restriction = "restrict";
			this.view.updateRestrictionSpan(this.restriction);
		});

		this.view.save_btn.addEventListener("click", async () => {
			if (!this.currentPhoto) return;

			await this._savePhoto();
		});
	}

	async _loadPhoto(photo) {
		this.currentPhoto = photo;
		this.photo_hashtags = [];
		this.restriction = photo.restriction ?? "private";

		this.view.title_input.value = photo.title;
		this.view.description_input.value = photo.description ?? "";
		this.view.messages_allowed_btn.textContent = photo.messages_allowed ? "Oui" : "Non";
		this.view.updateRestrictionSpan(this.restriction);
		this.view.clearHashtags();

		try {
			const res = await fetch("http://localhost:8000/rest/api/v1/photos.php?action=get_photo_hashtags", {
				method: "POST",
				body: JSON.stringify({
					photo_id: photo.id
				})
			});

			if (res.ok) {
				const data = await res.json();

				if (data.success) {
					data.hashtags.forEach(h => {
						this.photo_hashtags.push(h.name);
						this.view.createHashtagElement(h.name);
					});
				}
			}
		} catch (e) {
			console.error(e);
		}

		this.view.showEditSection(photo);
	}

	async _savePhoto() {
		try {
			const res = await fetch("http://localhost:8000/rest/api/v1/photos.php?action=update_photo", {
				method: "POST",
				body: JSON.stringify({
					photo_id: this.currentPhoto.id,
					title: this.view.title_input.value,
					description: this.view.description_input.value,
					messages_allowed: this.model.booleanValue(this.view.messages_allowed_btn.textContent),
					restriction: this.restriction
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

			if (!data.success) return;

			await fetch("http://localhost:8000/rest/api/v1/photos_hashtags.php?action=sync_photos_hashtags", {
				method: "POST",
				body: JSON.stringify({
					photo_id: this.currentPhoto.id,
					hashtag_names: this.photo_hashtags
				})
			});

			this.view.msg_box.textContent = "Photo modifiée avec succès";
		} catch (e) {
			this.view.msg_box.textContent = "Erreur serveur";
			console.error(e);
		}
	}
}