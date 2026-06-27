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
	createPostElement(file_directory, title, import_date, hashtags, description) {
		const post = document.createElement("div");
		post.classList.add("post");

		// Photo
		const img = document.createElement("img");
		img.src = file_directory;
		img.alt = "Img utilisateur";

		// ===== Informations =====
		const infos = document.createElement("div");
		infos.classList.add("post_infos");

		// Titre
		const titleContainer = document.createElement("div");
		titleContainer.classList.add("title_container");

		const titleLabel = document.createElement("strong");
		titleLabel.textContent = "Titre : ";

		const titleSpan = document.createElement("span");
		titleSpan.textContent = title;

		titleContainer.appendChild(titleLabel);
		titleContainer.appendChild(titleSpan);

		// Date
		const dateContainer = document.createElement("div");
		dateContainer.classList.add("date_container");

		const dateLabel = document.createElement("strong");
		dateLabel.textContent = "Date : ";

		const dateSpan = document.createElement("span");
		dateSpan.textContent = import_date;

		dateContainer.appendChild(dateLabel);
		dateContainer.appendChild(dateSpan);

		infos.appendChild(titleContainer);
		infos.appendChild(dateContainer);

		// ===== Hashtags =====
		const hashtagsContainer = document.createElement("div");
		hashtagsContainer.classList.add("hashtags_container");

		const hashtagsTitle = document.createElement("strong");
		hashtagsTitle.textContent = "Hashtags";

		const hashtagsUl = document.createElement("ul");
		hashtagsUl.classList.add("hashtags");

		if (hashtags && hashtags.length > 0) {
			hashtags.forEach(hashtag => {
				const li = document.createElement("li");
				li.textContent = `#${hashtag}`;
				hashtagsUl.appendChild(li);
			});
		}

		hashtagsContainer.appendChild(hashtagsTitle);
		hashtagsContainer.appendChild(hashtagsUl);

		// ===== Description =====
		const descriptionContainer = document.createElement("div");
		descriptionContainer.classList.add("description_container");

		const descriptionTitle = document.createElement("strong");
		descriptionTitle.textContent = "Description";

		const descriptionP = document.createElement("p");
		descriptionP.classList.add("description");
		descriptionP.textContent = description;

		descriptionContainer.appendChild(descriptionTitle);
		descriptionContainer.appendChild(descriptionP);

		// Lire la suite
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

		if (file_directory !== "") {
			post.appendChild(img);
		}
		post.appendChild(infos);

		if (hashtags && hashtags.length > 0) {
			post.appendChild(hashtagsContainer);
		}

		post.appendChild(descriptionContainer);
		post.appendChild(readMoreBtn);
		post.appendChild(interactionsUl);

		this.posts_container.appendChild(post);

		return post;
	}
}