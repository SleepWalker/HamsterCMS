-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Окт 27 2012 г., 15:51
-- Версия сервера: 5.5.24
-- Версия PHP: 5.3.10-1ubuntu3.4

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `pwn-zone`
--

-- --------------------------------------------------------

--
-- Структура таблицы `shop`
--

CREATE TABLE IF NOT EXISTS `shop` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT,
  `code` INT UNSIGNED NOT NULL,
  `supplier_id` tinyint(2) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `edit_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `add_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `page_title` varchar(256) NOT NULL,
  `page_alias` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(19,2) unsigned NOT NULL,
  `cat_id` mediumint(4) unsigned NOT NULL,
  `brand_id` mediumint(4) unsigned NOT NULL,
  `product_name` varchar(256) NOT NULL,
  `rating` decimal(7,3) unsigned DEFAULT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `shop_extra` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `cat_id` (`cat_id`),
  KEY `user_id` (`user_id`),
  KEY `code` (`code`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `shop_brand`
--

CREATE TABLE IF NOT EXISTS `shop_brand` (
  `brand_id` mediumint(4) unsigned NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(128) NOT NULL,
  `brand_alias` varchar(128) NOT NULL,
  `brand_logo` varchar(128) NOT NULL,
  `description` TEXT,
  PRIMARY KEY (`brand_id`),
  UNIQUE KEY `brands_alias` (`brand_alias`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `shop_categorie`
--

CREATE TABLE IF NOT EXISTS `shop_categorie` (
  `cat_id` mediumint(4) unsigned NOT NULL AUTO_INCREMENT,
  `cat_alias` varchar(128) NOT NULL,
  `cat_name` varchar(128) NOT NULL,
  `cat_logo` varchar(128) NOT NULL,
  `cat_parent` mediumint(4) unsigned NOT NULL,
  `cat_sindex` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `cat_alias` (`cat_alias`),
  KEY `cat_parent` (`cat_parent`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `shop_char`
--

CREATE TABLE IF NOT EXISTS `shop_char` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prod_id` int(11) unsigned NOT NULL,
  `char_id` int(10) unsigned NOT NULL,
  `char_value` varchar(300) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prod_id_2` (`prod_id`,`char_id`),
  KEY `char_id` (`char_id`),
  KEY `prod_id` (`prod_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `shop_char_shema`
--

CREATE TABLE IF NOT EXISTS `shop_char_shema` (
  `char_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` mediumint(8) unsigned NOT NULL,
  `char_name` varchar(128) NOT NULL,
  `char_suff` text NOT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `sindex` int(11) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`char_id`),
  KEY `CATEGORY` (`cat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `shop_rating`
--

CREATE TABLE IF NOT EXISTS `shop_rating` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `prod_id` int(6) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `value` enum('1','2','3','4','5') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_id` (`prod_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `shop_supplier`
--

CREATE TABLE IF NOT EXISTS `shop_supplier` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `shop`
--
ALTER TABLE `shop`
  ADD CONSTRAINT `shop_ibfk_3` FOREIGN KEY (`cat_id`) REFERENCES `shop_categorie` (`cat_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shop_ibfk_4` FOREIGN KEY (`brand_id`) REFERENCES `shop_brand` (`brand_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shop_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shop_ibfk_6` FOREIGN KEY (`supplier_id`) REFERENCES `shop_supplier` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_char`
--
ALTER TABLE `shop_char`
  ADD CONSTRAINT `shop_char_ibfk_1` FOREIGN KEY (`prod_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shop_char_ibfk_2` FOREIGN KEY (`char_id`) REFERENCES `shop_char_shema` (`char_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_char_shema`
--
ALTER TABLE `shop_char_shema`
  ADD CONSTRAINT `shop_char_shema_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `shop_categorie` (`cat_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_rating`
--
ALTER TABLE `shop_rating`
  ADD CONSTRAINT `shop_rating_ibfk_3` FOREIGN KEY (`prod_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shop_rating_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
