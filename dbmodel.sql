
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- eriantyspas implementation : © Pietro Luigi Porcedda <pietro.l.porcedda@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

CREATE TABLE IF NOT EXISTS `island` (
    `pos` TINYINT UNSIGNED NOT NULL,
    `id` TINYINT UNSIGNED NOT NULL,
    `x` FLOAT(24) NOT NULL,
    `y` FLOAT(24) NOT NULL,
    `group` TINYINT UNSIGNED,
    `angle` INT UNSIGNED,
    PRIMARY KEY (`id`),
    UNIQUE (`pos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- note:
-- id used to identify island graphic art
-- pos used to identify island pos in clockwise order
-- attach side depends on pos