
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- eriantyspas implementation : © Pietro Luigi Porcedda <pietro.l.porcedda@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

CREATE TABLE IF NOT EXISTS `island` (
    `pos` TINYINT UNSIGNED NOT NULL,
    `type` TINYINT UNSIGNED NOT NULL,
    `x` FLOAT(24) NOT NULL,
    `y` FLOAT(24) NOT NULL,
    `group` TINYINT UNSIGNED,
    PRIMARY KEY (`pos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- note:
-- type used to identify island graphic art
-- pos used to identify island pos in clockwise order. maybe change name to id?
-- attach side depends on pos

CREATE TABLE IF NOT EXISTS `island_influence` (
    `island_pos` TINYINT UNSIGNED NOT NULL,
    `green` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `red` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `yellow` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `pink` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `blue` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `white_tower` BIT NOT NULL DEFAULT 0,
    `black_tower` BIT NOT NULL DEFAULT 0,
    `grey_tower` BIT NOT NULL DEFAULT 0,
    PRIMARY KEY (`island_pos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `school` (
    `player` INT(8) UNSIGNED NOT NULL,
    `green` TINYINT UNSIGNED DEFAULT 0,
    `red` TINYINT UNSIGNED DEFAULT 0,
    `yellow` TINYINT UNSIGNED DEFAULT 0,
    `pink` TINYINT UNSIGNED DEFAULT 0,
    `blue` TINYINT UNSIGNED DEFAULT 0,
    `green_teacher` BIT DEFAULT 0,
    `red_teacher` BIT DEFAULT 0,
    `yellow_teacher` BIT DEFAULT 0,
    `pink_teacher` BIT DEFAULT 0,
    `blue_teacher` BIT DEFAULT 0,
    `towers` TINYINT UNSIGNED DEFAULT 8,
    `coins` TINYINT UNSIGNED DEFAULT 1,
    PRIMARY KEY (`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `school_entrance` (
    `player` INT(8) UNSIGNED NOT NULL,
    `green` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `red` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `yellow` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `pink` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `blue` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cloud` (
    `id` TINYINT UNSIGNED NOT NULL,
    `green` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `red` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `yellow` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `pink` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `blue` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;