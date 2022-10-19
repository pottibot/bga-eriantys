<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * eriantyspas implementation : © Pietro Luigi Porcedda <pietro.l.porcedda@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */
 
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
    		"transitions" => array("" => 11)
    ),

    11 => array(
        "name" => "nextPlayerPlanning",
        "type" => "game",
        "action" => "stNextPlayerPlanning",
        "transitions" => array( "nextTurn" => 10, "nextPhase" => 20 )
    ),

    20 => array(
        "name" => "moveStudents",
        "description" => clienttranslate('${actplayer} must move one of his/her new students (${stud_count}/${stud_max})'),
        "descriptionmyturn" => clienttranslate('${you} must move one of your new students (${stud_count}/${stud_max})'),
        "type" => "activeplayer",
        "args" => "argMoveStudents",
        "possibleactions" => array( "moveStudent"),
        "transitions" => array("" => 21)
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
        "possibleactions" => array( "moveMona"),
        "transitions" => array("pickCloud" => 40, "gameEnd" => 99)
    ),

    40 => array(
        "name" => "cloudTileDrafting",
        "description" => clienttranslate('${actplayer} must choose a Cloud tile'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Cloud tile'),
        "type" => "activeplayer",
        "args" => "argCloudTileDrafting",
        "possibleactions" => array( "chooseCloudTile"),
        "transitions" => array("" => 41)
    ),

    41 => array(
        "name" => "nextPlayerAction",
        "type" => "game",
        "action" => "stNextPlayerAction",
        "transitions" => array( "nextPlayerAction" => 20, "nextRound" => 10, "gameEnd" => 99)
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



