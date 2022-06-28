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
    <div id="main_game_area"> <!-- rename, misleading -->
        <div id='controls_div'>
            <div id='control_rotation' class='controls'>
                <svg id="rotate_left" class="svg_icon rotate_icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12,6C6.3,6,2,8.15,2,11c0,2.45,3.19,4.38,7.71,4.88l-.42.41a1,1,0,0,0,0,1.42,1,1,0,0,0,1.42,0l2-2a1,1,0,0,0,.21-.33,1,1,0,0,0,0-.76,1,1,0,0,0-.21-.33l-2-2a1,1,0,0,0-1.42,1.42l.12.11C6,13.34,4,12,4,11c0-1.22,3.12-3,8-3s8,1.78,8,3c0,.83-1.45,2-4.21,2.6A1,1,0,0,0,15,14.79a1,1,0,0,0,1.19.77C19.84,14.76,22,13.06,22,11,22,8.15,17.7,6,12,6Z"/>
                </svg>
                <svg id="rotate_right" class="svg_icon rotate_icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12,6C6.3,6,2,8.15,2,11c0,2.45,3.19,4.38,7.71,4.88l-.42.41a1,1,0,0,0,0,1.42,1,1,0,0,0,1.42,0l2-2a1,1,0,0,0,.21-.33,1,1,0,0,0,0-.76,1,1,0,0,0-.21-.33l-2-2a1,1,0,0,0-1.42,1.42l.12.11C6,13.34,4,12,4,11c0-1.22,3.12-3,8-3s8,1.78,8,3c0,.83-1.45,2-4.21,2.6A1,1,0,0,0,15,14.79a1,1,0,0,0,1.19.77C19.84,14.76,22,13.06,22,11,22,8.15,17.7,6,12,6Z"/>
                </svg>
                <!-- <input type="button" id='rotate_islands_left' value=' <- '>
                <input type="button" id='rotate_islands_right' value=' -> '> -->
            </div>
            
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
                <!-- <input type="button" id='scale_islands_minus' value=' - '>
                <input type="button" id='scale_islands_plus' value=' + '> -->
            </div>
        </div>
        <div id="islands_div">
            <div id="scale_wrap">
                <div id="perspective_wrap">
                    <div id="islands_cont">
                    </div>
                </div>
            </div>
        </div>
        <div id='game_area_bottom'>
            <div id='heroes'></div>
            <div id='students_draft'>
                <div id='students_clouds_div'></div>
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
let jstpl_island = "<div id='island_${pos}'class='island island_type_${type}' style='--angle: ${angle}deg; left:${left}px; top:${top}px;'>\
                        <div class='influence_cont' style='--angle: -${angle}deg; --offLeft: ${left}px; --offTop: ${top}px;'>\
                            <div class='students_influence'></div>\
                        </div>\
                    </div>"
let jstpl_island_group = "<div id='island_group_${id}' class='island_group'></div>";

let jstpl_game_player_board =  "<div id='game_player_board_${id}' class='game_player_board' style='border-color:#${color}'>\
                                    <span class='school_name'>${name}</span>\
                                    <div class='school_entrance school_room'></div>\
                                    <div class='school_hall school_room'>\
                                        <div class='students_tables'>\
                                            <div class='table_green students_table'></div>\
                                            <div class='table_red students_table'></div>\
                                            <div class='table_yellow students_table'></div>\
                                            <div class='table_pink students_table'></div>\
                                            <div class='table_blue students_table'></div>\
                                        </div>\
                                        <div class='teachers_table'></div>\
                                    </div>\
                                    <div class='school_yard school_room'></div>\
                                </div>";

let jstpl_hero = "<div id='hero_${n}' class='hero card'></div>";
let jstpl_cloud = "<div id='cloud_${id}' class='cloud_type_${type} students_cloud'></div>";

let jstpl_student = "<div class='student_${color} student'></div>";
let jstpl_teacher = "<div class='teacher_${color} teacher'></div>";

let jstpl_tower = "<div class='tower_${color} tower'></div>";
let jstpl_mother_nature = "<div id='mother_nature'></div>";

let jstpl_coin = "<div class='coin'></div>";

let jstpl_player_board =    "<div id='inner_player_board_${pId}' class='inner_player_board'>\
                                <div class='player_coins'><div class='coin'></div><span id='coins_${pId}'></span></div>\
                                <div class='player_turn_order'><div class='mona_icon'></div><span id='turn_order_${pId}'></span></div>\
                                <div class='player_students'>\
                                    <div class='green_counter color_counter' style='--col:#25ac74'>\
                                        <svg class='teacher_marker' xmlns='http://www.w3.org/2000/svg' version='1.1' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 30 30'>\
                                            <polygon class='hex_shape' points='30,15 22.5,28 7.5,28 0,15 7.5,2 22.5,2'></polygon>\
                                        </svg>\
                                        <div class='student_green student'></div>\
                                        <span id='green_students_${pId}'></span>\
                                    </div>\
                                    <div class='red_counter color_counter' style='--col:#e20913'>\
                                        <svg  class='teacher_marker' xmlns='http://www.w3.org/2000/svg' version='1.1' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 30 30'>\
                                            <polygon class='hex_shape' points='30,15 22.5,28 7.5,28 0,15 7.5,2 22.5,2'></polygon>\
                                        </svg>\
                                        <div class='student_red student'></div>\
                                        <span id='red_students_${pId}'></span>\
                                    </div>\
                                    <div class='yellow_counter color_counter'  style='--col:#f9b01d'>\
                                        <svg  class='teacher_marker' xmlns='http://www.w3.org/2000/svg' version='1.1' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 30 30'>\
                                            <polygon class='hex_shape' points='30,15 22.5,28 7.5,28 0,15 7.5,2 22.5,2'></polygon>\
                                        </svg>\
                                        <div class='student_yellow student'></div>\
                                        <span id='yellow_students_${pId}'></span>\
                                    </div>\
                                    <div class='pink_counter color_counter' style='--col:#f8c8df'>\
                                        <svg  class='teacher_marker' xmlns='http://www.w3.org/2000/svg' version='1.1' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 30 30'>\
                                            <polygon class='hex_shape' points='30,15 22.5,28 7.5,28 0,15 7.5,2 22.5,2'></polygon>\
                                        </svg>\
                                        <div class='student_pink student'></div>\
                                        <span id='pink_students_${pId}'></span>\
                                    </div>\
                                    <div class='blue_counter color_counter' style='--col:#3abff0'>\
                                        <svg  class='teacher_marker' xmlns='http://www.w3.org/2000/svg' version='1.1' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 30 30'>\
                                            <polygon class='hex_shape' points='30,15 22.5,28 7.5,28 0,15 7.5,2 22.5,2'></polygon>\
                                        </svg>\
                                        <div class='student_blue student'></div>\
                                        <span id='blue_students_${pId}'></span>\
                                    </div>\
                                </div>\
                            </div>";


</script>  

{OVERALL_GAME_FOOTER}
