const username_span = document.getElementById("username_span");
const msg_box = document.getElementById("msg_box");

async function getUser() {
	try {
		const response = await fetch ("http://localhost:8000/rest/api/v1/users.php?action=getUser", {
			credentials: "include"
		});
		if(!response.ok) {
			msg_box.textContent = "Erreur HTTP";
		}
		const result = await response.json();
		msg_box.textContent = result.message;
		username_span.textContent = result.user.name;
	}
	catch (error) {
		msg_box.textContent = "Erreur Serveur";
		console.error(error);
	}
}
getUser();