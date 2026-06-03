const logout_btn = document.getElementById("logout_btn");

logout_btn.addEventListener("click", async() => {
	try {
		const response = await fetch ("http://localhost:8000/rest/api/v1/logout.php", {
			method: "POST",
			credentials: "include"
		})
		if(!response.ok) {
			if(msg_box) {
				msg_box.textContent = "Erreur HTTP";
			}
			return;
		}
		document.location.href = "../form/form.html";
	}
	catch(error) {
		msg_box.textContent = "Erreur Serveur";
		console.error(error);
	}
})