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

$stats_type = array(

    // Statistics global to table
    "table" => array(
        
        "towers_placed" => array(
            "id"=> 11,
            "name" => totranslate("Towers placed"),
            "type" => "int"
        ),
        
        "islands_groups" => array(
            "id"=> 12,
            "name" => totranslate("Groups of islands formed"),
            "type" => "int"
        ),
        
        "assistants_played" => array(
            "id"=> 13,
            "name" => totranslate("Assistants played"),
            "type" => "int"
        ),
        
        "students_drawn" => array(
            "id"=> 14,
            "name" => totranslate("Students drawn"),
            "type" => "int"
        ),
        
        "mona_travel" => array(
            "id"=> 15,
            "name" => totranslate("Islands travelled by Mother Nature"),
            "type" => "int"
        ),
        
        "contested_group" => array(
            "id"=> 16,
            "name" => totranslate("Most contested islands group (highest total influence by all players/teams)"),
            "type" => "int"
        ),
        
        "contended_professor" => array(
            "id"=> 17,
            "name" => totranslate("Most contended professor"),
            "type" => "int"
        ),

        "powerful_student" => array(
            "id"=> 18,
            "name" => totranslate("Most powerful student (biggest population on the totality of the islands)"),
            "type" => "int"
        ),
    ),
    
    // Statistics existing for each player
    "player" => array(
        
        "final_towers" => array(
            "id"=> 10,
            "name" => totranslate("Final towers placed"),
            "type" => "int"
        ),
        
        "islands_conquered" => array(
            "id"=> 11,
            "name" => totranslate("Total conquered islands"),
            "type" => "int"
        ),
        
        "islands_lost" => array(
            "id"=> 12,
            "name" => totranslate("Islands lost"),
            "type" => "int"
        ),
        
        "islands_stolen" => array(
            "id"=> 13,
            "name" => totranslate("Islands stolen"),
            "type" => "int"
        ),
        
        "islands_groups" => array(
            "id"=> 21,
            "name" => totranslate("Islands groups controlled"),
            "type" => "int"
        ),

        "final_professors" => array(
            "id"=> 23,
            "name" => totranslate("Final professors"),
            "type" => "int"
        ),
        
        "professors_influenced" => array(
            "id"=> 14,
            "name" => totranslate("Total influenced professors"),
            "type" => "int"
        ),
        
        "professors_lost" => array(
            "id"=> 15,
            "name" => totranslate("Professors lost"),
            "type" => "int"
        ),
        
        "professors_stolen" => array(
            "id"=> 16,
            "name" => totranslate("Professors stolen"),
            "type" => "int"
        ),
        
        "islands_students" => array(
            "id"=> 17,
            "name" => totranslate("Students sent to the islands"),
            "type" => "int"
        ),
        
        "hall_students" => array(
            "id"=> 18,
            "name" => totranslate("Students in the Dining Hall"),
            "type" => "int"
        ),
        
        "favourite_student" => array(
            "id"=> 22,
            "name" => totranslate("Favourite student"),
            "type" => "int"
        ),
        
        "highest_island_influence" => array(
            "id"=> 19,
            "name" => totranslate("Highest islands group influence achieved"),
            "type" => "int"
        ),
        
        "characters_used" => array(
            "id"=> 20,
            "name" => totranslate("Characters ability activated"),
            "type" => "int"
        ),
    ),

    "value_labels" => array(
		17 => array( 
			0 => totranslate("Frog (green)"),
			1 => totranslate("Dragon (red)"), 
			2 => totranslate("Gnome (yellow)"), 
			3 => totranslate("Unicorn (blue)"), 
			4 => totranslate("Fairy (cyan)")
		),
        18 => array( 
			0 => totranslate("Frog (green)"),
			1 => totranslate("Dragon (red)"), 
			2 => totranslate("Gnome (yellow)"), 
			3 => totranslate("Unicorn (blue)"), 
			4 => totranslate("Fairy (cyan)")
		),
        22 => array( 
			0 => totranslate("Frog (green)"),
			1 => totranslate("Dragon (red)"), 
			2 => totranslate("Gnome (yellow)"), 
			3 => totranslate("Unicorn (blue)"), 
			4 => totranslate("Fairy (cyan)")
		),
	)

);
