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
        },
        
        setup: function(gamedatas) {

            console.log( "Starting game setup" );

            // INIT PLAYER BOARDS COUNTERS
            this.counters.playerBoard = {};
            
            // SETUP SCHOOLS, PLAYER BOARDS AND COUNTERS
            for( var player_id in gamedatas.players ) {
                var player = gamedatas.players[player_id];
                this.counters.playerBoard[player_id] = {};

                let towerMax = (Object.keys(gamedatas.players).length == 3)? 6 : 8;

                let pb = $('player_board_'+player_id);
                pb.insertAdjacentHTML('beforeend',this.format_block('jstpl_player_board', {
                    pId: player_id,
                    col: this.getColorName(player.color),
                    towerMax: towerMax,
                    pos: this.format_block('jstpl_turn_position_indicator', {turnPos: gamedatas.players[player_id].turnPos}),
                    steps: this.format_block('jstpl_turn_steps_indicator', {steps: gamedatas.players[player_id].monaSteps}),
                }));

                this.addTooltip(`player_towers_count_${player_id}`,_("These are the player's/faction's towers. When all towers have been placed the player/faction wins the game"),'');
                this.addTooltip(`player_coins_cont_${player_id}`,_("These are the player's coins. They can be used to purchase one of the available Characters' special abilities for one turn"),'');
                this.addTooltip(`turn_position_cont_${player_id}`,_("This is the player's turn order. During the planning phase, playing an Assistant with a lower value, will allow you to go first."),'');
                this.addTooltip(`mona_movement_cont_${player_id}`,_("This is how far this player can move Mother Nature. The player/faction with most Influence on the Island where Mother Nature stops, will control it and place one of their Towers."),'');

                let towersCount = 0;
                for (const p in gamedatas.schools) {
                    if (gamedatas.players[p].color == player.color) {
                        towersCount += parseInt(gamedatas.schools[p].towers);
                    }
                } 

                // set tower counter
                let counter = new ebg.counter();
                counter.create($('towers_'+player_id));
                counter.setValue(towersCount);
                this.counters.playerBoard[player_id]['towers'] = counter;

                let isTeam = !this.isSpectator && (player_id == this.getCurrentPlayerId() || gamedatas.players[player_id].color == gamedatas.players[this.getCurrentPlayerId()].color);

                let schoolCont = document.querySelector(((isTeam)?'#team_school_area':'#opponents_school_area')+' .schools_cont');
                let rows = {'2': 4, '3': 3, '4': 2};         

                schoolCont.insertAdjacentHTML('beforeend',this.format_block('jstpl_school', {
                    name: gamedatas.players[player_id].name,
                    id: player_id,
                    color: '#'+gamedatas.players[player_id].color,
                    altcol: '#'+gamedatas.players[player_id].alt_col,
                    rows: rows[Object.keys(gamedatas.players).length]
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

                            let pc = color.split('_')[0];
                            let professor_seat = document.querySelector(`#school_${player_id} .${pc}_row .professor_seat`);

                            if (gamedatas.schools[player_id][color] == 1) {

                                professor_seat.insertAdjacentHTML('beforeend',this.format_block('jstpl_professor', {color: pc}));
                                document.querySelector(`#player_board_${player_id} .${pc}_counter .professor_marker`).style.visibility = 'unset';

                                let translated = dojo.string.substitute(_("This player controls the ${color} Professor. ${color} Tokens will count towards their Influence on the Islands"), {
                                    color: this.getColorTranslation(pc)
                                } );
            
                                this.addTooltip(`${pc}_counter_${player_id}`,translated,'');
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

            // PLACE PLAYED ASSISTANTS
            for (let i = 0; i < Object.keys(gamedatas.players).length; i++) {

                let p = Object.values(gamedatas.players).filter(p => p.turnPos == i+1)[0];
                let col = p.color;
                console.log(i+1,p.id,col);

                $('assistant_cards_played').insertAdjacentHTML('beforeend',this.format_block('jstpl_assistant_placeholder',{color: '#'+col, altcol: '#'+p.alt_col, id: p.id, name:p.name}));

                let ph = $('assistant_cards_played').lastElementChild;

                if (gamedatas.played_assistants[p.id].assistant) {
                    ph.insertAdjacentHTML('beforeend',this.format_block('jstpl_assistant',{n: gamedatas.played_assistants[p.id].assistant}));
                    if (gamedatas.played_assistants[p.id].old == 1) ph.lastElementChild.classList.add('old');
                }
            }

            if (this.isSpectator) {
                $('assistant_cards_myhand').style.display = 'none';
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
            } else document.querySelectorAll('.team_name').forEach(el => {el.style.display = 'none'});


            // SETUP ISLANDS GROUPS
            gamedatas.islandsGroups.forEach(g => {
                $('islands_cont').insertAdjacentHTML('beforeend',this.format_block('jstpl_island_group', {id: g}));
                //$('island_group_'+g).addEventListener('click', evt => {console.log(evt.target.parentElement.id);})
            });

            // set islands groups counter
            let counter = new ebg.counter();
            counter.create($('groups_counter'));
            counter.setValue(gamedatas.islandsGroups.length);
            this.counters.islands_groups = counter;

            this.addTooltip('islands_groups',_('Adjacent Islands merge when they fall under control of the same player/faction. When Islands group are 3 or less, the game will end immediatly.'),'');

            this.updateStudentsBagCounter(gamedatas.students_left);

            // PLACE ISLANDS INSIDE GROUPS
            gamedatas.islands.forEach(i => {
                let groupEl = $('island_group_'+i.group);
                groupEl.insertAdjacentHTML('beforeend',this.format_block('jstpl_island', {pos: i.pos, type: i.type, left: i.x, top: -i.y}));
            });

            this.updateFactionsIslandsInfluence(gamedatas.players_influence);

            // PLACE STUDENTS ON ISLANDS
            this.counters.islandsInfluence = {};

            gamedatas.islands_influence.forEach(island => {

                this.counters.islandsInfluence[island.island_pos] = {};

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

                            let couterEl = $(`influence_${color}_${island.island_pos}`);
                            let counter = new ebg.counter();
                            counter.create(couterEl);
                            counter.setValue(island[color]);
                            this.counters.islandsInfluence[island.island_pos][color] = counter;

                            if (counter.getValue() == 0) couterEl.parentElement.style.display = 'none';
                            break;

                        case 'white_tower':
                        case 'grey_tower':
                        case 'black_tower':
                            if (island[color] == 1) {
                                //document.querySelector(`#island_${island.island_pos} .influence_cont`).insertAdjacentHTML('beforeend',this.format_block('jstpl_tower', {color: color.split('_')[0]}));
                                let tower = document.querySelector(`#island_${island.island_pos} .tower`);
                                let towerCol = color.split('_')[0];
                                tower.classList.remove('mock');
                                tower.classList.add('tower_'+towerCol);
                            }
                                
                            break;
                        
                        case 'no_entry':
                            if (island[color] == 1) {
                                let token = document.querySelector(`#island_${island.island_pos} .no-entry_token`);
                                token.classList.remove('mock');
                            }
                            break;

                    }
                }
            });

            // PLACE MOther NAture (MONA)
            document.querySelector(`#island_${gamedatas.mother_nature} .mother_nature`).classList.remove('mock');
            document.querySelector('.mother_nature:not(.mock)').closest('.island').style.zIndex = 5;

            // PLACE CHARACTERS
            if (gamedatas.characters) {
                console.log('CHARACTERS',gamedatas.characters);
                for (const chId in gamedatas.characters) {
                    console.log('CHARACTER',chId);
                    let character = gamedatas.characters[chId];
                    $('characters').insertAdjacentHTML('beforeend',this.format_block('jstpl_character', {n: chId}));
                    let charEl = $('characters').lastElementChild;

                    charEl.insertAdjacentHTML('beforeend','<div class="character_coin"></div>');
                    let charCost = charEl.lastElementChild;

                    charCost.insertAdjacentHTML('beforeend',this.format_block('jstpl_coin'));
                    charCost.lastElementChild.insertAdjacentHTML('beforeend','<div class="cost_text">+1</div>');
                    if (character.cost_mod > 0) charCost.style.visibility = 'unset';

                    if (character.active == 1) charEl.classList.add("activated");
                    if (character.used == 1) charEl.classList.add("used");

                    if (character.data) {
                        let data = JSON.parse(character.data);
                        console.log(data);

                        charEl.insertAdjacentHTML('beforeend','<div class="character_tokens"></div>');
                        let charTokens = charEl.lastChild;

                        for (const tokenType in data) {
                            switch (tokenType) {
                                case 'students':
                                    charTokens.classList.add('type-students');

                                    let studentsSet = ['green','red','yellow','pink','blue'];

                                    data['students'].forEach(s => {
                                        charTokens.insertAdjacentHTML('beforeend',this.format_block('jstpl_student', {color: studentsSet[s]}));
                                    });
                                    
                                    break;

                                case 'noEntry':
                                    charTokens.classList.add('type-no-entry');

                                    for (let i = 0; i < data['noEntry']; i++) {
                                        charTokens.insertAdjacentHTML('beforeend','<div class="no-entry_token"></div>');
                                    }
                                    break;
                            }
                        }
                        
                    }
                }

                this.updateCharactersTooltips();
            } else {

                $('characters').style.display = 'none';
                $('cloud_tiles_div').classList.add('no-characters');

                document.querySelectorAll('.player_coins_cont').forEach(el => { el.style.visibility = 'hidden'});
            }

            // PLACE CLOUDS
            gamedatas.clouds.forEach((cloud,i) => {
                let type = (gamedatas.clouds.length == 2 || gamedatas.clouds.length == 4)? 'multi' : i+1;

                $('cloud_tiles_div').insertAdjacentHTML('beforeend',this.format_block('jstpl_cloud', {id:i+1, type: type}));
                
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
            
            // SETUP PREFERENCE OBSERVER
            this.initPreferencesObserver();

            console.log( "Ending game setup" );
        },

        // setup zoom and screen adapt controls for islands
        setupControls: function() {

            // SETTINGS ARROW
            let settings_panel = $('player_board_config');

            let settings_arrow = $('settings-arrow');
            let settings_options = $('settings-options');
            settings_arrow.addEventListener('click', evt => {

                settings_panel.style.height = 'auto';
                if (settings_arrow.classList.contains('open')) {
                    settings_arrow.classList.remove('open');
                    settings_options.style.height = '0px';
                    
                } else {
                    settings_arrow.classList.add('open');
                    settings_options.style.height = 'fit-content';
                    let h = settings_options.offsetHeight;
                    settings_options.style.height = '0px';
                    settings_options.offsetHeight;
                    settings_options.style.height = h+'px';
                }
            })

            // ASSITANT DRAWER ARROW
            let arrow = $('assistants_drawer_arrow');
            console.log('arrow',arrow);
            arrow.addEventListener('click', () => {

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

                    if (document.documentElement.classList.contains('drawer-fixed')) return;

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

            // ISLANDS ZOOM
            let zoom_island_range = document.querySelector('#islands-scale-pref input');
            zoom_island_range.addEventListener('input', () => {
                localStorage.setItem('islandsZoom', zoom_island_range.value);

                let s = zoom_island_range.value / 1000;

                let game_area = $('main_game_area');
                game_area.style.setProperty('--scale',s);

                this.onScreenWidthChange();
            })

            let islandsZoom = localStorage.getItem('islandsZoom');
            if (islandsZoom) {
                zoom_island_range.value = islandsZoom;
                zoom_island_range.dispatchEvent(new Event('input'));
            }

            // ISLAND INFLUENCE
            let isl_inf = document.querySelector('#island-influence-pref select');
            isl_inf.innerHTML += `<option value="1">${_('Default')}</option>`;
            isl_inf.innerHTML += `<option value="2">${_('Compact')}</option>`;
            isl_inf.addEventListener('change', () => {
                this.updatePreference(104,isl_inf.value);
            })

            // INFLUENCE DETECTOR
            let inf_detector = document.querySelector('#influence-detector-pref select');
            inf_detector.innerHTML += `<option value="1">${_('On')}</option>`;
            inf_detector.innerHTML += `<option value="2">${_('Off')}</option>`;
            inf_detector.addEventListener('change', () => {
                this.updatePreference(100,inf_detector.value);
            })

            // DISPLAY OPPONENTS' SCHOOL
            let opponents_school = document.querySelector('#opponents-school-pref select');
            opponents_school.innerHTML += `<option value="1">${_('Side')}</option>`;
            opponents_school.innerHTML += `<option value="2">${_('Bottom')}</option>`;
            opponents_school.addEventListener('change', () => {
                this.updatePreference(101,opponents_school.value);
            })

            // DISPLAY ASSISTANTS
            let display_asssitants = document.querySelector('#assistant-drawer-pref select');
            display_asssitants.innerHTML += `<option value="1">${_('"Drawer"')}</option>`;
            display_asssitants.innerHTML += `<option value="2">${_('Fixed')}</option>`;
            display_asssitants.addEventListener('change', () => {
                this.updatePreference(102,display_asssitants.value);
            })

            // PIECES ASPECT
            let pieces_aspect = document.querySelector('#pieces-aspect-pref select');
            pieces_aspect.innerHTML += `<option value="1">${_('3D')}</option>`;
            pieces_aspect.innerHTML += `<option value="2">${_('Flat')}</option>`;
            pieces_aspect.addEventListener('change', () => {
                this.updatePreference(103,pieces_aspect.value);
            })
        },

        // called everytime viewport changes size
        // adapts islands zoom to new viewport size. if gets smaller, then zoom out islands to not make it overflow
        onScreenWidthChange: function() {            
            // adapt assistant drawer if open
            if ($('assistant_cards_drawer').offsetHeight != 0) {
                $('assistant_cards_drawer').style.height = 'fit-content';
            }

            let players_school = $('players_school');
            let h = $('main_game_area').offsetHeight;

            if (players_school.scrollHeight > h) {
                players_school.style.alignContent = 'flex-start';
                players_school.style.overflowY = 'scroll';
            }
            else players_school.style.overflowY = '';

            if ($('main_game_area').offsetWidth < $('game_ui').offsetWidth) {
                players_school.style.maxHeight = h+'px';
            } else {
                players_school.style.maxHeight = '';
                players_school.style.overflowY = '';
            }
        },

        // needed to inject html into log, imported from doc
        format_string_recursive : function(log, args) {
            try {
                if (log && args && !args.processed) {
                    args.processed = true;

                    for (const key in args) {

                        if (key == 'mother_nature') {
                            args[key] = this.format_block('jstpl_mother_nature');
                        }

                        if (key == 'coins') {
                            args[key] = this.format_block('jstpl_coin');
                        }

                        if (key == 'cloud_tile') {
                            args[key] = "<div class='cloud_type_multi cloud_tile'></div>";
                        }

                        if (key == 'no_entry_token') {
                            args[key] = "<div class='no-entry_token'></div>";
                        }

                        if (key.includes('student_')) {
                            args[key] = this.format_block('jstpl_student',{color: args.color});
                        }

                        if (key.includes('professor_')) {
                            args[key] = this.format_block('jstpl_professor',{color: args.color});
                        }

                        if (key.includes('assistant_')) {
                            args[key] = this.format_block('jstpl_turn_position_indicator',{turnPos: args.n});
                        }

                        if (key.includes('steps_')) {
                            args[key] = this.format_block('jstpl_turn_steps_indicator',{steps: args.mov});
                        }

                    }
                }
            } catch (e) {
                //console.error(log,args,"Exception thrown", e.stack);
            }
            return this.inherited(arguments);
        },

        updatePlayerOrdering() {
            this.inherited(arguments);
            dojo.place('player_board_config', 'player_boards', 'first');
        },

        // preference change observer (copyed from doc)
        initPreferencesObserver: function () {      
            console.log('Initiating preferences observer');
            // Call onPreferenceChange() when any value changes
            dojo.query('.preference_control').on('change', (e) => {
                const match = e.target.id.match(/^preference_[cf]ontrol_(\d+)$/);
                if (!match) {
                    return;
                }
                const pref = match[1];
                const newValue = e.target.value;
                this.prefs[pref].value = newValue;
                this.onPreferenceChange(pref, newValue);
            });

            console.log('Checking all preferences');
            // Call onPreferenceChange() now
            dojo.forEach(
                dojo.query("#ingame_menu_content .preference_control"),
                function (el) {
                    // Create a new 'change' event
                    var event = new CustomEvent('change');

                    // Dispatch it.
                    el.dispatchEvent(event);
                }
            );
        },

        // change preference programmatically (copyed from doc)
        updatePreference: function(prefId, newValue) {
            console.log("Updating preference", prefId, newValue);
            // Select preference value in control:
            dojo.query('#preference_control_' + prefId + ' > option[value="' + newValue
            // Also select fontrol to fix a BGA framework bug:
                + '"], #preference_fontrol_' + prefId + ' > option[value="' + newValue
                + '"]').forEach((value) => dojo.attr(value, 'selected', true));
            // Generate change event on control to trigger callbacks:
            const newEvt = new Event('change');
            $('preference_control_' + prefId).dispatchEvent(newEvt);
        },
        
        onPreferenceChange: function (prefId, prefValue) {
            console.log("Preference changed", prefId, prefValue);

            let prefSelect = $('pref_select_'+prefId)
            if (prefSelect) prefSelect.value = prefValue;
            
            switch (prefId) {
                case '100':
                    if (prefValue == 1) {
                        document.documentElement.classList.add('detect-influence');
                        this.addTooltipToClass('island',_('This is the total Influence each player/faction has over this Island group'),'',1000);
                    } else {
                        document.documentElement.classList.remove('detect-influence');
                        document.querySelectorAll('.island').forEach(i => { this.removeTooltip(i.id); });
                    }
                    
                    break;

                case '101':
                    if (prefValue == 1) {
                        document.documentElement.classList.remove('opponents-bottom');
                        $('players_school').append($('opponents_school_area'));
                        this.onScreenWidthChange();
                    } else {
                        document.documentElement.classList.add('opponents-bottom');
                        $('players_school').after($('opponents_school_area'));
                        this.onScreenWidthChange();
                    }

                    break;

                case '102':
                    if (prefValue == 2) {
                        this.openAssistantDrawer(()=>{document.documentElement.classList.add('drawer-fixed');});
                    } else document.documentElement.classList.remove('drawer-fixed');
                    
                    break;

                case '103':
                    if (prefValue == 2) {
                        document.documentElement.classList.add('flat-pieces');
                    } else document.documentElement.classList.remove('flat-pieces');
                    
                    break;

                case '104':
                    if (prefValue == 2) {
                        document.documentElement.classList.add('influence_compact');
                    } else document.documentElement.classList.remove('influence_compact');
                    
                    break;
            
                default:
                    break;
            }
        },

        //#endregion

        // ------------------- //
        // --- GAME STATES --- //
        // ------------------- //
        // #region
        
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
                        this.showMessage(_('It is not your turn'),'error');
                        return;
                    }

                    if (el.classList.contains('blocked')) {
                        this.showMessage(_('You cannot play an Assistant that has already been played by another player'),'info');
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

            this.deactivateStudentsDestinations();
            if (!args.from_char) this.activateCharacters();

            let origin;
            if (args.from_char) origin = document.querySelector(`#character_${args.from_char} .character_tokens`);
            else origin = document.querySelector(`#school_${this.getActivePlayerId()} .school_entrance`);

            origin.classList.add('active');

            if (!this.isCurrentPlayerActive()) return;

            this.gamedatas.gamestate.clientData = {};
            this.gamedatas.gamestate.activeChar = args.from_char;

            let actionText = this.gamedatas.gamestate.descriptionmyturn;

            origin.childNodes.forEach(student => {

                student.classList.add('selectable');

                student.onclick = evt => {

                    console.log('clicked on student');

                    if (!student.classList.contains('selected')) {

                        this.gamedatas.gamestate.descriptionmyturn = _('${you} must choose where to move your student');
                        this.updatePageTitle();
                        if (!args.from_char) this.activateCharacters();

                        let previousSelected = document.querySelector(`.student.selected`);
                        if (previousSelected) {

                            previousSelected.classList.remove('selected');

                            let prev_table = document.querySelector(`#school_${this.getActivePlayerId()} .${this.getStudentElementColor(previousSelected)}_row .students_table`);
                            prev_table.classList.remove('active');
                            prev_table.onclick = '';
                        }

                        student.classList.add('selected');

                        this.gamedatas.gamestate.clientData.student = this.getStudentElementColor(student);

                        this.activateStudentsDestinations();

                    } else {

                        this.gamedatas.gamestate.descriptionmyturn = actionText;
                        this.updatePageTitle();

                        student.classList.remove('selected');

                        this.deactivateStudentsDestinations();
                    }                
                }
            });
        },

        onEnteringState_moveMona: function(args) {

            args.destinations.forEach((g,i) => {
                let group = $('island_group_'+g);
                group.classList.add('active');

                if (args.incMovement) {
                    if (i == args.destinations.length-1 || i == args.destinations.length-2) {
                        group.style.setProperty('--act-color','#ff6400');
                    }
                }

                group.onclick = evt => {
                    if (!this.isCurrentPlayerActive()) {
                        this.showMessage(_('It is not your turn'),'error');
                        return;
                    }
                    this.ajaxcallwrapper('moveMona',{group: g});
                }
            });

            // characters
            if (!args.from_char) this.activateCharacters();
        },

        onEnteringState_cloudTileDrafting: function(args) {

            args.cloudTiles.forEach(c => {
                let cloud = $('cloud_'+c);
                cloud.classList.add('active');

                cloud.onclick = evt => {
                    if (!this.isCurrentPlayerActive()) {
                        this.showMessage(_('It is not your turn'),'error');
                        return;
                    }
                    this.ajaxcallwrapper('chooseCloudTile',{cloud: c});
                }
            });

            // characters
            this.activateCharacters();
        },

        onEnteringState_character1_ability: function(args) {
            this.onEnteringState_moveStudents(args);
        },

        onEnteringState_character2_ability: function(args) {
            this.onEnteringState_moveMona(args);
        },

        onEnteringState_character4_ability: function(args) {
            
            document.querySelectorAll('.island .influence_cont').forEach(island => {
                island.classList.add('active');

                island.onclick = () => { this.ajaxcallwrapper('placeNoEntry', { island: island.parentElement.id.split("_").pop() }); };
            });
        },

        onEnteringState_character10_ability: function(args) {
            this.onEnteringState_moveStudents(args);
        },

        onLeavingState: function(stateName) {
            console.log('Leaving state: '+stateName);
            
            switch ( stateName ) {
                case 'playAssistant': 
                    document.querySelectorAll(`#assistant_cards_myhand .assistant`).forEach(assistant => {
                        assistant.onclick = '';
                        assistant.classList.remove('blocked','selected','unselected');
                    });
                    break;

                case 'character11_ability':
                case 'character10_ability':
                case 'moveStudents':
                    document.querySelectorAll(`#school_${this.getActivePlayerId()} .school_entrance .student`).forEach(student => { student.onclick = ''; });
                    if (this.gamedatas.gamestate.from_char) document.querySelector(`#character_${this.gamedatas.gamestate.from_char} .character_tokens`).classList.remove('active');
                    else document.querySelector(`#school_${this.getActivePlayerId()} .school_entrance`).classList.remove('active');
                    break;

                case 'character2_ability':
                case 'moveMona':
                    document.querySelectorAll(`.island_group`).forEach(group => { group.classList.remove('active'); group.onclick = ''; group.style = ''});
                    break;
                
                case 'cloudTileDrafting':
                    document.querySelectorAll(`.cloud_tile`).forEach(cloud => { cloud.onclick = ''; });

                    if (this.gamedatas.characters) {
                        document.querySelectorAll('.character').forEach(char => { char.onclick = ''; char.classList.remove('active','activated','used')});
                    }
                    break;
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

        updateStudentsBagCounter: function(studLeft) {
            this.addTooltip('students_bag',_('Students left:') + ' ' + studLeft + '. '+ _('When the last Student has been drawn from the bag, the game end will trigger, granting one last turn to play'),'');
        },

        getColorName: function(colval) {
            switch (colval) {
                case '000000': return 'black';
                case 'ffffff': return 'white';
                case '7b7b7b': return 'grey';
            }
        },

        getColorTranslation: function(col) {
            switch (col) {
                case 'green': return _('Green');
                case 'red': return _('Red');
                case 'yellow': return _('Yellow');
                case 'pink': return _('Pink');
                case 'blue': return _('Blue');
            }
        },

        getInverseColor: function(colval) {
            switch (colval) {
                case '000000': return 'ffffff';
                case 'ffffff': return '000000';
                case '7b7b7b': return 'ffffff';
            }
        },

        findCommonAncestors: function(el1, el2) {

            let range = document.createRange();

            range.setStart(el1,0);
            range.setEnd(el2,0);

            return range.commonAncestorContainer;
        },

        getContScale: function(el) {
            let m = getComputedStyle(el).transform;

            if (m == 'none') return null;

            m = m.replace('matrix','');
            m = m.replaceAll(' ','');
            m = m.substring(1,m.length-2);
            m = m.split(',');
            scale = {x: m[0], y: m[3]}

            return scale;
        },

        placeElement: function(el, target, movingSurface=null) {

            if (!movingSurface) movingSurface = $('game_play_area');
            let movingSurfacePos = movingSurface.getBoundingClientRect();
            let targetPos = target.getBoundingClientRect();

            let surfaceScale = this.getContScale(movingSurface);
            let counterScale = 1;
            if (surfaceScale) {
                counterScale = 1/surfaceScale.x;
            }

            //console.log('moving surface', movingSurface, movingSurfacePos);
            //console.log('target', target, targetPos);
            
            if (el.parentElement != movingSurface) movingSurface.append(el);

            // position it on its current coordinates, but on oversurface
            el.style.position = 'absolute';
            el.style.left = counterScale*(targetPos.left - movingSurfacePos.left) + 'px';
            el.style.top = counterScale*(targetPos.top - movingSurfacePos.top) + 'px';

            el.offsetWidth;
        },

        moveElement: function(el,target, movingSurface=null, duration=0, delay=0, onEnd=()=>{}) {

            if (!movingSurface) movingSurface = $('game_play_area');

            if (el.parentElement != movingSurface) this.placeElement(el,el,movingSurface);

            if (this.instantaneousMode) {
                duration = 0;
                delay = 0;
            }

            el.style.transition = `all ${duration}ms ${delay}ms ease-in-out`;
            el.offsetWidth;

            setTimeout(() => {
                el.style.transition = '';
                onEnd();
            }, duration+delay);

            this.placeElement(el,target,movingSurface);
        },

        moveElementAndAppend: function(el,target, movingSurface=null, duration=0, delay=0, onEnd=()=>{}) {

            if (!movingSurface) movingSurface = $('game_play_area');

            let elClone = el.cloneNode();
            movingSurface.append(elClone);

            let prepare = 200;

            if (this.instantaneousMode) {
                duration = 0;
                delay = 0;
                prepare = 0;
            }

            el.style.visibility = 'hidden';
            el.style.transition = `all ${prepare}ms ease-in-out`;
            el.offsetWidth;

            this.placeElement(elClone,el,movingSurface);

            let placeholder = el.cloneNode();
            placeholder.style.visibility = 'hidden';
            placeholder.style.width = '0px';
            placeholder.style.height = '0px';
            target.append(placeholder);
            placeholder.style.transition = `all ${prepare}ms ${delay}ms ease-in-out`;

            placeholder.offsetWidth;

            placeholder.style.width = '';
            placeholder.style.height = '';

            setTimeout(() => {
                this.moveElement(elClone,placeholder,movingSurface,duration,delay,()=>{
                    el.remove();
                    placeholder.remove();
                    target.append(elClone);
    
                    elClone.style.position = '';
                    elClone.style.left = '';
                    elClone.style.top = '';
                    elClone.style.transition = '';
    
                    onEnd();
                });
            }, prepare);
        },

        openAssistantDrawer: function(onEnd = () => {}) {

            if ($('assistants_drawer_arrow').classList.contains('open_drawer')) {

                $('assistant_cards_drawer').ontransitionend = () => {
                    $('assistant_cards_drawer').style.height = 'fit-content';

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

        activateStudentsIslands: function() {
            document.querySelectorAll('.island .influence_cont').forEach(island => {
                island.classList.add('active');

                island.onclick = evt => {

                    let currentSel = this.gamedatas.gamestate.clientData.student;
                    
                    if (currentSel) {
                        this.ajaxcallwrapper('moveStudent',{student:currentSel, place:island.parentElement.id.split('_')[1]});
                    } else {
                        this.showMessage(_("You need to first select a student to send to this island"),'error');
                    }
                }
            });
        },

        activateStudentsTable: function() {
            let col = this.getStudentElementColor(document.querySelector('.student.selected'));

            if (this.gamedatas.gamestate.args.free_tables.includes(col)) {

                let students_table = document.querySelector(`#school_${this.getActivePlayerId()} .${col}_row .students_table`);
                students_table.classList.add('active');

                students_table.onclick = evt => {

                    let currentSel = this.gamedatas.gamestate.clientData.student;
                    if (currentSel) {
                        this.ajaxcallwrapper('moveStudent',{student:currentSel});
                    } else {
                        this.showMessage(_("You need to select an Assistant card to play"),'error')
                    }
                }
            }
        },

        activateStudentsDestinations: function() {
            if (this.gamedatas.gamestate.activeChar != 10) this.activateStudentsIslands();
            if (this.gamedatas.gamestate.activeChar != 1) this.activateStudentsTable();
        },

        deactivateStudentsIslands: function() {
            document.querySelectorAll('.island .influence_cont').forEach(island => {
                island.classList.remove('active');
                island.onclick = null;
            })
        },

        deactivateStudentsTables: function() {
            document.querySelectorAll(`#school_${this.getActivePlayerId()} .students_table`).forEach(table => {
                table.classList.remove('active');
                table.onclick = null;
            });
        },

        deactivateStudentsDestinations: function() {
            this.deactivateStudentsIslands();
            this.deactivateStudentsTables();
        },

        updateFactionsIslandsInfluence: function(influenceData) {

            document.querySelectorAll('.factions_influence').forEach( e => { e.innerHTML = ''; });

            for (const gid in influenceData) {
                g = influenceData[gid];

                let influence_cont = document.querySelector(`#island_${g.mid} .factions_influence`);

                let inner = [];
                for (const f in g.influence) {

                    console.log(f,g.influence[f]);

                    inner.push(this.format_block('jstpl_island_faction_influence',{
                        col: f,
                        invcol: this.getInverseColor(f),
                        num: g.influence[f],
                        modtri: g.mod[f]
                    }));
                }

                influence_cont.innerHTML = '<div>'+inner.join(' - ')+'</div>';
            }
        },

        activateCharacters: function() {
            if (this.gamedatas.characters) {

                this.gamedatas.gamestate.args.avail_characters.forEach(c => {

                    let char = $('character_'+c);

                    char.classList.add('active');

                    char.onclick = () => {
                        if (this.checkAction('useCharacter')) {

                            let charData = this.gamedatas.characters[c];
                            if (this.counters.playerBoard[this.getActivePlayerId()]['coins'].getValue() < (1*charData.cost + 1*charData.cost_mod)) {
                                this.showMessage(_("You don't have enough coins to activate this Character ability"),'error');
                                return;
                            }

                            this.ajaxcallwrapper('useCharacter',{id: c});
                        }
                    }
                });

                if (this.isCurrentPlayerActive() && this.gamedatas.gamestate.args.avail_characters.length > 0) {
                    this.gamedatas.gamestate.descriptionmyturn += "  |  " + _(" or activate one of the <a>Characters</a> abilities");
                    this.updatePageTitle();

                    document.querySelector('#pagemaintitletext a').onclick = () => {

                        let y = $('characters').getBoundingClientRect().y - 300 - document.documentElement.getBoundingClientRect().y;
                        console.log(y);

                        window.scroll({
                            top: y,
                            left: 0,
                            behavior: 'smooth'
                        });

                        window.onscroll = () => {
                            if (Math.round($('characters').getBoundingClientRect().y) == 300) {
                                $('characters').style.boxShadow = '0px 0px 20px #ff8700';

                                setTimeout(() => {
                                    $('characters').style.boxShadow = '';

                                    window.onscroll = '';
                                }, 1500);
                            }
                        };
                    }
                }
            }
        },

        updateCharactersTooltips: function() {

            document.querySelectorAll(`#characters .character`).forEach(charEl => {

                let charId = charEl.id.split('_').pop();
                let charObj = this.gamedatas.characters[charId];

                this.addTooltipHtml(charEl.id,this.format_block('jstpl_cardTooltip', {
                    img: charEl.outerHTML,
                    effectLable: _('EFFECT')+': ',
                    effect: _(charObj.tooltip),
                    costLable: _('COST')+': ',
                    cost: 1*charObj.cost + 1*charObj.cost_mod
                }));
            });
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
            this.notifqueue.setSynchronous('displayPoints', 10);

            dojo.subscribe('playAssistant', this, "notif_playAssistant");
            this.notifqueue.setSynchronous('playAssistant');

            dojo.subscribe('resolvePlanning', this, "notif_resolvePlanning");
            this.notifqueue.setSynchronous('resolvePlanning');

            dojo.subscribe('moveStudent', this, "notif_moveStudent");
            this.notifqueue.setSynchronous('moveStudent',700);

            dojo.subscribe('gainCoin', this, "notif_gainCoin");
            this.notifqueue.setSynchronous('gainCoin',500);

            dojo.subscribe('useCharacter', this, "notif_useCharacter");
            this.notifqueue.setSynchronous('useCharacter',500);

            dojo.subscribe('endCharacterAbility', this, "notif_endCharacterAbility");
            this.notifqueue.setSynchronous('endCharacterAbility',100);
            
            dojo.subscribe('gainProfessor', this, "notif_gainProfessor");
            this.notifqueue.setSynchronous('gainProfessor',500);

            dojo.subscribe('moveMona', this, "notif_moveMona");
            this.notifqueue.setSynchronous('moveMona');

            dojo.subscribe('moveTower', this, "notif_moveTower");
            this.notifqueue.setSynchronous('moveTower',500);

            dojo.subscribe('joinIslandsGroups', this, "notif_joinIslandsGroups");
            this.notifqueue.setSynchronous('joinIslandsGroups', 2000);

            dojo.subscribe('updateInfluence', this, "notif_updateInfluence");
            this.notifqueue.setSynchronous('updateInfluence', 100);

            dojo.subscribe('chooseCloudTile', this, "notif_chooseCloudTile");
            this.notifqueue.setSynchronous('chooseCloudTile');

            dojo.subscribe('refillClouds', this, "notif_refillClouds");
            this.notifqueue.setSynchronous('refillClouds');

            dojo.subscribe('refillCharStudent', this, "notif_refillCharStudent");
            this.notifqueue.setSynchronous('refillCharStudent',1000);

            dojo.subscribe('placeNoEntry', this, "notif_placeNoEntry");
            this.notifqueue.setSynchronous('placeNoEntry',500);

            dojo.subscribe('blockInfluenceResolve', this, "notif_blockInfluenceResolve");
            this.notifqueue.setSynchronous('blockInfluenceResolve',500);
        },

        // debugging notif
        notif_displayPoints: function(notif) {

            console.log('DISPLAYING POINTS',notif.args);

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

                let target = $('placeholder_'+notif.args.player_id);
                let el;
    
                // depending on active or not, move card to discard pile from different starting location (myhand or side player board)
                if (this.isCurrentPlayerActive()) {
                    el = document.querySelector(`.assistant_${notif.args.n}`);
                    el.classList.remove('selected');
                } else {
    
                    $('player_board_'+this.getActivePlayerId()).insertAdjacentHTML('beforeend',this.format_block('jstpl_assistant',{n: notif.args.n}));
                    el = $('player_board_'+this.getActivePlayerId()).lastElementChild;
                }

                this.moveElementAndAppend(el,target,null,500,0,()=>{
                    this.notifqueue.setSynchronousDuration(100);
                });
            });
        },

        notif_resolvePlanning: function(notif) {
            console.log(notif.args);

            // open drawer if closed, on end: ..
            this.openAssistantDrawer(() => {

                let d = 0;
                notif.args.players.forEach(player => {

                    console.log('player',player);

                    let a = document.querySelector(`#placeholder_${player.id} .assistant`);

                    // animate turn position indicator
                    setTimeout(() => {

                        console.log('turn indicator for player',player);

                        // place turn indicator
                        a.insertAdjacentHTML('beforeend',this.format_block('jstpl_turn_position_indicator',{turnPos: player.turn_pos}));
                        let indicator = a.lastChild;

                        // fetch dest
                        let target = document.querySelector(`#player_board_${player.id} .turn_position_cont`);
                        indicator.onanimationend = () => { // set animation end (move to dest)
                            indicator.classList.remove('bounce_animation');
                            let oldpos = document.querySelector(`#player_board_${player.id} .turn_position`);

                            this.moveElement(indicator,target,$('overall-content'),500,0,()=>{
                                oldpos.className = indicator.className;
                                indicator.remove();
                            });
                        }

                        if (this.instantaneousMode) indicator.style.animationDuration = '0ms';
                        indicator.classList.add('bounce_animation'); // trigger animation (bounce)
                        
                    }, (this.instantaneousMode)?0:d);

                    // same as above for different icon
                    setTimeout(() => {

                        console.log('movement indicator for player',player);

                        a.insertAdjacentHTML('beforeend',this.format_block('jstpl_turn_steps_indicator',{steps: player.steps}));
                        let indicator = a.lastChild;

                        let target = document.querySelector(`#player_board_${player.id} .mona_movement_cont`);
                        indicator.onanimationend = () => {
                            indicator.classList.remove('bounce_animation');
                            let oldsteps = document.querySelector(`#player_board_${player.id} .mona_movement`);

                            this.moveElement(indicator,target,$('overall-content'),500,0,()=>{
                                oldsteps.className = indicator.className;
                                indicator.remove();
                            });
                        }

                        if (this.instantaneousMode) indicator.style.animationDuration = '0ms';
                        indicator.classList.add('bounce_animation');
                        
                    }, (this.instantaneousMode)? 0:(d+1000));

                    d = d+2000+100;
                });

                setTimeout(() => {
                    document.querySelectorAll('.assistant_placeholder .assistant').forEach(a => {
                        a.classList.add('old');
                    });

                    let event = new Event('click') 
                    $('assistants_drawer_arrow').dispatchEvent(event);
                    
                }, (this.instantaneousMode)?0:d);

                this.notifqueue.setSynchronousDuration(d);
            });
        },

        notif_moveStudent: function(notif) {
            console.log(notif.args);

            // fetch student and clean interactables
            let student;
            let origin;

            if (notif.args.from_char) {
                origin = `#character_${notif.args.from_char}`;
            } else {
                origin = `#school_${notif.args.player_id} .school_entrance`;
            }

            if (this.isCurrentPlayerActive()) {                
                this.deactivateStudentsDestinations();

                student = document.querySelector(`${origin} .student_${notif.args.color}.selected`);

                student.classList.remove('selected');
                student.parentElement.childNodes.forEach(s => {
                    s.classList.remove('selectable');
                });   
            } else {
                student = document.querySelector(`${origin} .student_${notif.args.color}`);
            }

            student.parentElement.classList.remove('active');

            // fetch destination element
            let destination;
            let onEnd = ()=>{};

            if (notif.args.destination) {

                destination = document.querySelector(`#island_${notif.args.destination} .students_influence`);

                onEnd = () => {
                    let infCounter = this.counters.islandsInfluence[notif.args.destination][notif.args.color];
                    
                    infCounter.incValue(1);
                    infCounter.span.parentElement.style.display = '';
                };
                
            } else {

                destination = document.querySelector(`#school_${notif.args.player_id} .${notif.args.color}_row .students_table`);

                onEnd = () => {
                    this.counters.playerBoard[notif.args.player_id][notif.args.color].incValue(1);
                };
            }

            console.log(student,destination);
            this.moveElementAndAppend(student,destination,null,500,0,onEnd);
        },

        notif_gainCoin: function(notif) {

            setTimeout(() => {

                // fetch coin student pos
                let lastStudent = document.querySelector(`#school_${notif.args.player_id} .${notif.args.color}_row .student:last-child`);
                console.log(lastStudent);
                lastStudent.insertAdjacentHTML('beforeend',this.format_block('jstpl_coin'));
                let movingCoin = lastStudent.lastElementChild;
                console.log(movingCoin);

                // fetch destination
                let coinCont = document.querySelector(`#player_coins_cont_${notif.args.player_id}`);
                console.log(coinCont);
            
                this.moveElement(movingCoin,coinCont,null,500,0,() => {
                    movingCoin.remove();

                    this.counters.playerBoard[notif.args.player_id]['coins'].incValue(1);
                })
            }, 100);
        },

        notif_useCharacter: function(notif) {

            let character = $(`character_${notif.args.char_id}`);
            character.classList.remove('active');
            character.onclick = '';
            character.classList.add('activated');

            // fetch coin
            let coinCont = document.querySelector(`#player_coins_cont_${notif.args.player_id}`);
            coinCont.insertAdjacentHTML('beforeend',this.format_block('jstpl_coin'));

            let movingCoin = coinCont.lastElementChild;

            let charCoin = document.querySelector(`#character_${notif.args.char_id} .character_coin`);

            setTimeout(() => {

                this.moveElement(movingCoin,charCoin,null,500,0,() => {
                    charCoin.style.visibility = 'unset';

                    movingCoin.remove();

                    this.counters.playerBoard[notif.args.player_id]['coins'].incValue(-notif.args.n);

                    this.gamedatas.characters[notif.args.char_id].cost_mod = 1;

                    this.updateCharactersTooltips();
                });
            },300);
        },

        notif_endCharacterAbility: function(notif) {
            document.querySelectorAll('.character.activated').forEach(char => {
                let id = char.id.split('_').pop();

                if (id != 3 && id != 5 && id != 7 && id != 12) {
                    char.classList.remove('activated');
                    char.classList.add('used');
                }
            });
        },

        notif_gainProfessor: function(notif) {

            // fetch student and clean interactables
            let professor;

            // if stealing professor
            if (notif.args.player_2) {
                professor = document.querySelector(`#school_${notif.args.player_2} .${notif.args.color}_row .professor`);                
                
                document.querySelector(`#player_board_${notif.args.player_2} .${notif.args.color}_counter .professor_marker`).style.visibility = '';
                this.removeTooltip(`${notif.args.color}_counter_${notif.args.player_2}`);
            
            } else { // if not
                $('player_board_'+notif.args.player_id).insertAdjacentHTML('beforeend',this.format_block('jstpl_professor',{color: notif.args.color}));
                professor = $('player_board_'+notif.args.player_id).lastElementChild;
            }

            // fetch destination element
            let destination = document.querySelector(`#school_${notif.args.player_id} .${notif.args.color}_row .professor_seat`);

            this.moveElementAndAppend(professor,destination,null,500,0);

            // show professor marker on side player board
            document.querySelector(`#player_board_${notif.args.player_id} .${notif.args.color}_counter .professor_marker`).style.visibility = 'unset';

            let translated = dojo.string.substitute(_("This player controls the ${color} Professor. ${color} Tokens will count towards their Influence on the Islands"), {
                color: this.getColorTranslation(notif.args.color)
            } );

            this.addTooltip(`${notif.args.color}_counter_${notif.args.player_id}`,translated,'');
        },

        notif_moveMona: function(notif) {

            this.notifqueue.setSynchronousDuration(notif.args.stops.length * 550);

            // deactivate islands
            document.querySelectorAll('.island_group.active').forEach(g => g.classList.remove('active'));

            document.querySelector('.mother_nature:not(.mock)').closest('.island').style.zIndex = ''; // reset island z-index (was needed to not make mona appear behind other islands)

            let previousMona = document.querySelector(`.mother_nature:not(.mock)`); // fetch previous mona
            let mona = previousMona.cloneNode(); // create temp mona for anim
            let movingSurface = $('islands_cont');
            
            previousMona.classList.add('mock'); // make mona invisible (only temp one used for anim will be visible)

            this.placeElement(mona,previousMona,movingSurface); // place temp mona on anim moving surface

            // for each stop mona has to take for reaching island of destination
            notif.args.stops.forEach((id,i) => {
                // trigger move anim
                setTimeout(() => {
                    let island_mona = document.querySelector(`#island_${id} .mother_nature`);

                    onend = ()=>{};
                    if (i == notif.args.stops.length-1) { // if stop is last
                        onend = () => {
                            // make mona of that island real and remove temp mona
                            island_mona.classList.remove('mock');
                            mona.remove();

                            document.querySelector('.mother_nature:not(.mock)').closest('.island').style.zIndex = 5;
                        }
                    }
                    this.moveElement(mona,island_mona,movingSurface,500,0,onend); // trigger
                }, (this.instantaneousMode)?0:(i*550)); // delay considers some time to make program catch up
            });
        },

        notif_moveTower: function(notif) {

            // two alternatives: place tower (args.place == true) / remove tower (arg.place == false)
            if (notif.args.place) {

                let mock = document.querySelector(`#island_${notif.args.island} .tower`) // fetch island tower mock (move anim dest)
                let tower = document.querySelector(`#school_${notif.args.player} .tower:last-child`); // fetch tower (move anime el)

                // trigger anim
                this.moveElement(tower,mock,null,500,0,()=>{
                    // at animation end, change mock to be real tower
                    mock.classList.remove('mock');
                    mock.classList.add('tower_' + notif.args.color);
                    tower.remove();
                });

                this.scoreCtrl[notif.args.player].incValue(1);
                this.counters.playerBoard[notif.args.player]['towers'].incValue(-1);


            } else {
                let mock = document.querySelector(`#island_${notif.args.island} .tower`) // fetch mock 
                let tower = mock.cloneNode();  // make clone (move anim el)

                mock.classList.remove('tower_' + notif.args.color);
                mock.classList.add('mock');

                mock.after(tower);
                this.moveElementAndAppend(tower,document.querySelector(`#school_${notif.args.player} .school_yard`),null,500,0);

                this.scoreCtrl[notif.args.player].incValue(-1);
                this.counters.playerBoard[notif.args.player]['towers'].incValue(1);
            }
        },

        notif_joinIslandsGroups: function(notif) {

            console.log(notif.args);

            let transitionCount = 0;

            // for each group to join (always 2)
            for (const gNum in notif.args.groups) {
                let g = notif.args.groups[gNum];

                // for each island in that group
                document.querySelectorAll(`#island_group_${g.id} > .island`).forEach(island => {
                    // set new coordinates (transition will trigger)
                    island.style.left = (+island.style.left.split('px').shift() + g.translation.x) + 'px';
                    island.style.top = (+island.style.top.split('px').shift() - g.translation.y) + 'px';
                });
            };

            // after animation ends for all islands, create new island group
            setTimeout(() => {
                let g1 = $('island_group_'+notif.args.groups.g1.id);
                let g2 = $('island_group_'+notif.args.groups.g2.id);

                // g2.innerHTML += g1.innerHTML;

                g2.append(...g1.childNodes)
                g1.remove();

            }, (this.instantaneousMode)?0:1500);

            this.counters.islands_groups.incValue(-1);

            /* let joinGroup = notif.args.groups.filter(g => g.id != notif.args.groupTo).pop()['id'];
            console.log(joinGroup); */
        },

        notif_updateInfluence: function(notif) {
            this.updateFactionsIslandsInfluence(notif.args.influence_data);
        },

        notif_chooseCloudTile: function(notif) {

            console.log(notif.args);

            let cloud = $('cloud_'+notif.args.cloud); // fetch el (selected cloud)
            let school_entrance = document.querySelector(`#school_${notif.args.player_id} .school_entrance`); // fetch destination (player school entrance)

            // deactivate cloud tiles and remove handlers
            document.querySelectorAll('.cloud_tile').forEach(c => {
                c.onclick = null;
                c.classList.remove('active');
            });
            
            // fetch selected cloud students
            let students = cloud.childNodes;
            
            // set notification duration
            this.notifqueue.setSynchronousDuration(students.length * (300+200));

            // for each, trigger move animation
            for (let i = students.length-1; i >= 0 ; i--) {
                let s = students[i];

                setTimeout(() => {
                    this.moveElementAndAppend(s,school_entrance,null,300);
                }, (this.instantaneousMode)?0:(i*(300+200+100))); // delay is move anim duration + making space duration (200ms)
            }
        },

        notif_refillClouds: function(notif) {

            console.log(notif.args);

            let studentsSet = ['green','red','yellow','pink','blue'];

            let k = 0;

            let bag = $('students_bag'); // fetch bag
            if (this.instantaneousMode) bag.style.animationDuration = '0ms';
            bag.classList.add('draw_animation');

            let totRefilledStudents = 0;

            Object.values(notif.args.clouds).forEach(cloud => {
                totRefilledStudents += cloud.reduce((a, b) => a + b, 0);
            });

            setTimeout(() => {
                Object.values(notif.args.clouds).forEach((cloud,id) => { // for each cloud to fill                    
                    
                    cloud.forEach((sn,sc) => { // for each student type

                        for (let i = 0; i < sn; i++) { // for the amount of students of that type

                            // trigger draw student anim
                            setTimeout(() => {

                                // insert student and trigger move animation to cloud
                                bag.insertAdjacentHTML('beforeend',this.format_block('jstpl_student', {color: studentsSet[sc]}));
                                let student = bag.lastElementChild;
                                this.moveElementAndAppend(student,$('cloud_'+(id+1)),null,500);

                            }, (this.instantaneousMode)?0:(k*(200))); // delay is student anim + making space anim (moveElementAppend) + bag anim

                            k++;
                        }
                    });
                });

                if (k == totRefilledStudents) {
                    this.notifqueue.setSynchronousDuration(k*(200)+500);
                }

                bag.classList.remove('draw_animation');

                this.updateStudentsBagCounter(notif.args.students_left);
            }, (this.instantaneousMode)?0:500);
        },

        notif_refillCharStudent: function(notif) {

            let bag = $('students_bag'); // fetch bag
            if (this.instantaneousMode) bag.style.animationDuration = '0ms';
            bag.classList.add('draw_animation');

            let studentsSet = ['green','red','yellow','pink','blue'];

            setTimeout(() => {
                bag.insertAdjacentHTML('beforeend',this.format_block('jstpl_student', {color: studentsSet[notif.args.student]}));
                let student = bag.lastElementChild;
                this.moveElementAndAppend(student,document.querySelector(`#character_${notif.args.char} .character_tokens`),null,500,0,() => {
                    this.updateCharactersTooltips();
                });

                bag.classList.remove('draw_animation');
                this.updateStudentsBagCounter(notif.args.students_left);
                
            }, (this.instantaneousMode)?0:500);
        },

        notif_placeNoEntry: function(notif) {

            let token = document.querySelector('#character_4 .no-entry_token:last-child');
            let destination = document.querySelector(`#island_${notif.args.island} .no-entry_token`);

            this.moveElement(token,destination,null,500,0,() => {
                destination.classList.remove('mock');
                token.remove();

                this.updateCharactersTooltips();
            });
        },

        notif_blockInfluenceResolve: function(notif) {

            let token = document.querySelector(`#island_${notif.args.blocked_island} .no-entry_token`);
            let destination = document.querySelector('#character_4 .character_tokens');

            console.log(token);
            let movingToken = token.cloneNode();
            token.after(movingToken);
            token.classList.add('mock');

            this.moveElementAndAppend(movingToken,destination,null,500,0,() => {
                this.updateCharactersTooltips();
            });
        },

        // #endregion
   });             
});
