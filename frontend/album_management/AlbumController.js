export default class AlbumController {
	constructor(album_view, album_model) {
		this.view = album_view;
		this.model = album_model;
		this.result_photo_max = 3;
		this.result_photo_count;
		this.album_photos = [];
		this.album_hashtags = [];

		this.view.search_photo.addEventListener("input", async () => {
			const letters = this.view.search_photo.value;
			try {
				this.view.removeImgElement();
				const search_response = await fetch ("http://localhost:8000/rest/api/v1/search_photo.php?action=search_photo", {
					method: "POST",
					body: JSON.stringify({
						letters: letters
					})
				});
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
						this.view.createAlbumPhotoElement(imgSrc);
						// push photos in an array. Will be use for http  request 
						this.album_photos.push(search_result.photos_result[i]);
					})
				}

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
						creation_date: creation_date
					})
				});
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
				this.album_photos.forEach(async (photos, index) => {
					const photos_album_response = await fetch("http://localhost:8000/rest/api/v1/photos_albums.php", {
						method: "POST",
						body: JSON.stringify({
							photo_id: photos.id,
							album_id: create_result.album_id,
							display_order: index
						})
					});
					if(!photos_album_response.ok) {
						this.view.msg_box.textContent = "Erreur HTTP";
						return;
					}   
					const photos_album_result = await photos_album_response.json();
					this.view.msg_box.textContent = photos_album_result.message;
				})

				// create hashtag
				this.album_hashtags.forEach(async (hashtag) => {
					const create_hashtag_response = await fetch ("http://localhost:8000/rest/api/v1/hashtags.php?action=create_hashtag", {
						method: "POST",
						body: JSON.stringify({
							name: hashtag
						})
					});
					if(!create_hashtag_response.ok) {
						this.view.msg_box.textContent = "Erreur HTTP";
						return;
					}

					const create_hashtag_result = await create_hashtag_response.json();
					this.view.msg_box.textContent = create_hashtag_result.message;
					if(!create_hashtag_result.success) {
						return;
					}
					this.view.msg_box.textContent = "Tous les hashtags ont été créée";				
				});
			} catch (error) {
				this.view.msg_box.textContent = "Erreur serveur";
				console.error(error);
			}
		});
		this.view.add_hashtag_btn.addEventListener("click", async () => {
			const hashtag_name = this.view.hashtag_input.value;
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
	}
}