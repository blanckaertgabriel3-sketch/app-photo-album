export default class view_album_management {
	constructor() {
		this.msg_box = document.getElementById("msg_box");
		this.create_btn = document.getElementById("create_btn");
		this.title_input = document.getElementById("title_input");
		this.photo_management = document.getElementById("photo_management");
		this.search_photo = document.getElementById("search_photo");
		this.newA;

	}
	createImgElement(srcValue) {
		this.newA = document.createElement("a");
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

		this.photo_management.appendChild(this.newA);
		this.newA.appendChild(newImg);
	}
	removeImgElement() {
		document.querySelectorAll(".result_image").forEach(img => img.remove());
	}
}