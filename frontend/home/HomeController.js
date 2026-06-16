export default class HomeController {
	constructor(view) {
		this.view = view;
		this.getUser();
		
		// pop_up
		this.view.open_pop_up.addEventListener("click", () => {
			this.view.modal.style.display = "flex";
		});

		this.view.close_pop_up.addEventListener("click", () => {
			this.view.modal.style.display = "none";
		});
	}
	async getUser() {
		try {
			const response = await fetch(
				"http://localhost:8000/rest/api/v1/users.php?action=getUser",
				{
					method: "POST",
					credentials: "include"
				}
			);
			if (!response.ok) {
				throw new Error("Erreur HTTP");
				return;
			}

			const result = await response.json();

			msg_box.textContent = result.message;
			this.view.username_span.textContent = result.user.name;
		}
		catch (error) {
			msg_box.textContent = "Erreur Serveur";
			console.error(error);
		}
	}
}