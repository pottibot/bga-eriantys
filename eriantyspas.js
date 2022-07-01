/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * eriantyspas implementation : © Pietro Luigi Porcedda <pietro.l.porcedda@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, declare) {

    return declare("bgagame.eriantyspas", ebg.core.gamegui, {

        // ------------- //
        // --- SETUP --- //
        // ------------- //
        // #region

        constructor: function(){

            console.log('eriantyspas constructor');

            this.counters = {};

            // needed for regulating UI containers size when "zooming" islands
            this.scaleMap = [
                {
                    scale: 0.8,
                    width: 560
                },
                {
                    scale: 1,
                    width: 700
                },
                {
                    scale: 1.2,
                    width: 840
                },
                {
                    scale: 1.4,
                    width: 980
                },
                {
                    scale: 1.6,
                    width: 1120
                },
                {
                    scale: 1.8,
                    width: 1260
                },
                {
                    scale: 2,
                    width: 1400
                }
            ]
        },
        
        setup: function(gamedatas) {

            console.log( "Starting game setup" );

            // INIT PLAYER BOARDS COUNTERS
            this.counters.playerBoard = {};
            
            // SETUP SCHOOLS, PLAYER BOARDS AND COUNTERS
            for( var player_id in gamedatas.players ) {
                var player = gamedatas.players[player_id];
                this.counters.playerBoard[player_id] = {};

                // setup counter
                let pb = $('player_board_'+player_id);
                pb.insertAdjacentHTML('beforeend',this.format_block('jstpl_player_board', {pId: player_id}));

                let counter = new ebg.counter();
                counter.create($('turn_order_'+player_id));
                counter.setValue(0);
                this.counters.playerBoard[player_id]['turnOrder'] = counter;

                let isTeam = player_id == this.getCurrentPlayerId() || gamedatas.players[player_id].color == gamedatas.players[this.getCurrentPlayerId()].color;

                let schoolCont = document.querySelector(((isTeam)?'#team_school_area':'#opponents_school_area')+' .schools_cont');
                         
                schoolCont.insertAdjacentHTML('beforeend',this.format_block('jstpl_school', {
                    name: gamedatas.players[player_id].name,
                    id: player_id,
                    color: gamedatas.players[player_id].color
                }));

                // PLACE STUDENTS AT SCHOOL ENTRANCE
                for (const color in gamedatas.schools_entrance[player_id]) {
                    if (color != 'player') {
                        for (let i = 0; i < gamedatas.schools_entrance[player_id][color]; i++) {
                            document.querySelector(`#school_${player_id} .school_entrance`).insertAdjacentHTML('beforeend',this.format_block('jstpl_student', {color: color}));
                        }
                    }
                }

                // PLACE STUDENTS TEACHERS AND TOWERS IN SCHOOL
                for (const color in gamedatas.schools[player_id]) {

                    switch (color) {
                        case 'green':
                        case 'red':
                        case 'yellow':
                        case 'pink':
                        case 'blue': {

                            let counter = new ebg.counter();
                            counter.create($(color+'_students_'+player_id));
                            counter.setValue(gamedatas.schools[player_id][color]);
                            this.counters.playerBoard[player_id]['coins'] = counter; 

                            let table = document.querySelector(`#school_${player_id} .table_${color}`);

                            for (let i = 0; i < gamedatas.schools[player_id][color]; i++) {
                                table.insertAdjacentHTML('beforeend',this.format_block('jstpl_student', {color: color}));
                            }
                            
                            break;
                        }

                        case 'green_teacher':
                        case 'red_teacher':
                        case 'yellow_teacher':
                        case 'pink_teacher':
                        case 'blue_teacher':

                            let tc = color.split('_')[0];
                            let teachers_table = document.querySelector(`#school_${player_id} .teachers_table`);
                            teachers_table.insertAdjacentHTML('beforeend',this.format_block('jstpl_teacher', {color: tc}));

                            if (gamedatas.schools[player_id][color] == 1) {

                                document.querySelector(`#player_board_${player_id} .${tc}_counter .teacher_marker`).style.visibility = 'unset';
                            } else {

                                teachers_table.lastElementChild.style.visibility = 'hidden';
                            }
                          
                            break;
                        
                        case 'towers':
                            let tcol = '';
                            switch (gamedatas.players[player_id].color) {
                                case '000000': tcol = 'black'; break; 
                                case 'ffffff': tcol = 'white'; break;
                                case '7b7b7b': tcol = 'grey'; break;
                            }
                            
                            for (let i = 0; i < gamedatas.schools[player_id]['towers']; i++) {
                                document.querySelector(`#school_${player_id} .school_yard`).insertAdjacentHTML('beforeend',this.format_block('jstpl_tower', {color: tcol}));
                            }
                    
                        case 'coins': {
                            let counter = new ebg.counter();
                            counter.create($('coins_'+player_id));
                            counter.setValue(gamedatas.schools[player_id]['coins']);
                            this.counters.playerBoard[player_id]['coins'] = counter; 
                            break;
                        }
                    }
                }
            }

            // SET TEAM NAMES AND COLORS (if 4 player game)
            if (Object.keys(this.gamedatas.players).length == 4) {

                let currBlack = gamedatas.players[this.getCurrentPlayerId()].color == '000000'
                console.log(currBlack);

                if (currBlack) {
                    document.querySelector('#team_school_area .team_name').innerHTML = _('Black Team');
                    $('team_school_area').style.setProperty("--color", 'black');
                    $('team_school_area').style.setProperty("--alt-color", 'white');

                    document.querySelector('#opponents_school_area .team_name').innerHTML = _('White Team');
                    $('opponents_school_area').style.setProperty("--color", 'white');
                    $('opponents_school_area').style.setProperty("--alt-color", 'black');
                } else {
                    document.querySelector('#team_school_area .team_name').innerHTML = _('White Team');
                    $('team_school_area').style.setProperty("--color", 'white');
                    $('team_school_area').style.setProperty("--alt-color", 'black');

                    document.querySelector('#opponents_school_area .team_name').innerHTML = _('Black Team');
                    $('opponents_school_area').style.setProperty("--color", 'black');
                    $('opponents_school_area').style.setProperty("--alt-color", 'white');
                }
            }

            // render point, origin to islands placement
            // $('islands_cont').insertAdjacentHTML('beforeend',this.format_block('jstpl_point',{left: 0, top: 0}));

            // SETUP ISLANDS GROUPS
            gamedatas.islandGroups.forEach(g => {
                $('islands_cont').insertAdjacentHTML('beforeend',this.format_block('jstpl_island_group', {id: g}));
                $('island_group_'+g).addEventListener('click', evt => {console.log(evt.target.parentElement.id);})
            });

            // PLACE ISLANDS INSIDE GROUPS
            gamedatas.islands.forEach(i => {
                $('island_group_'+i.group).insertAdjacentHTML('beforeend',this.format_block('jstpl_island', {pos: i.pos, type: i.type, left: i.x, top: -i.y}));
            });

            // PLACE STUDENTS ON ISLANDS
            gamedatas.islands_influence.forEach(island => {

                for (const color in island) {
                    switch (color) {
                        case 'green':
                        case 'red':
                        case 'yellow':
                        case 'pink':
                        case 'blue':
                            for (let i = 0; i < island[color]; i++) {
                                document.querySelector(`#island_${island.island_pos} .students_influence`).insertAdjacentHTML('beforeend',this.format_block('jstpl_student', {color: color}));
                            }
                            break;

                        case 'white_tower':
                        case 'grey_tower':
                        case 'black_tower':
                            if (island[color] == 1)
                                document.querySelector(`#island_${island.island_pos} .influence_cont`).insertAdjacentHTML('beforeend',this.format_block('jstpl_tower', {color: color.split('_')[0]}));
                            break;
                    }
                }
            });

            // PLACE MOther NAture (MONA)
            document.querySelector(`#island_${gamedatas.mother_nature} .influence_cont`).insertAdjacentHTML('afterbegin',this.format_block('jstpl_mother_nature'));

            // PLACE HEROES
            for (let i = 0; i < 3; i++) {
                $('heroes').insertAdjacentHTML('beforeend',this.format_block('jstpl_hero', {n: i+1}));
            }

            // PLACE STUDENTS CLOUDS
            for (let i = 0; i < 4; i++) {
                $('students_clouds_div').insertAdjacentHTML('beforeend',this.format_block('jstpl_cloud', {id:i+1, type: 'multi'}));
            }

            // PLACE STUDENTS ON CLOUDS
            gamedatas.clouds.forEach(cloud => {

                for (const color in cloud) {
                    if (color != 'id') {
                        for (let i = 0; i < cloud[color]; i++) {
                            $('cloud_'+cloud.id).insertAdjacentHTML('beforeend',this.format_block('jstpl_student', {color: color}));
                        }
                    }
                }
            });

            // SETUP UI CONTROLS
            this.setupControls();
 
            // SETUP NOTIFICATIONS
            this.setupNotifications();

            console.log( "Ending game setup" );
        },

        // setup zoom and screen adapt controls for islands
        setupControls: function() {

            // handle zoom control
            document.querySelectorAll('#control_zoom > .zoom_icon').forEach(el => {
                el.addEventListener('click', evt => {
                    this.zoomIslands((evt.target.closest('#zoom_in'))?1:-1);
                });
            });

            // handle screen adapt control
            document.querySelectorAll('#control_zoom > .adapt_screen_icon').forEach(el => {
                el.addEventListener('click', evt => {

                    let full = evt.target.closest('#screen_full');

                    while (this.zoomIslands((full)? 1:-1)); // returns false and stops when zoom is no longer possible
                });
            });
        },

        // called everytime viewport changes size
        // adapts islands zoom to new viewport size. if gets smaller, then zoom out islands to not make it overflow
        onScreenWidthChange: function() {

            let ui_w = $('game_ui').clientWidth;
            
            let min_islands_w = this.scaleMap.filter(sw => sw.scale == getComputedStyle($('main_game_area')).getPropertyValue("--scale"))[0].width;
            $('main_game_area').style.minWidth = min_islands_w + 'px';

            if (ui_w < min_islands_w) {
                this.zoomIslands(-1);

                min_islands_w = this.scaleMap.filter(sw => sw.scale == getComputedStyle($('main_game_area')).getPropertyValue("--scale"))[0].width;
                $('main_game_area').style.minWidth = min_islands_w + 'px';
            }
                
        },

        //#endregion

        // ------------------- //
        // --- GAME STATES --- //
        // ------------------- //
        // #region
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
/*               
                 Example:
 
                 case 'myGameState':
                    
                    // Add 3 action buttons in the action status bar:
                    
                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                    break;
*/
                }
            }
        },
        
        // #endregion

        // ----------------------- //
        // --- UTILITY METHODS --- //
        // ----------------------- //
        // #region

        zoomIslands: function(d, effective=true) {

            let f = getComputedStyle($('game_ui')).getPropertyValue("--islands-fr").split('%')[0];
            f = +f + 5*d;

            let s = getComputedStyle($('main_game_area')).getPropertyValue("--scale");
            s = Number.parseFloat(+s + 0.2*d).toFixed(1);

            if (s < 0.8 || s > 2) return false;

            console.log(s);

            if (s == 2) {
                $('screen_full').style.display= 'none';
                $('screen_normal').style.display= '';
            } else {
                $('screen_full').style.display= '';
                $('screen_normal').style.display= 'none';
            }

            let w = this.scaleMap.filter(sw => sw.scale == s)[0].width;
            if ($('game_ui').offsetWidth >= w) {
                if (effective) {
                    $('game_ui').style.setProperty("--islands-fr", f+'%');
                    $('main_game_area').style.setProperty("--scale", s);
                    $('main_game_area').style.minWidth = w + 'px'; 
                }
                

                console.log('new islands fraction diff', f+'%');
                console.log('new islands scale',s);
                console.log($('islands_div').offsetHeight, $('game_ui').offsetWidth);
                console.log(s,w);

                return true;
            }
            
            return false
        },

        // #endregion

        // ---------------------- //
        // --- PLAYER ACTIONS --- //
        // ---------------------- //
        // #region


        // #endregion

        // --------------------- //
        // --- NOTIFICATIONS --- //
        // --------------------- //
        // #region

        setupNotifications: function() {
            console.log( 'notifications subscriptions setup' );

            dojo.subscribe('displayPoints', this, "notif_displayPoints");
            this.notifqueue.setSynchronous( 'joinIslandGroups', 0);

            dojo.subscribe('joinIslandGroups', this, "notif_joinIslandGroups");
            this.notifqueue.setSynchronous( 'joinIslandGroups', 2000);
        },

        // debugging notif
        notif_displayPoints: function(notif) {

            document.querySelectorAll('.point').forEach(p => {
                p.remove();
            });

            notif.args.points.forEach(p => {
                $('islands_cont').insertAdjacentHTML('beforeend',this.format_block('jstpl_point', {left: p.x, top: -p.y}));
            });
        },

        notif_joinIslandGroups: function(notif) {
            console.log(notif.args);

            let transitionCount = 0;

            for (const gNum in notif.args.groups) {
                let g = notif.args.groups[gNum];
                document.querySelectorAll(`#island_group_${g.id} > .island`).forEach(island => {
                    island.style.left = (+island.style.left.split('px').shift() + g.translation.x) + 'px';
                    island.style.top = (+island.style.top.split('px').shift() - g.translation.y) + 'px';

                    island.ontransitionend = () => {
                        transitionCount++
                        //console.log(transitionCount,notif.args.islandsCount);
                        //console.log(transitionCount == notif.args.islandsCount*2);
                        if (transitionCount == notif.args.islandsCount*2) {
                            //console.log('in');

                            let g1 = $('island_group_'+notif.args.groups.g1.id);
                            let g2 = $('island_group_'+notif.args.groups.g2.id);
                            //console.log(g1,g2);

                            g2.innerHTML += g1.innerHTML;

                            g1.remove();
                        }
                    }
                })
            };

            /* let joinGroup = notif.args.groups.filter(g => g.id != notif.args.groupTo).pop()['id'];
            console.log(joinGroup); */
        },

        // #endregion
   });             
});
