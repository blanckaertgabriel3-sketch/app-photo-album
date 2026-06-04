const create_btn = document.getElementById("create_btn");
const msg_box = document.getElementById("msg_box");

const title_input = document.getElementById("title_input");
const file_input = document.getElementById("file_input");
const description_photo = document.getElementById("description_photo");


create_btn.addEventListener("click", async () => {

    const file = file_input.files[0];

    if (!file) {
        msg_box.textContent = "Veuillez sélectionner un fichier";
        return;
    }

    const formData = new FormData();

    formData.append("file", file);

    try {
        msg_box.textContent = "Upload en cours...";

        const upload_response = await fetch("http://localhost:8000/rest/api/v1/upload.php", {
            method: "POST",
            body: formData
        });
        const allowed_types = [
            "image/jpeg",
            "image/png",
            "image/webp",
            "image/gif"
        ];
        if (!allowed_types.includes(file.type)) {
            msg_box.textContent = "Type de fichier interdit";
            return;
        }
        if(!upload_response.ok) {
            msg_box.textContent = "Erreur HTTP";
            return;
        }
        const upload_result = await upload_response.json();
        msg_box.textContent = upload_result.message;
        if(!upload_result.success) {
            return;
        }
        const create_response = await fetch("http://localhost:8000/rest/api/v1/create_photo.php", {
            method: "POST",
            body: JSON.stringify({
                title: title_input.value,
                description: description_photo.value,
                file_directory: upload_result.targetPath
            })
        });
        if(!create_response.ok) {
            msg_box.textContent = "Erreur HTTP";
            return;
        }   
        const create_result = await create_response.json();
        msg_box.textContent = create_result.message;
    } catch (error) {

        msg_box.textContent = "Erreur serveur";

        console.error(error);
    }

});