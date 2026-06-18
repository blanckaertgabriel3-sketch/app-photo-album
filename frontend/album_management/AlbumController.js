export default class AlbumController {
	constructor(album_view, album_model) {
		this.view = album_view;
		this.model = album_model;
		this.result_photo_max = 3;
		this.result_photo_count;
		this.album_photos = [];
		this.album_hashtags = [];
		this.collaborators = [];
		this.restriction = "private";

		this.view.updateView(this.restriction);
		// search photo
		this.view.search_photo.addEventListener("input", async () => {
			const letters = this.view.search_photo.value;
			try {
				this.view.removeImgElement();
				const search_response = await fetch ("http://localhost:8000/rest/api/v1/photos.php?action=search", {
					method: "POST",
					body: JSON.stringify({
						letters: letters
					})
				});
				if (search_response.status === 401) {
					window.location.href = "../form/form.html";
					return;
				}
				if(!search_response.ok) {
					this.view.msg_box.textContent = "Erreur HTTP";
					return;
				}
				const search_result = await search_response.json();
				this.view.msg_box.textContent = search_result.message;
				if(!search_result.success) {
					return;
				}
				// 	Create img element. Add listener to it.
				const result_photo_nb = search_result.photos_result.length;
				if(!result_photo_nb > 0) {
					this.view.msg_box.textContent = "Aucune photo trouvé";			
					return;
				}
				if(result_photo_nb < this.result_photo_max) {
					this.result_photo_count = result_photo_nb;
				}else {
					this.result_photo_count = this.result_photo_max;
				}
				for(let i = 0 ; i<this.result_photo_count ; i++) {
					const imgSrc = search_result.photos_result[i].file_directory;
					this.view.createImgElement(imgSrc);
					this.view.newA.addEventListener("click", () => {
						const photo = search_result.photos_result[i];

						const { photoContainer, removeIcon } =
							this.view.createAlbumPhotoElement(imgSrc);

						this.album_photos.push(photo);

						removeIcon.addEventListener("click", () => {
							photoContainer.remove();

							const index = this.album_photos.indexOf(photo);

							if (index !== -1) {
								this.album_photos.splice(index, 1);
							}
						});
					});
				}

			} catch (error) {
				this.view.msg_box.textContent = "Erreur Serveur";
				console.error(error);
			}
		});

		// search collaborators
		this.view.friend_input_search.addEventListener("input", async () => {
			this.view.removeFriendElement();
			const letters = this.view.friend_input_search.value.trim();
			if(letters == "") {
				this.view.msg_box.textContent = "Entrez une lettre pour rechercher des collaborateurs";
				return;
			}
			try {
				const search_response = await fetch ("http://localhost:8000/rest/api/v1/users.php?action=search_users", {
					method: "POST",
					body: JSON.stringify({
						letters: letters
					})
				});
				if (search_response.status === 401) {
					window.location.href = "../form/form.html";
					return;
				}
				if(!search_response.ok) {
					this.view.msg_box.textContent = "Erreur HTTP";
					return;
				}
				const search_result = await search_response.json();
				this.view.msg_box.textContent = search_result.message;
				if(!search_result.success) {
					return;
				}
				// create element for each user result
				search_result.users_result.forEach(async (user_result, index) => {
					// listener for li element
					let username = user_result.name;
					this.view.createFriendElement(username).addEventListener("click", () => {
						let collaborator_index = this.collaborators.indexOf(username);
						if(collaborator_index === -1) {
							// add collaborators, add listener for the remove btn
							this.view.createCollaboratorsElement(username).addEventListener("click", () => {
								this.view.removeCollaboratorsElement(username);
								collaborator_index = this.collaborators.indexOf(username);
								this.collaborators.splice(collaborator_index, 1);
								console.log("collaborators array : ", this.collaborators);
							})
							this.collaborators.push(username);
							console.log("collaborators array : ", this.collaborators);
						}
					});
				});
			} catch (error) {
				this.view.msg_box.textContent = "Erreur Serveur";
				console.error(error);
			}
		});

		// create an album.
		this.view.create_btn.addEventListener("click", async () => {
			const creation_date = new Date().toISOString().slice(0, 19).replace("T", " ");
			try {
				
				//create_album
				const create_response = await fetch("http://localhost:8000/rest/api/v1/albums.php?action=create_album", {
					method: "POST",
					body: JSON.stringify({
						title: this.view.title_input.value,
						description: this.view.description_input.value,
						messages_allowed: this.model.booleanValue(this.view.messages_allowed_btn.textContent),
						creation_date: creation_date,
						restriction: this.restriction
					})
				});
				if(create_response.status === 401) {
					window.location.href = "../form/form.html";
					return;
				}
				if(!create_response.ok) {
					this.view.msg_box.textContent = "Erreur HTTP";
					return;
				}   
				const create_result = await create_response.json();
				this.view.msg_box.textContent = create_result.message;
				if(!create_result.success) {
					return;
				}

				// //photos_album
				if(!create_result.album_id) {
					this.view.msg_box.textContent = "Il manque l'idendifiant de l'album";
					return;
				}
				if(this.album_photos.length <= 0) {
					console.log("Aucune photo dans l'album");
				}
				this.album_photos.forEach(async (photos, index) => {
					console.log("photo_id", photos.id);
					console.log("album_id", create_result.album_id,);
					console.log("index", index);

					const photos_album_response = await fetch("http://localhost:8000/rest/api/v1/photos_albums.php?action=photos_albums", {
						method: "POST",
						body: JSON.stringify({
							photo_id: photos.id,
							album_id: create_result.album_id,
							display_order: index
						})
					});
					if (photos_album_response.status === 401) {
						window.location.href = "../form/form.html";
						return;
					}
					if(!photos_album_response.ok) {
						this.view.msg_box.textContent = "Erreur HTTP";
						return;
					} 
					const photos_album_result = await photos_album_response.json();
					this.view.msg_box.textContent = photos_album_result.message;
					if(!photos_album_result.success) {
						return;
					}
				})
				// -------------------- -------------------- -------------------- -------------------- --------------------
				// -------------------- -------------------- -------------------- -------------------- --------------------
				// -------------------- -------------------- -------------------- -------------------- --------------------
				// -------------------- -------------------- -------------------- -------------------- --------------------
				// -------------------- -------------------- -------------------- -------------------- --------------------
				return;
				// -------------------- -------------------- -------------------- -------------------- --------------------
				// -------------------- -------------------- -------------------- -------------------- --------------------
				// -------------------- -------------------- -------------------- -------------------- --------------------
				// -------------------- -------------------- -------------------- -------------------- --------------------
				// -------------------- -------------------- -------------------- -------------------- --------------------

				// create hashtag
				if(this.album_hashtags.length <= 0) {
					console.log("Aucun hashtag dans l'album");
				}
				this.album_hashtags.forEach(async (hashtag) => {
					const create_hashtag_response = await fetch ("http://localhost:8000/rest/api/v1/hashtags.php?action=create_hashtag", {
						method: "POST",
						body: JSON.stringify({
							name: hashtag
						})
					});
					if (create_hashtag_response.status === 401) {
						window.location.href = "../form/form.html";
						return;
					}
					if(!create_hashtag_response.ok) {
						this.view.msg_box.textContent = "Erreur HTTP";
						return;
					}

					const create_hashtag_result = await create_hashtag_response.json();
					this.view.msg_box.textContent = create_hashtag_result.message;
					if(!create_hashtag_result.success) {
						return;
					}

					// albums_hashtags
					const albums_hashtags_response = await fetch ("http://localhost:8000/rest/api/v1/albums_hashtags.php?action=albums_hashtags", {
						method: "POST",
						body: JSON.stringify({
							hashtag_id: create_hashtag_result.hashtag_id,
							album_id: create_result.album_id
						})
					});
					if (albums_hashtags_response.status === 401) {
						window.location.href = "../form/form.html";
						return;
					}
					if(!albums_hashtags_response.ok) {
						this.view.msg_box.textContent = "Erreur HTTP";
						return;
					}
					const albums_hashtags_result = await albums_hashtags_response.json();
					this.view.msg_box.textContent = albums_hashtags_result.message;
					if(!albums_hashtags_result.success) {
						return;
					}
					this.view.msg_box.textContent = "Tous les hashtags ont été créée";				
				});
				console.log("Album Créée");
				console.log("----------------------------------------------");
			} catch (error) {
				this.view.msg_box.textContent = "Erreur serveur";
				console.error(error);
			}
		});
		this.view.add_hashtag_btn.addEventListener("click", async () => {
			const hashtag_name = this.view.hashtag_input.value.trim();
			const album_hashtags_index = this.album_hashtags.indexOf(hashtag_name);
			if(album_hashtags_index >= 0) {
				this.view.msg_box.textContent = "Cet hashtag existe déjà pour l'album";
				return;
			}
			if(hashtag_name  == "") {
				this.view.msg_box.textContent = "Impossible de mettre un hashtag vide";
				return;
			}
			this.view.createHashtagElement(hashtag_name);
			this.album_hashtags.push(hashtag_name);
		})
		this.view.messages_allowed_btn.addEventListener("click", () => {
			this.view.messages_allowed_btn.textContent = this.model.swapValue(this.view.messages_allowed_btn.textContent);
		})
		this.view.hashtag_result.addEventListener("click", (event) => {
			if(event.target.classList.contains("hashtag_remove_btn")) {
				event.target.parentElement.remove();

				const hashtag = event.target.parentElement.firstChild.textContent.trim();
				const hashtagPosition = this.album_hashtags.indexOf(hashtag);
				if(hashtagPosition !== -1) {
					this.album_hashtags.splice(hashtagPosition, 1);
					this.view.msg_box.textContent = "Hashtag supprimé";
				}
			}
		});
		// Restriction listeners
		this.view.restriction_public_btn.addEventListener("click", () => {
			this.restriction = "public";
			this.view.updateView(this.restriction);
		})
		this.view.restriction_private_btn.addEventListener("click", () => {
			this.restriction = "private";
			this.view.updateView(this.restriction);
		})
		this.view.restriction_restrict_btn.addEventListener("click", () => {
			this.restriction = "restrict";
			this.view.updateView(this.restriction);
		})
	}
}