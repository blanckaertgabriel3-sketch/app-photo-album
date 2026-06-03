const form_validate_btn = document.getElementById("form_validate_btn");
const form_name = document.getElementById("form_name");
const form_password = document.getElementById("form_password");
const msg_box = document.getElementById("msg_box");
const go_register = document.getElementById("go_register");

localStorage.clear();

form_validate_btn.addEventListener("click", () => {
	login();
})
go_register.addEventListener("click", () => {
	document.location.href="../register/register.html";
})

async function login() {
	try {
		const response = await fetch (`http://localhost:8000/rest/api/v1/users.php?action=login`, {
			method: "POST",
  			credentials: "include",
			headers: {
			"Content-Type": "application/json"
			},
			body: JSON.stringify({
				name: form_name.value,
				password: form_password.value
			})
		});
		if(!response.ok) {
			msg_box.textContent = "Erreur HTTP";
			return;
		}
		const data = await response.json();
		if(data.success) {
			document.location.href="../home/home.html";
		}
		else {
			msg_box.textContent = data.message;
		}
	}
	catch (error) {
		msg_box.textContent = "Erreur Serveur";
		console.error(error);
	}
}