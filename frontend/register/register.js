const go_connection = document.getElementById("go_connection");
const register_form_name = document.getElementById("register_form_name");
const register_form_password = document.getElementById("register_form_password");
const register_form_validate_btn = document.getElementById("register_form_validate_btn");
const msg_box = document.getElementById("msg_box");


go_connection.addEventListener("click", () => {
	document.location.href="../form/form.html";
})
register_form_validate_btn.addEventListener("click", () => {
	register();
})

async function register() {
	try {
		const response = await fetch(`http://localhost:8000/rest/api/v1/users.php?action=register`, {
			method: "POST",
			headers: {
			"Content-Type": "application/json"
			},
			body: JSON.stringify({
				name: register_form_name.value,
				password: register_form_password.value
			})
		});
		if(!response.ok) {
			msg_box.textContent = "Erreur HTTP";
			return;
		}
		const data = await response.json();
		msg_box.textContent = data.message;
	}
	catch (error) {
		console.error(error);
		msg_box.textContent = "Erreur Serveur";
	}
}

