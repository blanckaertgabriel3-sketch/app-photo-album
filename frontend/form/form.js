const form_validate_btn = document.getElementById("form_validate_btn");
const form_name = document.getElementById("form_name");
const form_password = document.getElementById("form_password");
const form_error_box = document.getElementById("form_error_box");
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
			headers: {
			"Content-Type": "application/json"
			},
			body: JSON.stringify({
				name: form_name.value,
				password: form_password.value
			})
		});
		if(!response.ok) {
			form_error_box.textContent = "HTTP error";
			return;
		}
		const data = await response.json();
		console.log("data : ", JSON.stringify(data));
		console.log("Data success : ", JSON.stringify(data.success))
		if(data.success) {
			console.log("Redirection");
			// document.location.href="../home/home.html";
		}else {
			console.log("No redirection");
		}
		form_error_box.textContent = data.message;
	}
	catch (error) {
		console.error(error);
		form_error_box.textContent = "Server error";
	}
}