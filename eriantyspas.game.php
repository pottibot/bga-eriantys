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

require_once(APP_GAMEMODULE_PATH.'module/table/table.game.php');
require_once('modules/EriantysPoint.php');

class eriantyspas extends Table {

/* ------------- */
/* --- SETUP --- */
/* ------------- */
#region

	function __construct() {

        parent::__construct();
        
        self::initGameStateLabels(array(
            "characters" => 100,
            "mother_nature_pos" => 10,
            "last_round" => 10
        ));        
	}
	
    protected function getGameName() {
        return "eriantyspas";
    }	

    protected function setupNewGame($players, $options = array()) {

        //$gameinfos = self::getGameinfos();

        $students_bag = str_repeat('G',26) . str_repeat('R',26) . str_repeat('Y',26) . str_repeat('P',26) . str_repeat('B',26);
        $students_bag = str_shuffle($students_bag);
        self::dbQuery("INSERT INTO students_bag (students) VALUES ('$students_bag')");
        
        $default_colors = ['000000', 'ffffff', '7b7b7b'];
        $fourPlayersColors = ['000000', 'ffffff', '000000', 'ffffff'];
        $fourPlayersTeams = [0,1,0,1];
 
        // INITIATE PLAYER TABLE
        $sql = "INSERT INTO player (player_id, player_color, player_alternative_color, player_canal, player_name, player_avatar) VALUES ";
        $sql2 = "INSERT INTO player_assistants (player) VALUES ";
        $sql3 = "INSERT INTO played_assistants (player) VALUES ";
        $values = array();
        $values2 = array();
        foreach($players as $player_id => $player) {

            $color = ((count($players) < 4)?array_shift($default_colors):array_shift($fourPlayersColors));
            $altCol = ($color == 'ffffff')? '000000' : 'ffffff';
            $values[] = "('".$player_id."','$color',"."'$altCol','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
            $values2[] = "($player_id)";
        }
        $sql .= implode($values,',');
        self::DbQuery($sql);

        $sql2 .= implode($values2,',');
        self::DbQuery($sql2);
        $sql3 .= implode($values2,',');
        self::DbQuery($sql3);

        $sql = "UPDATE player
                SET player_turn_position = player_no";
        self::DbQuery($sql);

        //self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        // --- GAME INITIAL STATE INIT --- //

        // INIT GLOBALS
        self::setGameStateInitialValue('characters', $this->gamestate->table_globals[100]);
        self::setGameStateInitialValue('mother_nature_pos', 0);
        self::setGameStateInitialValue('last_round', 0);
        
        
        // INIT STATISTICS
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // SETUP ISLANDS POSITIONING
        $typePool = [1,1,1,1,2,2,2,2,3,3,3,3]; // graphic style assignment (plains, fields, mountains)
        shuffle($typePool);

        $sql = "INSERT INTO island (pos, `type`, x, y, `group`) VALUES ";
        $values = array();
        for ($i=0; $i < 12; $i++) { 

            $type = array_pop($typePool);

            // generate center points around a circle
            $the = -$i*M_PI/6 + M_PI_2;
            $ro = 300; // (($i%2 == 0)? 1 : (sqrt(3)/2)) // to display them as a hex

            $center = EriantysPoint::createPolarVector($ro,$the);

            ['x'=>$x, 'y'=>$y] = $center->coordinates();

            $values[] = "($i,$type,$x,$y,$i)";
        }
        $sql .= implode($values,',');
        self::DbQuery($sql);

        // POPULATE ISLANDS WITH FIRST STUDENTS (1 each except first and middle island)
        $sql = "INSERT INTO island_influence (island_pos, green, red, yellow, pink, blue) VALUES ";
        $values = [];
        for ($i=0; $i < 12; $i++) {
            $students = array_fill(0,5,0);
            if ($i != 0 && $i != 6) $students[self::drawStudents()] = 1; // draft initial students for given island;
            $students = implode(',',$students);

            $values[] = "($i,$students)";
        }
        self::dbQuery($sql . implode(',',$values));

        // SETUP PLAYER SCHOOLS
        $sql = "INSERT INTO school (player, towers) VALUES ";
        $values = [];
        foreach ($players as $pId => $player) {
            $towers = (count($players) == 3)?6:(4 * ((count($players) == 2)?2:1));
            
            $values[] = "($pId, $towers)";
        }
        self::dbQuery($sql . implode(',',$values));

        // POPULATE SCHOOL ENTRANCE (7 each, 9 for 3 player games)
        $sql = "INSERT INTO school_entrance (player, green, red, yellow, pink, blue) VALUES ";
        $values = [];
        foreach($players as $player_id => $player) {
            $students = array_fill(0,5,0);

            $drawn = self::drawStudents((count($players) == 3)?9:7);

            foreach ($drawn as $s) {
                $students[$s] += 1;
            }

            $students = implode(',',$students);

            $values[] = "($player_id,$students)";
        }
        self::dbQuery($sql . implode(',',$values));

        // SETUP STUDENTS CLOUD
        $sql = "INSERT INTO cloud (id, green, red, yellow, pink, blue) VALUES ";
        $values = [];
        for ($i=0; $i < count($players); $i++) {
            $id = $i+1;
            $students = array_fill(0,5,0);
            $students = implode(',',$students);
            $values[] = "($id,$students)";
        }
        self::dbQuery($sql . implode(',',$values));
        $this->refillClouds(); // THEN FILL THEM FOR FIRST ROUND

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();
    }

