export default class PhotoController {
    constructor(photo_view) {
        this.view = photo_view;
        this.view.msg_box.textContent = "";
        
        this.view.create_btn.addEventListener("click", async () => {
            const file = this.view.file_input.files[0];
            const formData = new FormData();
            formData.append("file", file);

            try {
                this.view.msg_box.textContent = "Upload en cours...";

                const upload_response = await fetch("http://localhost:8000/rest/api/v1/photos.php?action=upload", {
                    method: "POST",
                    body: formData
                });
                if (upload_response.status === 401) {
                    window.location.href = "../form/form.html";
                    return;
                }
                if(!upload_response.ok) {
                    this.view.msg_box.textContent = "Erreur HTTP";
                    return;
                }
                const upload_result = await upload_response.json();
                this.view.msg_box.textContent = upload_result.message;

                if(!upload_result.success) {
                    return;
                }

                // create photo
                const creation_date = new Date().toISOString().slice(0, 19).replace("T", " ");
                const create_response = await fetch("http://localhost:8000/rest/api/v1/photos.php?action=create", {
                    method: "POST",
                    body: JSON.stringify({
                        title: this.view.title_input.value,
                        description: this.view.description_photo.value,
                        file_directory: upload_result.targetPath,
                        import_date: creation_date,
                        messages_allowed: 0
                    })
                });
                if (create_response.status === 401) {
                    window.location.href = "../form/form.html";
                    return;
                }
                if(!create_response.ok) {
                    this.view.msg_box.textContent = "Erreur HTTP";
                    return;
                }   
                const create_result = await create_response.json();
                this.view.msg_box.textContent = create_result.message;
            } catch (error) {

                this.view.msg_box.textContent = "Erreur serveur";

                console.error(error);
            }

        });        
    }
}

