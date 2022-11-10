<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * eriantys implementation : © Pietro Luigi Porcedda <pietro.l.porcedda@gmail.com>
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

$this->studentsReference = ['green', 'red', 'yellow', 'pink', 'blue'];

$this->characters = [
    // (MOVE STUDENT) take student and place on island [setup: draw 4 stud on this card]
    1 => [
        'cost' => 1,
        'tooltip' => clienttranslate("Take 1 Student from this card and place it on an Island of your choice. Then, draw a new Student from the Bag and place it on this card."),
    ],

    // (MOVE MONA) resolve an island of choice
    2 => [
        'cost' => 3,
        'tooltip' => clienttranslate("Choose an Island and resolve the Island as if Mother Nature had ended her movement there. Mother Nature will still move and the Island where she ends her movement will also be resolved."),
    ],

    // [PASSIVE] (MOVE MONA) increase mona movement by 2
    3 => [
        'cost' => 1,
        'tooltip' => clienttranslate("You may move Mother Nature up to 2 additional Islands than is indicated by the Assistant card you’ve played."),
    ],

    // (INFLUENCE CALC) place no entry token on island, blocks resolving island influence one time [setup: place 4 noentry tokens on this card]
    4 => [
        'cost' => 2,
        'tooltip' => clienttranslate("Place a No Entry tile on an Island of your choice. The first time Mother Nature ends her movement there, put the No Entry tile back onto this card DO NOT calculate influence on that Island, or place any Towers."),
    ],

    // [PASSIVE] (INFLUENCE CALC) when resolving islands influence, tower don't add up to the total inf
    5 => [
        'cost' => 3,
        'tooltip' => clienttranslate("When resolving a Conquering on an Island, Towers do not count towards influence."),
    ],

    // (REPLACE STUDENT) replace up to 3 students from school entrance with students on this card [setup: draw 6 stud on this card]
    6 => [
        'cost' => 1,
        'tooltip' => clienttranslate("You may take up to 3 Students from this card and replace them with the same number of Students from your Entrance."),
    ],

    // [PASSIVE] (INFLUENCE CALC) +2 influence this turn
    7 => [
        'cost' => 2,
        'tooltip' => clienttranslate("During the influence calculation this turn, you count as having 2 more influence."),
    ],

    // (PICK STUDENT COLOR) student of chosen color don't add up to total influence this turn
    8 => [
        'cost' => 3,
        'tooltip' => clienttranslate("Choose a color of Student: during the influence calculation this turn, that color adds no influence."),
    ],

    // (REPLACE STUDENT) replace up to 2 students from dining room to school entrance 
    9 => [
        'cost' => 1,
        'tooltip' => clienttranslate("You may exchange up to 2 Students between your Entrance and your Dining Room."),
    ],

    // (MOVE STUDENT) move one student from this card to the dining room [setup: draw 4 stud on this card]
    10 => [
        'cost' => 2,
        'tooltip' => clienttranslate("Take 1 Student from this card and place it in your Dining Room. Then, draw a new Student from the Bag and place it on this card."),
    ],

    // (PICK STUDENT COLOR) ALL players return 3 students of the chosen color from the dining room to the students bag
    11 => [
        'cost' => 3,
        'tooltip' => clienttranslate("Choose a type of Student: every player (including yourself) must return 3 Students of that type from their Dining Room to the bag. If any player has fewer than 3 Students of that type, return as many Students as they have."),
    ],

    // [PASSIVE] (MOVE STUDENT) this turn you take control of professors even if you tie the number of students of that color with another player
    12 => [
        'cost' => 2,
        'tooltip' => clienttranslate("During this turn, you take control of any number of Professors even if you have the same number of Students as the player who currently controls them."),
    ],
];