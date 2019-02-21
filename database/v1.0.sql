CREATE TABLE `cb_labels` (
  `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `ascendants_id` json DEFAULT NULL,
  `slug` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='version:1.0';
