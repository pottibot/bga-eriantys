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

<div id="game_ui">
    <!-- <div id='pieces_movement_oversurface_cont'></div> -->
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
    <div id="main_game_area">
        <div id='controls_div'>            
            <div id='control_zoom' class='controls'>
                <svg version="1.1" id="zoom_out" class="svg_icon zoom_icon"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve">
                    <g>
                        <path d="M497.938,430.063l-112-112c-0.367-0.367-0.805-0.613-1.18-0.965C404.438,285.332,416,248.035,416,208
                            C416,93.313,322.695,0,208,0S0,93.313,0,208s93.305,208,208,208c40.035,0,77.332-11.563,109.098-31.242
                            c0.354,0.375,0.598,0.813,0.965,1.18l112,112C439.43,507.313,451.719,512,464,512c12.281,0,24.57-4.688,33.938-14.063
                            C516.688,479.203,516.688,448.797,497.938,430.063z M64,208c0-79.406,64.602-144,144-144s144,64.594,144,144
                            c0,79.406-64.602,144-144,144S64,287.406,64,208z"/>
                        <path d="M272,176H144c-17.672,0-32,14.328-32,32s14.328,32,32,32h128c17.672,0,32-14.328,32-32S289.672,176,272,176z"/>
                    </g>
                </svg>
                <svg version="1.1" id="zoom_in" class="svg_icon zoom_icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve">
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
                <svg version="1.1" id="screen_full" class="svg_icon adapt_screen_icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve">
                    <g>
                        <path d="M192,64H32C14.328,64,0,78.328,0,96v96c0,17.672,14.328,32,32,32s32-14.328,32-32v-64h128c17.672,0,32-14.328,32-32
                            S209.672,64,192,64z"/>
                        <path d="M480,64H320c-17.672,0-32,14.328-32,32s14.328,32,32,32h128v64c0,17.672,14.328,32,32,32s32-14.328,32-32V96
                            C512,78.328,497.672,64,480,64z"/>
                        <path d="M480,288c-17.672,0-32,14.328-32,32v64H320c-17.672,0-32,14.328-32,32s14.328,32,32,32h160c17.672,0,32-14.328,32-32v-96
                            C512,302.328,497.672,288,480,288z"/>
                        <path d="M192,384H64v-64c0-17.672-14.328-32-32-32S0,302.328,0,320v96c0,17.672,14.328,32,32,32h160c17.672,0,32-14.328,32-32
                            S209.672,384,192,384z"/>
                    </g>
                </svg>
                <svg version="1.1" id="screen_normal" class="svg_icon adapt_screen_icon" style="display:none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve">
                    <g>
                        <path d="M192,64c-17.672,0-32,14.328-32,32v64H32c-17.672,0-32,14.328-32,32s14.328,32,32,32h160c17.672,0,32-14.328,32-32V96
                            C224,78.328,209.672,64,192,64z"/>
                        <path d="M320,224h160c17.672,0,32-14.328,32-32s-14.328-32-32-32H352V96c0-17.672-14.328-32-32-32s-32,14.328-32,32v96
                            C288,209.672,302.328,224,320,224z"/>
                        <path d="M480,288H320c-17.672,0-32,14.328-32,32v96c0,17.672,14.328,32,32,32s32-14.328,32-32v-64h128c17.672,0,32-14.328,32-32
                            S497.672,288,480,288z"/>
                        <path d="M192,288H32c-17.672,0-32,14.328-32,32s14.328,32,32,32h128v64c0,17.672,14.328,32,32,32s32-14.328,32-32v-96
                            C224,302.328,209.672,288,192,288z"/>
                    </g>
                </svg>
            </div>
        </div>
        <div id="islands_div">
            <div id="islands_cont">
            </div>
        </div>
        <div id='game_area_bottom'>
            <div id='heroes'></div>
            <div id='students_draft'>
                <div id='cloud_tiles_div'></div>
                <div id='students_bag'></div>
            </div>
        </div>
    </div>
    <div id="team_school_area" class='schools_area'>
        <div class='team_name'></div>
        <div class='schools_cont'></div>
    </div>
    <div id="opponents_school_area" class='schools_area'>
        <div class='team_name'></div>
        <div class='schools_cont'></div>
    </div>    
</div>

<script type="text/javascript">

let jstpl_point = "<div class='point' style='left:${left}px; top:${top}px;'></div>";
let jstpl_island = "<div id='island_${pos}'class='island island_type_${type}' style='left:${left}px; top:${top}px;'>\
                        <div class='influence_cont'>\
                            <div class='students_influence'></div>\
                            <div class='students_influence_alt'></div>\
                        </div>\
                    </div>"
let jstpl_island_group = "<div id='island_group_${id}' class='island_group'></div>";

let jstpl_assistant = "<div class='assistant_${n} assistant card' data-n='${n}'></div>";
let jstpl_assistant_placeholder = "<div id='placeholder_${id}' class='assistant_placeholder' style='--color: ${color}; --alt-color: ${altcol}'><span class='placeholder_lable'>${name}</span></div>";

let jstpl_hero = "<div id='hero_${n}' class='hero card'></div>";
let jstpl_cloud = "<div id='cloud_${id}' class='cloud_type_${type} cloud_tile'></div>";

let jstpl_student = "<div class='student_${color} student'></div>";
let jstpl_professor = "<div class='professor_${color} professor'></div>";

let jstpl_tower = "<div class='tower_${color} tower'></div>";
let jstpl_mother_nature = "<div id='mother_nature'></div>";

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
