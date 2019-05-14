-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Client :  127.0.0.1
-- Généré le :  Mer 08 Mai 2019 à 12:34
-- Version du serveur :  10.3.13-MariaDB-1-log
-- Version de PHP :  7.3.3-1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données :  `alternc`
--

-- --------------------------------------------------------

--
-- Structure de la table `mailman`
--



CREATE TABLE IF NOT EXISTS `mailman` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `list` varchar(128) NOT NULL DEFAULT '',
  `domain` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '', -- password is not used in mailman3 because list don't have password anymore
  `owner` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `mailman_action` enum('OK','CREATE','DELETE','PASSWORD','GETURL','SETURL','DELETING','REGENERATE','REGENERATE-2') NOT NULL DEFAULT 'OK',
  `mailman_result` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `mailman_action` (`mailman_action`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Mailman mailing lists';


--
-- Structure de la table `mailman_account`
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

