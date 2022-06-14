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

<div id="main_game_area">
    <div id="islands_cont"></div>
    <!-- <div class="point"></div>

    <div class='hexwrap'>
        <div class='hex'>
            <div class='hexpart1'></div>
            <div class='hexpart2'></div>
            <div class='hexpart3'></div>
        </div>
    </div> -->
    
</div>


<script type="text/javascript">

let jstpl_point = "<div class='point' style='left:${left}px; top:${top}px;'></div>";
let jstpl_island = "<div id='island_${id}' class='hexwrap' style='left:${left}px; top:${top}px;'>\
                        <div class='hex'>\
                            <div class='hexpart1'></div>\
                            <div class='hexpart2'></div>\
                            <div class='hexpart3'></div>\
                        </div>\
                    </div>";

</script>  

{OVERALL_GAME_FOOTER}
