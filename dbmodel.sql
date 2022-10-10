
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- eriantyspas implementation : © Pietro Luigi Porcedda <pietro.l.porcedda@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

ALTER TABLE `player`
ADD `player_turn_position` TINYINT UNSIGNED NOT NULL,
ADD `player_alternative_color` VARCHAR(6) NOT NULL,
ADD `player_mona_steps` TINYINT UNSIGNED NOT NULL DEFAULT 0;


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
    `green_professor` BIT DEFAULT 0,
    `red_professor` BIT DEFAULT 0,
    `yellow_professor` BIT DEFAULT 0,
    `pink_professor` BIT DEFAULT 0,
    `blue_professor` BIT DEFAULT 0,
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

CREATE TABLE IF NOT EXISTS `player_assistants` (
    `player` INT(8) UNSIGNED NOT NULL,
    `1` BIT DEFAULT 1,
    `2` BIT DEFAULT 1,
    `3` BIT DEFAULT 1,
    `4` BIT DEFAULT 1,
    `5` BIT DEFAULT 1,
    `6` BIT DEFAULT 1,
    `7` BIT DEFAULT 1,
    `8` BIT DEFAULT 1,
    `9` BIT DEFAULT 1,
    `10` BIT DEFAULT 1,
    PRIMARY KEY (`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `played_assistants` (
    `player` INT(8) UNSIGNED NOT NULL,
    `assistant` TINYINT UNSIGNED,
    `old` BIT NOT NULL DEFAULT 0,
    PRIMARY KEY (`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `students_bag` (
    `students` VARCHAR(130)
);