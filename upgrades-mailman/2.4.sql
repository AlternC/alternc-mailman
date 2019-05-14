--
-- Structure de la table `mailman_account` pour la gestion des compte mailman
--

CREATE TABLE IF NOT EXISTS `mailman_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `mailman_action` enum('OK','CREATE','DELETE') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`,`username`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

