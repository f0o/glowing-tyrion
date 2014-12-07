-- update to add alerting tables
DROP TABLE IF EXISTS `alerts`;
CREATE TABLE IF NOT EXISTS `alerts` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `rule_id` int(11) NOT NULL,  `device_id` int(11) NOT NULL,  `state` int(11) NOT NULL,  `details` longblob NOT NULL,  `time_logged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,  `alerted` smallint(6) NOT NULL DEFAULT '0',  KEY `id` (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `alert_rules`;
CREATE TABLE IF NOT EXISTS `alert_rules` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `device_id` int(11) NOT NULL,  `rule` text NOT NULL,  `severity` enum('ok','warning','critical') NOT NULL,  `disabled` tinyint(1) NOT NULL,  PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `alert_templates`;
CREATE TABLE IF NOT EXISTS `alert_templates` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `rule_id` varchar(255) NOT NULL DEFAULT ',',  `template` longtext NOT NULL,  PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
