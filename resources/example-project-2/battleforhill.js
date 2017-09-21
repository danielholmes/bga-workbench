"use strict";

define([
    "dojo",
    "dojo/_base/declare",
    "dojo/_base/lang",
    "dojo/dom",
    "dojo/query",
    "dojo/_base/array",
    "dojo/dom-construct",
    "dojo/dom-class",
    "dojo/dom-geometry",
    "dojo/fx",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/scrollmap"
],
function (dojo, declare, lang, dom, query, array, domConstruct, domClass, domGeom, fx) {
    return declare("bgagame.battleforhill", ebg.core.gamegui, {
        constructor: function() {
        },
        
        /**
         * Sets up the game user interface according to current game situation specified
         * in parameters. Method is called each time the game interface is displayed to a player, ie:
         *  - when the game starts
         *  - when a player refreshes the game page (F5)
         */
        setup: function(datas) {

        },

        ///////////////////////////////////////////////////
        //// Game & client states
        onEnteringState: function(stateName, event) {

        },

        ///////////////////////////////////////////////////
        // onLeavingState: this method is called each time we are leaving a game state.
        onLeavingState: function(stateName) {

        },

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function(stateName, args) {

        },        

        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications
        setupNotifications: function() {

        }
   });             
});
