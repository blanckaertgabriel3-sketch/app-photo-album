export default class AlbumController {
	constructor(album_view) {
		this.result_photo_max = 3;
		this.result_photo_count;
		this.album_photos = [];
		this.view = album_view;
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
				//create html element. Add listener to it.
				for(let i = 0 ; i<this.result_photo_count ; i++) {
					const imgSrc = search_result.photos_result[i].file_directory;
					this.view.createImgElement(imgSrc);
					this.view.newA.addEventListener("click", () => {
						this.view.createAlbumPhotoElement(imgSrc);
						//insert photo info in an array.
						this.album_photos.push(search_result.photos_result[i]);
					})
				}

			}
			catch (error) {
				this.view.msg_box.textContent = "Erreur Serveur";
				console.error(error);
			}
		});

		// create an album.
		this.view.create_btn.addEventListener("click", async () => {
		try {
			//create_album
			const create_response = await fetch("http://localhost:8000/rest/api/v1/albums.php?action=create_album", {
				method: "POST",
				body: JSON.stringify({
					title: this.view.title_input.value
				})
			});
			if(!create_response.ok) {
				this.view.msg_box.textContent = "Erreur HTTP";
				return;
			}   
			const create_result = await create_response.json();
			this.view.msg_box.textContent = create_result.message;
			// //photos_album
			// if(!create_result.album_id) {
			// 	// this.view.msg_box.textContent = "Il manque album_id";			
			// 	return;
			// }
			// for(let i = 0 ; i < this.album_photos.length ; i++) {
			// 	const photos_album_response = await fetch("http://localhost:8000/rest/api/v1/photos_albums.php", {
			// 		method: "POST",
			// 		body: JSON.stringify({
			// 			photo_id: this.album_photos[i].id,
			// 			album_id: create_result.album_id,
			// 			display_order: i
			// 		})
			// 	});
			// 	if(!photos_album_response.ok) {
			// 		this.view.msg_box.textContent = "Erreur HTTP";
			// 		return;
			// 	}   
			// 	const photos_album_result = await photos_album_response.json();
			// 	this.view.msg_box.textContent = photos_album_result.message;
			// }
			} catch (error) {
				this.view.msg_box.textContent = "Erreur serveur";
				console.error(error);
			}
		});

	}
}