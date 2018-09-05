--
-- Estrutura para tabela `cb_labels`
--

CREATE TABLE `cb_labels` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `uri` varchar(50) NOT NULL,
  `status` tinyint(4) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE VIEW cb_labels_complete AS
SELECT
    `labels`.`id`,
    `labels`.`title`,
    `labels`.`uri`,
    `labels`.`status`,
    `parent`.`id` as `parent_id`,
    `parent`.`title` as `parent_title`,
    `parent`.`uri` as `parent_uri`,
    `parent`.`status` as `parent_status`,
FROM `cb_labels` AS `labels`
INNER JOIN `cb_labels` AS `parent`
	ON `labels`.`parent` = `labels`.`id`
