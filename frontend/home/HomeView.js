export default class HomeView {
	constructor() {
		this.username_span = document.getElementById("username_span");
		this.posts_container = document.getElementById("posts_container");
		
		// pop_up
		this.open_pop_up = document.getElementById("open_pop_up");
		this.close_pop_up = document.getElementById("close_pop_up");
		this.modal = document.getElementById("modal");

		this.msg_box = document.getElementById("msg_box");

		// pop_up
		this.modal.style.display = "none";

	}
	createPostElement(imgSrc, title, creationDate, hashtags, description) {
		const post = document.createElement("div");
		post.classList.add("post");

		// Image
		const img = document.createElement("img");
		img.src = imgSrc;
		img.alt = "Img utilisateur";

		// Titre + date
		const titleDate = document.createElement("div");
		titleDate.classList.add("title_date");

		const titleP = document.createElement("p");
		titleP.classList.add("title");
		titleP.textContent = title;

		const separator = document.createElement("p");
		separator.textContent = " - ";

		const dateP = document.createElement("p");
		dateP.classList.add("creation_date");
		dateP.textContent = creationDate;

		titleDate.appendChild(titleP);
		titleDate.appendChild(separator);
		titleDate.appendChild(dateP);

		// Hashtags
		const hashtagsUl = document.createElement("ul");
		hashtagsUl.classList.add("hashtags");

		hashtags.forEach(hashtag => {
			const li = document.createElement("li");
			li.textContent = hashtag;
			hashtagsUl.appendChild(li);
		});

		// Description
		const descriptionP = document.createElement("p");
		descriptionP.classList.add("description");
		descriptionP.textContent = description;

		// Bouton lire la suite
		const readMoreBtn = document.createElement("button");
		readMoreBtn.classList.add("read_more");
		readMoreBtn.textContent = "Lire la suite";

		// Interactions
		const interactionsUl = document.createElement("ul");
		interactionsUl.classList.add("interactions_with_post");

		const interactions = [
			"fa-message",
			"fa-heart",
			"fa-share"
		];

		interactions.forEach(iconClass => {
			const li = document.createElement("li");
			const a = document.createElement("a");
			const icon = document.createElement("i");

			a.href = "#";

			icon.classList.add("fa-solid", iconClass);

			a.appendChild(icon);
			li.appendChild(a);
			interactionsUl.appendChild(li);
		});

		// Construction du post
		post.appendChild(img);
		post.appendChild(titleDate);
		post.appendChild(hashtagsUl);
		post.appendChild(descriptionP);
		post.appendChild(readMoreBtn);
		post.appendChild(interactionsUl);

		// Ajout au conteneur
		this.posts_container.appendChild(post);

		return post;
	}
}