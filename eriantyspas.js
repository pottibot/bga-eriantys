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
                pb.insertAdjacentHTML('beforeend',this.format_block('jstpl_player_board', {
                    pId: player_id,
                    pos: this.format_block('jstpl_turn_position_indicator', {turnPos: gamedatas.players[player_id].turnPos}),
                    steps: this.format_block('jstpl_turn_steps_indicator', {steps: gamedatas.players[player_id].monaSteps}),
                }));

                /* let counter = new ebg.counter();
                counter.create($('turn_order_'+player_id));
                counter.setValue(0);
                this.counters.playerBoard[player_id]['turnOrder'] = counter; */

                let isTeam = player_id == this.getCurrentPlayerId() || gamedatas.players[player_id].color == gamedatas.players[this.getCurrentPlayerId()].color;

                let schoolCont = document.querySelector(((isTeam)?'#team_school_area':'#opponents_school_area')+' .schools_cont');
                         
                schoolCont.insertAdjacentHTML('beforeend',this.format_block('jstpl_school', {
                    name: gamedatas.players[player_id].name,
                    id: player_id,
                    color: '#'+gamedatas.players[player_id].color,
                    altcol: '#'+gamedatas.players[player_id].alt_col
                }));

                // PLACE STUDENTS AT SCHOOL ENTRANCE
                for (const color in gamedatas.schools_entrance[player_id]) {
                    if (color != 'player') {
                        for (let i = 0; i < gamedatas.schools_entrance[player_id][color]; i++) {
                            document.querySelector(`#school_${player_id} .school_entrance`).insertAdjacentHTML('beforeend',this.format_block('jstpl_student', {color: color}));
                        }
                    }
                }

                // PLACE STUDENTS PROFESSORS AND TOWERS IN SCHOOL
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
                            this.counters.playerBoard[player_id][color] = counter; 

                            let table = document.querySelector(`#school_${player_id} .${color}_row .students_table`);

                            for (let i = 0; i < gamedatas.schools[player_id][color]; i++) {
                                table.insertAdjacentHTML('beforeend',this.format_block('jstpl_student', {color: color}));
                            }
                            
                            break;
                        }

                        case 'green_professor':
                        case 'red_professor':
                        case 'yellow_professor':
                        case 'pink_professor':
                        case 'blue_professor':

                            let tc = color.split('_')[0];
                            let professor_seat = document.querySelector(`#school_${player_id} .${tc}_row .professor_seat`);

                            if (gamedatas.schools[player_id][color] == 1) {

                                professor_seat.insertAdjacentHTML('beforeend',this.format_block('jstpl_professor', {color: tc}));
                                document.querySelector(`#player_board_${player_id} .${tc}_counter .professor_marker`).style.visibility = 'unset';
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

            let currPID = this.getCurrentPlayerId();

            // PLACE ASSISTANT CARDS
            $('myhand_lable').innerHTML= _('My Assistant cards');
            $('played_lable').innerHTML= _('Assistants played');

            $('assistant_cards_played').style.setProperty('--players',Object.keys(gamedatas.players).length);

            for (const a in gamedatas.players_assistants[currPID]) {
                if (a != 'player' && gamedatas.players_assistants[currPID][a] == 1) {
                    $('assistant_cards_myhand').insertAdjacentHTML('beforeend',this.format_block('jstpl_assistant', {n: a}));
                }
            }

            for (let i = 0; i < Object.keys(gamedatas.players).length; i++) {

                let p = Object.values(gamedatas.players).filter(p => p.turnPos == i+1)[0];
                let col = p.color;
                console.log(i+1,p.id,col);

                $('assistant_cards_played').insertAdjacentHTML('beforeend',this.format_block('jstpl_assistant_placeholder',{color: '#'+col, altcol: '#'+p.alt_col, id: p.id, name:p.name}));

                let ph = $('assistant_cards_played').lastElementChild;

                if (gamedatas.played_assistants[p.id]) {
                    ph.insertAdjacentHTML('beforeend',this.format_block('jstpl_assistant',{n: gamedatas.played_assistants[p.id]}));
                }
            }

            // SET TEAM NAMES AND COLORS (if 4 player game)
            if (Object.keys(this.gamedatas.players).length == 4) {

                let col = '#'+gamedatas.players[this.getCurrentPlayerId()].color;
                let alt =  '#'+gamedatas.players[this.getCurrentPlayerId()].alt_col;

                let teamCol = (col == '#000000')? _('Black Team') : _('White Team');
                let oppCol = (col == '#000000')? _('White Team') : _('Black Team');

                $('team_school_area').style.setProperty("--color", col);
                $('team_school_area').style.setProperty("--alt-color", alt);
                document.querySelector('#team_school_area .team_name').innerHTML = teamCol;

                $('opponents_school_area').style.setProperty("--color", alt);
                $('opponents_school_area').style.setProperty("--alt-color", col);
                document.querySelector('#opponents_school_area .team_name').innerHTML = oppCol;
            }

            // render point, origin to islands placement
            // $('islands_cont').insertAdjacentHTML('beforeend',this.format_block('jstpl_point',{left: 0, top: 0}));

            // SETUP ISLANDS GROUPS
            gamedatas.islandsGroups.forEach(g => {
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
                $('cloud_tiles_div').insertAdjacentHTML('beforeend',this.format_block('jstpl_cloud', {id:i+1, type: 'multi'}));
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

            // handle drawer handle
            let arrow = $('assistants_drawer_arrow');
            console.log('arrow',arrow);
            arrow.addEventListener('click', evt => {

                console.log('click on drawer arrow');

                let drawer = $('assistant_cards_drawer');

                if (arrow.classList.contains('open_drawer')) {

                    console.log('opening assistant drawer');
                    
                    drawer.style.height = 'fit-content';
                    let h = drawer.offsetHeight;

                    drawer.style.height = '0px';
                    drawer.offsetHeight; // repaint

                    drawer.style.height = h + 'px';

                    arrow.classList.remove('open_drawer');
                    arrow.classList.add('close_drawer');

                } else {
                    console.log('closing assistant drawer');

                    let h = drawer.offsetHeight;
                    drawer.style.height = h + 'px';
                    drawer.offsetHeight; // repaint

                    drawer.style.height = '0px';

                    arrow.classList.remove('close_drawer');
                    arrow.classList.add('open_drawer');
                }
                
                //$('assistant_cards_div').style.height = '0px';
            })
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

            
            // adapt assistant drawer if open
            if ($('assistant_cards_drawer').offsetHeight != 0) {
                $('assistant_cards_drawer').style.height = 'fit-content';
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
        onEnteringState: function(stateName,args) {
            console.log('Entering state: '+stateName);
            console.log('State arguments: ',args.args);

            // way of calling state handlers dinamically without big f switch
            // Call appropriate method
            var methodName = "onEnteringState_" + stateName;
            if (this[methodName] !== undefined) {             
                console.log('Calling ' + methodName, args.args);
                this[methodName](args.args);
            }
        },

        onEnteringState_playAssistant: function(args) {

            this.openAssistantDrawer();
            
    
            this.gamedatas.gamestate.clientData = {};

            document.querySelectorAll('#assistant_cards_myhand .assistant').forEach( el => {

                if (args.assistants.includes(el.dataset.n)) el.classList.add('blocked');
                else el.classList.add('unselected');

                el.onclick = evt => {
                    console.log('clicked on assistant',el);

                    if (!this.isCurrentPlayerActive()) {
                        this.showMessage(_('It is not your turn'));
                        return;
                    }

                    if (el.classList.contains('blocked')) {
                        this.showMessage(_('You cannot play an Assistant that has already been played by another player'));
                        return;
                    }

                    if (el.classList.contains('selected')) {
                        console.log('unselecting assistant');
                        this.gamedatas.gamestate.clientData.selected = null;
                        el.classList.remove('selected');
                        el.classList.add('unselected');
                    } else {
                        console.log('selecting assistant');

                        document.querySelectorAll('#assistant_cards_myhand .assistant').forEach( el2 => {
                            el2.classList.remove('selected');
                            el2.classList.add('unselected');
                        });

                        el.classList.remove('unselected');
                        el.classList.add('selected');

                        this.gamedatas.gamestate.clientData.selected = el.dataset.n;
                    }
                }
            })
        },

        onEnteringState_moveStudents: function(args) {
            let entrance = document.querySelector(`#school_${this.getCurrentPlayerId()} .school_entrance`);

            entrance.classList.add('active');

            if (!this.isCurrentPlayerActive()) return;

            this.gamedatas.gamestate.clientData = {};

            let actionText = this.gamedatas.gamestate.descriptionmyturn;

            entrance.childNodes.forEach(student => {

                student.classList.add('selectable');

                student.onclick = evt => {

                    console.log('clicked on student');

                    if (!student.classList.contains('selected')) {

                        this.gamedatas.gamestate.descriptionmyturn = _('${you} must choose where to move your student');
                        this.updatePageTitle();

                        let previousSelected = document.querySelector(`.school_entrance .student.selected`);
                        if (previousSelected) {

                            previousSelected.classList.remove('selected');

                            let prev_table = document.querySelector(`#school_${this.getActivePlayerId()} .${this.getStudentElementColor(previousSelected)}_row .students_table`);
                            prev_table.classList.remove('active');
                            prev_table.onclick = null;
                        }

                        student.classList.add('selected');

                        this.gamedatas.gamestate.clientData.student = this.getStudentElementColor(student);

                        this.activateStudentsDestinations(student);

                    } else {

                        this.gamedatas.gamestate.descriptionmyturn = actionText;
                        this.updatePageTitle();

                        student.classList.remove('selected');

                        this.deactivateStudentsDestinations(student);
                    }                
                }
            });
        },

        onEnteringState_moveMona: function(args) {

            args.destinations.forEach(g => {
                let group = $('island_group_'+g);
                group.classList.add('active');

                group.onclick = evt => {

                    this.ajaxcallwrapper('moveMona',{group: g});
                }
            });
        },

        onEnteringState_cloudTileDrafting: function(args) {

            args.cloudTiles.forEach(c => {
                let cloud = $('cloud_'+c);
                cloud.classList.add('active');

                cloud.onclick = evt => {

                    this.ajaxcallwrapper('chooseCloudTile',{cloud: c});
                }
            });
        },

        onLeavingState: function(stateName) {
            console.log('Leaving state: '+stateName);
            
            switch ( stateName ) {
            }               
        }, 
    
        onUpdateActionButtons: function(stateName, args) {
            console.log('onUpdateActionButtons: '+stateName);
            console.log(args);
                      
            if( this.isCurrentPlayerActive()) {            
                var methodName = "onUpdateActionButtons_" + stateName;
                if (this[methodName] !== undefined) {             
                    console.log('Calling ' + methodName, args);
                    this[methodName](args);
                }
            }
        },

        onUpdateActionButtons_playAssistant: function(args) {

            this.addActionButton('confirmAssistant_button',_('Confirm'),evt => {
                let selected = this.gamedatas.gamestate.clientData.selected;
                if (selected) {
                    console.log('confirmed', selected);
                    this.ajaxcallwrapper('playAssistant',{n:selected});
                } else {
                    this.showMessage(_("You need to select an Assistant card to play"),'error')
                }
            })
        },
        
        // #endregion

        // ----------------------- //
        // --- UTILITY METHODS --- //
        // ----------------------- //
        // #region

        ajaxcallwrapper: function(action, args, handler) {
            if (!args) args = [];
                
            args.lock = true;
            if (this.checkAction(action)) {
                this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/" + action + ".html", args, this, (result) => { }, handler);
            }
        },    

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

        // WARNING: this will reset specific properties position, left, top
        moveElement: function(el, target, duration=0, delay=0, append = false, onEnd=()=>{}) {

            // get oversurface peices movement cont
            let movingSurface = $('overall-content');

            // get el parents for reattaching at anim end (if not append)
            let elParent = el.parentElement;

            // get all useful elements current pos
            let elPos = el.getBoundingClientRect();
            let targetPos = target.getBoundingClientRect();
            let movingSurfacePos = movingSurface.getBoundingClientRect();

            console.log('el',elPos.x,elPos.y);
            console.log('target',targetPos.x,targetPos.y);
            console.log('moving surface',movingSurfacePos.x,movingSurfacePos.y);

            // append el clone to moving oversurface
            let elClone = el.cloneNode()
            movingSurface.append(elClone);

            // create empty space where element was
            el.style.visibility = 'hidden';

            // position it on its current coordinates, but on oversurface
            elClone.style.position = 'absolute';
            elClone.style.left = (elPos.left - movingSurfacePos.left) + 'px';
            elClone.style.top = (elPos.top - movingSurfacePos.top) + 'px';

            elClone.offsetWidth; //repaint

            // calculate offset between el and target
            offset = {
                x: targetPos.left - elPos.left,
                y: targetPos.top - elPos.top
            }

            // now set transition
            elClone.style.transition = `all ${duration}ms ${delay}ms ease-in-out`;
            el.style.transition = `all 200ms ease-in-out`; // transition for disappearing empty space

            // set left, top properties given current pos in oversurface + calculated offset
            elClone.style.left = (elClone.offsetLeft + offset.x) + 'px';
            elClone.style.top = (elClone.offsetTop + offset.y) + 'px';

            // on animation end
            setTimeout(() => {

                // unset transition prop
                elClone.style.transition = '';

                // set size proprs to animate element occupied space disappearing
                el.style.width = '0px';
                el.style.height = '0px';

                setTimeout(() => {
                    el.remove();
                }, 200);

                if (append) {
                    // append element on previous parent or on target
                    target.append(elClone);

                    // reset positioning props
                    elClone.style.position = '';
                    elClone.style.left = '';
                    elClone.style.top = '';
                } else {

                    let elParentPos = elParent.getBoundingClientRect();
                    let elClonePos = elClone.getBoundingClientRect();

                    offset = {
                        x: elClonePos.left - elParentPos.left,
                        y: elClonePos.top - elParentPos.top 
                    }

                    elParent.append(elClone);

                    elClone.style.left = offset.x + 'px';
                    elClone.style.top = offset.y + 'px';                    
                }
                
                // call on end handler, if given
                onEnd();
                console.log('TRANSITION END');
            }, duration+delay);

            return elClone;
        },

        openAssistantDrawer: function(onEnd = () => {}) {

            if ($('assistants_drawer_arrow').classList.contains('open_drawer')) {

                $('assistant_cards_drawer').ontransitionend = () => {
                    console.log('DRAWER ANIM END');

                    onEnd();

                    $('assistant_cards_drawer').ontransitionend = '';
                }

                let event = new Event('click') 
                $('assistants_drawer_arrow').dispatchEvent(event);
            } else onEnd();
        },

        getStudentElementColor: function(el) {

            if (el.className.includes('green')) return 'green';
            if (el.className.includes('red')) return 'red';
            if (el.className.includes('yellow')) return 'yellow';
            if (el.className.includes('pink')) return 'pink';
            if (el.className.includes('blue')) return 'blue';
        },

        activateStudentsDestinations: function(selectedStudent) {

            // activate islands
            document.querySelectorAll('.island .students_influence').forEach(island => {
                island.classList.add('active');

                island.onclick = evt => {

                    let currentSel = this.gamedatas.gamestate.clientData.student;
                    
                    if (currentSel) {
                        this.ajaxcallwrapper('moveStudent',{student:currentSel, place:island.parentElement.parentElement.id.split('_')[1]});
                    } else {
                        this.showMessage(_("You need to select an Assistant card to play"),'error')
                    }
                }
            })

            // activate students table
            let students_table = document.querySelector(`#school_${this.getActivePlayerId()} .${this.getStudentElementColor(selectedStudent)}_row .students_table`);
            students_table.classList.add('active');

            students_table.onclick = evt => {

                let currentSel = this.gamedatas.gamestate.clientData.student;
                if (currentSel) {
                    this.ajaxcallwrapper('moveStudent',{student:currentSel});
                } else {
                    this.showMessage(_("You need to select an Assistant card to play"),'error')
                }
            }
        },

        deactivateStudentsDestinations: function(selectedStudent) {
            // deactivate islands
            document.querySelectorAll('.island .students_influence').forEach(island => {
                island.classList.remove('active');
                island.onclick = null;
            })

            // deactivate students table
            let students_table = document.querySelector(`#school_${this.getActivePlayerId()} .${this.getStudentElementColor(selectedStudent)}_row .students_table`);
            students_table.classList.remove('active');
            students_table.onclick = null;
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
            this.notifqueue.setSynchronous('joinIslandsGroups', 0);

            dojo.subscribe('playAssistant', this, "notif_playAssistant");
            this.notifqueue.setSynchronous('playAssistant');

            dojo.subscribe('resolvePlanning', this, "notif_resolvePlanning");
            this.notifqueue.setSynchronous('resolvePlanning');

            dojo.subscribe('moveStudent', this, "notif_moveStudent");
            this.notifqueue.setSynchronous('moveStudent',500);

            dojo.subscribe('gainProfessor', this, "notif_gainProfessor");
            this.notifqueue.setSynchronous('gainProfessor',500);

            dojo.subscribe('joinIslandsGroups', this, "notif_joinIslandsGroups");
            this.notifqueue.setSynchronous('joinIslandsGroups', 2000);
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

        notif_playAssistant: function(notif) {
            console.log(notif.args);

            this.openAssistantDrawer(() => {

                this.notifqueue.setSynchronousDuration(700);

                let target = $('placeholder_'+notif.args.player_id);
                let el;
    
                if (this.isCurrentPlayerActive()) {
    
                    document.querySelectorAll('#assistant_cards_myhand .assistant').forEach(a => {
                        a.classList.remove('blocked','selected','unselected');
                    });
    
                    el = document.querySelector(`.assistant_${notif.args.n}`);
                    this.moveElement(el,target,500,0,true);
    
                } else {
    
                    $('player_board_'+this.getActivePlayerId()).insertAdjacentHTML('beforeend',this.format_block('jstpl_assistant',{n: notif.args.n}));
                    el = $('player_board_'+this.getActivePlayerId()).lastElementChild;
                    
                    this.moveElement(el,target,500,0,true);
                }
            });
        },

        notif_resolvePlanning: function(notif) {
            console.log(notif.args);

            this.openAssistantDrawer(() => {

                this.notifqueue.setSynchronousDuration(100 + Object.keys(notif.args.players).length * 2000);

                let d = 100;
                notif.args.players.forEach(player => {

                    let a = document.querySelector(`#placeholder_${player.id}`);
                    let cont = $('assistant_cards_played');

                    // animate turn position indicator
                    setTimeout(() => {

                        cont.insertAdjacentHTML('beforeend',this.format_block('jstpl_turn_position_indicator',{turnPos: player.turn_pos}));
                        let indicator = cont.lastChild;

                        indicator = this.moveElement(indicator,a,0);

                        indicator.style.left = (indicator.offsetLeft + 4) + 'px';
                        indicator.style.top = (indicator.offsetTop + 4) + 'px';

                        let target = document.querySelector(`#player_board_${player.id} .turn_position_cont`);
                        indicator.onanimationend = () => {
                            indicator.classList.remove('bounce_animation');
                            let oldpos = document.querySelector(`#player_board_${player.id} .turn_position`);

                            this.moveElement(indicator,target,500,0,true,()=>{oldpos.remove()});
                        }

                        indicator.classList.add('bounce_animation');
                        
                    }, d);

                    setTimeout(() => {

                        cont.insertAdjacentHTML('beforeend',this.format_block('jstpl_turn_steps_indicator',{steps: player.steps}));
                        let indicator = cont.lastChild;

                        indicator = this.moveElement(indicator,a,0);

                        indicator.style.left = (indicator.offsetLeft + 73) + 'px';
                        indicator.style.top = (indicator.offsetTop + 4) + 'px';

                        let target = document.querySelector(`#player_board_${player.id} .mona_movement_cont`);
                        indicator.onanimationend = () => {
                            indicator.classList.remove('bounce_animation');
                            let oldsteps = document.querySelector(`#player_board_${player.id} .mona_movement`);

                            this.moveElement(indicator,target,500,0,true,()=>{oldsteps.remove()});
                        }

                        indicator.classList.add('bounce_animation');
                        
                    }, d+1000);

                    d = d+2000;
                });
            });
        },

        notif_moveStudent: function(notif) {
            console.log(notif.args);

            // fetch student and clean interactables
            let student;

            if (this.isCurrentPlayerActive()) {
                student = document.querySelector(`#school_${notif.args.player_id} .student.selected`);
                
                this.deactivateStudentsDestinations(student);

                student.classList.remove('selected');
                document.querySelectorAll(`#school_${notif.args.player_id} .school_entrance .student.selectable`).forEach(s => {
                    s.classList.remove('selectable');
                });
                
            } else {
                student = document.querySelector(`#school_${notif.args.player_id} .school_entrance .student_${notif.args.student}`);
            }

            document.querySelector(`#school_${notif.args.player_id} .school_entrance`).classList.remove('active');

            // fetch destination element
            let destination;

            if (notif.args.destination) {

                destination = document.querySelector(`#island_${notif.args.destination} .students_influence`);
            } else {
                // inc counter
                this.counters.playerBoard[notif.args.player_id][notif.args.student].incValue(1);
                destination = document.querySelector(`#school_${notif.args.player_id} .${notif.args.student}_row .students_table`);
            }

            console.log(student,destination);
            this.moveElement(student,destination,500,0,true);
        },

        notif_gainProfessor: function(notif) {

            // fetch student and clean interactables
            let professor;

            if (notif.args.player_2) {
                professor = document.querySelector(`#school_${notif.args.player_2} .${notif.args.color}_row .professor`);                
                
                document.querySelector(`#player_board_${notif.args.player_2} .${notif.args.color}_counter .professor_marker`).style.visibility = '';
            
            } else {
                $('player_board_'+notif.args.player_id).insertAdjacentHTML('beforeend',this.format_block('jstpl_professor',{color: notif.args.color}));
                professor = $('player_board_'+notif.args.player_id).lastElementChild;
            }

            document.querySelector(`#player_board_${notif.args.player_id} .${notif.args.color}_counter .professor_marker`).style.visibility = 'unset';

            // fetch destination element
            let destination;

            destination = document.querySelector(`#school_${notif.args.player_id} .${notif.args.color}_row .professor_seat`);

            console.log(professor,destination);
            this.moveElement(professor,destination,500,0,true);
        },

        notif_joinIslandsGroups: function(notif) {

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
