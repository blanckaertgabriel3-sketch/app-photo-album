export default class User_Profile_Controller {
	constructor(view) {
		this.view = view;

		this.view.delete_account_btn.addEventListener("click", async () => {
			try {
				const res = await fetch("http://localhost:8000/rest/api/v1/users.php", {
					method: "DELETE"
				});

				if (res.status === 401) {
					window.location.href = "../form/form.html";
					return;
				}

				if (!res.ok) {
					this.view.msg_box.textContent = "Erreur HTTP";
					return;
				}

				const data = await res.json();

				this.view.msg_box.textContent = data.message;

				if (!data.success) {
					return;
				}
				console.log("Utilisateur supprimé");
				window.location.href = "../form/form.html";
			} catch (e) {
				this.view.msg_box.textContent = "Erreur serveur";
				console.error(e);
			}
		})
	}
}