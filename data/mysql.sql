CREATE TABLE `access_tokens` (
  `userId` bigint(20) UNSIGNED NOT NULL,
  `tokenId` varchar(80) NOT NULL,
  `isRevoked` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `expiry` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `secret` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `redirect_url` varchar(128) NOT NULL,
  `is_confidential` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `clients` (`id`, `secret`, `name`, `redirect_url`, `is_confidential`) VALUES
(1, '$2y$10$5m1jvrkBZDkCZDfyJrv0A.TlkETpwpWjzx29ZxzlolwGtBXaHOkJa', 'Super App', 'http://example.com/super-app', 1);

CREATE TABLE `refresh_tokens` (
  `userId` bigint(20) UNSIGNED NOT NULL,
  `tokenId` varchar(80) NOT NULL,
  `isRevoked` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `expiry` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `access` tinyint(1) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `books` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `author` varchar(64) NOT NULL,
  `title` varchar(64) NOT NULL,
  `year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `username`, `password`, `access`) VALUES
(1, 'stan', '$2y$10$8yjhRKQmDIXYl/pAbloBD.5vuGr/xkzCLeJCw5H5sycD8QbcDfZzC', 1);

INSERT INTO `books` (`id`, `author`, `title`, `year`) VALUES
(1, 'John Doe', 'Greatest book', 2010),
(2, 'John Doe', 'Book of books', 2010),
(3, 'Stanimir Stoyanov', 'OAuth2 with Phalcon', 2016);

ALTER TABLE `access_tokens`
  ADD UNIQUE KEY `tokenId` (`tokenId`),
  ADD KEY `userId` (`userId`);

ALTER TABLE `clients`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `refresh_tokens`
  ADD UNIQUE KEY `tokenId` (`tokenId`),
  ADD KEY `userId` (`userId`);

ALTER TABLE `users`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `access_tokens`
  ADD CONSTRAINT `access_tokens_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`);

ALTER TABLE `refresh_tokens`
  ADD CONSTRAINT `refresh_tokens_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`);

ALTER TABLE `books`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `books`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;