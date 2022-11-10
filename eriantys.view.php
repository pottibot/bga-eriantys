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
  
    require_once( APP_BASE_PATH."view/common/game.view.php" );
    
    class view_eriantys_eriantys extends game_view {
        function getGameName() {
            return "eriantys";
        }

    	function build_page($viewArgs) {

    	    // Get players & players number
            $players = $this->game->loadPlayersBasicInfos();
            $players_nbr = count( $players );


            // Display a string to be translated in all languages: 
            $this->tpl['ISLANDS_GROUPS'] = self::_("Islands groups");
            $this->tpl['ISLANDS_SIZE'] = self::_("Islands zoom");
            $this->tpl['ISLAND_INFLUENCE'] = self::_("Display island students");
            $this->tpl['INFLUENCE_DETECTOR'] = self::_("Influence detector");
            $this->tpl['OPPONENT_SCHOOLS'] = self::_("Display opponents school");
            $this->tpl['ASSISTANT_DRAWER'] = self::_("Display Assistants");
            $this->tpl['PIECES_ASPECT'] = self::_("Pieces aspect");
    	}
    }
  

