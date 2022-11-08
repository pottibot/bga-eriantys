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
            "last_round" => 11,
            "charPausedState" => 12
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
        $sql4 = "INSERT INTO last_coin_gained_position (player) VALUES ";
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
        $sql4 .= implode($values2,',');
        self::DbQuery($sql4);

        self::DbQuery("INSERT INTO professor_steals (color) VALUES ('green'),('red'),('yellow'),('pink'),('blue')");

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
        self::setGameStateInitialValue('charPausedState', 20);
        
        // INIT STATISTICS
        self::initStat('table', 'towers_placed', 0);
        self::initStat('table', 'islands_groups', 12);
        self::initStat('table', 'assistants_played', 0);
        self::initStat('table', 'students_drawn', 0);
        self::initStat('table', 'mona_travel', 0);
        self::initStat('table', 'contested_group', 0);
        self::initStat('table', 'contended_professor', 0);
        self::initStat('table', 'powerful_student', 0);

        self::initStat('player', 'final_towers', 0);
        self::initStat('player', 'islands_conquered', 0);
        self::initStat('player', 'islands_lost', 0);
        self::initStat('player', 'islands_stolen', 0);
        self::initStat('player', 'islands_groups', 0);
        self::initStat('player', 'final_professors', 0);
        self::initStat('player', 'professors_influenced', 0);
        self::initStat('player', 'professors_lost', 0);
        self::initStat('player', 'professors_stolen', 0);
        self::initStat('player', 'islands_students', 0);
        self::initStat('player', 'hall_students', 0);
        self::initStat('player', 'favourite_student', 0);
        self::initStat('player', 'highest_island_influence', 0);

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
            if ($i != 0 && $i != 6) $students[self::drawStudents()[0]] = 1; // draft initial students for given island;
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

        // SETUP CHARACTERS
        if ($this->gamestate->table_globals[100] == 1) {

            self::initStat('player', 'characters_used', 0);

            $charPool = range(1,12);
            shuffle($charPool);

            $sql = "INSERT INTO `character` (`id`,`data`,`tooltip`,`cost`) VALUES ";
            $values = [];
            for ($i=0; $i < 3; $i++) { 
                $id = array_shift($charPool);
                $data;

                switch ($id) {

                    // draw 6 (n4) or 4 (n1 and n10) studs
                    case 1:
                    case 6: 
                    case 10:
                        $n = ($id == 6)? 6 : 4;
                        $data = json_encode(['students' => self::drawStudents($n)]);

                        break;

                    // draw 4 noentry token
                    case 4:
                        $data = json_encode(['noEntry' => 4]);
                        break;
                    
                    default:
                        $data = null;
                        break;
                }

                $tooltip = $this->characters[$id]['tooltip'];
                $cost = $this->characters[$id]['cost'];
                
                $values[] = "($id,'$data','$tooltip',$cost)";
            }

            self::dbQuery($sql . implode(',',$values));
        }

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

        $result['players_influence'] = self::updateInfluenceData();

        $result['schools'] = self::getCollectionFromDb("SELECT * FROM school");
        $result['schools_entrance'] = self::getCollectionFromDb("SELECT * FROM school_entrance");

        $result['clouds'] = self::getObjectListFromDb("SELECT * FROM cloud");

        $result['mother_nature'] = self::getGameStateValue('mother_nature_pos');

        $result['characters'] = (self::getGameStateValue('characters') == 1)? self::getCollectionFromDb("SELECT * FROM `character`") : false;
        //$result['characters'] = self::getGameStateValue('characters') == 1;

        $result['students_left'] = self::getStudentsLeft();

        return $result;
    }

    function getGameProgression() {
        // 4 progressions
        $towerStat = self::getStat('towers_placed');
        $groupsStat = self::getStat('islands_groups');
        $assistantsStat = self::getStat('assistants_played');
        $studentsStat = self::getStat('students_drawn');

        // towers placed
        $totTowers = (self::getPlayersNumber() == 3)? 6 : 8;
        $minTowers = self::getUniqueValueFromDb("SELECT SUM(s.towers) FROM school s JOIN player p ON s.player = p.player_id GROUP BY p.player_color ORDER BY SUM(s.towers) ASC LIMIT 1");
        $towers = 1 - ($minTowers / $totTowers);
        self::setStat(max($towerStat,($totTowers-$minTowers)),'towers_placed');

        // islands groups
        $totGroups = 12;
        $islGroups = count(self::getAllIslandsGroups());
        $groups = 1 - (($islGroups - 3) / 9);
        self::setStat(min($groupsStat,$islGroups),'islands_groups');

        // assistant played
        $assistantsLeft = self::getUniqueValueFromDb("SELECT (`1` + `2` + `3` + `4` + `5` + `6` + `7` + `8` + `9` + `10`) as assistants_played FROM player_assistants ORDER BY assistants_played LIMIT 1");
        $assistants = 1 - ($assistantsLeft / 10);
        self::setStat(max($assistantsStat,(10-$assistantsLeft)),'assistants_played');

        // students left in the bag
        $studentsLeft = self::getStudentsLeft();
        $studentsTot = 120 - (self::getPlayersNumber() * (((self::getPlayersNumber() == 3)? 4:3) + ((self::getPlayersNumber() == 3)? 9:7)));
        $students = 1 - ($studentsLeft / $studentsTot);
        self::setStat(max($studentsStat,130-$studentsLeft),'students_drawn');

        return (($towers+$groups+$assistants+$students)/4) * 100;
    }


#endregion