    protected function getAllDatas() {

        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();

        $result['players'] = self::getCollectionFromDb("SELECT player_id id, player_score score, player_turn_position turnPos, player_mona_steps monaSteps, player_alternative_color alt_col FROM player ");

        $result['players_assistants'] = self::getCollectionFromDb("SELECT * FROM player_assistants");

        $result['played_assistants'] = self::getCollectionFromDb("SELECT player, assistant, old FROM played_assistants");

        $result['islands'] = self::getObjectListFromDb("SELECT pos, x, y, `group`, `type` FROM island ORDER BY pos ASC");

        $result['islandsGroups'] = self::getObjectListFromDb("SELECT DISTINCT `group` FROM island ORDER BY `group` ASC", true);

        $result['islands_influence'] = self::getObjectListFromDb("SELECT * FROM island_influence");

        $result['players_influence'] = self::getAllInfluenceData();

        $result['schools'] = self::getCollectionFromDb("SELECT * FROM school");
        $result['schools_entrance'] = self::getCollectionFromDb("SELECT * FROM school_entrance");

        $result['clouds'] = self::getObjectListFromDb("SELECT * FROM cloud");

        $result['mother_nature'] = self::getGameStateValue('mother_nature_pos');
        $result['characters'] = self::getGameStateValue('characters') == 1;

        return $result;
    }

    function getGameProgression() {
        return 0;
    }


#endregion

/* ----------------------- */
/* --- UTILITY METHODS --- */
/* ----------------------- */
#region 

    // -- DEBUGGING METHODS --

        function isLastRound() {
            self::dump('// IS LAST ROUND',self::getGameStateValue('last_round'));
        }

        function test() {
            /* $array = ['a','b','c'];
            self::dump("// RESULT",implode('',$array)); */
            $count = [
                'G' => 0,
                'R' => 0,
                'Y' => 0,
                'P' => 0,
                'B' => 0,
            ];

            for ($i=0; $i < 130; $i++) { 
                $s = self::drawStudents();

                $count[$s] += 1;

                if ($i == 9 || $i == 74 || $i == 129) {
                    self::dump("// RESULTS AT $i",[
                        'G' => $count['G'] / $i,
                        'R' => $count['R'] / $i,
                        'Y' => $count['Y'] / $i,
                        'P' => $count['P'] / $i,
                        'B' => $count['B'] / $i, 
                    ]);
                }
            }
        }

        // display an array of EriantysPoints on UI
        function displayPoints($points) {

            foreach ($points as &$p) {
                $p = $p->coordinates();
            } unset($p);

            self::notifyAllPlayers('displayPoints','',['points' => $points]);
        }

        // resets islands pos according to setupIsland()
        function reset() {
            self::dbQuery("DELETE FROM island");
            self::setupIslands();
        }

        // pupulates db with students and towers on the islands. missing population of player schools
        function populateDb() {

            // populate islands
            for ($i=0; $i < 12; $i++) { 

                $students = array_fill(0,5,0);

                $n = rand(2,6);

                for ($j=0; $j < $n; $j++) { 
                    $students[rand(0,4)] += 1;
                }

                $green = $students[0];
                $red = $students[1];
                $yellow = $students[2];
                $pink = $students[3];
                $blue = $students[4];

                self::dump("// STUDENTS",$students);

                $towers = [1,0,0,0];
                shuffle($towers);

                self::dump("// TOWERS",$towers);

                $black = array_shift($towers);
                $white = array_shift($towers);

                self::dbQuery("UPDATE island_influence SET green = $green, red = $red, yellow = $yellow, pink = $pink, blue = $blue, black_tower = $black, white_tower = $white WHERE island_pos = $i");
            }
        }

        // setups initial islands position // USE ONLY FOR DEBUG THEN REMOVE
        function setupIslands() {

            $typePool = [1,1,1,1,2,2,2,2,3,3,3,3];
            shuffle($typePool);

            $sql = "INSERT INTO island (pos, `type`, x, y, `group`) VALUES ";
            $values = array();
            for ($i=0; $i < 12; $i++) { 

                $type = array_pop($typePool);

                $the = -$i*M_PI/6 + M_PI_2;
                $ro = 300; // (($i%2 == 0)? 1 : (sqrt(3)/2)) // to display them as a hex

                $center = EriantysPoint::createPolarVector($ro,$the);

                ['x'=>$x, 'y'=>$y] = $center->coordinates();

                $values[] = "($i,$type,$x,$y,$i)";
            }

            $sql .= implode($values,',');
            self::DbQuery($sql);
        }

    // -- --- --

