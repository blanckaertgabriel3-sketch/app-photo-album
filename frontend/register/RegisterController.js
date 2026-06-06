export default class RegisterController {
	constructor(register_view) {
		this.view = register_view;

		this.view.go_connection.addEventListener("click", () => {
			document.location.href = "../form/form.html";
		});

		this.view.register_form_validate_btn.addEventListener("click", async () => {
			try {
				const response = await fetch(
					"http://localhost:8000/rest/api/v1/users.php?action=register",
					{
						method: "POST",
						headers: {
							"Content-Type": "application/json"
						},
						body: JSON.stringify({
							name: this.view.register_form_name.value,
							password: this.view.register_form_password.value
						})
					}
				);

				if (!response.ok) {
					this.view.msg_box.textContent = "Erreur HTTP";
					return;
				}

				const data = await response.json();
				this.view.msg_box.textContent = data.message;
			} catch (error) {
				console.error(error);
				this.view.msg_box.textContent = "Erreur Serveur";
			}
		});
	}
}