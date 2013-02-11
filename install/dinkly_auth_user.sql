SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `dinkly_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `username` varchar(24) NOT NULL,
  `password` varchar(128) NOT NULL,
  `last_login_at` datetime NOT NULL,
  `login_count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;