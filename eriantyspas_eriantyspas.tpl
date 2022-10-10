{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- eriantyspas implementation : © Pietro Luigi Porcedda <pietro.l.porcedda@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->

<div class="player-board" id="player_board_config">
    <div id="settings-icon">
        <svg id="cog-icon" xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24">
            <path d="M21.32,9.55l-1.89-.63.89-1.78A1,1,0,0,0,20.13,6L18,3.87a1,1,0,0,0-1.15-.19l-1.78.89-.63-1.89A1,1,0,0,0,13.5,2h-3a1,1,0,0,0-.95.68L8.92,4.57,7.14,3.68A1,1,0,0,0,6,3.87L3.87,6a1,1,0,0,0-.19,1.15l.89,1.78-1.89.63A1,1,0,0,0,2,10.5v3a1,1,0,0,0,.68.95l1.89.63-.89,1.78A1,1,0,0,0,3.87,18L6,20.13a1,1,0,0,0,1.15.19l1.78-.89.63,1.89a1,1,0,0,0,.95.68h3a1,1,0,0,0,.95-.68l.63-1.89,1.78.89A1,1,0,0,0,18,20.13L20.13,18a1,1,0,0,0,.19-1.15l-.89-1.78,1.89-.63A1,1,0,0,0,22,13.5v-3A1,1,0,0,0,21.32,9.55ZM20,12.78l-1.2.4A2,2,0,0,0,17.64,16l.57,1.14-1.1,1.1L16,17.64a2,2,0,0,0-2.79,1.16l-.4,1.2H11.22l-.4-1.2A2,2,0,0,0,8,17.64l-1.14.57-1.1-1.1L6.36,16A2,2,0,0,0,5.2,13.18L4,12.78V11.22l1.2-.4A2,2,0,0,0,6.36,8L5.79,6.89l1.1-1.1L8,6.36A2,2,0,0,0,10.82,5.2l.4-1.2h1.56l.4,1.2A2,2,0,0,0,16,6.36l1.14-.57,1.1,1.1L17.64,8a2,2,0,0,0,1.16,2.79l1.2.4ZM12,8a4,4,0,1,0,4,4A4,4,0,0,0,12,8Zm0,6a2,2,0,1,1,2-2A2,2,0,0,1,12,14Z"/>
        </svg>
        <div id="settings-arrow"></div>
    </div>
    <div id="settings-options">
        <div id="islands-scale-pref">
            <div class='pref-lable'>{ISLANDS_SIZE}:</div>
            <svg version="1.1" id="zoom-out" class='svg-zoom' xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve">
                <g>
                    <path d="M497.938,430.063l-112-112c-0.367-0.367-0.805-0.613-1.18-0.965C404.438,285.332,416,248.035,416,208
                        C416,93.313,322.695,0,208,0S0,93.313,0,208s93.305,208,208,208c40.035,0,77.332-11.563,109.098-31.242
                        c0.354,0.375,0.598,0.813,0.965,1.18l112,112C439.43,507.313,451.719,512,464,512c12.281,0,24.57-4.688,33.938-14.063
                        C516.688,479.203,516.688,448.797,497.938,430.063z M64,208c0-79.406,64.602-144,144-144s144,64.594,144,144
                        c0,79.406-64.602,144-144,144S64,287.406,64,208z"/>
                    <path d="M272,176H144c-17.672,0-32,14.328-32,32s14.328,32,32,32h128c17.672,0,32-14.328,32-32S289.672,176,272,176z"/>
                </g>
            </svg><input type="range" id="islands-scale" min="500" max="1500" value="800" style="margin: 5px;position: relative;top: 3px;"><svg version="1.1" id="zoom-in" class='svg-zoom' xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve">
                <g>
                    <path d="M497.938,430.063l-112-112c-0.313-0.313-0.637-0.607-0.955-0.909C404.636,285.403,416,248.006,416,208
                        C416,93.313,322.695,0,208,0S0,93.313,0,208s93.305,208,208,208c40.007,0,77.404-11.364,109.154-31.018
                        c0.302,0.319,0.596,0.643,0.909,0.955l112,112C439.43,507.313,451.719,512,464,512c12.281,0,24.57-4.688,33.938-14.063
                        C516.688,479.203,516.688,448.797,497.938,430.063z M64,208c0-79.406,64.602-144,144-144s144,64.594,144,144
                        c0,79.406-64.602,144-144,144S64,287.406,64,208z"/>
                    <path d="M272,176h-32v-32c0-17.672-14.328-32-32-32s-32,14.328-32,32v32h-32c-17.672,0-32,14.328-32,32s14.328,32,32,32h32v32
                        c0,17.672,14.328,32,32,32s32-14.328,32-32v-32h32c17.672,0,32-14.328,32-32S289.672,176,272,176z"/>
                </g>
            </svg>
        </div>
        <div id="island-influence-pref">
            <div class='pref-lable' style="display: inline-block;">{ISLAND_INFLUENCE}:</div>
            <select id="pref_select_104">
            </select>
        </div>
        <div id="influence-detector-pref">
            <div class='pref-lable' style="display: inline-block;">{INFLUENCE_DETECTOR}:</div>
            <select id="pref_select_100">
            </select>
        </div>
        <div id="opponents-school-pref">
            <div class='pref-lable' style="display: inline-block;">{OPPONENT_SCHOOLS}:</div>
            <select id="pref_select_101">
            </select>
        </div>
        <div id="assistant-drawer-pref">
            <div class='pref-lable' style="display: inline-block;">{ASSISTANT_DRAWER}:</div>
            <select id="pref_select_102">
            </select>
        </div>
        <div id="pieces-aspect-pref">
            <div class='pref-lable' style="display: inline-block;">{PIECES_ASPECT}:</div>
            <select id="pref_select_103">
            </select>
        </div>
    </div>
</div>
<div id='assistant_cards_drawer'>
    <div id='assistant_cards_div'>
        <div id='assistant_cards_myhand'>
            <span id='myhand_lable'></span>
        </div>
        <div id='assistant_cards_played'>
            <span id='played_lable'></span>
        </div>
    </div>
</div>
<div id='assistants_arrow_cont'>
    <svg id='assistants_drawer_arrow' class='open_drawer' xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="25px" height="25px" viewBox="0 0 30.021 30.021" xml:space="preserve">
        <g>
            <path d="M28.611,13.385l-11.011,9.352c-0.745,0.633-1.667,0.949-2.589,0.949c-0.921,0-1.842-0.316-2.589-0.949L1.411,13.385   c-1.684-1.43-1.89-3.954-0.46-5.638c1.431-1.684,3.955-1.89,5.639-0.459l8.421,7.151l8.42-7.151   c1.686-1.43,4.209-1.224,5.639,0.459C30.5,9.431,30.294,11.955,28.611,13.385z"/>
        </g>
    </svg>
</div>
<div id="game_ui">
    <div id="main_game_area">
        <div id="islands_div">
            <span id="islands_groups">{ISLANDS_GROUPS}: <span id="groups_counter"></span></span>
            <div id="islands_cont">
            </div>
        </div>
        <div id='game_area_bottom'>
            <div id='characters'></div>
            <div id='students_draft'>
                <div id='cloud_tiles_div'></div>
                <div id='students_bag'></div>
            </div>
        </div>
    </div>
    <div id="players_school">
        <div id="team_school_area" class='schools_area'>
            <div class='team_name'></div>
            <div class='schools_cont'></div>
        </div>
        <div id="opponents_school_area" class='schools_area'>
            <div class='team_name'></div>
            <div class='schools_cont'></div>
        </div>  
    </div>
</div>

<script type="text/javascript">

let jstpl_point = "<div class='point' style='left:${left}px; top:${top}px;'></div>";
let jstpl_island = "<div id='island_${pos}'class='island island_type_${type}' style='left:${left}px; top:${top}px;'>\
                        <div class='mother_nature mock'></div>\
                        <div class='tower mock'></div>\
                        <div class='influence_cont'>\
                            <div class='students_influence'></div>\
                            <div class='students_influence_alt'>\
                                <span class='influence_green influence_count'><span id='influence_green_${pos}'></span><div class='student_green student'></div></span>\
                                <span class='influence_red influence_count'><span id='influence_red_${pos}'></span><div class='student_red student'></div></span>\
                                <span class='influence_yellow influence_count'><span id='influence_yellow_${pos}'></span><div class='student_yellow student'></div></span>\
                                <span class='influence_pink influence_count'><span id='influence_pink_${pos}'></span><div class='student_pink student'></div></span>\
                                <span class='influence_blue influence_count'><span id='influence_blue_${pos}'></span><div class='student_blue student'></div></span>\
                            </div>\
                        </div>\
                        <div class='factions_influence'></div>\
                    </div>"

let jstpl_island_group = "<div id='island_group_${id}' class='island_group'></div>";
let jstpl_island_faction_influence = "<span class='faction_influence_indicator' style='--col: #${col}; --invcol: #${invcol}'>${num}</span>";

let jstpl_assistant = "<div class='assistant_${n} assistant card' data-n='${n}'></div>";
let jstpl_assistant_placeholder = "<div id='placeholder_${id}' class='assistant_placeholder' style='--color: ${color}; --alt-color: ${altcol}'><span class='placeholder_lable'>${name}</span></div>";

let jstpl_character = "<div id='character_${n}' class='character card'></div>";
let jstpl_cloud = "<div id='cloud_${id}' class='cloud_type_${type} cloud_tile'></div>";

let jstpl_student = "<div class='student_${color} student'></div>";
let jstpl_professor = "<div class='professor_${color} professor'></div>";

let jstpl_tower = "<div class='tower_${color} tower'></div>";
let jstpl_mother_nature = "<div class='mother_nature'></div>";

let jstpl_coin = "<div class='coin'></div>";
let jstpl_turn_position_indicator = "<div class='turn_position_${turnPos} turn_position turn_indicator'></div>";
let jstpl_turn_steps_indicator = "<div class='mona_movement_${steps} mona_movement turn_indicator'></div>";

let jstpl_school =  "<div id='school_${id}' class='school' style='--color: ${color}; --alt-color: ${altcol}'>\
                        <span class='school_name'>${name}</span>\
                        <div class='school_entrance school_room'></div>\
                        <div class='school_hall school_room'>\
                            <div class='tables'>\
                                <div class='green_row'><div class='students_table'></div><div class='professor_seat'></div></div>\
                                <div class='red_row'><div class='students_table'></div><div class='professor_seat'></div></div>\
                                <div class='yellow_row'><div class='students_table'></div><div class='professor_seat'></div></div>\
                                <div class='pink_row'><div class='students_table'></div><div class='professor_seat'></div></div>\
                                <div class='blue_row'><div class='students_table'></div><div class='professor_seat'></div></div>\
                            </div>\
                        </div>\
                        <div class='school_yard school_room'></div>\
                    </div>";

let jstpl_player_board =    "<div id='inner_player_board_${pId}' class='inner_player_board'>\
                            <div class='player_towers_count'><div class='tower tower_${col}'></div><span><span id='towers_${pId}'></span>/${towerMax}</span></div>\
                                <div class='player_coins'><div class='coin'></div><span id='coins_${pId}'></span></div>\
                                <div class='player_turn'>\
                                    <div class='turn_position_cont'>${pos}</div>\
                                    <div class='mona_movement_cont'>${steps}</div>\
                                </div>\
                                <div class='player_students'>\
                                    <div class='green_counter color_counter' style='--col:#25ac74'>\
                                        <svg class='professor_marker' xmlns='http://www.w3.org/2000/svg' version='1.1' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 30 30'>\
                                            <polygon class='hex_shape' points='30,15 22.5,28 7.5,28 0,15 7.5,2 22.5,2'></polygon>\
                                        </svg>\
                                        <div class='student_green student'></div>\
                                        <span id='green_students_${pId}'></span>\
                                    </div>\
                                    <div class='red_counter color_counter' style='--col:#e20913'>\
                                        <svg  class='professor_marker' xmlns='http://www.w3.org/2000/svg' version='1.1' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 30 30'>\
                                            <polygon class='hex_shape' points='30,15 22.5,28 7.5,28 0,15 7.5,2 22.5,2'></polygon>\
                                        </svg>\
                                        <div class='student_red student'></div>\
                                        <span id='red_students_${pId}'></span>\
                                    </div>\
                                    <div class='yellow_counter color_counter'  style='--col:#f9b01d'>\
                                        <svg  class='professor_marker' xmlns='http://www.w3.org/2000/svg' version='1.1' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 30 30'>\
                                            <polygon class='hex_shape' points='30,15 22.5,28 7.5,28 0,15 7.5,2 22.5,2'></polygon>\
                                        </svg>\
                                        <div class='student_yellow student'></div>\
                                        <span id='yellow_students_${pId}'></span>\
                                    </div>\
                                    <div class='pink_counter color_counter' style='--col:#f8c8df'>\
                                        <svg  class='professor_marker' xmlns='http://www.w3.org/2000/svg' version='1.1' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 30 30'>\
                                            <polygon class='hex_shape' points='30,15 22.5,28 7.5,28 0,15 7.5,2 22.5,2'></polygon>\
                                        </svg>\
                                        <div class='student_pink student'></div>\
                                        <span id='pink_students_${pId}'></span>\
                                    </div>\
                                    <div class='blue_counter color_counter' style='--col:#3abff0'>\
                                        <svg  class='professor_marker' xmlns='http://www.w3.org/2000/svg' version='1.1' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 30 30'>\
                                            <polygon class='hex_shape' points='30,15 22.5,28 7.5,28 0,15 7.5,2 22.5,2'></polygon>\
                                        </svg>\
                                        <div class='student_blue student'></div>\
                                        <span id='blue_students_${pId}'></span>\
                                    </div>\
                                </div>\
                            </div>";


</script>  

{OVERALL_GAME_FOOTER}