    // returns n students from the bag in the form of an array where the student is represented by a number from 0 to 4 (0 -> green, 1 -> red, ... , 4 -> blue)
    // so [0,3,2,3,4,4,1] is a draw of green, pink, yellow, pink, blue, blue, red
    function drawStudents($n=1) {

        $students_bag = self::getUniqueValueFromDb("SELECT students FROM students_bag");
        $students_left = strlen($students_bag);

        if ($students_left < $n) throw new BgaSystemException("Students bag doesn't have enough students ($n, available $students_left)");

        $students_bag = str_split($students_bag);
        shuffle($students_bag);

        $studentsSet = ['G','R','Y','P','B'];

        $ret = [];
        for ($i=0; $i < $n; $i++) {

            $s = array_shift($students_bag);

            $s = array_search($s,$studentsSet);

            /* switch ($s) {
                case 'G': $s = 'green';
                    break;
                case 'R': $s = 'red';
                    break;
                case 'Y': $s = 'yellow';
                    break;
                case 'P': $s = 'pink';
                    break;
                case 'B': $s = 'blue';
                    break;
            } */
            $ret[] = $s;            
        }

        $students_bag = implode('',$students_bag);

        self::dbQuery("UPDATE students_bag SET students = '$students_bag'");

        if (count($ret) == 1) $ret = $ret[0];
        return $ret;
    }

    // refills students clouds by drawing new students and sends notification to display it visually
    function refillClouds() {
        $allClouds = self::getObjectListFromDb("SELECT id FROM cloud", true);
        $retClouds = [];

        foreach ($allClouds as $cloud) {
            $students = array_fill(0,5,0);

            try {
                $drawn = self::drawStudents((count($allClouds) == 3)?4:3);
            } catch (Exception $e) {
                $drawn = [];

                self::setGameStateValue('last_round',1);
                self::notifyAllPlayers('lastRound',clienttranslate('Game end has been triggered: there are no more Students in the bag. <b>This is the last round</b>.'),[]);
            }
            
            if (!empty($drawn)) {
                foreach ($drawn as $s) {
                    $students[$s] += 1;
                }
    
                $green = $students[0];
                $red = $students[1];
                $yellow = $students[2];
                $pink = $students[3];
                $blue = $students[4];
    
                self::dbQuery("UPDATE cloud SET green = $green, red = $red, yellow = $yellow, pink = $pink, blue = $blue WHERE id = $cloud");    
            }

            $retClouds[$cloud] = $students;
        }

        self::notifyAllPlayers('refillClouds',"",[
            'clouds' => $retClouds,
            'students_each' => (count($allClouds) == 3)?4:3
        ]);
    }

