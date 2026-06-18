export default class HomeController {
	constructor(view) {
		this.view = view;
		this.getUser();
		this.generatePosts();
		
		// pop_up
		this.view.open_pop_up.addEventListener("click", () => {
			this.view.modal.style.display = "flex";
		});

		this.view.close_pop_up.addEventListener("click", () => {
			this.view.modal.style.display = "none";
		});
	}
	async getUser() {
		try {
			// get user data
			const response = await fetch(
				"http://localhost:8000/rest/api/v1/users.php?action=getUser",
				{
					method: "POST",
					credentials: "include"
				}
			);
			if (response.status === 401) {
				window.location.href = "../form/form.html";
				return;
			}
			if (!response.ok) {
				throw new Error("Erreur HTTP");
				return;
			}

			const result = await response.json();

			msg_box.textContent = result.message;
			this.view.username_span.textContent = result.user.name;			
		}
		catch (error) {
			msg_box.textContent = "Erreur Serveur";
			console.error(error);
		}
	}
	async generatePosts() {
		try {
			const response = await fetch(
				"http://localhost:8000/rest/api/v1/albums.php?action=get_album_full",
				{
					method: "POST",
					credentials: "include"
				}
			);
			if (response.status === 401) {
				window.location.href = "../form/form.html";
				return;
			}
			if (!response.ok) {
				throw new Error("Erreur HTTP");
			}

			const result = await response.json();

			if (!result.success) {
				this.view.msg_box.textContent = result.message;
				return;
			}

			const albums = result.rows_albums;
			const photosAlbums = result.rows_photos_albums;
			const photos = result.rows_photos;
			const albumsHashtags = result.rows_albums_hashtags;

			albums.forEach(album => {

				// Première photo de l'album
				const relation = photosAlbums.find(
					row => row.album_id == album.id
				);

				let file_directory = "";

				if (relation) {
					const photo = photos.find(
						p => p.id == relation.photo_id
					);

					if (photo) {
						file_directory = photo.file_directory;
					}
				}

				// hashtags de l'album
				const hashtags = albumsHashtags
					.filter(row => row.album_id == album.id)
					.map(row => row.hashtag_name);

				this.view.createPostElement(
					file_directory,
					album.title,
					album.creation_date,
					hashtags,
					album.description
				);
			});
		}
		catch (error) {
			this.view.msg_box.textContent = "Erreur serveur";
			console.error(error);
		}
	}
}	