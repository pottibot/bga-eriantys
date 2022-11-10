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

$charAbilities = ["char_1" => 51, "char_2" => 52, "char_4" => 53, "char_6" => 54, "char_8" => 55, "char_9" => 56, "char_10" => 57, "char_11" => 58];
 
$machinestates = array(

    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => 10)
    ),

// --- PLANNING PHASE --- //

    10 => array(
    		"name" => "playAssistant", // change all activplayer state names from verb to nouns (playAssistant -> assistantPhase, moveStudents -> studentsMovement)
    		"description" => clienttranslate('${actplayer} must play an Assistant card'),
    		"descriptionmyturn" => clienttranslate('${you} must play an Assistant card'),
    		"type" => "activeplayer",
            "args" => "argPlayAssistant",
    		"possibleactions" => array( "playAssistant"),
    		"transitions" => array("next" => 11, "zombiePass" => 99)
    ),

    11 => array(
        "name" => "nextPlayerPlanning",
        "type" => "game",
        "description" => clienttranslate('Resolving planning phase'),
        "action" => "stNextPlayerPlanning",
        "updateGameProgression" => true,
        "transitions" => array("nextTurn" => 10, "nextPhase" => 20)
    ),

    20 => array(
        "name" => "moveStudents",
        "description" => clienttranslate('${actplayer} must move one of his/her new students (${stud_count}/${stud_max})'),
        "descriptionmyturn" => clienttranslate('${you} must move one of your new students (${stud_count}/${stud_max})'),
        "type" => "activeplayer",
        "args" => "argMoveStudents",
        "possibleactions" => array("moveStudent","useCharacter"),
        "transitions" => array_merge(array("next" => 21, "useCharacter" => 60, "zombiePass" => 99),$charAbilities)
    ),

    21 => array(
        "name" => "moveAgain",
        "type" => "game",
        "action" => "stMoveAgain",
        "transitions" => array( "again" => 20, "next" => 30)
    ),

    30 => array(
        "name" => "moveMona",
        "description" => clienttranslate('${actplayer} must move Mother Nature'),
        "descriptionmyturn" => clienttranslate('${you} must move Mother Nature'),
        "type" => "activeplayer",
        "args" => "argMoveMona",
        "possibleactions" => array("moveMona","useCharacter"),
        "transitions" => array_merge(array("pickCloud" => 40, "useCharacter" => 60, "endTurn" => 41, "gameEnd" => 99, "zombiePass" => 99),$charAbilities)
    ),

    40 => array(
        "name" => "cloudTileDrafting",
        "description" => clienttranslate('${actplayer} must choose a Cloud tile'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Cloud tile'),
        "type" => "activeplayer",
        "args" => "argCloudTileDrafting",
        "possibleactions" => array("chooseCloudTile","useCharacter"),
        "transitions" => array_merge(array("endTurn" => 41, "useCharacter" => 60, "zombiePass" => 99),$charAbilities)
    ),

    41 => array(
        "name" => "nextPlayerAction",
        "type" => "game",
        "description" => clienttranslate('Resolving action phase'),
        "action" => "stNextPlayerAction",
        "updateGameProgression" => true,
        "transitions" => array( "nextPlayerAction" => 20, "nextRound" => 10, "gameEnd" => 99)
    ),

    50 => array(
        "name" => "useCharacterAbility",
        "type" => "game",
        "action" => "stUseCharacterAbility",
        "transitions" => array("char_1" => 20, "char_2" => 52, "char_4" => 53, "char_6" => 54, "char_8" => 55, "char_9" => 56, "char_10" => 20, "char_11" => 58)
    ),

    // CHAR 1: take student and place on island (then draw a new one)
    51 => array(
        "name" => "character1_ability",
        "description" => clienttranslate('${actplayer} must must move a Student from the Character card to an Island'),
        "descriptionmyturn" => clienttranslate('${you} must must move a Student from the Character card to an Island'),
        "type" => "activeplayer",
        "args" => "argMoveStudents",
        "possibleactions" => array("moveStudent"),
        "transitions" => array("endAbility" => 60, "zombiePass" => 99)
    ),

    // CHAR 2: resolve an island of choice
    52 => array(
        "name" => "character2_ability",
        "description" => clienttranslate('${actplayer} must choose an Island to resolve'),
        "descriptionmyturn" => clienttranslate('${you} must choose an Island to resolve'),
        "type" => "activeplayer",
        "args" => "argMoveMona",
        "possibleactions" => array("moveMona"),
        "transitions" => array("endAbility" => 60, "zombiePass" => 99)
    ),

    // CHAR 4: place a noEntry token on an island
    53 => array(
        "name" => "character4_ability",
        "description" => clienttranslate('${actplayer} must place a No Entry token on an Island'),
        "descriptionmyturn" => clienttranslate('${you} must place a No Entry token on an Island'),
        "type" => "activeplayer",
        "possibleactions" => array("placeNoEntry"),
        "transitions" => array("endAbility" => 60, "zombiePass" => 99)
    ),

    // CHAR 6: replace up to 3 students from school entrance with students from this card
    54 => array(
        "name" => "character6_ability",
        "description" => clienttranslate('${actplayer} may replace up to 3 Students from his/her School entrance with Students on the Character card'),
        "descriptionmyturn" => clienttranslate('${you} may replace up to 3 Students from your School entrance with Students on the Character card'),
        "type" => "activeplayer",
        "possibleactions" => array("replaceStudents"),
        "transitions" => array("endAbility" => 60, "zombiePass" => 99)
    ),

    // CHAR 8: choose a student color, students of that color won't add up to island influence this turn 
    55 => array(
        "name" => "character8_ability",
        "description" => clienttranslate('${actplayer} must choose a Student color'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Student color'),
        "type" => "activeplayer",
        "possibleactions" => array("pickStudentColor"),
        "transitions" => array("endAbility" => 60, "zombiePass" => 99)
    ),

    // CHAR 9: replace up to 2 students from dining hall with students in your School entrance
    56 => array(
        "name" => "character9_ability",
        "description" => clienttranslate('${actplayer} may replace up to 2 Students from his/her School entrance with Students in his/her School Dining Hall'),
        "descriptionmyturn" => clienttranslate('${you} may replace up to 2 Students from your School entrance with Students in your School Dining Hall'),
        "type" => "activeplayer",
        "possibleactions" => array("replaceStudents"),
        "transitions" => array("endAbility" => 60, "zombiePass" => 99)
    ),

    // CHAR 10: take student and place on dining hall (then draw a new one)
    57 => array(
        "name" => "character10_ability",
        "description" => clienttranslate('${actplayer} must must move a Student from the Character card to his/her School Dining Hall'),
        "descriptionmyturn" => clienttranslate('${you} must must move a Student from the Character card to your School Dining Hall'),
        "type" => "activeplayer",
        "args" => "argMoveStudents",
        "possibleactions" => array("moveStudent"),
        "transitions" => array("endAbility" => 60, "zombiePass" => 99)
    ),

    // CHAR 11: all players must return 3 students of chosen color from their respective School dining room to the students bag
    58 => array(
        "name" => "character11_ability",
        "description" => clienttranslate('${actplayer} must choose a Student color'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Student color'),
        "type" => "activeplayer",
        "possibleactions" => array("pickStudentColor"),
        "transitions" => array("endAbility" => 60, "zombiePass" => 99)
    ),

    60 => array(
        "name" => "endCharacterAbility",
        "type" => "game",
        "action" => "stEndCharacterAbility",
        "transitions" => array("moveStudents" => 20, "moveMona" => 30, "cloudTileDrafting" => 40)
    ),

// --- --- --- //
    
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