/* ----------------------- */
/* --- UTILITY METHODS --- */
/* ----------------------- */
#region 

    /* // -- DEBUGGING METHODS --

        function testPassiveCharacters() {

            if ($this->gamestate->table_globals[100] == 1) {

                self::dbQuery("DELETE FROM `character`");
    
                $charPool = [3,5,7,12];
    
                $sql = "INSERT INTO `character` (`id`,`data`,`tooltip`,`cost`) VALUES ";
                $values = [];
                for ($i=0; $i < 4; $i++) { 
                    $id = array_shift($charPool);
                    $data = null;
    
                    $tooltip = $this->characters[$id]['tooltip'];
                    $cost = $this->characters[$id]['cost'];
                    
                    $values[] = "($id,'$data','$tooltip',$cost)";
                }
    
                self::dbQuery($sql . implode(',',$values));

                self::dbQuery("UPDATE school SET coins = 10");
            }
        }

        function testCharacters(...$characters) {
            if ($this->gamestate->table_globals[100] == 1) {

                self::dbQuery("DELETE FROM `character`");
    
                $sql = "INSERT INTO `character` (`id`,`data`,`tooltip`,`cost`) VALUES ";
                $values = [];

                foreach ($characters as $id) {
                    $data = null;

                    switch ($id) {

                        // draw 6 (n4) or 4 (n1 and n10) studs
                        case 1:
                        case 6: 
                        case 10:
                            $n = ($id == 6)? 6 : 4;
                            $data = json_encode(['students' => self::drawStudents($n)]);
    
                            break;
    
                        // draw 4 noentry token
                        case 4:
                            $data = json_encode(['noEntry' => 4]);
                            break;
                    }
    
                    $tooltip = $this->characters[$id]['tooltip'];
                    $cost = $this->characters[$id]['cost'];
                    
                    $values[] = "($id,'$data','$tooltip',$cost)";
                }
    
                self::dbQuery($sql . implode(',',$values));

                self::dbQuery("UPDATE school SET coins = 10");
            }
        }

        function test() {
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

        function populateSchools() {
            $professors = $this->studentsReference;

            foreach (self::getObjectListFromDb("SELECT player FROM school",true) as $pid) {
                $values = [];
                
                foreach ($this->studentsReference as $col) {

                    $values[$col] = bga_rand(0,6);

                    $professor = 0;
                    if (bga_rand(0,1) == 0 && in_array($col,$professors)) {
                        $values[$col.'_professor'] = 1;
                        unset($professors[array_search($col,$professors)]);
                    } else $values[$col.'_professor'] = 0;
                }

                self::dbQuery("UPDATE school SET green = ".$values['green'].", red = ".$values['red'].", yellow = ".$values['yellow'].", pink = ".$values['pink'].", blue = ".$values['blue']." WHERE player = $pid");
                self::dbQuery("UPDATE school SET green_professor = ".$values['green_professor'].", red_professor = ".$values['red_professor'].", yellow_professor = ".$values['yellow_professor'].", pink_professor = ".$values['pink_professor'].", blue_professor = ".$values['blue_professor']." WHERE player = $pid");
            }
        }

        // pupulates db with students and towers on the islands. missing population of player schools
        function populateIslands() {

            // populate islands
            for ($i=0; $i < 12; $i++) { 

                $students = array_fill(0,5,0);

                $n = bga_rand(2,5);

                for ($j=0; $j < $n; $j++) { 
                    $students[bga_rand(0,4)] += 1;
                }

                $green = $students[0];
                $red = $students[1];
                $yellow = $students[2];
                $pink = $students[3];
                $blue = $students[4];

                $towers = [1,0,0,0];
                shuffle($towers);

                $black = array_shift($towers);
                $white = array_shift($towers);

                $grey = (self::getPlayersNumber() == 3)?array_shift($towers):0;

                self::dbQuery("UPDATE island_influence SET green = $green, red = $red, yellow = $yellow, pink = $pink, blue = $blue, black_tower = $black, white_tower = $white, grey_tower = $grey WHERE island_pos = $i");
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

    // -- --- -- */

    function getStudentsLeft() {
        return strlen(self::getUniqueValueFromDb("SELECT students FROM students_bag"));
    }

    // returns n students from the bag in the form of an array where the student is represented by a number from 0 to 4 (0 -> green, 1 -> red, ... , 4 -> blue)
    // so [0,3,2,3,4,4,1] is a draw of green, pink, yellow, pink, blue, blue, red
    function drawStudents($n=1) {

        $students_bag = self::getUniqueValueFromDb("SELECT students FROM students_bag");
        $students_left = strlen($students_bag);

        // if ($students_left < $n) throw new BgaSystemException("Students bag doesn't have enough students ($n, available $students_left)");

        $students_bag = str_split($students_bag);
        shuffle($students_bag);

        $studentsSet = ['G','R','Y','P','B'];

        $ret = [];
        for ($i=0; $i < $n; $i++) {

            if ($students_left > 0) {

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
                
                $students_left--;
            }
        }

        $students_bag = implode('',$students_bag);

        self::dbQuery("UPDATE students_bag SET students = '$students_bag'");

        return $ret;
    }

    // refills students clouds by drawing new students and sends notification to display it visually
    function refillClouds() {
        $allClouds = self::getObjectListFromDb("SELECT id FROM cloud", true);
        $retClouds = [];

        foreach ($allClouds as $cloud) {
            $students = array_fill(0,5,0);

            $drawNum = (count($allClouds) == 3)?4:3;
            $drawn = self::drawStudents((count($allClouds) == 3)?4:3);

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

            if (count($drawn) < $drawNum || self::getStudentsLeft() == 0) {
                self::setGameStateValue('last_round',1);
                self::notifyAllPlayers('lastRound',clienttranslate('Game end has been triggered: there are no more Students in the bag. <b>This is the last round</b>.'),[]);
                break;
            }
        }

        self::notifyAllPlayers('refillClouds',"",[
            'clouds' => $retClouds,
            'students_each' => (count($allClouds) == 3)?4:3,
            'students_left' => self::getStudentsLeft(),
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
            'islandsCount' => count($g1) + count($g2)
        ]);

        self::updateInfluenceData();
    }

    // > used by joinIslandsGroups
    // get angle from which every hex should joing depending from side (left/right) and island pos
    function getAttachAngle($side,$pos) {

        // starting from a certain pos and angle depending on side, increse angle by 2*30deg every two islands
        switch ($side) {
            case 'left':
                return 7*M_PI/6 + -2*M_PI/6 * floor(($pos+1)/2);  
                break;

            case 'right':
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
        $islandlist = self::getObjectListFromDb("SELECT pos FROM island WHERE `group` = $group ORDER BY pos ASC",true);

        $first = self::getGroupExtremes($group)['left'];
        $k = array_search($first,$islandlist);
        if ($k == 0) {
            return $islandlist;
        } else {
            $ret = [$first];
            $len = count($islandlist);

            for ($i=1; $i < $len; $i++) { 
                $ret[] = $islandlist[($k + $i) % $len];
            }

            return $ret;
        }
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
                if (self::isCharacterActive(8) && self::getUniqueValueFromDb("SELECT `data` FROM `character` WHERE id = 8") == $col) $amt = 0;

                if ($professors[$col]) $totinf += $amt;
            }
        }

        // if team/player controls islands group, add as much influence to the total as there are islands in the group (1 controlled island = 1 tower = +1 influence)
        if (self::controlsIslandGroup($islandsGroup,$teamCol)) $totinf += count($groupIslands);

        $activeTeam = self::getUniqueValueFromDb("SELECT player_color FROM player WHERE player_id = ".self::getActivePlayerId());

        // check +2 inf modifier
        if ($teamCol == $activeTeam && self::isCharacterActive(7)) {
            $totinf += 2;
        }

        // check ignore towers modifier for active team;
        if (self::controlsIslandGroup($islandsGroup,$teamCol) && $teamCol != $activeTeam && self::isCharacterActive(5)) {
            $totinf -= count($groupIslands);
        }

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

    function controlsProfessor($col, $teamCol) {
        return self::getUniqueValueFromDb("SELECT SUM(".$col."_professor) FROM school JOIN player ON player = player_id WHERE player_color = '$teamCol' GROUP BY player_color") > 0;
    }

    // resolve any islands group influence and notif place tower if owner changed
    function resolveIslandGroupInfluence($islandsGroup) {

        $blockedIsland = self::getUniqueValueFromDb("SELECT island_pos FROM island_influence JOIN island ON island_pos = island_pos WHERE no_entry = 1 LIMIT 1");
        if (!is_null($blockedIsland)) {

            self::dbQuery("UPDATE island_influence SET no_entry = 0 WHERE island_pos = $blockedIsland");

            $charTokens = json_decode(self::getUniqueValueFromDb("SELECT `data` FROM `character` WHERE id = 4"),true)['noEntry']+1;
            $charData = json_encode(['noEntry' => $charTokens]);
            self::dbQuery("UPDATE `character` SET `data` = '$charData' WHERE id = 4");
            
            self::notifyAllPlayers("blockInfluenceResolve",clienttranslate('Island Influence resolve is blocked by ${no_entry_token}'),[
                'no_entry_token' => clienttranslate('a "No Entry" token'),
                'i18n' => ['no_entry_token'],
                'blocked_island' => $blockedIsland
            ]);

            return;
        }

        //self::trace("// RESOLVING ISLAND $islandsGroup INFLUENCE");
        
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

        //self::dump("// island factions influence",$teamsInf);
        //self::dump("// owner faction",$ownerTeam);

        // get max influence, check if unique in array (team/player has most influence and not tied, thus controls islands group)
        // if not unique, no influence is greater then the others, islands group owner doesn't change. return
        $maxInf = max($teamsInf);
        if (1 === count(array_keys($teamsInf, $maxInf))) {
            // maxInf is unique

            // get winning team color
            $winningTeam = array_search($maxInf,$teamsInf);

            //self::trace("// WINNING TEAM: $winningTeam");

            // change of power is effective only if previous owner is not the winning team
            if ($winningTeam != $ownerTeam) {

                //self::trace("// CHANGE OF POWER EFFECTIVE");

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

                        // set stats for each player of faction
                        foreach (self::getObjectListFromDb("SELECT player_id FROM player WHERE player_color = '$ownerTeam'",true) as $pid) {
                            self::incStat(1,'islands_lost',$pid);
                            self::incStat(-1,'final_towers',$pid);
                        }

                        // send notif
                        self::notifyAllPlayers('moveTower','', array(
                            'island' => $island,
                            'place' => false,
                            'color' => self::getColorName($ownerTeam),
                            'player' => $owner_player
                            ) 
                        );

                        self::updateInfluenceData();
                    }

                    // set stats for each player of faction
                    foreach (self::getObjectListFromDb("SELECT player_id FROM player WHERE player_color = '$ownerTeam'",true) as $pid) {
                        self::incStat(-1,'islands_groups',$pid);
                    }
                }

                // set stats for each player of faction
                foreach (self::getObjectListFromDb("SELECT player_id FROM player WHERE player_color = '$winningTeam'",true) as $pid) {
                    self::incStat(1,'islands_groups',$pid);
                }

                // place winning team towers on conquered islands group
                $colName = self::getColorName($winningTeam);
                foreach (self::getGroupIslands($islandsGroup) as $island) {

                    if (self::getUniqueValueFromDb("SELECT SUM(s.towers) FROM school s JOIN player p ON s.player = p.player_id WHERE p.player_color = '$winningTeam' GROUP BY p.player_color") > 0) {

                        if (self::getPlayersNumber() == 4) {
                            $id = self::getActivePlayerId();
                            if (self::getUniqueValueFromDb("SELECT s.towers FROM school s JOIN player p ON s.player = p.player_id WHERE p.player_id = $id AND p.player_color = '$winningTeam'") > 0 ) $placing_player = $id;
                            else $placing_player = self::getObjectListFromDb("SELECT s.player FROM school s JOIN player p ON s.player = p.player_id WHERE p.player_color = '$winningTeam' AND s.towers > 0 ",true)[0];
                        } else $placing_player = self::getUniqueValueFromDb("SELECT player_id FROM player WHERE player_color = '$winningTeam'");

                        self::dbQuery("UPDATE island_influence SET ".$colName."_tower = 1 WHERE island_pos = $island");
                        self::dbQuery("UPDATE school SET towers = towers-1 WHERE player = $placing_player");
                        self::dbQuery("UPDATE player SET player_score = player_score+1 WHERE player_color = '$winningTeam'");

                        // set stats for each player of faction
                        foreach (self::getObjectListFromDb("SELECT player_id FROM player WHERE player_color = '$winningTeam'",true) as $pid) {
                            self::incStat(1,'islands_conquered',$pid);
                            self::incStat(1,'final_towers',$pid);

                            if (!is_null($ownerTeam)) self::incStat(1,'islands_stolen',$pid);
                        }

                        // send notif
                        self::notifyAllPlayers('moveTower','', array(
                            'island' => $island,
                            'place' => true,
                            'color' => self::getColorName($winningTeam),
                            'player' => $placing_player
                            ) 
                        );

                        self::updateInfluenceData();
                    }
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
                //self::trace("//CHECK FOR MERGING GROUPS");

                $allgroups = self::getAllIslandsGroups();

                $thisgroup = array_search($islandsGroup,$allgroups);
                $groupstot = count($allgroups);

                $prevGroup = $allgroups[($thisgroup - 1 + $groupstot) % $groupstot];
                $nextGroup = $allgroups[($thisgroup + 1 + $groupstot) % $groupstot];

                //self::trace("// PREV GROUP $prevGroup");
                //self::trace("// NEXT GROUP $nextGroup");

                if (self::controlsIslandGroup($prevGroup,$winningTeam)) {
                    //self::trace("// MERGING WITH PREV");
                    self::joinIslandsGroups($prevGroup,$islandsGroup);

                    foreach (self::getObjectListFromDb("SELECT player_id FROM player WHERE player_color = '$winningTeam'",true) as $pid) {
                        self::incStat(-1,'islands_groups',$pid);
                    }
                }

                if (self::controlsIslandGroup($nextGroup,$winningTeam)) {
                    //self::trace("// MERGING WITH NEXT");
                    self::joinIslandsGroups($nextGroup,$islandsGroup);

                    foreach (self::getObjectListFromDb("SELECT player_id FROM player WHERE player_color = '$winningTeam'",true) as $pid) {
                        self::incStat(-1,'islands_groups',$pid);
                    }
                }

                self::checkGameEndCondition();
            }
        }
    }

    function resolveProfessorInfluence($color,$id) {

        if (!self::getUniqueValueFromDb("SELECT ".$color."_professor FROM school WHERE player = $id")) {

            $newAmount = self::getUniqueValueFromDb("SELECT $color FROM school WHERE player = $id");

            $gainProfessor = true;
            $stealProfessor = false;
            $stealFrom = null;

            foreach (self::getCollectionFromDb("SELECT player, $color, ".$color."_professor FROM school WHERE player != $id") as $p => $s) {
                $winsProfessor = $newAmount > $s[$color];
                if ($id == self::getActivePlayerId() && self::isCharacterActive(12)) {
                    $winsProfessor = $newAmount >= $s[$color];
                }

                if (!$winsProfessor) {
                    $gainProfessor = false; break;
                } else if ($s[$color."_professor"]) {
                    $stealProfessor = true;
                    $stealFrom = $p;
                }
            }

            if ($gainProfessor) {

                self::dbQuery("UPDATE school SET ".$color."_professor = 1 WHERE player = $id");
                self::dbQuery("UPDATE player SET player_score_aux = player_score_aux + 1 WHERE player_id = $id");

                self::incStat(1,'professors_influenced',$id);
                self::incStat(1,'final_professors',$id);

                if ($stealProfessor) {
                    self::dbQuery("UPDATE school SET ".$color."_professor = 0 WHERE player = $stealFrom");
                    self::dbQuery("UPDATE player SET player_score_aux = player_score_aux - 1 WHERE player_id = $stealFrom");
                    self::dbQuery("UPDATE professor_steals SET steals = steals + 1 WHERE color = '$color'");

                    self::incStat(1,'professors_stolen',$id);
                    self::incStat(1,'professors_lost',$stealFrom);
                    self::incStat(-1,'final_professors',$stealFrom);

                    $log = clienttranslate('${player_name} takes control of ${professor}, taking it away from ${player_name2}');

                    self::setStat(
                        array_search(
                            self::getUniqueValueFromDb("SELECT color FROM professor_steals ORDER BY steals DESC LIMIT 1"),
                            $this->studentsReference),
                        'contended_professor'
                    );

                } else {
                    $log = clienttranslate('${player_name} takes control of ${professor}');
                }

                self::notifyAllPlayers('gainProfessor', $log, array(
                    'player_id' => self::getActivePlayerId(),
                    'player_name' => self::getActivePlayerName(),
                    'color' => $color,
                    'player_2' => $stealFrom,
                    'player_name2' => (is_null($stealFrom))? null : self::getPlayerNameById($stealFrom),
                    'professor' => [
                        'log' => '${professor_'.$color.'}',
                        'args' => ["professor_$color" => self::getStudentProfessorTranslation($color,false)],
                        'i18n' => ["professor_$color"]
                    ]) 
                );

                self::updateInfluenceData();
            }
        }
    }

    // returns influence for each faction on each islands. array indexed by island group with field $influence, indexed by faction color and field $mid containing island id on where to display data
    function updateInfluenceData() {

        $ret = [];
        foreach (self::getAllIslandsGroups() as $g) {
            $contestedGroup = self::getStat('contested_group');

            $ret[$g]['mid'] = self::getGroupMid($g);
            $totIslInf = 0;
            foreach (self::getAllFactions() as $f) {
                $ret[$g]['influence'][$f] = self::getInfluenceOnIslandGroup($g,$f);
                $totIslInf += $ret[$g]['influence'][$f];

                $ret[$g]['mod'][$f] = '';

                $activeTeam = self::getUniqueValueFromDb("SELECT player_color FROM player WHERE player_id = ".self::getActivePlayerId());

                // check +2 inf modifier
                if ($f == $activeTeam && self::isCharacterActive(7)) {
                    $ret[$g]['mod'][$f] = 'up';
                }

                // check ignore towers modifier for active team;
                if (self::controlsIslandGroup($g,$f) && $f != $activeTeam && self::isCharacterActive(5)) {
                    $ret[$g]['mod'][$f] = 'down';
                }

                if (self::isCharacterActive(8)) {
                    $ignoreColor = self::getUniqueValueFromDb("SELECT `data` FROM `character` WHERE id = 8");
                    if (!empty($ignoreColor)){
                        if (self::controlsProfessor($ignoreColor,$f) && self::getUniqueValueFromDb("SELECT SUM($ignoreColor) FROM island_influence JOIN island ON island_pos = pos WHERE `group` = $g GROUP BY `group`") > 0) {
                            $ret[$g]['mod'][$f] = 'down';
                        }
                    }
                }

                // set stats for each player of faction
                foreach (self::getObjectListFromDb("SELECT player_id FROM player WHERE player_color = '$f'",true) as $pid) {
                    self::setStat(max(self::getStat('highest_island_influence',$pid),$ret[$g]['influence'][$f]),'highest_island_influence',$pid);
                }
            }

            self::setStat(max($contestedGroup,$totIslInf),'contested_group');
        }

        self::notifyAllPlayers('updateInfluence','',[
            'influence_data' => $ret
        ]);

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
        $winner = array_values(array_filter($factionsTotTowers, function($f) { return $f['towers'] == 0; }));
        if (!empty($winner)) {
            $winnerFaction = $winner[0]['col'];

            if (self::getPlayersNumber() == 4) {
                $winner = self::getTeamFullName($winnerFaction);
                $log = clienttranslate('<b>${player_name} place their last tower and win the game!</b>');
            } else {
                $winner = self::getUniqueValueFromDb("SELECT player_name FROM player WHERE player_color = '$winnerFaction'");
                $log = clienttranslate('<b>${player_name} places his/her last tower and wins the game!</b>');
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

    function getPlayerCoins($id) {
        return self::getUniqueValueFromDb("SELECT coins FROM school WHERE player = $id");
    }

    function getAvailableCharacters($id) {
        $coins = self::getPlayerCoins($id);
        return self::getObjectListFromDb("SELECT id FROM `character` WHERE cost + cost_mod <= $coins AND active = 0 AND used = 0",true);
    }

    function activateCharacter($id) {
        self::dbQuery("UPDATE `character` SET active = 1 WHERE id = $id");
    }

    function isCharacterActive($id) {
        return boolval(self::getUniqueValueFromDb("SELECT active FROM `character` WHERE id = $id AND used != 1"));
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

        if (self::getUniqueValueFromDb("SELECT count(player) FROM player_assistants WHERE `1` + `2` + `3` + `4` + `5` + `6` + `7` + `8` + `9` + `10` = 0") > 0) {
            self::setGameStateValue('last_round',1);
            self::notifyAllPlayers('lastRound',clienttranslate('Game end has been triggered: the last Assistant has been played. <b>This is the last round</b>.'),[]);
        }

        $this->gamestate->nextState('next');
    }
}

function moveStudent($color, $place) {

    if ($this->checkAction('moveStudent')) {

        $id = self::getActivePlayerId();

        $sRef = $this->studentsReference;

        $from_char = $this->gamestate->state()['args']['from_char'];

        if (is_null($from_char)) {
            if (!in_array($color,$sRef)) throw new BgaVisibleSystemException("Invalid student color");
            if (!is_null($place) && ($place < 0 || $place > 11)) throw new BgaVisibleSystemException("Invalid student destination");
            if (self::getObjectFromDb("SELECT * FROM school_entrance WHERE player = $id")[$color] < 1) throw new BgaVisibleSystemException("You don't have a Student of this color in your school entrance");
            if (is_null($place) && !in_array($color,self::argMoveStudents()['free_tables'])) throw new BgaVisibleSystemException("You cannot have more than 10 students of the same color in your School Hall");    
        
            // remove student from school entrance
            self::dbQuery("UPDATE school_entrance SET $color = $color - 1 WHERE player = $id");

        } else {
            $charStudents = json_decode(self::getUniqueValueFromDB("SELECT `data` FROM `character` WHERE id = $from_char"),true)['students'];
            $colIndex = array_search($color,$sRef);
        
            if (is_null($place) && $from_char == 1) throw new BgaVisibleSystemException("You cannot send Students to the Dining Hall using this Character");
            if (!is_null($place) && $from_char == 10) throw new BgaVisibleSystemException("You cannot send Students to the Islands using this Character");
            if (!in_array($color,$sRef)) throw new BgaVisibleSystemException("Invalid student color");
            if (!is_null($place) && ($place < 0 || $place > 11)) throw new BgaVisibleSystemException("Invalid student destination");
            if (!in_array($colIndex,$charStudents)) throw new BgaVisibleSystemException("The Character doesn't hold a Student of this color");
            if (is_null($place) && !in_array($color,self::argMoveStudents()['free_tables'])) throw new BgaVisibleSystemException("You cannot have more than 10 students of the same color in your School Hall");
    
            // remove replace students from char card with a new one;
            $charStudents[array_search($colIndex,$charStudents)] = $refilledStud = self::drawStudents(1)[0];
            $charData = ['students' => $charStudents];
            $charData = json_encode($charData);
            self::dbQuery("UPDATE `character` SET `data` = '$charData' WHERE id = $from_char");
        }

        self::dbQuery("UPDATE player SET player_tot_$color = player_tot_$color + 1 WHERE player_id = $id");
        $totStudentsMoved = self::getObjectFromDb("SELECT player_tot_green as green, player_tot_red as red, player_tot_yellow as yellow, player_tot_pink as pink, player_tot_blue as blue FROM player WHERE player_id = $id");
        asort($totStudentsMoved);
        $totStudentsMoved = array_reverse($totStudentsMoved);
        foreach ($totStudentsMoved as $col => $tot) {
            self::setStat(array_search($col,$sRef),'favourite_student',$id);
            break;
        }

        if (is_null($place)) {
            self::moveStudentToSchool($color,$id,$from_char);
        } else {
            self::moveStudentToIsland($color,$place,$from_char);
        }

        if (!is_null($from_char)) {
            self::notifyAllPlayers('refillCharStudent','',[
                'char' => $from_char,
                'student' => $refilledStud,
                'students_left' => self::getStudentsLeft(),
            ]);
        }

        if (is_null($from_char)) $this->gamestate->nextState('next');
        else $this->gamestate->nextState('endAbility');
    }
}

function moveStudentFromChar($charId, $color, $place) {

    if ($this->checkAction('moveStudentFromChar')) {

        $id = self::getActivePlayerId();

        $sRef = $this->studentsReference;

        $charData = json_decode(self::getUniqueValueFromDB("SELECT `data` FROM `character` WHERE id = $charId"));
        
        if (!in_array($color,$sRef)) throw new BgaVisibleSystemException("Invalid student color");
        if (!is_null($place) && ($place < 0 || $place > 11)) throw new BgaVisibleSystemException("Invalid student destination");
        if (!in_array($sRef[$color],$charData['students'])) throw new BgaVisibleSystemException("The Character doesn't hold a Student of this color");
        if (is_null($place) && !in_array($color,self::argMoveStudents()['free_tables'])) throw new BgaVisibleSystemException("You cannot have more than 10 students of the same color in your School Hall");

        // remove replace students from char card with a new one;
        $charData['students'][array_search($sRef[$color],$charData['students'])] = self::drawStudents(1)[0];
        self::dbQuery("UPDATE `character` SET `data` = $charData WHERE id = $charId");

        self::dbQuery("UPDATE player SET player_tot_$color = player_tot_$color + 1 WHERE player_id = $id");
        $totStudentsMoved = self::getObjectFromDb("SELECT player_tot_green as green, player_tot_red as red, player_tot_yellow as yellow, player_tot_pink as pink, player_tot_blue as blue FROM player WHERE player_id = $id");
        asort($totStudentsMoved);
        $totStudentsMoved = array_reverse($totStudentsMoved);
        foreach ($totStudentsMoved as $col => $tot) {
            self::setStat(array_search($col,$sRef),'favourite_student',$id);
            break;
        }

        if (is_null($place)) {
            self::moveStudentToSchool($color,$id,$charId);

        } else {
            self::moveStudentToIsland($color,$place,$charId);
        }

        $this->gamestate->nextState('endAbility');
    }
}

// sub step of moveStudent
function moveStudentToSchool($color, $player, $fromChar = null) {

    self::dbQuery("UPDATE school SET $color = $color + 1 WHERE player = $player");
    self::incStat(1,'hall_students',$player);

    self::notifyAllPlayers('moveStudent', clienttranslate('${player_name} moves ${student} inside his/her school'), array(
        'player_id' => $player,
        'player_name' => self::getActivePlayerName(),
        'color' => $color,
        'from_char' => $fromChar,
        'student' => [
            'log' => '${student_'.$color.'}',
            'args' => ["student_$color" => self::getStudentProfessorTranslation($color)],
            'i18n' => ["student_$color"]
        ])
    );

    if (self::getGameStateValue('characters') == 1 &&
        self::getUniqueValueFromDb("SELECT $color FROM school WHERE player = $player") >= self::getUniqueValueFromDb("SELECT $color FROM last_coin_gained_position WHERE player = $player")+3) {
            self::gainCoin($player,$color);
    }

    self::resolveProfessorInfluence($color,$player);
}

function gainCoin($player,$color) {
    self::dbQuery("UPDATE school SET coins = coins + 1 WHERE player = $player");
    self::dbQuery("UPDATE last_coin_gained_position SET $color = $color + 3 WHERE player = $player");

    $position = self::getUniqueValueFromDb("SELECT $color FROM last_coin_gained_position WHERE player = $player");
    
    self::notifyAllPlayers('gainCoin', clienttranslate('${player_name} gains 1 ${coins}'), array(
        'player_id' => self::getActivePlayerId(),
        'player_name' => self::getActivePlayerName(),
        'coins' => clienttranslate('coin(s)'),
        'i18n' => ['coins'],
        'color' => $color,
        'position' => $position
    ));
}

// sub step of moveStudent
function moveStudentToIsland($color, $island, $fromChar = null) {
    $id = self::getActivePlayerId();
    $sRef = $this->studentsReference;
    
    self::dbQuery("UPDATE island_influence SET $color = $color +1 WHERE island_pos = $island");
    self::incStat(1,'islands_students',$id);

    self::notifyAllPlayers('moveStudent', clienttranslate('${player_name} sent ${student} to an island'), array(
        'player_id' => self::getActivePlayerId(),
        'player_name' => self::getActivePlayerName(),
        'destination' => $island,
        'color' => $color,
        'from_char' => $fromChar,
        'student' => [
            'log' => '${student_'.$color.'}',
            'args' => ["student_$color" => self::getStudentProfessorTranslation($color)],
            'i18n' => ["student_$color"]
        ])
    );

    self::updateInfluenceData();

    $students_populations = self::getObjectFromDb("SELECT SUM('green') as green, SUM('red') as red, SUM('yellow') as yellow, SUM('pink') as pink, SUM('blue') as blue FROM island_influence");
    asort($students_populations);
    $students_populations = array_reverse($students_populations,true);
    foreach ($students_populations as $col => $tot) {
        self::setStat(array_search($col,$sRef),'powerful_student');
        break;
    }
}

function moveMona($group) {
    if ($this->checkAction('moveMona')) {

        $id = self::getActivePlayerId();
        $arg = self::argMoveMona();
        if (!in_array($group,$arg['destinations'])) throw new BgaVisibleSystemException("Invalid Mother Nature destination");

        if ($arg['from_char'] != 2) {

            $islandsStops = [];
            foreach ($arg['destinations'] as $g) {
                $islandsStops[] = self::getGroupMid($g);
                if ($g == $group) break;
            }

            self::incStat(count($islandsStops),'mona_travel');

            self::notifyAllPlayers('moveMona', clienttranslate('${player_name} moves ${mother_nature}'), array(
                'player_id' => self::getActivePlayerId(),
                'player_name' => self::getActivePlayerName(),
                'mother_nature' => clienttranslate('Mother Nature'),
                'i18n' => ['mother_nature'],
                'stops' => $islandsStops,
                'group' => $group,
                ) 
            ); 

            self::setGameStateValue('mother_nature_pos',$group);
        }

        // CHECK INFLUENCE 
        $ownerChanged = self::resolveIslandGroupInfluence($group);

        if ($arg['from_char'] != 2) $this->gamestate->nextState('pickCloud');
        else $this->gamestate->nextState('endAbility');
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
        $cloudArgs = [];
        foreach ($cloudarr as $col => $n) {
            for ($i=0; $i < $n; $i++) { 
                $cloudLog[] = '${student_'.$col.'}';
                $cloudArgs["student_$col"] = self::getStudentProfessorTranslation($col);
            }
        }

        $cloudLog = implode(' ',$cloudLog);
        $cloudi18n = array_keys($cloudArgs);

        self::notifyAllPlayers('chooseCloudTile', clienttranslate('${player_name} chooses ${cloud_tile} <span class="cloud_group">(${students})</span>'), array(
            'player_id' => self::getActivePlayerId(),
            'player_name' => self::getActivePlayerName(),
            'cloud' => $cloud,
            'cloud_tile' => clienttranslate('Cloud tile'),
            'i18n' => ['cloud_tile'],
            'students' => [
                'log' => $cloudLog,
                'args' => $cloudArgs,
                'i18n' => $cloudi18n
            ],
        ));

        $this->gamestate->nextState('endTurn');
    }
}

function useCharacter($chid) {

    if ($this->checkAction('useCharacter')) {

        $id = self::getActivePlayerId();
        $allCharacters = self::getCollectionFromDb("SELECT * FROM `character`");

        if ($chid < 1 || $chid > 12) throw new BgaSystemException("Invalid Characer id");
        if (!in_array($chid,array_keys($allCharacters))) throw new BgaSystemException("This Character is not available for this game");
        if (!in_array($chid,self::getAvailableCharacters($id))) throw new BgaUserException(clienttranslation("You don't have enough coins to activate this Character ability"));

        $char = $allCharacters[$chid];
        $cost = $char['cost'] + $char['cost_mod'];

        self::notifyAllPlayers("useCharacter",clienttranslate('${player_name} activates a Character ability for ${n} ${coins}'),[
            'player_name' => self::getPlayerNameById($id),
            'player_id' => $id,
            'n' => $cost,
            'coins' => clienttranslate('coin(s)'),
            'i18n' => ['coins'],
            'char_id' => $chid
        ]);

        self::dbQuery("UPDATE school SET coins = coins - $cost WHERE player = $id");
        self::dbQuery("UPDATE `character` SET cost_mod = 1, active = 1 WHERE id = $chid");

        self::setGameStateValue('charPausedState',$this->gamestate->state_id());

        self::incStat(1,'characters_used',$id);

        switch ($chid) {

            case 3:
                if ($this->gamestate->state()['name'] == 'cloudTileDrafting') throw new BgaUserException(clienttranslate("You have passed the phase were this effect would be meaningful"));
                self::activateCharacter(3);

                self::notifyAllPlayers("incMonaMovement",clienttranslate('${player_name} gains +2 movement for ${mother_nature}'),[
                    'player_name' => self::getPlayerNameById($id),
                    'player_id' => $id,
                    'mother_nature' => clienttranslate('Mother Nature'),
                    'i18n' => ['mother_nature'],
                ]);

                $this->gamestate->nextState('endAbility');
                break;

            case 5:
                if ($this->gamestate->state()['name'] == 'cloudTileDrafting') throw new BgaUserException(clienttranslate("You have passed the phase were this effect would be meaningful"));
                self::activateCharacter(5);

                self::notifyAllPlayers("ignoreTowers",clienttranslate('${player_name} will ignore towers influence when conquering Islands this turn'),[
                    'player_name' => self::getPlayerNameById($id),
                    'player_id' => $id
                ]);

                self::updateInfluenceData();

                $this->gamestate->nextState('endAbility');
                break;

            case 7:
                if ($this->gamestate->state()['name'] == 'cloudTileDrafting') throw new BgaUserException(clienttranslate("You have passed the phase were this effect would be meaningful"));
                self::activateCharacter(7);

                self::notifyAllPlayers("incInfluence",clienttranslate('${player_name} gains +2 Influence this turn'),[
                    'player_name' => self::getPlayerNameById($id),
                    'player_id' => $id
                ]);

                self::updateInfluenceData();

                $this->gamestate->nextState('endAbility');
                break;

            case 12:
                self::activateCharacter(12);

                self::notifyAllPlayers("winTieProfessors",clienttranslate('${player_name} will win any professor with tied influence this turn'),[
                    'player_name' => self::getPlayerNameById($id),
                    'player_id' => $id
                ]);

                foreach ($this->studentsReference as $col) {
                    self::resolveProfessorInfluence($col,$id);
                }

                $this->gamestate->nextState('endAbility');
                break;

            case 9:
                if (self::getUniqueValueFromDb("SELECT green + red + yellow + pink + blue FROM school WHERE player = $id") < 1)
                    throw new BgaUserException(clienttranslate("You don't have any Student in the dining hall to replace"));
                else $this->gamestate->nextState('char_9');
                break;
            case 4:
                if (json_decode(self::getUniqueValueFromDb("SELECT `data` FROM `character` WHERE id = 4"),true)['noEntry'] < 1)
                    throw new BgaUserException(clienttranslate('There are no "No Entry" tokens left on this character'));
                else $this->gamestate->nextState('char_4');
                break;

            case 1:
            case 2:
            
            case 6:
            case 7:
            case 8:
            case 10:
            case 11: $this->gamestate->nextState('char_'.$chid);
                break;
        }
    }
}

function placeNoEntry($island) {
    if ($this->checkAction('placeNoEntry')) {

        if (self::getUniqueValueFromDb("SELECT no_entry FROM island_influence WHERE island_pos = $island") == 1)
            throw new BgaUserException(clienttranslate('This island already have a "No Entry" token on it'));

        self::dbQuery("UPDATE island_influence SET no_entry = 1 WHERE island_pos = $island");

        $charTokens = json_decode(self::getUniqueValueFromDb("SELECT `data` FROM `character` WHERE id = 4"),true)['noEntry']-1;
        $charData = json_encode(['noEntry' => $charTokens]);
        self::dbQuery("UPDATE `character` SET `data` = '$charData' WHERE id = 4");

        self::notifyAllPlayers("placeNoEntry",clienttranslate('${player_name} places ${no_entry_token} on an island'),[
            'player_name' => self::getActivePlayerName(),
            'no_entry_token' => clienttranslate('a "No Entry" token'),
            'i18n' => ['no_entry_token'],
            'island' => $island
        ]);

        $this->gamestate->nextState('endAbility');
    }
}

function replaceStudents($selLoc1, $selLoc2) {
    if ($this->checkAction('replaceStudents')) {

        $id = self::getActivePlayerId();
        $sRef = $this->studentsReference;
        $loc1 = self::getObjectFromDb("SELECT green, red, yellow, pink, blue FROM school_entrance WHERE player = $id");
        $loc2;
        $amt;

        if (self::isCharacterActive(6)) {
            $loc2_name = clienttranslate('on the Character card');

            $loc2 = ['green' => 0, 'red' => 0, 'yellow' => 0, 'pink' => 0, 'blue' => 0];
            $charStudents = json_decode(self::getUniqueValueFromDb("SELECT `data` FROM `character` WHERE id = 6"),true)['students'];
            foreach ($charStudents as $colIndex) {
                $loc2[$sRef[$colIndex]] += 1;
            }

            $amt = 3;
        }

        if (self::isCharacterActive(9)) {
            $loc2_name = clienttranslate('in the School Dining Hall');
            $loc2 = self::getObjectFromDb("SELECT green, red, yellow, pink, blue FROM school WHERE player = $id");

            $amt = 2;
        }

        // check sel students are equal amt in both loc
        if (count($selLoc1) != count($selLoc1)) throw new BgaSystemException("You need to select at least one student from both locations");
        // check sel students are not zero
        else if (count($selLoc1) == 0) throw new BgaSystemException("You need to select the same number of students to replace in both locations");
            // check sel students are not zero
            else if (count($selLoc1) > $amt) throw new BgaSystemException("You cannot replace this many students");
        
        // check each location has those students
        // loc 1
        $selLoc1obj = ['green' => 0, 'red' => 0, 'yellow' => 0, 'pink' => 0, 'blue' => 0];
        foreach ($selLoc1 as $colIndex) {
            $selLoc1obj[$sRef[$colIndex]] += 1;
        }
        foreach ($selLoc1obj as $col => $q) {
            if ($loc1[$col] < $q) throw new BgaSystemException("Location 1 doesn't hold the selected students");
        }

        // loc 2
        $selLoc2obj = ['green' => 0, 'red' => 0, 'yellow' => 0, 'pink' => 0, 'blue' => 0];
        foreach ($selLoc2 as $colIndex) {
            $selLoc2obj[$sRef[$colIndex]] += 1;
        }
        foreach ($selLoc2obj as $col => $q) {
            if ($loc2[$col] < $q) throw new BgaSystemException("Location 2 doesn't hold the selected students");
        }

        // apply changes to db
        // format changes
        foreach ($selLoc1obj as $col => $q) {
            $loc1[$col] -= $q;
            $loc2[$col] += $q;
        }

        foreach ($selLoc2obj as $col => $q) {
            $loc2[$col] -= $q;
            $loc1[$col] += $q;
        }

        
        // apply changes for loc 1
        ['green' => $green, 'red' => $red, 'yellow' => $yellow, 'pink' => $pink, 'blue' => $blue] = $loc1;
        self::dbQuery("UPDATE school_entrance SET green = $green, red = $red, yellow = $yellow, pink = $pink, blue = $blue WHERE player = $id");

        if (self::isCharacterActive(6)) {
            $charStudents = [];

            foreach ($loc2 as $col => $q) {
                $colIndex = array_search($col,$sRef);
                for ($i=0; $i < $q; $i++) { 
                    $charStudents[] = $colIndex;
                }
            }

            $charData = ['students' => $charStudents];
            $charData = json_encode($charData);

            self::dbQuery("UPDATE `character` SET `data` = '$charData' WHERE id = 6");
        }
        
        if (self::isCharacterActive(9)) {
            ['green' => $green, 'red' => $red, 'yellow' => $yellow, 'pink' => $pink, 'blue' => $blue] = $loc2;
            self::dbQuery("UPDATE school SET green = $green, red = $red, yellow = $yellow, pink = $pink, blue = $blue WHERE player = $id");


            // update stat
            foreach ($selLoc1obj as $color => $v) {
                self::dbQuery("UPDATE player SET player_tot_$color = player_tot_$color + $v WHERE player_id = $id");
            }
            foreach ($selLoc2obj as $color => $v) {
                self::dbQuery("UPDATE player SET player_tot_$color = player_tot_$color - $v WHERE player_id = $id");
            }
            
            $totStudentsMoved = self::getObjectFromDb("SELECT player_tot_green as green, player_tot_red as red, player_tot_yellow as yellow, player_tot_pink as pink, player_tot_blue as blue FROM player WHERE player_id = $id");
            asort($totStudentsMoved);
            $totStudentsMoved = array_reverse($totStudentsMoved);
            foreach ($totStudentsMoved as $col => $tot) {
                self::setStat(array_search($col,$this->studentsReference),'favourite_student',$id);
                break;
            }
        }


        // format log to inject imgs
        $loc_students = [];
        foreach ([$selLoc1,$selLoc2] as $i => $selLoc) {

            $selLoc_log = [];
            $selLoc_args = [];
            foreach ($selLoc as $colIndex) {
                $col = $sRef[$colIndex];
                $selLoc_log[] = '$'."{student_$col}";
                $selLoc_args["student_$col"] = self::getStudentProfessorTranslation($col);
            }

            $selLoc_i18n = array_keys($selLoc_args);
            $selLoc_log = implode(', ',$selLoc_log);

            $loc_students[$i+1] = [
                'log' => $selLoc_log,
                'args' => $selLoc_args,
                'i18n' => $selLoc_i18n
            ];
        }

        self::notifyAllPlayers('replaceStudents',clienttranslate('${player_name} replaces ${loc1_students} form School Entrance with ${loc2_students} ${loc2_name}'),[
            'player_id' => $id, 
            'player_name' => self::getActivePlayerName(),
            'character' => self::isCharacterActive(9)? 9 : 6,
            'sel_loc1' => $selLoc1,
            'sel_loc2' => $selLoc2,
            'loc1_students' => $loc_students[1],
            'loc2_students' => $loc_students[2],
            'loc2_name' => $loc2_name,
            'i18n' => ['loc2_name']
        ]);

        if (self::isCharacterActive(9)) {
            foreach ($loc2 as $col => $q) {
                if ($q >= self::getUniqueValueFromDb("SELECT $col FROM last_coin_gained_position WHERE player = $id")+3) {
                    self::gainCoin($id,$col);
                }

                self::resolveProfessorInfluence($col,$id);
            }
        }

        // stats?

        $this->gamestate->nextState('endAbility');
    }
}

function pickStudentColor($color) {
    if ($this->checkAction('pickStudentColor')) {

        $char;
        if (self::isCharacterActive(8)) $char = 8;
        if (self::isCharacterActive(11)) $char = 11;

        switch ($char) {
            case 8:
                self::dbQuery("UPDATE `character` SET `data` = '$color' WHERE id = 8");

                self::notifyAllPlayers('pickStudentColor',clienttranslate('${student} adds to no influence this turn'),[
                    'student' => [
                        'log' => '${student_'.$color.'}',
                        'args' => ["student_$color" => self::getStudentProfessorTranslation($color)],
                        'i18n' => ["student_$color"]
                    ]
                ]);

                break;
            
            case 11:
                self::notifyAllPlayers('pickStudentColor',clienttranslate('Every player returns 3 ${student} to the Student bag'),[
                    'student' => [
                        'log' => '${student_'.$color.'}',
                        'args' => ["student_$color" => self::getStudentProfessorTranslation($color)],
                        'i18n' => ["student_$color"]
                    ]
                ]);

                foreach (self::getObjectListFromDb("SELECT player_id FROM player ORDER BY player_turn_position ASC",true) as $pid) {
                    $q = self::getUniqueValueFromDb("SELECT $color FROM school WHERE player = $pid");
                    $q = max(0,$q-3);
                    self::dbQuery("UPDATE school SET $color = $q WHERE player = $pid");

                    self::notifyAllPlayers('returnStudentsToBag','',[
                        'player_id' => $pid,
                        'color' => $color,
                        'to_value' => $q
                    ]);
                }
                break;
        }

        $this->gamestate->nextState('endAbility');
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

    $playerAssistants = self::getObjectFromDb("SELECT `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8`, `9`, `10` FROM player_assistants WHERE player = $id");

    $hasFree = false;
    foreach ($playerAssistants as $a => $inHand) {
        if ($inHand && !in_array($a,$playedAssistants)) {
            $hasFree = true;
            break;
        }
    }

    if (!$hasFree) $playedAssistants = [];

    return ['assistants' => $playedAssistants];
}

function argMoveStudents() {

    $id = self::getActivePlayerId();

    $from_char = null;
    if (self::isCharacterActive(1)) $from_char = 1;
    if (self::isCharacterActive(10)) $from_char = 10;

    $student_actions = (self::getPlayersNumber() == 3)? 4 : 3;
    $entrance_students_base = (self::getPlayersNumber() == 3)? 9 : 7;
    $entrance_stuents_curr = self::getUniqueValueFromDb("SELECT green + red + yellow + pink + blue FROM school_entrance WHERE player = $id");

    $move_student_count = ($entrance_students_base - $entrance_stuents_curr) + 1;

    $freeTables = self::getObjectFromDb("SELECT green, red, yellow, pink, blue FROM school WHERE player = $id");
    $freeTables = array_keys(array_filter($freeTables, function($studentsNum) { return $studentsNum < 10; }));

    return ['from_char' => $from_char, 'free_tables' => $freeTables, 'stud_count' => $move_student_count,'stud_max' => $student_actions, 'avail_characters' => self::getAvailableCharacters($id)];
}

function argMoveMona() {

    $id = self::getActivePlayerId();

    $monaPos = self::getGameStateValue('mother_nature_pos');
    $steps = self::getUniqueValueFromDb("SELECT player_mona_steps FROM player WHERE player_id = $id");
    $groups = self::getAllIslandsGroups();

    $incMovement = self::isCharacterActive(3);
    if ($incMovement) {
        $steps += 2;
    }

    $from_char = null;
    if (self::isCharacterActive(2)) {
        $from_char = 2;
        $steps = count($groups);
    }
    
    $monaKey = array_search($monaPos,$groups);

    $destinations = [];
    $steps = min(count($groups),$steps);
    for ($i=0; $i < $steps; $i++) { 
        $destinations[] = $groups[($monaKey+$i+1)%count($groups)];
    }

    return ['from_char' => $from_char, 'destinations' => $destinations, 'incMovement' => $incMovement,'avail_characters' => self::getAvailableCharacters($id)];
}

function argCloudTileDrafting() {

    $id = self::getActivePlayerId();
    $cloudTiles = self::getObjectListFromDb("SELECT id FROM cloud WHERE (green + red + yellow + pink + blue) > 0",true);

    return ['cloudTiles' => $cloudTiles, 'avail_characters' => self::getAvailableCharacters($id)];
}

#endregion

/* --------------------- */
/* --- STATE ACTIONS --- */
/* --------------------- */
#region 

function stNextPlayerPlanning() {

    $id = self::getActivePlayerId();
    $turnPos = self::getUniqueValueFromDb("SELECT player_turn_position FROM player WHERE player_id = $id");
    $nextTurnPos = $turnPos+1;
    if ($nextTurnPos <= self::getPlayersNumber()) {

        // activate next player
        $np = self::getUniqueValueFromDb("SELECT player_id FROM player WHERE player_turn_position = $nextTurnPos");

        self::giveExtraTime($np);
        $this->gamestate->changeActivePlayer($np);
        $this->gamestate->nextState('nextTurn');

    } else {

        // attribute player order and mona steps

        $newOrder = self::getObjectListFromDb("SELECT a.player, a.assistant FROM played_assistants a JOIN player p ON a.player = p.player_id ORDER BY a.assistant ASC, p.player_turn_position ASC");
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

        self::giveExtraTime($firstPlayer);
        $this->gamestate->changeActivePlayer($firstPlayer);
        $this->gamestate->nextState('nextPhase');
    }

}

function stMoveAgain() {

    $id = self::getActivePlayerId();
    $total = self::getUniqueValueFromDb("SELECT (green + red + yellow + pink + blue) as total FROM school_entrance WHERE player = $id");

    if ($total > ((self::getPlayersNumber()==3)?5:4)) {
        $this->gamestate->nextState('again');
    } else $this->gamestate->nextState('next');
}

function stNextPlayerAction() {

    $id = self::getActivePlayerId();

    // clear modifiers if present
    if (self::getGameStateValue('characters') == 1) {
        self::dbQuery("UPDATE `character` SET `data` = '' WHERE id = 8");
        self::dbQuery("UPDATE `character` SET active = 0, used = 0");
        self::updateInfluenceData();
    }

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

function stEndCharacterAbility() {
    $prevState = $this->gamestate->states[self::getGameStateValue('charPausedState')];

    self::dbQuery("UPDATE `character` SET active = 0, used = 1 WHERE active = 1 AND used = 0 AND id != 3 AND id != 5 AND id != 7 AND id != 8 AND id != 12");
    self::notifyAllPlayers("endCharacterAbility",'',[]);

    $this->gamestate->nextState($prevState['name']);
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

                self::notifyAllPlayers("zombieGameEnd",clienttranslate("The game cannot continue after a player leaves"),[]);
                $this->gamestate->nextState( "zombiePass" );
                break;
        }

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
