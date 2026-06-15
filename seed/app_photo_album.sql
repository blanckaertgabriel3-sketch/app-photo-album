-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : db
-- Généré le : lun. 15 juin 2026 à 12:45
-- Version du serveur : 9.4.0
-- Version de PHP : 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `app_photo_album`
--

-- --------------------------------------------------------

--
-- Structure de la table `albums`
--

CREATE TABLE `albums` (
  `id` int NOT NULL,
  `owner_id` int NOT NULL,
  `title` varchar(64) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `creation_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `messages_allowed` tinyint(1) NOT NULL DEFAULT '1',
  `restriction` enum('private','public','restrict') NOT NULL DEFAULT 'private'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `albums_collaborators`
--

CREATE TABLE `albums_collaborators` (
  `album_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `albums_hashtags`
--

CREATE TABLE `albums_hashtags` (
  `album_id` int NOT NULL,
  `hashtag_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `albums_messages`
--

CREATE TABLE `albums_messages` (
  `album_id` int NOT NULL,
  `message_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `favorite_albums_users`
--

CREATE TABLE `favorite_albums_users` (
  `album_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `favorite_photos_users`
--

CREATE TABLE `favorite_photos_users` (
  `photo_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `group_albums`
--

CREATE TABLE `group_albums` (
  `group_id` int NOT NULL,
  `album_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `group_messages`
--

CREATE TABLE `group_messages` (
  `group_id` int NOT NULL,
  `message_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `group_users`
--

CREATE TABLE `group_users` (
  `group_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `hashtags`
--

CREATE TABLE `hashtags` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `author_id` int NOT NULL,
  `message` varchar(255) NOT NULL,
  `creation_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `invitation_format` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `photos`
--

CREATE TABLE `photos` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(64) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `file_directory` text NOT NULL,
  `import_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `messages_allowed` tinyint(1) NOT NULL DEFAULT '1',
  `restriction` enum('private','public','restrict') NOT NULL DEFAULT 'private'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `photos_albums`
--

CREATE TABLE `photos_albums` (
  `photo_id` int NOT NULL,
  `album_id` int NOT NULL,
  `display_order` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `photos_messages`
--

CREATE TABLE `photos_messages` (
  `photo_id` int NOT NULL,
  `message_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(64) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_groups`
--

CREATE TABLE `user_groups` (
  `id` int NOT NULL,
  `owner_id` int NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Index pour la table `albums_collaborators`
--
ALTER TABLE `albums_collaborators`
  ADD PRIMARY KEY (`album_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `albums_hashtags`
--
ALTER TABLE `albums_hashtags`
  ADD PRIMARY KEY (`album_id`,`hashtag_id`),
  ADD KEY `hashtag_id` (`hashtag_id`);

--
-- Index pour la table `albums_messages`
--
ALTER TABLE `albums_messages`
  ADD PRIMARY KEY (`album_id`,`message_id`),
  ADD KEY `message_id` (`message_id`);

--
-- Index pour la table `favorite_albums_users`
--
ALTER TABLE `favorite_albums_users`
  ADD PRIMARY KEY (`album_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `favorite_photos_users`
--
ALTER TABLE `favorite_photos_users`
  ADD PRIMARY KEY (`photo_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `group_albums`
--
ALTER TABLE `group_albums`
  ADD PRIMARY KEY (`group_id`,`album_id`),
  ADD KEY `album_id` (`album_id`);

--
-- Index pour la table `group_messages`
--
ALTER TABLE `group_messages`
  ADD PRIMARY KEY (`group_id`,`message_id`),
  ADD KEY `message_id` (`message_id`);

--
-- Index pour la table `group_users`
--
ALTER TABLE `group_users`
  ADD PRIMARY KEY (`group_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `hashtags`
--
ALTER TABLE `hashtags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Index pour la table `photos`
--
ALTER TABLE `photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `photos_albums`
--
ALTER TABLE `photos_albums`
  ADD PRIMARY KEY (`photo_id`,`album_id`),
  ADD KEY `album_id` (`album_id`);

--
-- Index pour la table `photos_messages`
--
ALTER TABLE `photos_messages`
  ADD PRIMARY KEY (`photo_id`,`message_id`),
  ADD KEY `message_id` (`message_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user_groups`
--
ALTER TABLE `user_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `hashtags`
--
ALTER TABLE `hashtags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `photos`
--
ALTER TABLE `photos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user_groups`
--
ALTER TABLE `user_groups`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `albums`
--
ALTER TABLE `albums`
  ADD CONSTRAINT `albums_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `albums_collaborators`
--
ALTER TABLE `albums_collaborators`
  ADD CONSTRAINT `albums_collaborators_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `albums_collaborators_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `albums_hashtags`
--
ALTER TABLE `albums_hashtags`
  ADD CONSTRAINT `albums_hashtags_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `albums_hashtags_ibfk_2` FOREIGN KEY (`hashtag_id`) REFERENCES `hashtags` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `albums_messages`
--
ALTER TABLE `albums_messages`
  ADD CONSTRAINT `albums_messages_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `albums_messages_ibfk_2` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `favorite_albums_users`
--
ALTER TABLE `favorite_albums_users`
  ADD CONSTRAINT `favorite_albums_users_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorite_albums_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `favorite_photos_users`
--
ALTER TABLE `favorite_photos_users`
  ADD CONSTRAINT `favorite_photos_users_ibfk_1` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorite_photos_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `group_albums`
--
ALTER TABLE `group_albums`
  ADD CONSTRAINT `group_albums_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_albums_ibfk_2` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `group_messages`
--
ALTER TABLE `group_messages`
  ADD CONSTRAINT `group_messages_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_messages_ibfk_2` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `group_users`
--
ALTER TABLE `group_users`
  ADD CONSTRAINT `group_users_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `photos`
--
ALTER TABLE `photos`
  ADD CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `photos_albums`
--
ALTER TABLE `photos_albums`
  ADD CONSTRAINT `photos_albums_ibfk_1` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `photos_albums_ibfk_2` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `photos_messages`
--
ALTER TABLE `photos_messages`
  ADD CONSTRAINT `photos_messages_ibfk_1` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `photos_messages_ibfk_2` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_groups`
--
ALTER TABLE `user_groups`
  ADD CONSTRAINT `user_groups_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
