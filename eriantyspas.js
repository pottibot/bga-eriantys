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
        constructor: function(){
            console.log('eriantyspas constructor');
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

            this.scaleMap = [
                {
                    scale: 0.8,
                    width: 850
                },
                {
                    scale: 1,
                    width: 850
                },
                {
                    scale: 1.2,
                    width: 1020
                },
                {
                    scale: 1.4,
                    width: 1190
                },
                {
                    scale: 1.6,
                    width: 1360
                },
                {
                    scale: 1.8,
                    width: 1530
                },
                {
                    scale: 2,
                    width: 1700
                }
            ]
        },
        
        setup: function(gamedatas) {
            console.log( "Starting game setup" );
            
            // Setting up player boards
            for( var player_id in gamedatas.players ) {
                var player = gamedatas.players[player_id];

                let isTeam = player_id == this.getCurrentPlayerId() || gamedatas.players[player_id].color == gamedatas.players[this.getCurrentPlayerId()].color;

                let schoolCont = document.querySelector(((isTeam)?'#team_school_area':'#opponents_school_area')+' .schools_cont');
                         
                schoolCont.insertAdjacentHTML('beforeend',this.format_block('jstpl_game_player_board', {
                    name: gamedatas.players[player_id].name,
                    id: player_id,
                    color: gamedatas.players[player_id].color
                }));

                for (const color in gamedatas.schools_entrance[player_id]) {
                    if (color != 'player') {
                        for (let i = 0; i < gamedatas.schools_entrance[player_id][color]; i++) {
                            document.querySelector(`#game_player_board_${player_id} .school_entrance`).insertAdjacentHTML('beforeend',this.format_block('jstpl_student', {color: color}));
                        }
                    }
                }

                for (const color in gamedatas.schools[player_id]) {

                    switch (color) {
                        case 'green':
                        case 'red':
                        case 'yellow':
                        case 'pink':
                        case 'blue':

                            let table = document.querySelector(`#game_player_board_${player_id} .table_${color}`);

                            for (let i = 0; i < gamedatas.schools[player_id][color]; i++) {
                                table.insertAdjacentHTML('beforeend',this.format_block('jstpl_student', {color: color}));
                            }
                            
                            break;

                        case 'green_teacher':
                        case 'red_teacher':
                        case 'yellow_teacher':
                        case 'pink_teacher':
                        case 'blue_teacher':

                            if (gamedatas.schools[player_id][color] == 1) {
                                document.querySelector(`#game_player_board_${player_id} .teachers_table`).insertAdjacentHTML('beforeend',this.format_block('jstpl_teacher', {color: color.split('_')[0]}));
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
                                document.querySelector(`#game_player_board_${player_id} .school_yard`).insertAdjacentHTML('beforeend',this.format_block('jstpl_tower', {color: tcol}));
                            }
                    
                        default:
                            break;
                    }
                }
            }

            if (Object.keys(this.gamedatas.players).length == 4) {

                let currBlack = gamedatas.players[this.getCurrentPlayerId()].color == '000000'
                console.log(currBlack);

                if (currBlack) {
                    document.querySelector('#team_school_area .team_name').innerHTML = _('Black Team');
                    $('team_school_area').style.setProperty("--color", 'black');

                    document.querySelector('#opponents_school_area .team_name').innerHTML = _('White Team');
                    $('opponents_school_area').style.setProperty("--color", 'white');
                } else {
                    document.querySelector('#team_school_area .team_name').innerHTML = _('White Team');
                    $('team_school_area').style.setProperty("--color", 'white');

                    document.querySelector('#opponents_school_area .team_name').innerHTML = _('Black Team');
                    $('opponents_school_area').style.setProperty("--color", 'black');
                }
            }

            // render point, origin to islands placement
            $('islands_cont').insertAdjacentHTML('beforeend',this.format_block('jstpl_point',{left: 0, top: 0}));

            // create islands groups
            gamedatas.islandGroups.forEach(g => {
                $('islands_cont').insertAdjacentHTML('beforeend',this.format_block('jstpl_island_group', {id: g}));
                $('island_group_'+g).addEventListener('click', evt => {console.log(evt.target.parentElement.id);})
            });

            // place islands in groups
            gamedatas.islands.forEach(i => {
                $('island_group_'+i.group).insertAdjacentHTML('beforeend',this.format_block('jstpl_island', {pos: i.pos, type: i.type, left: i.x, top: -i.y, angle: 60*Math.floor(Math.random() * (6 - 0) + 0)}));
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
                                document.querySelector(`#island_${island.island} .students_influence`).insertAdjacentHTML('beforeend',this.format_block('jstpl_student', {color: color}));
                            }
                            break;

                        case 'white_tower':
                        case 'grey_tower':
                        case 'black_tower':
                            if (island[color] == 1)
                                document.querySelector(`#island_${island.island} .influence_cont`).insertAdjacentHTML('beforeend',this.format_block('jstpl_tower', {color: color.split('_')[0]}));
                            break;
                    }
                }
            });

            // PLACE MONA
            document.querySelector(`#island_${gamedatas.mother_nature} .influence_cont`).insertAdjacentHTML('afterbegin',this.format_block('jstpl_mother_nature'));

            // SOME OF THIS PROBABLY SHOULD BE DONE WITH TEMPLATES

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

            // setup UI controls
            this.setupControls();
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },

        setupControls: function() {

            // handle rot control
            document.querySelectorAll('#control_rotation > svg').forEach(el => {
                el.addEventListener('click', evt => {
                    this.rotateIslands(((el.id == 'rotate_left')?1:-1));
                });
            });

            // handle mouse wheel control
            $('islands_div').addEventListener('wheel', evt =>{
                evt.preventDefault();

                this.rotateIslands(evt.wheelDelta/120);
            });

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

                    if (this.zoomIslands(1,false)) {
                        $('screen_full').style.display= '';
                        $('screen_normal').style.display= 'none';
                    } else {
                        $('screen_full').style.display= 'none';
                        $('screen_normal').style.display= '';
                    }
                });
            });
        },

        onScreenWidthChange: function() {
            console.log('SCREEND WIDTH CHANGED');

            let ui_w = $('game_ui').clientWidth;

            let newMin = Math.max(750, Math.min(ui_w, 850));
            
            $('main_game_area').style.minWidth = newMin + 'px';

            let min_islands_w = this.scaleMap.filter(sw => sw.scale == getComputedStyle($('main_game_area')).getPropertyValue("--scale"))[0].width;

            console.log('ui width',ui_w);
            console.log('islands current min width to scale',min_islands_w);

            if (ui_w < min_islands_w) {
                this.zoomIslands(-1);
            }
                
        },

        ///////////////////////////////////////////////////
        //// Game & client states
        
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

        ///////////////////////////////////////////////////
        //// Utility methods

        zoomIslands: function(d, effective=true) {

            let f = getComputedStyle($('game_ui')).getPropertyValue("--islands-fr").split('%')[0];
            f = +f + 5*d;

            let s = getComputedStyle($('main_game_area')).getPropertyValue("--scale");
            s = Number.parseFloat(+s + 0.2*d).toFixed(1);

            if (s < 1 || s > 2) return false;

            console.log(s);

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

        rotateIslands: function(d) {
            let r = getComputedStyle($('islands_cont')).getPropertyValue("--z-rot").split('deg')[0];
            r = +r + 30*d;
            $('islands_cont').style.setProperty("--z-rot", r+"deg");

            document.querySelectorAll('.influence_cont').forEach(el => {
                let r = getComputedStyle(el).getPropertyValue("--angle").split('deg')[0];
                r = +r + 30*-d;
                $(el).style.setProperty("--angle", r+"deg");
            });
        },


        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
        
        /* Example:
        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/eriantyspas/eriantyspas/myAction.html", { 
                                                                    lock: true, 
                                                                    myArgument1: arg1, 
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 }, 
                         this, function( result ) {
                            
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            
                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );        
        },        
        
        */

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your eriantyspas.game.php file.
        
        */
        setupNotifications: function() {
            console.log( 'notifications subscriptions setup' );
            
            // TODO: here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 

            dojo.subscribe('displayPoints', this, "notif_displayPoints");
            this.notifqueue.setSynchronous( 'joinIslandGroups', 0);

            dojo.subscribe('joinIslandGroups', this, "notif_joinIslandGroups");
            this.notifqueue.setSynchronous( 'joinIslandGroups', 2000);
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        
        notif_displayPoints: function(notif) {
            console.log('notif_displayPoints');
            console.log(notif);

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
   });             
});
