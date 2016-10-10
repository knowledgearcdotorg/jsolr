CREATE TABLE IF NOT EXISTS `#__jsolr_dimensions` (
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL default '',
    `alias` VARCHAR(255) NOT NULL default '',
    `published` TINYINT(1) NOT NULL default '0',
    `checked_out` INTEGER UNSIGNED NOT NULL default '0',
    `checked_out_time` DATETIME NOT NULL default '0001-01-01 00:00:00',
    `ordering` INT(11) NOT NULL DEFAULT 0,
    `params` TEXT NOT NULL,
    `access` TINYINT(3) UNSIGNED NOT NULL default '0',
    `created` DATETIME NOT NULL default '0001-01-01 00:00:00',
    `created_by` INT(10) UNSIGNED NOT NULL default '0',
    `modified` DATETIME NOT NULL default '0001-01-01 00:00:00',
    `modified_by` INT(10) UNSIGNED NOT NULL default '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
