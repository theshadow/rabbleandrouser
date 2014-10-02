CREATE TABLE user (
  user_id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  username VARCHAR(10) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(128) NOT NULL
);

CREATE TABLE post (
  post_id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  author_id UNSIGNED BIGINT NOT NULL,
  title VARCHAR(48) NOT NULL,
  content VARCHAR(144) NOT NULL,
  created DATETIME NOT NULL
);

ALTER TABLE post
ADD CONSTRAINT fk_post_author_id_to_user_user_id
FOREIGN KEY (author_id)
REFERENCES user (user_id);

INSERT INTO user (
  username,
  email,
  password_hash
)
VALUES (
  'admin',
  'xander.guzman@xanderguzman.com',
  '$2y$11$R/MqJHPVGG1ZdH3vR620euQ0ARM.VMqycMZoC9zIms4Ox2xbTAP8W'
);