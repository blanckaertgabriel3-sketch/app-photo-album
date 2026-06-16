export default class FormController {
	constructor(form_view) {
		this.view = form_view;


		localStorage.clear();
		this.view.form_validate_btn.addEventListener("click", async () => {
			try {
				const response = await fetch (`http://localhost:8000/rest/api/v1/users.php?action=login`, {
					method: "POST",
					credentials: "include",
					headers: {
					"Content-Type": "application/json"
					},
					body: JSON.stringify({
						name: this.view.form_name.value,
						password: this.view.form_password.value
					})
				});
				if(!response.ok) {
					this.view.msg_box.textContent = "Erreur HTTP";
					return;
				}
				const data = await response.json();
				if(data.success) {
					document.location.href="../home/home.html";
				}
				else {
					this.view.msg_box.textContent = data.message;
				}
			}
			catch (error) {
				this.view.msg_box.textContent = "Erreur Serveur";
				console.error(error);
			}
		})
		this.view.go_register.addEventListener("click", () => {
			document.location.href="../register/register.html";
		})
	}
}
