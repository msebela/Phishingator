-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost
-- Vytvořeno: Pon 09. kvě 2022, 14:40
-- Verze serveru: 10.3.34-MariaDB-0+deb10u1
-- Verze PHP: 7.3.31-1~deb10u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `phishingator`
--
CREATE DATABASE IF NOT EXISTS `phishingator` DEFAULT CHARACTER SET utf8 COLLATE utf8_czech_ci;
USE `phishingator`;

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_campaigns`
--

CREATE TABLE `phg_campaigns` (
  `id_campaign` smallint(5) UNSIGNED NOT NULL,
  `id_by_user` mediumint(8) UNSIGNED NOT NULL,
  `id_email` smallint(5) UNSIGNED NOT NULL,
  `id_website` smallint(5) UNSIGNED NOT NULL,
  `id_onsubmit` tinyint(3) UNSIGNED NOT NULL,
  `id_ticket` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `time_send_since` time NOT NULL,
  `active_since` date NOT NULL,
  `active_to` date NOT NULL,
  `date_added` datetime NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_campaigns_onsubmit`
--

CREATE TABLE `phg_campaigns_onsubmit` (
  `id_onsubmit` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `visible` tinyint(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `phg_campaigns_onsubmit`
--

INSERT INTO `phg_campaigns_onsubmit` (`id_onsubmit`, `name`, `url`, `visible`) VALUES
(1, 'Bez reakce', NULL, 1),
(2, 'Informace, že jde o test', '/prakticky-phishingovy-test', 1),
(3, 'Zobrazit chybovou hlášku o nesprávných přihlašovacích údajích', NULL, 1),
(5, 'Nechat uživatele dvakrát zadat přihlašovací údaje a po druhém zadání přesměrovat na stránku s indiciemi', '/prakticky-phishingovy-test', 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_campaigns_recipients`
--

CREATE TABLE `phg_campaigns_recipients` (
  `id_recipient` int(10) UNSIGNED NOT NULL,
  `id_campaign` smallint(5) UNSIGNED NOT NULL,
  `id_user` mediumint(8) UNSIGNED NOT NULL,
  `id_sign_by_user` mediumint(8) UNSIGNED DEFAULT NULL,
  `sign_date` datetime DEFAULT NULL,
  `signed` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_captured_data`
--

CREATE TABLE `phg_captured_data` (
  `id_captured_data` int(10) UNSIGNED NOT NULL,
  `id_campaign` smallint(5) UNSIGNED NOT NULL,
  `id_user` mediumint(8) UNSIGNED NOT NULL,
  `id_action` tinyint(3) UNSIGNED NOT NULL,
  `used_email` varchar(256) COLLATE utf8_czech_ci NOT NULL,
  `visit_datetime` datetime DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `local_ip` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `browser_fingerprint` varchar(1024) COLLATE utf8_czech_ci DEFAULT NULL,
  `data_json` mediumtext COLLATE utf8_czech_ci DEFAULT NULL,
  `reported` tinyint(1) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_captured_data_actions`
--

CREATE TABLE `phg_captured_data_actions` (
  `id_action` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `hex_color` varchar(6) COLLATE utf8_czech_ci NOT NULL,
  `css_color_class` varchar(16) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `phg_captured_data_actions`
--

INSERT INTO `phg_captured_data_actions` (`id_action`, `name`, `hex_color`, `css_color_class`) VALUES
(1, 'bez reakce', '00c851', 'success'),
(2, 'návštěva stránky', '33b5e5', 'info'),
(3, 'zadání neplatných údajů', 'ffbb33', 'warning'),
(4, 'zadání platných údajů', 'ff4444', 'danger');

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_captured_data_end`
--

CREATE TABLE `phg_captured_data_end` (
  `id_captured_data` int(10) UNSIGNED NOT NULL,
  `id_campaign` smallint(5) UNSIGNED NOT NULL,
  `id_user` mediumint(8) UNSIGNED NOT NULL,
  `id_action` tinyint(3) UNSIGNED NOT NULL,
  `used_email` varchar(256) COLLATE utf8_czech_ci NOT NULL,
  `visit_datetime` datetime DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `local_ip` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `browser_fingerprint` varchar(1024) COLLATE utf8_czech_ci DEFAULT NULL,
  `data_json` mediumtext COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_emails`
--

CREATE TABLE `phg_emails` (
  `id_email` smallint(5) UNSIGNED NOT NULL,
  `id_by_user` mediumint(8) UNSIGNED NOT NULL,
  `name` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `sender_name` varchar(64) COLLATE utf8_czech_ci DEFAULT NULL,
  `sender_email` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `subject` varchar(256) COLLATE utf8_czech_ci NOT NULL,
  `body` text COLLATE utf8_czech_ci NOT NULL,
  `date_added` datetime NOT NULL,
  `hidden` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `visible` tinyint(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_emails_indications`
--

CREATE TABLE `phg_emails_indications` (
  `id_indication` int(10) UNSIGNED NOT NULL,
  `id_by_user` mediumint(8) UNSIGNED NOT NULL,
  `id_email` smallint(5) UNSIGNED NOT NULL,
  `expression` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `description` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `date_added` datetime NOT NULL,
  `visible` tinyint(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_sent_emails`
--

CREATE TABLE `phg_sent_emails` (
  `id_event` int(10) UNSIGNED NOT NULL,
  `id_campaign` smallint(5) UNSIGNED NOT NULL,
  `id_email` smallint(5) UNSIGNED NOT NULL,
  `id_user` mediumint(8) UNSIGNED NOT NULL,
  `date_sent` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_sent_notifications`
--

CREATE TABLE `phg_sent_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_campaign` smallint(5) UNSIGNED NOT NULL,
  `id_user` mediumint(8) UNSIGNED NOT NULL,
  `id_notification_type` tinyint(3) UNSIGNED NOT NULL,
  `date_sent` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_users`
--

CREATE TABLE `phg_users` (
  `id_user` mediumint(8) UNSIGNED NOT NULL,
  `id_by_user` mediumint(8) UNSIGNED DEFAULT NULL,
  `id_user_group` smallint(5) UNSIGNED NOT NULL,
  `url` varchar(6) COLLATE utf8_czech_ci NOT NULL,
  `username` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(256) COLLATE utf8_czech_ci NOT NULL,
  `recieve_email` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `email_limit` smallint(4) DEFAULT NULL,
  `date_added` datetime NOT NULL,
  `inactive` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `visible` tinyint(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_users_groups`
--

CREATE TABLE `phg_users_groups` (
  `id_user_group` smallint(5) UNSIGNED NOT NULL,
  `id_by_user` mediumint(8) UNSIGNED NOT NULL,
  `id_parent_group` smallint(5) UNSIGNED DEFAULT NULL,
  `role` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `description` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `emails_restrictions` varchar(255) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `ldap_groups` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `date_added` datetime NOT NULL,
  `visible` tinyint(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `phg_users_groups`
--

INSERT INTO `phg_users_groups` (`id_user_group`, `id_by_user`, `id_parent_group`, `role`, `name`, `description`, `emails_restrictions`, `ldap_groups`, `date_added`, `visible`) VALUES
(1, 1, NULL, 1, 'Administrátoři', '', '', '', '0000-00-00 00:00:00', 1),
(2, 1, NULL, 2, 'Správci testů', '', '', '', '0000-00-00 00:00:00', 1),
(3, 1, NULL, 3, 'Uživatelé', '', '', '', '0000-00-00 00:00:00', 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_users_login_log`
--

CREATE TABLE `phg_users_login_log` (
  `id_record` int(10) UNSIGNED NOT NULL,
  `id_user` mediumint(8) UNSIGNED NOT NULL,
  `login_datetime` datetime NOT NULL,
  `ip` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `local_ip` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_users_participation_log`
--

CREATE TABLE `phg_users_participation_log` (
  `id_record` int(10) UNSIGNED NOT NULL,
  `id_user` mediumint(8) UNSIGNED NOT NULL,
  `date_participation` datetime NOT NULL,
  `logged` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_users_roles`
--

CREATE TABLE `phg_users_roles` (
  `id_user_role` tinyint(3) UNSIGNED NOT NULL,
  `value` tinyint(1) UNSIGNED NOT NULL,
  `name` varchar(32) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `phg_users_roles`
--

INSERT INTO `phg_users_roles` (`id_user_role`, `value`, `name`) VALUES
(1, 0, 'Administrátor'),
(2, 1, 'Správce testů'),
(3, 2, 'Uživatel');

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_websites`
--

CREATE TABLE `phg_websites` (
  `id_website` smallint(5) UNSIGNED NOT NULL,
  `id_by_user` mediumint(8) UNSIGNED NOT NULL,
  `id_template` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `date_added` datetime NOT NULL,
  `active` tinyint(1) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_websites_preview`
--

CREATE TABLE `phg_websites_preview` (
  `id_preview` int(10) UNSIGNED NOT NULL,
  `id_website` smallint(5) UNSIGNED NOT NULL,
  `id_user` mediumint(8) UNSIGNED NOT NULL,
  `id_campaign` smallint(5) UNSIGNED DEFAULT NULL,
  `hash` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `active_since` datetime NOT NULL,
  `active_to` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `phg_websites_templates`
--

CREATE TABLE `phg_websites_templates` (
  `id_website_template` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `server_dir` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `visible` tinyint(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `phg_websites_templates`
--

INSERT INTO `phg_websites_templates` (`id_website_template`, `name`, `server_dir`, `visible`) VALUES
(1, 'Univerzální přihlašovací formulář', '/var/www/phishingator/templates/websites/1-universal-login/', 1);

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `phg_campaigns`
--
ALTER TABLE `phg_campaigns`
  ADD PRIMARY KEY (`id_campaign`),
  ADD KEY `id_by_user` (`id_by_user`),
  ADD KEY `id_email` (`id_email`),
  ADD KEY `id_website` (`id_website`),
  ADD KEY `id_onsubmit` (`id_onsubmit`);

--
-- Indexy pro tabulku `phg_campaigns_onsubmit`
--
ALTER TABLE `phg_campaigns_onsubmit`
  ADD PRIMARY KEY (`id_onsubmit`);

--
-- Indexy pro tabulku `phg_campaigns_recipients`
--
ALTER TABLE `phg_campaigns_recipients`
  ADD PRIMARY KEY (`id_recipient`),
  ADD KEY `id_campaign` (`id_campaign`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_sign_by_user` (`id_sign_by_user`);

--
-- Indexy pro tabulku `phg_captured_data`
--
ALTER TABLE `phg_captured_data`
  ADD PRIMARY KEY (`id_captured_data`),
  ADD KEY `id_campaign` (`id_campaign`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_action` (`id_action`);

--
-- Indexy pro tabulku `phg_captured_data_actions`
--
ALTER TABLE `phg_captured_data_actions`
  ADD PRIMARY KEY (`id_action`);

--
-- Indexy pro tabulku `phg_captured_data_end`
--
ALTER TABLE `phg_captured_data_end`
  ADD PRIMARY KEY (`id_captured_data`);

--
-- Indexy pro tabulku `phg_emails`
--
ALTER TABLE `phg_emails`
  ADD PRIMARY KEY (`id_email`),
  ADD KEY `id_by_user` (`id_by_user`);

--
-- Indexy pro tabulku `phg_emails_indications`
--
ALTER TABLE `phg_emails_indications`
  ADD PRIMARY KEY (`id_indication`),
  ADD KEY `id_by_user` (`id_by_user`),
  ADD KEY `id_email` (`id_email`);

--
-- Indexy pro tabulku `phg_sent_emails`
--
ALTER TABLE `phg_sent_emails`
  ADD PRIMARY KEY (`id_event`),
  ADD KEY `id_campaign` (`id_campaign`),
  ADD KEY `id_email` (`id_email`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexy pro tabulku `phg_sent_notifications`
--
ALTER TABLE `phg_sent_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_campaign` (`id_campaign`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexy pro tabulku `phg_users`
--
ALTER TABLE `phg_users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `url` (`url`),
  ADD KEY `id_by_user` (`id_by_user`),
  ADD KEY `id_user_group` (`id_user_group`);

--
-- Indexy pro tabulku `phg_users_groups`
--
ALTER TABLE `phg_users_groups`
  ADD PRIMARY KEY (`id_user_group`),
  ADD KEY `id_by_user` (`id_by_user`),
  ADD KEY `id_parent_group` (`id_parent_group`);

--
-- Indexy pro tabulku `phg_users_login_log`
--
ALTER TABLE `phg_users_login_log`
  ADD PRIMARY KEY (`id_record`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexy pro tabulku `phg_users_participation_log`
--
ALTER TABLE `phg_users_participation_log`
  ADD PRIMARY KEY (`id_record`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexy pro tabulku `phg_users_roles`
--
ALTER TABLE `phg_users_roles`
  ADD PRIMARY KEY (`id_user_role`);

--
-- Indexy pro tabulku `phg_websites`
--
ALTER TABLE `phg_websites`
  ADD PRIMARY KEY (`id_website`),
  ADD KEY `id_by_user` (`id_by_user`),
  ADD KEY `id_template` (`id_template`);

--
-- Indexy pro tabulku `phg_websites_preview`
--
ALTER TABLE `phg_websites_preview`
  ADD PRIMARY KEY (`id_preview`),
  ADD KEY `id_website` (`id_website`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_campaign` (`id_campaign`);

--
-- Indexy pro tabulku `phg_websites_templates`
--
ALTER TABLE `phg_websites_templates`
  ADD PRIMARY KEY (`id_website_template`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `phg_campaigns`
--
ALTER TABLE `phg_campaigns`
  MODIFY `id_campaign` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `phg_campaigns_onsubmit`
--
ALTER TABLE `phg_campaigns_onsubmit`
  MODIFY `id_onsubmit` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pro tabulku `phg_campaigns_recipients`
--
ALTER TABLE `phg_campaigns_recipients`
  MODIFY `id_recipient` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `phg_captured_data`
--
ALTER TABLE `phg_captured_data`
  MODIFY `id_captured_data` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `phg_captured_data_actions`
--
ALTER TABLE `phg_captured_data_actions`
  MODIFY `id_action` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pro tabulku `phg_captured_data_end`
--
ALTER TABLE `phg_captured_data_end`
  MODIFY `id_captured_data` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `phg_emails`
--
ALTER TABLE `phg_emails`
  MODIFY `id_email` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `phg_emails_indications`
--
ALTER TABLE `phg_emails_indications`
  MODIFY `id_indication` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `phg_sent_emails`
--
ALTER TABLE `phg_sent_emails`
  MODIFY `id_event` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `phg_sent_notifications`
--
ALTER TABLE `phg_sent_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `phg_users`
--
ALTER TABLE `phg_users`
  MODIFY `id_user` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `phg_users_groups`
--
ALTER TABLE `phg_users_groups`
  MODIFY `id_user_group` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pro tabulku `phg_users_login_log`
--
ALTER TABLE `phg_users_login_log`
  MODIFY `id_record` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `phg_users_participation_log`
--
ALTER TABLE `phg_users_participation_log`
  MODIFY `id_record` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `phg_users_roles`
--
ALTER TABLE `phg_users_roles`
  MODIFY `id_user_role` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `phg_websites`
--
ALTER TABLE `phg_websites`
  MODIFY `id_website` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `phg_websites_preview`
--
ALTER TABLE `phg_websites_preview`
  MODIFY `id_preview` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `phg_websites_templates`
--
ALTER TABLE `phg_websites_templates`
  MODIFY `id_website_template` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `phg_campaigns`
--
ALTER TABLE `phg_campaigns`
  ADD CONSTRAINT `phg_campaigns_ibfk_1` FOREIGN KEY (`id_by_user`) REFERENCES `phg_users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `phg_campaigns_ibfk_2` FOREIGN KEY (`id_email`) REFERENCES `phg_emails` (`id_email`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `phg_campaigns_ibfk_3` FOREIGN KEY (`id_website`) REFERENCES `phg_websites` (`id_website`),
  ADD CONSTRAINT `phg_campaigns_ibfk_4` FOREIGN KEY (`id_onsubmit`) REFERENCES `phg_campaigns_onsubmit` (`id_onsubmit`);

--
-- Omezení pro tabulku `phg_campaigns_recipients`
--
ALTER TABLE `phg_campaigns_recipients`
  ADD CONSTRAINT `phg_campaigns_recipients_ibfk_1` FOREIGN KEY (`id_campaign`) REFERENCES `phg_campaigns` (`id_campaign`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `phg_campaigns_recipients_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `phg_users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `phg_campaigns_recipients_ibfk_3` FOREIGN KEY (`id_sign_by_user`) REFERENCES `phg_users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `phg_captured_data`
--
ALTER TABLE `phg_captured_data`
  ADD CONSTRAINT `phg_captured_data_ibfk_1` FOREIGN KEY (`id_campaign`) REFERENCES `phg_campaigns` (`id_campaign`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `phg_captured_data_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `phg_users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `phg_emails`
--
ALTER TABLE `phg_emails`
  ADD CONSTRAINT `phg_emails_ibfk_1` FOREIGN KEY (`id_by_user`) REFERENCES `phg_users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `phg_emails_indications`
--
ALTER TABLE `phg_emails_indications`
  ADD CONSTRAINT `phg_emails_indications_ibfk_1` FOREIGN KEY (`id_by_user`) REFERENCES `phg_users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `phg_emails_indications_ibfk_2` FOREIGN KEY (`id_email`) REFERENCES `phg_emails` (`id_email`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `phg_sent_emails`
--
ALTER TABLE `phg_sent_emails`
  ADD CONSTRAINT `phg_sent_emails_ibfk_1` FOREIGN KEY (`id_campaign`) REFERENCES `phg_campaigns` (`id_campaign`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `phg_sent_emails_ibfk_2` FOREIGN KEY (`id_email`) REFERENCES `phg_emails` (`id_email`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `phg_sent_emails_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `phg_users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `phg_sent_notifications`
--
ALTER TABLE `phg_sent_notifications`
  ADD CONSTRAINT `phg_sent_notifications_ibfk_1` FOREIGN KEY (`id_campaign`) REFERENCES `phg_campaigns` (`id_campaign`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `phg_sent_notifications_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `phg_users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `phg_users`
--
ALTER TABLE `phg_users`
  ADD CONSTRAINT `phg_users_ibfk_1` FOREIGN KEY (`id_by_user`) REFERENCES `phg_users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `phg_users_ibfk_2` FOREIGN KEY (`id_user_group`) REFERENCES `phg_users_groups` (`id_user_group`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `phg_users_groups`
--
ALTER TABLE `phg_users_groups`
  ADD CONSTRAINT `phg_users_groups_ibfk_1` FOREIGN KEY (`id_by_user`) REFERENCES `phg_users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `phg_users_groups_ibfk_2` FOREIGN KEY (`id_parent_group`) REFERENCES `phg_users_groups` (`id_user_group`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `phg_users_login_log`
--
ALTER TABLE `phg_users_login_log`
  ADD CONSTRAINT `phg_users_login_log_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `phg_users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `phg_users_participation_log`
--
ALTER TABLE `phg_users_participation_log`
  ADD CONSTRAINT `phg_users_participation_log_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `phg_users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `phg_websites`
--
ALTER TABLE `phg_websites`
  ADD CONSTRAINT `phg_websites_ibfk_1` FOREIGN KEY (`id_by_user`) REFERENCES `phg_users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `phg_websites_ibfk_2` FOREIGN KEY (`id_template`) REFERENCES `phg_websites_templates` (`id_website_template`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `phg_websites_preview`
--
ALTER TABLE `phg_websites_preview`
  ADD CONSTRAINT `phg_websites_preview_ibfk_1` FOREIGN KEY (`id_website`) REFERENCES `phg_websites` (`id_website`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `phg_websites_preview_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `phg_users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `phg_websites_preview_ibfk_3` FOREIGN KEY (`id_campaign`) REFERENCES `phg_campaigns` (`id_campaign`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
