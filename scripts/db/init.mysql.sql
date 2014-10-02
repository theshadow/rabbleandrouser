CREATE TABLE IF NOT EXISTS `user` (
  `user_id` BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(30) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `website` VARCHAR(255),
  `password_hash` VARCHAR(128) NOT NULL
);

CREATE TABLE IF NOT EXISTS `post` (
  `post_id` BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `author_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(48) NOT NULL,
  `content` VARCHAR(144) NOT NULL,
  `created` BIGINT UNSIGNED NOT NULL
);

ALTER TABLE `post`
ADD CONSTRAINT `fk_post_author_id_to_user_user_id`
FOREIGN KEY (`author_id`)
REFERENCES `user` (`user_id`);

INSERT INTO `user` (
  `username`,
  `email`,
  `password_hash`
)
VALUES (
  'Xander Guzman',
  'xander.guzman@xanderguzman.com',
  '$2y$11$R/MqJHPVGG1ZdH3vR620euQ0ARM.VMqycMZoC9zIms4Ox2xbTAP8W'
);