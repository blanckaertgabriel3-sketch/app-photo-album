const msg_box = document.getElementById("msg_box");
const create_btn = document.getElementById("create_btn");

const title_input = document.getElementById("title_input");

const photo_management = document.getElementById("photo_management");
const search_photo = document.getElementById("search_photo");
const result_image = document.getElementsByClassName("result_image")[0];

search_photo.addEventListener("input", async () => {
	const letters = search_photo.value;
	try {
		const search_response = await fetch ("http://localhost:8000/rest/api/v1/search_photo.php", {
			method: "POST",
			body: JSON.stringify({
				letters: letters
			})
		});
		if(!search_response.ok) {
			msg_box.textContent = "Erreur HTTP";
			return;
		}
		const search_result = await search_response.json();
		msg_box.textContent = search_result.message;
		if(search_result.success) {
			console.log("search_result", search_result.PHOTOS);
		}
	}
	catch (error) {
		msg_box.textContent = "Erreur Serveur";
		console.error(error);
	}
});

create_btn.addEventListener("click", async () => {
	const srcValue = "../../rest/api/v1/uploads/fraise2.jpg";
	create_resultImage(srcValue);
	try {
		const create_response = await fetch("http://localhost:8000/rest/api/v1/create_album.php", {
			method: "POST",
			body: JSON.stringify({
				title: title_input.value
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
function create_resultImage(srcValue) {
	const newImg = document.createElement("img");
	const attributeClass = document.createAttribute("class");
	const attributeAlt = document.createAttribute("alt");
	const attributeSrc = document.createAttribute("src");
	attributeClass.value = "result_image";
	attributeAlt.value = "Image crée par l'utilisateur";
	attributeSrc.value = srcValue;
	newImg.setAttributeNode(attributeClass);
	newImg.setAttributeNode(attributeAlt);
	newImg.setAttributeNode(attributeSrc);

	photo_management.appendChild(newImg);
}