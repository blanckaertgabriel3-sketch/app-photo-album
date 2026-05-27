const go_connection = document.getElementById("go_connection");
const register_form_name = document.getElementById("register_form_name");
const register_form_password = document.getElementById("register_form_password");
const register_form_validate_btn = document.getElementById("register_form_validate_btn");
const register_form_error_box = document.getElementById("register_form_error_box");


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
			register_form_error_box.textContent = "HTTP error";
			return;
		}
		const data = await response.json();
		console.log("data : ", JSON.stringify(data));
		register_form_error_box.textContent = data.message;
	}
	catch (error) {
		console.error(error);
		register_form_error_box.textContent = "Server error";
	}
}

