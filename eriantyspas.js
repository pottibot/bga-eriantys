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

        },
        
        setup: function(gamedatas) {
            console.log( "Starting game setup" );
            
            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                         
                $((player_id == this.getCurrentPlayerId())? 'player_school_area' : 'opponents_school_area').insertAdjacentHTML('beforeend',this.format_block('jstpl_game_player_board', {
                    id: player_id,
                    color: gamedatas.players[player_id].color
                }));
            }

            $('islands_cont').insertAdjacentHTML('beforeend',this.format_block('jstpl_point',{left: 0, top: 0}));

            gamedatas.islandGroups.forEach(g => {
                $('islands_cont').insertAdjacentHTML('beforeend',this.format_block('jstpl_island_group', {id: g}));
                $('island_group_'+g).addEventListener('click', evt => {console.log(evt.target.parentElement.id);})
            });

            gamedatas.islands.forEach(i => {
                $('island_group_'+i.group).insertAdjacentHTML('beforeend',this.format_block('jstpl_island', {pos: i.pos, type: i.type, left: i.x, top: -i.y, angle: 60*Math.floor(Math.random() * (6 - 0) + 0)}));
            });

            // handle rot control
            document.querySelectorAll('#control_rotation > input').forEach(el => {
                el.addEventListener('click', evt => {
                    let r = getComputedStyle($('islands_cont')).getPropertyValue("--z-rot").split('deg')[0];
                    r = +r + 30*((el.id == 'rotate_islands_right')?1:-1);

                    $('islands_cont').style.setProperty("--z-rot", r+"deg");
                })
            })

            // handle zoom control
            document.querySelectorAll('#control_zoom > input').forEach(el => {
                el.addEventListener('click', evt => {
                    let s = getComputedStyle($('islands_div')).getPropertyValue("--scale");
                    s = +s + 0.2*((el.id == 'scale_islands_plus')?1:-1);
                    $('islands_div').style.setProperty("--scale", s);
                })
            })

            $('islands_div').addEventListener('wheel', evt =>{
                evt.preventDefault();

                let r = getComputedStyle($('islands_cont')).getPropertyValue("--z-rot").split('deg')[0];
                r = +r + 30*evt.wheelDelta/120;

                $('islands_cont').style.setProperty("--z-rot", r+"deg");
            })


            for (let i = 0; i < 3; i++) {
                $('heroes').insertAdjacentHTML('beforeend',this.format_block('jstpl_hero', {n: i+1}));
            }
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
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

        test: function() {



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