    // joins two groups of islands and trigger UI animation
    function joinIslandsGroups($g1id, $g2id) {

        // --- safety checks ---
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

        // --- check if group extremes are next to each others and memorize them as they will define join vectors
        $g1Extr = self::getGroupExtremes($g1id);
        $g2Extr = self::getGroupExtremes($g2id);

        if (($g1Extr['right']+1 +12)%12 == $g2Extr['left']) {
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
        $g1AttachPoint = $g1AttachPoint->translatePolar(75 * sin(M_PI/3), $g1AttachAngle);

        $g2AttachAngle = self::getAttachAngle($g2AttachSide,$g2Extr['pos']);
        $g2AttachPoint = new EriantysPoint($g2Extr['x'],$g2Extr['y']);
        $g2AttachPoint = $g2AttachPoint->translatePolar(75 * sin(M_PI/3), $g2AttachAngle);

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

        /* self::notifyAllPlayers('displayPoints','',[
            'points' => [$g1AttachPoint->coordinates(), $g2AttachPoint->coordinates(), $attachPoint->coordinates()]
        ]); */

        $id = self::getActivePlayerId();
        self::notifyAllPlayers('joinIslandsGroups',clienttranslate('${player_name} unifies two groups of islands'),[
            'player_id' => $id,
            'player_name' => self::getActivePlayerName($id),
            'groups' => [
                'g1' => ['id' => $g1id, 'translation' => $g1translation->coordinates()],
                'g2' => ['id' => $g2id, 'translation' => $g2translation->coordinates()]
            ],
            'groupTo' => $g2id,
            'islandsCount' => count($g1) + count($g2),
            'influence_data' => self::getAllInfluenceData()
        ]);
    }

    // > used by joinIslandsGroups
    // get angle from which every hex should joing depending from side (left/right) and island pos
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

    // > used by joinIslandsGroups
    // get group side extremes by parsing array of positions and checking gaps on clock array
    function getGroupExtremes($gId) {
        $group = self::getObjectListFromDb("SELECT pos FROM island WHERE `group` = $gId ORDER BY pos ASC", true);

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

        return ['right'=>$rightExtr, 'left'=>$leftExtr];
    }

    // returns mid island in group, if islands count is even, mid is rounded to floor
    function getGroupMid($gId) {
        $islands = self::getGroupIslands($gId);

        return $islands[floor(count($islands)/2)];
    }

    // [used for factions] return color name given value (ffffff -> white)
    function getColorName($colorVal) {
        switch ($colorVal) {
            case '000000': return 'black';
                break;

            case 'ffffff': return 'white';
                break;

            case '7b7b7b': return 'grey';
                break;
        }
    }

    // [used for students and professors]
    function getStudentProfessorTranslation($color,$student=true) {
        switch ($color) {
            case 'green': return (($student)? clienttranslate('green Student') : clienttranslate('green Professor'));
            case 'red': return (($student)? clienttranslate('red Student') : clienttranslate('red Professor'));
            case 'yellow': return (($student)? clienttranslate('yellow Student') : clienttranslate('yellow Professor'));
            case 'pink': return (($student)? clienttranslate('pink Student') : clienttranslate('pink Professor'));
            case 'blue': return (($student)? clienttranslate('blue Student') : clienttranslate('blue Professor'));
        }
    }

    function getPlayerColorNameById($id) {
        return self::getColorName(self::getPlayerColorById($id));
    }

    // used for translation a couple of times
    function getTeamFullName($teamCol) {
        switch ($teamCol) {
            case '000000': return clienttranslate("The Black Team");
                break;

            case 'ffffff': return clienttranslate("The White Team");
                break;

            case '7b7b7b': throw new BgaVisibleSystemException("Team with this color shouldn't exist");
                break;
        }
    }

    // return ids of the current groups of islands
    function getAllIslandsGroups() {
        return self::getObjectListFromDb("SELECT `group` FROM island GROUP BY `group` ORDER BY `group` ASC",true);
    }

    function getAllFactions() {
        return self::getObjectListFromDb("SELECT player_color FROM player GROUP BY player_color",true);
    }

    // return pos id of each islands in a given group
    function getGroupIslands($group) {
        return self::getObjectListFromDb("SELECT pos FROM island WHERE `group` = $group",true);
    }

    // calculates the total amount of influence for a player OR team, given the respective color,
    function getInfluenceOnIslandGroup($islandsGroup, $teamCol) {

        $totinf = 0;

        // get professors controlled by player/team
        $sql = "SELECT sum(green_professor) green, sum(red_professor) red, sum(yellow_professor) yellow, sum(pink_professor) pink, sum(blue_professor) blue
                FROM school s JOIN player p on s.player = p.player_id
                WHERE p.player_color = '$teamCol'
                GROUP by p.player_color";
        $professors = self::getObjectFromDb($sql);

        // get islands in group
        $groupIslands = self::getGroupIslands($islandsGroup);
        foreach ($groupIslands as $island) {

            // get island influence
            $inf = self::getObjectFromDb("SELECT green, red, yellow, pink, blue FROM island_influence WHERE island_pos = $island");
            
            // if team/player has professor of color add the amount of student of that color in the island to total team/player influence
            foreach ($inf as $col => $amt) {
                if ($professors[$col]) $totinf += $amt;
            }
        }

        // if team/player controls islands group, add as much influence to the total as there are islands in the group (1 controlled island = 1 tower = +1 influence)
        if (self::controlsIslandGroup($islandsGroup,$teamCol)) $totinf += count($groupIslands);

        self::dump("// INFLUENCE ON ISLAND GROUP $islandsGroup FOR TEAM $teamCol",$totinf);
        return $totinf;
    }

    // checks if team of color controls a group of islands (has towers of respective color on each islands of the group)
    // warning: does not check for unexpected exceptions such as a group of islands not having a consistent presence of towers of one color
    function controlsIslandGroup($islandsGroup, $teamCol) {

        $colName = self::getColorName($teamCol);

        $sql = "SELECT inf.".$colName."_tower
                FROM island_influence inf JOIN island i ON inf.island_pos = i.pos
                WHERE i.group = $islandsGroup";
        $islands = self::getObjectListFromDb($sql, true);

        foreach ($islands as $hasTower) {
            if (!$hasTower) return false;
        }

        return true;
    }

    // resolve any islands group influence and notif place tower if owner changed
    function resolveIslandGroupInfluence($islandsGroup) {

        self::trace("// RESOLVING ISLAND $islandsGroup INFLUENCE");

        // get teams/players colors (to handle both 2/3 and 4 players games)
        $teams = self::getAllFactions();

        $teamsInf = [];
        $ownerTeam = null;

        foreach ($teams as $tCol) {
            // mem influence for all group island for each team, indexed by team color value
            $teamsInf[$tCol] = self::getInfluenceOnIslandGroup($islandsGroup,$tCol);

            // mem previous island group owner, if present
            if (self::controlsIslandGroup($islandsGroup,$tCol)) {
                $ownerTeam = $tCol;
            }
        }

        self::dump("// island factions influence",$teamsInf);
        self::dump("// owner faction",$ownerTeam);

        // get max influence, check if unique in array (team/player has most influence and not tied, thus controls islands group)
        // if not unique, no influence is greater then the others, islands group owner doesn't change. return
        $maxInf = max($teamsInf);
        if (1 === count(array_keys($teamsInf, $maxInf))) {
            // maxInf is unique

            // get winning team color
            $winningTeam = array_search($maxInf,$teamsInf);

            self::trace("// WINNING TEAM: $winningTeam");

            // change of power is effective only if previous owner is not the winning team
            if ($winningTeam != $ownerTeam) {

                self::trace("// CHANGE OF POWER EFFECTIVE");

                // change notif log depending from change of power type (if group had previous owner or not)               
                $log = clienttranslate('${player_name} takes control of an island');

                $placing_player = null;
                $owner_player = null;

                if (!is_null($ownerTeam)) {
                    $log = clienttranslate('${player_name} takes control of an island, taking it away from ${player_name2}');
                    
                    // if had owner, then remove all his towers from each island in the group
                    $colName = self::getColorName($ownerTeam);
                    foreach (self::getGroupIslands($islandsGroup) as $island) {
                        if (self::getPlayersNumber() == 4) {
                            $owner_player = self::getObjectListFromDb("SELECT s.player FROM school s JOIN player p ON s.player = p.player_id WHERE p.player_color = '$ownerTeam' AND towers < 4 ",true)[0];
                        } else $owner_player = self::getUniqueValueFromDb("SELECT player_id FROM player WHERE player_color = '$ownerTeam'");

                        self::dbQuery("UPDATE island_influence SET ".$colName."_tower = 0 WHERE island_pos = $island");
                        self::dbQuery("UPDATE school SET towers = towers+1 WHERE player = $owner_player");
                        self::dbQuery("UPDATE player SET player_score = player_score-1 WHERE player_color = '$ownerTeam'");

                        self::notifyAllPlayers('moveTower','', array(
                            'island' => $island,
                            'place' => false,
                            'color' => self::getColorName($ownerTeam),
                            'player' => $owner_player,
                            'influence_data' => self::getAllInfluenceData()
                            ) 
                        );
                    }
                }

                // place winning team towers on conquered islands group
                $colName = self::getColorName($winningTeam);
                foreach (self::getGroupIslands($islandsGroup) as $island) {

                    if (self::getPlayersNumber() == 4) {
                        $id = self::getActivePlayerId();
                        if (self::getUniqueValueFromDb("SELECT s.towers FROM school s JOIN player p ON s.player = p.player_id WHERE p.player_id = $id AND p.player_color = '$winningTeam'") > 0 ) $placing_player = $id;
                        else $placing_player = self::getObjectListFromDb("SELECT s.player FROM school s JOIN player p ON s.player = p.player_id WHERE p.player_color = '$winningTeam' AND s.towers > 0 ",true)[0];
                    } else $placing_player = self::getUniqueValueFromDb("SELECT player_id FROM player WHERE player_color = '$winningTeam'");

                    self::dbQuery("UPDATE island_influence SET ".$colName."_tower = 1 WHERE island_pos = $island");
                    self::dbQuery("UPDATE school SET towers = towers-1 WHERE player = $placing_player");
                    self::dbQuery("UPDATE player SET player_score = player_score+1 WHERE player_color = '$winningTeam'");

                    self::notifyAllPlayers('moveTower','', array(
                        'island' => $island,
                        'place' => true,
                        'color' => self::getColorName($winningTeam),
                        'player' => $placing_player,
                        'influence_data' => self::getAllInfluenceData()
                        ) 
                    );
                }

                // set owner entity name if present (for notif log)
                // TODO: make it better using $placing_player and $owner_player
                $player_name2 = null;
                if (!is_null($ownerTeam)) {
                    if (self::getPlayersNumber() == 4) {
                        $player_name2 = self::getTeamFullName($ownerTeam);
                    } else {
                        $player_name2 = self::getPlayerNameById(self::getUniqueValueFromDb("SELECT player_id FROM player WHERE player_color = '$ownerTeam'"));
                    }
                }

                // send notif
                self::notifyAllPlayers('placeTower', $log, array(
                    'player_id' => self::getActivePlayerId(),
                    'player_name' => self::getActivePlayerName(),
                    'island' => $island,
                    'ownerTeam' => $ownerTeam,
                    'player_name2' => $player_name2
                    ) 
                );

                //IF GROUP OWNER CHANGED, CHECK FOR MERGING ADJACENTS GROUPS AND VICTORY CONDITIONS
                self::trace("//CHECK FOR MERGING GROUPS");

                $allgroups = self::getAllIslandsGroups();

                $thisgroup = array_search($islandsGroup,$allgroups);
                $groupstot = count($allgroups);

                $prevGroup = $allgroups[($thisgroup - 1 + $groupstot) % $groupstot];
                $nextGroup = $allgroups[($thisgroup + 1 + $groupstot) % $groupstot];

                self::trace("// PREV GROUP $prevGroup");
                self::trace("// NEXT GROUP $nextGroup");

                if (self::controlsIslandGroup($prevGroup,$winningTeam)) {
                    self::trace("// MERGING WITH PREV");
                    self::joinIslandsGroups($prevGroup,$islandsGroup);
                }

                if (self::controlsIslandGroup($nextGroup,$winningTeam)) {
                    self::trace("// MERGING WITH NEXT");
                    self::joinIslandsGroups($nextGroup,$islandsGroup);
                }

                self::checkGameEndCondition();
            }
        }
    }

    // returns influence for each faction on each islands. array indexed by island group with field $influence, indexed by faction color and field $mid containing island id on where to display data
    function getAllInfluenceData() {

        $ret = [];
        foreach (self::getAllIslandsGroups() as $g) {
            $ret[$g]['mid'] = self::getGroupMid($g);
            foreach (self::getAllFactions() as $f) {
                $ret[$g]['influence'][$f] = self::getInfluenceOnIslandGroup($g,$f);

            }
        }

        return $ret;
    }

    // check immediate game end conditions
    // player/teams totals 8 towers placed (/6 towers placed for 3-players games) -> ends immediatly, that player/team wins
    // there are only 3 groups of islands -> ends immediatly, player/team with most towers placed win, in case of tie: most professors 
    function checkGameEndCondition() {

        $col = self::getPlayerColorById(self::getActivePlayerId());
        $sql = "SELECT p.player_color col, SUM(towers) towers
                FROM school s JOIN player p on s.player = p.player_id
                GROUP BY p.player_color";
        $factionsTotTowers = self::getObjectListFromDb($sql);
        $winner = array_filter($factionsTotTowers, function($f) { return $f['towers'] == 0; });
        if (!empty($winner)) {
            $winnerFaction = $winner[0]['col'];

            if (self::getPlayersNumber() == 4) {
                $winner = self::getTeamFullName($winnerFaction);
                $log = clienttranslate('${player_name} place their last tower and win the game');
            } else {
                $winner = self::getUniqueValueFromDb("SELECT player_name FROM player WHERE player_color = $winnerFaction");
                $log = clienttranslate('${player_name} places his/her last tower and wins the game');
            }

            self::notifyAllPlayers('gameEnd',$log,[
                'player_name' => $winner
            ]);

            $this->gamestate->nextState('gameEnd');
            
        } else if (count(self::getAllIslandsGroups()) <= 3) {

            self::notifyAllPlayers('gameEnd',clienttranslate('There are 3 (or less) groups of islands. The game ends'),[]);
            $this->gamestate->nextState('gameEnd');
        }

        
    }

#endregion

/* ---------------------- */
/* --- PLAYER ACTIONS --- */
/* ---------------------- */
#region 

function playAssistant($n) {

    if ($this->checkAction('playAssistant')) {
      
        $args = self::argPlayAssistant();
        $id = self::getActivePlayerId();

        if ($n < 1 || $n > 10) throw new BgaVisibleSystemException("Invalid Assistant number");
        if (in_array($n,$args['assistants'])) throw new BgaVisibleSystemException("You cannot play an Assistant that has already been played by another player");

        self::dbQuery("UPDATE played_assistants SET assistant = $n, old = 0 WHERE player = $id");
        self::dbQuery("UPDATE player_assistants SET `$n` = 0 WHERE player = $id");

        $mov = ceil($n / 2);

        self::notifyAllPlayers('playAssistant', clienttranslate('${player_name} plays Assistant ${num} <span>(${steps})</span>'), array(
            'player_id' => self::getActivePlayerId(),
            'player_name' => self::getActivePlayerName(),
            'n' => $n,
            'num' => [
                'log' => '${assistant_'.$n.'}',
                'args' => ["assistant_$n" => "#$n",'n'=>$n],
            ],
            'steps' => [
                'log' => '${steps_'.$mov.'} ${movement}',
                'args' => ["steps_$mov" => $mov, 'mov'=>$mov, 'movement' => clienttranslate("movement")],
                'i18n' => ["movement"]
            ],
        ));

        if (self::getUniqueValueFromDb("SELECT count(player) FROM player_assistants WHERE 1 + 2 + 3 + 4 + 4 + 5 + 6 + 7 + 8 + 9 + 10 = 0") > 0) {
            self::setGameStateValue('last_round',1);
            self::notifyAllPlayers('lastRound',clienttranslate('Game end has been triggered: the last Assistant has been played. <b>This is the last round</b>.'),[]);
        }

        $this->gamestate->nextState('');
    }
}

function moveStudent($color, $place) {

    if ($this->checkAction('moveStudent')) {

        $id = self::getActivePlayerId();

        $studentsReference = ['green', 'red', 'yellow', 'pink', 'blue'];
        if (!in_array($color,$studentsReference)) throw new BgaVisibleSystemException("Invalid student color");
        if (!is_null($place) && ($place < 0 || $place > 11)) throw new BgaVisibleSystemException("Invalid student destination");

        // remove student from school entrance
        self::dbQuery("UPDATE school_entrance SET $color = $color - 1 WHERE player = $id");

        if (is_null($place)) {

            self::dbQuery("UPDATE school SET $color = $color + 1 WHERE player = $id");

            self::notifyAllPlayers('moveStudent', clienttranslate('${player_name} moves ${student} inside his/her school'), array(
                'player_id' => self::getActivePlayerId(),
                'player_name' => self::getActivePlayerName(),
                'color' => $color,
                'student' => [
                    'log' => '${student_'.$color.'}',
                    'args' => ["student_$color" => self::getStudentProfessorTranslation($color), 'color' => $color],
                    'i18n' => ["student_$color"]
                ])
            );    

            // CHECK PROFESSOR INFLUENCE IF NOT CONTROLLED (move to separate method)
            if (!self::getUniqueValueFromDb("SELECT ".$color."_professor FROM school WHERE player = $id")) {

                $newAmount = self::getUniqueValueFromDb("SELECT $color FROM school WHERE player = $id");

                $gainProfessor = true;
                $stealProfessor = false;
                $stealFrom = null;
    
                foreach (self::getCollectionFromDb("SELECT player, $color, ".$color."_professor FROM school WHERE player != $id") as $p => $s) {
                    if ($newAmount <= $s[$color]) {
                        $gainProfessor = false; break;
                    } else if ($s[$color."_professor"]) {
                        $stealProfessor = true;
                        $stealFrom = $p;
                    }
                }
    
                if ($gainProfessor) {

                    self::dbQuery("UPDATE school SET ".$color."_professor = 1 WHERE player = $id");

                    if ($stealProfessor) {
                        self::dbQuery("UPDATE school SET ".$color."_professor = 0 WHERE player = $stealFrom");

                        $log = clienttranslate('${player_name} takes control of ${professor}, taking it away from ${player_name2}');
                    } else {
                        $log = clienttranslate('${player_name} takes control of ${professor}');
                    }
    
                    self::notifyAllPlayers('gainProfessor', $log, array(
                        'player_id' => self::getActivePlayerId(),
                        'player_name' => self::getActivePlayerName(),
                        'color' => $color,
                        'player_2' => $stealFrom,
                        'player_name2' => (is_null($stealFrom))? null : self::getPlayerNameById($stealFrom),
                        'influence_data' => self::getAllInfluenceData(),
                        'professor' => [
                            'log' => '${professor_'.$color.'}',
                            'args' => ["professor_$color" => self::getStudentProfessorTranslation($color,false), 'color' => $color],
                            'i18n' => ["professor_$color"]
                        ]) 
                    );
                }
            }

        } else {
            self::dbQuery("UPDATE island_influence SET $color = $color +1 WHERE island_pos = $place");

            self::notifyAllPlayers('moveStudent', clienttranslate('${player_name} sent ${student} to an island'), array(
                'player_id' => self::getActivePlayerId(),
                'player_name' => self::getActivePlayerName(),
                'destination' => $place,
                'color' => $color,
                'student' => [
                    'log' => '${student_'.$color.'}',
                    'args' => ["student_$color" => self::getStudentProfessorTranslation($color), 'color' => $color],
                    'i18n' => ["student_$color"]
                ])
            );
        }

        $this->gamestate->nextState('');
    }
}

function moveMona($group) {
    if ($this->checkAction('moveMona')) {

        $id = self::getActivePlayerId();
        $arg = self::argMoveMona();
        if (!in_array($group,$arg['destinations'])) throw new BgaVisibleSystemException("Invalid Mother Nature destination");

        $islandsStops = [];
        foreach ($arg['destinations'] as $g) {
            $islandsStops[] = self::getGroupMid($g);
            if ($g == $group) break;
        }

        self::notifyAllPlayers('moveMona', clienttranslate('${player_name} moves ${mother_nature}'), array(
            'player_id' => self::getActivePlayerId(),
            'player_name' => self::getActivePlayerName(),
            'mother_nature' => clienttranslate('Mother Nature'),
            'stops' => $islandsStops,
            'group' => $group,
            ) 
        ); 

        self::setGameStateValue('mother_nature_pos',$group);

        $playerCol = self::getPlayerColorById($id);

        // CHECK INFLUENCE 
        $ownerChanged = self::resolveIslandGroupInfluence($group);

        

        $this->gamestate->nextState('pickCloud');
    }
}

function chooseCloudTile($cloud) {
    if ($this->checkAction('chooseCloudTile')) {

        $id = self::getActivePlayerId();
        $arg = self::argCloudTileDrafting();

        if (!in_array($cloud,$arg['cloudTiles'])) throw new BgaVisibleSystemException("Invalid Cloud tile");

        $cloudarr = ['green'=>$green, 'red'=>$red, 'yellow'=>$yellow, 'pink'=>$pink,'blue'=>$blue,] = self::getObjectFromDb("SELECT green, red, yellow, pink, blue FROM cloud WHERE id = $cloud");
        self::dbQuery("UPDATE school_entrance SET green=green+$green, red=red+$red, yellow=yellow+$yellow, pink=pink+$pink, blue=blue+$blue WHERE player = $id");
        self::dbQuery("UPDATE cloud SET green=0, red=0, yellow=0, pink=0, blue=0 WHERE id = $cloud");

        $cloudLog = [];
        $cloudLogargs = [];
        $j = 0;
        foreach ($cloudarr as $col => $n) {
            for ($i=0; $i < $n; $i++) { 
                $cloudLog[] = "student_$j";
                $cloudLogargs["student_$j"] = [
                    'log' => '${student_'.$col.'}',
                    'args' => ["student_$col" => self::getStudentProfessorTranslation($col), 'color' => $col],
                    'i18n' => ["student_$col"]
                ];
                $j++;
            }
        }

        $cloudLoglog = '${'.implode('} ${',$cloudLog).'}';
        $cloudLog[] = 'cloud_tile_text';

        self::notifyAllPlayers('chooseCloudTile', clienttranslate('${player_name} chooses ${cloud_tile} <span class="cloud_group">(${students})</span>'), array(
            'player_id' => self::getActivePlayerId(),
            'player_name' => self::getActivePlayerName(),
            'cloud' => $cloud,
            'cloud_tile' => clienttranslate('Cloud tile'),
            'i18n' => ['cloud_tile'],
            'students' => [
                'log' => $cloudLoglog,
                'args' => $cloudLogargs,
                'i18n' => $cloudLog
            ],
            /* 'cloudlog' => $cloudLoglog,
            'cloudargs' => $cloudLogargs,
            'cloudi18n' => ['cloud_tile_text'] */
        ));

        $this->gamestate->nextState('');
    }
}

#endregion

/* ----------------------- */
/* --- STATE ARGUMENTS --- */
/* ----------------------- */
#region 

function argPlayAssistant() {

    $id = self::getActivePlayerId();
    $playedAssistants = self::getObjectListFromDb("SELECT assistant FROM played_assistants WHERE player != $id AND old = 0",true);

    /* $playedAssistants = self::getObjectListFromDb("SELECT assistant FROM played_assistants", true);

    self::dump("// ASSISTANT", $playedAssistants);

    $playedAssistants = array_values(array_filter($playedAssistants, function($a) { return !is_null($a); }));

    self::dump("// ASSISTANT FILTERED", $playedAssistants); */

    return ['assistants' => $playedAssistants];
}

function argMoveStudents() {

    $id = self::getActivePlayerId();

    $entrance_students_base = (self::getPlayersNumber() == 3)? 9 : 7;
    $entrance_stuents_curr = self::getUniqueValueFromDb("SELECT green + red + yellow + pink + blue FROM school_entrance WHERE player = $id");

    $move_student_count = ($entrance_students_base - $entrance_stuents_curr) + 1;

    return ['stud_count' => $move_student_count];
}

function argMoveMona() {

    $id = self::getActivePlayerId();

    $monaPos = self::getGameStateValue('mother_nature_pos');
    $steps = self::getUniqueValueFromDb("SELECT player_mona_steps FROM player WHERE player_id = $id");
    $groups = self::getAllIslandsGroups();
    $monaKey = array_search($monaPos,$groups);

    $destinations = [];

    for ($i=0; $i < $steps; $i++) { 
        $destinations[] = $groups[($monaKey+$i+1)%count($groups)];
    }

    return ['destinations' => $destinations];
}

function argCloudTileDrafting() {

    $cloudTiles = self::getObjectListFromDb("SELECT id FROM cloud WHERE (green + red + yellow + pink + blue) = 3",true);

    return ['cloudTiles' => $cloudTiles];
}

#endregion

/* --------------------- */
/* --- STATE ACTIONS --- */
/* --------------------- */
#region 

function stNextPlayerPlanning() {
    self::trace('// NEXT PLAYER PLANNING');

    $id = self::getActivePlayerId();
    $turnPos = self::getUniqueValueFromDb("SELECT player_turn_position FROM player WHERE player_id = $id");
    $nextTurnPos = $turnPos+1;
    self::trace("// TURN POS: $turnPos");
    self::trace("// NEXT TURN POS: $nextTurnPos");
    if ($nextTurnPos <= self::getPlayersNumber()) {

        // activate next player
        $np = self::getUniqueValueFromDb("SELECT player_id FROM player WHERE player_turn_position = $nextTurnPos");

        self::trace("// ACTIVATE NEXT PLAYER: $np");

        $this->gamestate->changeActivePlayer($np);
        $this->gamestate->nextState('nextTurn');

    } else {

        // attribute player order and mona steps

        $newOrder = self::getObjectListFromDb("SELECT player, assistant FROM played_assistants ORDER BY assistant ASC");
        $firstPlayer = $newOrder[0]['player'];

        foreach ($newOrder as $turnPos => $p) {
            $steps = ceil($p['assistant']/2);
            self::dbQuery("UPDATE player SET player_turn_position = $turnPos+1 , player_mona_steps = $steps WHERE player_id = ".$p['player']);
        }

        $newOrder = self::getObjectListFromDb("SELECT player_id id, player_turn_position turn_pos, player_mona_steps steps FROM player ORDER BY turn_pos ASC");

        self::notifyAllPlayers('resolvePlanning',clienttranslate("The planning phase ends. The turn order is reassigned based on the Assistants played"),[
            'players' => $newOrder
        ]);

        self::dbQuery("UPDATE played_assistants SET old = 1");

        $this->gamestate->changeActivePlayer($firstPlayer);
        $this->gamestate->nextState('nextPhase');
    }

}

function stMoveAgain() {

    $id = self::getActivePlayerId();
    $total = self::getUniqueValueFromDb("SELECT (green + red + yellow + pink + blue) as total FROM school_entrance WHERE player = $id");

    if ($total > ((self::getPlayersNumber()==3)?6:4)) {
        $this->gamestate->nextState('again');
    } else $this->gamestate->nextState('next');
}

function stNextPlayerAction() {

    $id = self::getActivePlayerId();
    $players = self::getObjectListFromDb("SELECT player_id FROM player ORDER BY player_turn_position ASC", true);

    $playerTurnPos = array_search($id, $players);

    $nextPlayerTurnPos = ($playerTurnPos + 1) % count($players);
    $nextPlayer = $players[$nextPlayerTurnPos];

    $this->gamestate->changeActivePlayer($nextPlayer);

    if ($nextPlayerTurnPos > 0) {

        $this->gamestate->nextState('nextPlayerAction');
    } else {

        if (self::getGameStateValue('last_round') == 1) $this->gamestate->nextState('gameEnd');
        else {
            self::notifyAllPlayers('newRound',clienttranslate("A new game round begins"),[]);
            self::refillClouds();
            // check victory condition (student pouch empty / players with only 1 assistant card in their hands)        
    
            $this->gamestate->nextState('nextRound');
        }
    }
}

#endregion

/* ------------------------- */
/* --- ZOMBIES MANAGMENT --- */
/* ------------------------- */
#region 

function zombieTurn($state, $active_player) {
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

#endregion
   
/* ------------------------ */
/* --- STATE DB UPGRADE --- */
/* ------------------------ */
#region 

function upgradeTableDb($from_version) {}    

#endregion
    
}
