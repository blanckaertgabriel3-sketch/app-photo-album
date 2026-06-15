const logout_btn = document.getElementById("logout_btn");

logout_btn.addEventListener("click", async() => {
	try {
		if(!msg_box) {
			console.log("Il manque une balise dans la page html, pour l'affichage des messages de déconnexion.");
			return;
		}
		msg_box.textContent = "Déconnexion ...";

		const response = await fetch ("http://localhost:8000/api/v1/logout.php", {
			method: "POST",
			credentials: "include"
		})
		if(!response.ok) {
			msg_box.textContent = "Erreur HTTP";
			return;
		}
		
		document.location.href = "../form/form.html";
	}
	catch(error) {
		msg_box.textContent = "Erreur Serveur";
		console.error(error);
	}
})