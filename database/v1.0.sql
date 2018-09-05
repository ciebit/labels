--
-- Estrutura para tabela `cb_labels`
--

CREATE TABLE `cb_labels` (
  `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `ascendants_id` varchar(255) DEFAULT NULL,
  `uri` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB;
