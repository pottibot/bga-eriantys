<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * eriantyspas implementation : © Pietro Luigi Porcedda <pietro.l.porcedda@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 */
  
  
class action_eriantyspas extends APP_GameAction {

   	public function __default() {

  	    if (self::isArg('notifwindow')) {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
  	    } else {
            $this->view = "eriantyspas_eriantyspas";
            self::trace( "Complete reinitialization of board game" );
        }
  	}

    public function playAssistant() {

        self::setAjaxMode();

        $n = self::getArg("n", AT_int, true);
        $this->game->playAssistant($n);

        self::ajaxResponse();
    }

    public function moveStudent() {

        self::setAjaxMode();

        $s = self::getArg("student", AT_alphanum, true);
        $p = self::getArg("place", AT_int, false, null); // if no arg given, assumed placed in school
        $this->game->moveStudent($s,$p);

        self::ajaxResponse();
    }

    public function moveMona() {

        self::setAjaxMode();

        $g = self::getArg("group", AT_int, true);
        $this->game->moveMona($g);

        self::ajaxResponse();
    }

    public function chooseCloudTile() {

        self::setAjaxMode();

        $c = self::getArg("cloud", AT_int, true);
        $this->game->chooseCloudTile($c);

        self::ajaxResponse();
    }
}