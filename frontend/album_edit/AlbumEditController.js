export default class AlbumEditController {
	constructor(view, model) {
		this.view = view;
		this.model = model;
		this.currentAlbum = null;
		this.album_photos = [];
		this.album_hashtags = [];
		this.collaborators = [];
		this.restriction = "private";
		this.result_photo_max = 3;
		this.loadingAlbum = false;

		this.view.search_album_input.addEventListener("input", async () => {
			const letters = this.view.search_album_input.value.trim();

			this.view.removeAlbumResultElements();

			if (letters === "") return;

			try {
				const res = await fetch("http://localhost:8000/rest/api/v1/albums.php?action=search_album", {
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

				data.albums_result.forEach(album => {
					this.view.createAlbumResultElement(album.title).addEventListener("click", () => {
						this._loadAlbum(album);
					});
				});
			} catch (e) {
				this.view.msg_box.textContent = "Erreur serveur";
				console.error(e);
			}
		});

		this.view.search_photo.addEventListener("input", async () => {
			const letters = this.view.search_photo.value;

			this.view.removeImgElement();

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

				const count = Math.min(data.photos_result.length, this.result_photo_max);

				for (let i = 0; i < count; i++) {
					const photo = data.photos_result[i];
					const element = this.view.createImgElement(photo.file_directory);

					element.addEventListener("click", () => {
						if (this.album_photos.some(p => p.id === photo.id)) {
							this.view.msg_box.textContent = "Cette photo est déjà dans l'album";
							return;
						}

						const { photoContainer, removeIcon } = this.view.createAlbumPhotoElement(photo.file_directory);

						this.album_photos.push(photo);

						removeIcon.addEventListener("click", () => {
							photoContainer.remove();
							this.album_photos.splice(this.album_photos.indexOf(photo), 1);
						});
					});
				}
			} catch (e) {
				this.view.msg_box.textContent = "Erreur serveur";
				console.error(e);
			}
		});

		this.view.friend_input_search.addEventListener("input", async () => {
			this.view.removeFriendElements();

			const letters = this.view.friend_input_search.value.trim();

			if (letters === "") return;

			try {
				const res = await fetch("http://localhost:8000/rest/api/v1/users.php?action=search_users", {
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

				if (!data.success) return;

				data.users_result.forEach(user => {
					this.view.createFriendElement(user.name).addEventListener("click", () => {
						if (this.collaborators.includes(user.name)) return;

						this.collaborators.push(user.name);

						this.view.createCollaboratorsElement(user.name).addEventListener("click", () => {
							this.view.removeCollaboratorsElement(user.name);
							this.collaborators.splice(this.collaborators.indexOf(user.name), 1);
						});
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

			if (this.album_hashtags.includes(name)) {
				this.view.msg_box.textContent = "Hashtag déjà présent";
				return;
			}

			this.album_hashtags.push(name);
			this.view.createHashtagElement(name);
			this.view.hashtag_input.value = "";
		});

		this.view.hashtag_result.addEventListener("click", event => {
			if (!event.target.classList.contains("hashtag_remove_btn")) return;

			const li = event.target.parentElement;
			const hashtag = li.firstChild.textContent.trim();

			li.remove();
			this.album_hashtags.splice(this.album_hashtags.indexOf(hashtag), 1);
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
			if (!this.currentAlbum) return;

			await this._saveAlbum();
		});		
	}
	async _loadAlbum(album) {
		if(this.loadingAlbum === true) {return;}
		this.loadingAlbum = true;

		try {
			this.currentAlbum = album;
			this.restriction = album.restriction;
			this.album_photos = [];
			this.album_hashtags = [];
			this.collaborators = [];

			this.view.title_input.value = album.title;
			this.view.description_input.value = album.description ?? "";
			this.view.messages_allowed_btn.textContent = album.messages_allowed ? "Oui" : "Non";
			this.view.updateRestrictionSpan(this.restriction);
			this.view.clearHashtags();
			this.view.clearCollaborators();
			this.view.clearSelectedPhotos();

			const hashtagsRes = await fetch("http://localhost:8000/rest/api/v1/albums.php?action=get_album_hashtags", {
				method: "POST",
				body: JSON.stringify({ album_id: album.id })
			});

			if (hashtagsRes.ok) {
				const data = await hashtagsRes.json();
				if (data.success) {
					data.hashtags.forEach(h => {
						this.album_hashtags.push(h.name);
						this.view.createHashtagElement(h.name);
					});
				}
			}

			const photosRes = await fetch("http://localhost:8000/rest/api/v1/photos_albums.php?action=get_photos_albums", {
				method: "POST",
				body: JSON.stringify({ album_id: album.id })
			});

			if (photosRes.ok) {
				const data = await photosRes.json();
				if (data.success) {
					data.photos_albums.forEach(pa => {
						this.album_photos.push(pa);

						const { photoContainer, removeIcon } =
							this.view.createAlbumPhotoElement(pa.file_directory);

						removeIcon.addEventListener("click", () => {
							photoContainer.remove();
							this.album_photos.splice(this.album_photos.indexOf(pa), 1);
						});
					});
				}
			}

			const collaboratorsRes = await fetch("http://localhost:8000/rest/api/v1/albums_collaborators.php?action=get_collaborators", {
				method: "POST",
				body: JSON.stringify({ album_id: album.id })
			});

			if (collaboratorsRes.ok) {
				const data = await collaboratorsRes.json();
				if (data.success) {
					data.collaborators.forEach(c => {
						this.collaborators.push(c.name);

						this.view.createCollaboratorsElement(c.name)
							.addEventListener("click", () => {
								this.view.removeCollaboratorsElement(c.name);
								this.collaborators.splice(this.collaborators.indexOf(c.name), 1);
							});
					});
				}
			}

			this.view.showEditSection(album.title);

		} catch (e) {
			console.error(e);
		} finally {
			this.loadingAlbum = false;
		}
	}
	async _saveAlbum() {
		try {
			const updateRes = await fetch("http://localhost:8000/rest/api/v1/albums.php?action=update_album", {
				method: "POST",
				body: JSON.stringify({
					album_id: this.currentAlbum.id,
					title: this.view.title_input.value,
					description: this.view.description_input.value,
					messages_allowed: this.model.booleanValue(this.view.messages_allowed_btn.textContent),
					restriction: this.restriction
				})
			});

			if (updateRes.status === 401) {
				window.location.href = "../form/form.html";
				return;
			}

			if (!updateRes.ok) {
				this.view.msg_box.textContent = "Erreur HTTP";
				return;
			}

			const updateData = await updateRes.json();

			this.view.msg_box.textContent = updateData.message;

			if (!updateData.success) return;

			const album_id = this.currentAlbum.id;

			const syncPhotosRes = await fetch("http://localhost:8000/rest/api/v1/photos_albums.php?action=sync_photos_albums", {
				method: "POST",
				body: JSON.stringify({
					album_id,
					photos: this.album_photos.map((p, i) => ({
						photo_id: p.id,
						display_order: i
					}))
				})
			});

			if (!syncPhotosRes.ok) {
				this.view.msg_box.textContent = "Erreur HTTP (photos)";
				return;
			}

			const syncPhotosData = await syncPhotosRes.json();

			if (!syncPhotosData.success) {
				this.view.msg_box.textContent = syncPhotosData.message;
				return;
			}

			for (const name of this.album_hashtags) {
				await fetch("http://localhost:8000/rest/api/v1/hashtags.php?action=create_hashtag", {
					method: "POST",
					body: JSON.stringify({ name })
				});
			}

			const syncHashtagsRes = await fetch("http://localhost:8000/rest/api/v1/albums_hashtags.php?action=sync_albums_hashtags", {
				method: "POST",
				body: JSON.stringify({
					album_id,
					hashtag_names: this.album_hashtags
				})
			});

			const syncHashtagsData = await syncHashtagsRes.json();
			if (!syncHashtagsData.success) {
				this.view.msg_box.textContent = syncHashtagsData.message;
				return;
			}

			await fetch("http://localhost:8000/rest/api/v1/albums_collaborators.php?action=sync_collaborators", {
				method: "POST",
				body: JSON.stringify({
					album_id,
					usernames: this.collaborators
				})
			});

			this.view.msg_box.textContent = "Album modifié avec succès";
		} catch (e) {
			this.view.msg_box.textContent = "Erreur serveur";
			console.error(e);
		}
	}
}
