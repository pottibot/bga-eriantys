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


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );
require_once('modules/EriantysPoint.php');

class eriantyspas extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array( 
            "next_available_group" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "eriantyspas";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = array()) {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue('next_available_group', 0);
        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        
        $this->setupIslands();
       

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb($sql);
  
        $sql = "SELECT id, pos, x, y, `group`, angle FROM island ORDER BY pos ASC";
        $result['islands'] = self::getObjectListFromDb($sql);

        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

    function reset() {
        self::dbQuery("DELETE FROM island");
        self::setupIslands();
    }

    function setupIslands() {

        //
        //    11 0  1
        //   10      2
        //  9    x    3
        //   8       4
        //    7  6  5
        //


        $idPool = range(1,12);
        shuffle($idPool);

        $sql = "INSERT INTO island (pos, id, x, y, `group`) VALUES ";
        $values = array();
        for ($i=0; $i < 12; $i++) { 

            $id = array_pop($idPool);

            $the = -$i*M_PI/6 + M_PI_2;
            $ro = 300 * (($i%2 == 0)? 1 : (sqrt(3)/2));

            ['x'=>$x, 'y'=>$y] = EriantysPoint::createPolarVector($ro,$the)->coordinates();

            $values[] = "($i,$id,$x,$y,$i)";
        }

        $sql .= implode($values,',');
        self::DbQuery($sql);
    }

    // TODO
    // weight translation point with all group centers
    // implement group to group join
    // add safety throws
    // add anim
    function groupNewIslandAndReposition($island, $group) {

        // --- checks ---
        if (!is_null(self::getUniqueValueFromDb("SELECT `group` FROM island WHERE pos = $island"))) throw new BgaVisibleSystemException(_("Island $island is already part of a group"));
        if (self::getUniqueValueFromDb("SELECT `group` FROM island WHERE pos = $island") == $group) throw new BgaVisibleSystemException(_("Island $island is already in group $group"));

        // insert island in group
        self::dbQuery("UPDATE island SET `group` = $group WHERE pos = $island");

        // if group is size 1 (just the island just set) do nothing
        if (self::getUniqueValueFromDb("SELECT COUNT(`group`) FROM island WHERE `group` = $group") == 1) return; // first island to join group, do nothing

        // determine if attach side left or right
        // (consider pos number are circular, so 11 is next to 0)

        // --- calc attach point and translation ---
        $side = '';
        $groupBorderIsland = null;

        if (self::getUniqueValueFromDb("SELECT `group` FROM island WHERE pos =".($island-1 + 12) % 12) == $group) { // if pos at the left of island to join is of group
            $side = 'left';
            $groupBorderIsland = ($island-1 + 12) % 12;
            
        } else if (self::getUniqueValueFromDb("SELECT `group` FROM island WHERE pos =".($island+1 + 12) % 12) == $group) { // else if at the right of island to join is of group
            $side = 'right';
            $groupBorderIsland = ($island+1 + 12) % 12;

        } else throw new BgaVisibleSystemException(_("Island $island is not next to group $group"));


        $islandAttPoint = new EriantysPoint(...array_values(self::getObjectFromDb("SELECT x, y FROM island WHERE pos = $island")));
        $islandAttPoint = $islandAttPoint->translatePolar(
            50 * sin(M_PI/3),
            self::getAttachAngle($side,$island)
        );

        $groupAttPoint = new EriantysPoint(...array_values(self::getObjectFromDb("SELECT x, y FROM island WHERE pos = $groupBorderIsland")));
        $groupAttPoint = $groupAttPoint->translatePolar(
            50 * sin(M_PI/3),
            self::getAttachAngle(($side == 'left')?'right':'left',$groupBorderIsland)
        );

        $midPointDest = EriantysPoint::midpoint($islandAttPoint,$groupAttPoint);
        // --- --- ---

        // --- apply translation on new island and group ---

        // translate attached island
        ['x'=>$x, 'y'=>$y] =  self::getObjectFromDb("SELECT x, y FROM island WHERE pos = $island");
        $islandNewPos = new EriantysPoint($x,$y);
        $islandDisplacement = EriantysPoint::displacementVector($islandAttPoint,$midPointDest);

        ['x'=>$x, 'y'=>$y] = $islandNewPos->translate($islandDisplacement->x(), $islandDisplacement->y())->coordinates();
        self::dbQuery("UPDATE island SET x=$x, y=$y WHERE pos = $island");

        // translate group
        $groupDisplacement = EriantysPoint::displacementVector($groupAttPoint,$midPointDest);
        foreach (self::getCollectionFromDb("SELECT pos, x, y FROM island WHERE `group` = $group AND pos != $island") as $pos => $coords) {
            $componentNewPos = new EriantysPoint($coords['x'],$coords['y']);
            
            ['x'=>$x, 'y'=>$y] = $componentNewPos->translate($groupDisplacement->x(), $groupDisplacement->y())->coordinates();
            self::dbQuery("UPDATE island SET x=$x, y=$y WHERE pos = $pos");
        }
        // --- --- ---

        self::displayPoints([$islandAttPoint,$groupAttPoint,$midPointDest]);
    }

    // second version of join algo
    // first group joins second
    function joinGroups($g1id, $g2id) {

        // --- checks ---

        // check if g1 has at least 1 island
        $g1 = self::getCollectionFromDb("SELECT pos, x, y, `group` FROM island WHERE `group` = $g1id");
        if (empty($g1)) throw new BgaVisibleSystemException(_("Island group $g1id doesn't exist"));
        // if group is single function behaviour will be different
        $g1isIsland = false;
        if (count($g1) == 1) $g1isIsland = true;

        // check if g2 has at least 1 island
        $g2 = self::getCollectionFromDb("SELECT pos, x, y, `group` FROM island WHERE `group` = $g2id");
        if (empty($g2)) throw new BgaVisibleSystemException(_("Island group $g2id doesn't exist"));
        // if group is single function behaviour will be different
        $g2isIsland = false;
        if (count($g2) == 1) $g2isIsland = true;

        // check if g1 and g2 are next to each others
        $g1Extr = self::getGroupExtremes($g1id);
        $g2Extr = self::getGroupExtremes($g2id);

        if (($g1Extr['right']+1 +12)%12 == $g2Extr['left']) {
            // mem stuff for next phase
            $g1Extr = $g1[$g1Extr['right']];
            $g2Extr = $g2[$g2Extr['left']];

            $g1AttachSide = 'right';
            $g2AttachSide = 'left';

        } else if (($g1Extr['left']-1 +12)%12 == $g2Extr['right']) {
            $g1Extr = $g1[$g1Extr['left']];
            $g2Extr = $g2[$g2Extr['right']];

            $g1AttachSide = 'left';
            $g2AttachSide = 'right';

        } else throw new BgaVisibleSystemException(_("Island groups $g1id and $g2id are not next to each others"));

        // --- calculate join translation ---
        $g1AttachAngle = self::getAttachAngle($g1AttachSide,$g1Extr['pos']);
        $g1AttachPoint = new EriantysPoint($g1Extr['x'],$g1Extr['y']);
        $g1AttachPoint = $g1AttachPoint->translatePolar(50 * sin(M_PI/3), $g1AttachAngle);

        $g2AttachAngle = self::getAttachAngle($g2AttachSide,$g2Extr['pos']);
        $g2AttachPoint = new EriantysPoint($g2Extr['x'],$g2Extr['y']);
        $g2AttachPoint = $g2AttachPoint->translatePolar(50 * sin(M_PI/3), $g2AttachAngle);

        $w = count($g1) / (count($g1) + count($g2));
        $attachPoint = EriantysPoint::lerp($g1AttachPoint,$g2AttachPoint,1-$w); // inverse of weight as most numerous group should be moving less

        $g1translation = EriantysPoint::displacementVector($g1AttachPoint,$attachPoint);
        $g2translation = EriantysPoint::displacementVector($g2AttachPoint,$attachPoint);

        // --- apply and notify translation ---
        foreach ($g1 as $pos => $island) {
            $coords = new EriantysPoint($island['x'],$island['y']);
            ['x'=>$x, 'y'=>$y] = $coords->translate($g1translation->x(), $g1translation->y())->coordinates();

            self::dbQuery("UPDATE island SET x=$x, y=$y, `group`=$g2id WHERE pos=$pos"); // here apply group change too
        }

        foreach ($g2 as $pos => $island) {
            $coords = new EriantysPoint($island['x'],$island['y']);
            ['x'=>$x, 'y'=>$y] = $coords->translate($g2translation->x(), $g2translation->y())->coordinates();

            self::dbQuery("UPDATE island SET x=$x, y=$y WHERE pos=$pos");
        }

        self::displayPoints([$g1AttachPoint,$g2AttachPoint,$attachPoint]);

    }

    function getAttachAngle($side,$pos) {

        // starting from a certain pos and angle depending on side, increse angle by 2*30deg every two islands
        switch ($side) {
            case 'left':
                self::trace("// ISLAND $pos ATTACHING FROM LEFT");
                return 7*M_PI/6 + -2*M_PI/6 * floor(($pos+1)/2);  
                break;

            case 'right':
                self::trace("// ISLAND $pos ATTACHING FROM RIGHT");
                return 11*M_PI/6 + -2*M_PI/6 * floor($pos/2);
                break;

            default:
                throw new BgaVisibleSystemException(_("Invalid attach side"));
                break;
        }
    }

    function getGroupExtremes($gId) {
        $group = self::getObjectListFromDb("SELECT pos FROM island WHERE `group` = $gId", true);

        $l = count($group);

        $rightExtr = null;
        $leftExtr = null;

        for ($i=0; $i < $l; $i++) {
            $next = ($i+1 + $l)%$l; // wrap on group array
            if ($group[$next] != (($group[$i]+1)+12)%12) // wrap on islands array
                $rightExtr = $group[$i];
        }

        for ($i=$l-1; $i >= 0; $i--) {
            $next = ($i-1 + $l)%$l; // wrap on group array
            if ($group[$next] != (($group[$i]-1)+12)%12) // wrap on islands array
                $leftExtr = $group[$i];
        }

        self::dump("// CALCULATING EXTREMES FOR ISLAND GROUP $gId",$group);
        self::dump('// EXTREMES',['right'=>$rightExtr, 'left'=>$leftExtr]);

        return ['right'=>$rightExtr, 'left'=>$leftExtr];
    }

    function displayPoints($points) {

        foreach ($points as &$p) {
            $p = $p->coordinates();
        } unset($p);

        self::notifyAllPlayers('displayPoints','',['points' => $points]);
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in eriantyspas.action.php)
    */

    /*
    
    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' ); 
        
        $player_id = self::getActivePlayerId();
        
        // Add your game logic to play a card there 
        ...
        
        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );
          
    }
    
    */

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
