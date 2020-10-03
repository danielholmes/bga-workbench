<?php

/**
 * Stub for the Deck module (self::getNew( "module.common.deck" )). You can add typehinting by defining the
 * property on your class that contains the result of the deck creation and adding this class as typehint (/** @var Deck)
 */
abstract class Deck
{
    /**
     * This controls whether auto-reshuffle functionality is used in various functions
     * 
     * @var bool
     */
    public $autoreshuffle = false;

    /**
     * If specified, this will trigger callbacks when auto reshuffles have been triggered.
     * The array needs 2 keys: obj (reference to instance) and method (name of method to call),
     * such that $autoreshuffle_trigger['obj']->${$autoreshuffle_trigger['method']}() gets called.
     * 
     * @var array
     */
    public $autoreshuffle_trigger;

    /**
     * Associative array mapping locations to be auto-reshuffled from which location
     * 
     * @var array
     */
    public $autoreshuffle_custom = ['deck' => 'discard'];

    abstract public function initialize(string $table_name);
    
    /**
     * @param array[] $cards array of ['type' => int, 'type_arg' => int, 'nbr' => int]
     * @param string $location the location that all created cards are placed in
     * @param int|null $location_arg
     */
    abstract public function createCards(array $cards, string $location = 'deck', ?int $location_arg = null);

    /**
     * Pick a card from a pile location (location where location_arg indicates position in pile)
     * and place the card into the location hand with location_arg player_id. This method automatically
     * applies auto-reshuffle if the location has been configured as such.
     * 
     * @param string $location where to pick a card from
     * @param int|string $player_id Which player's hand will the picked card be placed in
     * @return array|null null if location is empty, otherwise default card structure (@see Deck::getCard)
     */
    abstract public function pickCard(string $location, $player_id): ?array;

    /**
     * Pick cards from a pile location (location where location_arg indicates position in pile)
     * and place the cards into the location hand with location_arg player_id. This method automatically
     * applies auto-reshuffle if the location has been configured as such. If there are not enough cards in the location
     * (taking in account any auto-reshuffles), this will attempt to pick as many cards as possible.
     * 
     * @param int $nbr the number of cards to pick
     * @param string $location where to pick a card from
     * @param int|string $player_id Which player's hand will the picked card be placed in
     * @return array|null null if location is empty, otherwise indexed array of card id to default card structure (@see Deck::getCard)
     */
    abstract public function pickCards(int $nbr, string $location, $player_id): ?array

    /**
     * @see Deck::pickCard but where you can specify which destination location the picked card will be placed at.
     * 
     * @param string $from_location the location where a card will be picked from
     * @param string $to_location the location where the picked card will be placed in
     * @param int $location_arg
     * @return array|null see return of @see Deck::pickCard
     */
    abstract public function pickCardForLocation(string $from_location, string $to_location, int $location_arg = 0): array;
    
    /**
     * @see Deck::pickCards but where you can specify which destination location the picked cards will be placed at.
     * 
     * @param int $nbr the number of cards to pick
     * @param string $from_location the location where a card will be picked from
     * @param string $to_location the location where the picked card will be placed in
     * @param int $location_arg
     * @param bool $no_deck_reform if specified as true, auto-reshuffle functionality is disabled during this call
     * @return array|null see return of @see Deck::pickCard
     */
    abstract public function pickCardForLocation(int $nbr, string $from_location, string $to_location, int $location_arg = 0, bool $no_deck_reform = false): array;

    /**
     * Move the specific card to a given location
     * 
     * @param int $card_id the database id of the card to move
     * @param string $location which location to move the card to
     * @param int $location_arg which location_arg to move the card to
     */
    abstract public function moveCard(int $card_id, string $location, int $location_arg = 0);

    /**
     * @see Deck::moveCard but for multiple cards
     * 
     * @param int[] $cards array of the database id of the cards to move
     * @param string $location which location to move the cards to
     * @param int $location_arg which location_arg to move the cards to
     */
    abstract public function moveCard(array $cards, string $location, int $location_arg = 0);

    /**
     * Move card to a specific location where location_arg indicates position in pile. 
     * This method will ensure that the location_arg is only used by the card you just inserted
     * 
     * @param int $card_id the database id of the card to move
     * @param string $location which location to move the card to
     * @param int $location_arg the position in pile to insert the card at. If already taken, all cards after the inserted card will get an incremented location_arg
     */
    abstract public function insertCard(int $card_id, string $location, int $location_arg);

    /**
     * @see Deck::insertCard but instead of specifying a location arg, this inserts the card on top or bottom of the pile
     * 
     * @param int $card_id the database id of the card to move
     * @param string $location which location to move the card to
     * @param bool $bOnTop whether this card is placed on top or bottom of the pile
     */
    abstract public function insertCardOnExtremePosition(int $card_id, string $location, bool $bOnTop);

    /**
     * Move all cards in specified "from" location to given location
     * 
     * @param string|null $from_location location that contains the cards to be moved. If null, all locations will be used
     * @param string $to_location which location to move the cards to
     * @param int|null $from_location_arg if not null, only cards with given location_arg are moved
     * @param int $to_location_arg the location_arg that moved cards will get
     */
    abstract public function moveAllCardsInLocation(?string $from_location, string $to_location, ?int $from_location_arg = null, int $to_location_arg = 0);

    /**
     * @see Deck::moveAllCardsInLocation but this method does not change the location_arg of moved cards
     * 
     * @param string $from_location location that contains the cards to be moved.
     * @param string $to_location which location to move the cards to
     */
    abstract public function moveAllCardsInLocationKeepOrder(string $from_location, string $to_location);

    /**
     * Alias for Deck::insertCardOnExtremePosition($card_id, 'discard', true);
     * 
     * @param int $card_id the database id of the card to move to the discard pile location
     */
    abstract public function playCard(int $card_id);

    /**
     * Get the card information for a specific card
     * 
     * @param int $card_id the database id of the card
     * @return array null if not found, otherwise array with structure: ['id' => ..., 'type' => ..., 'type_arg' => ..., 'location' => ..., 'location_arg' => ...]
     */
    abstract public function getCard(int $card_id): ?array;

    /**
     * @see Deck::getCard but multiple cards. If any card is not found or if an id occurs multiple times,
     * this method throws an exception
     * 
     * @param int[] array of database id's of the cards
     * @return array of @see Deck::getCard
     */
    abstract public function getCards(array $cards): array;

    /**
     * @see Deck::getCard but for all cards in a specific location
     * 
     * @param string $location where the cards are
     * @param int|null $location_arg if not null, only get info for cards with specified location_arg
     * @param string|null $order_by if not null, order returned cards by the given database field (note: this is the column name as used in dbmodel.sql)
     * @return array of @see Deck::getCard. If location is empty, then it returns an empty array
     */
    abstract public function getCardsInLocation(string $location, ?int $location_arg = null, ?string $order_by = null): array;

    /**
     * Count the number of cards in the specified location
     * 
     * @param string $location where the cards are
     * @param int|null $location_arg if not null, only get info for cards with specified location_arg
     * @return int the number of cards in specified location
     */
    abstract public function countCardInLocation(string $location, ?int $location_arg = null): int;

    /**
     * Return the number of cards in each location of the game
     * 
     * @return int[] associative array with key $location and value $numberOfCards
     */
    abstract public function countCardsInLocations(): array;

    /**
     * Return the number of cards for each location_arg for the given location
     * 
     * @param string $location the location where the cards are in
     * @return int[] associative array with key $location_arg and value $numberOfCards
     */
    abstract public function countCardsByLocationArgs(string $location): array;

    /**
     * Alias for Deck::getCardsInLocation('hand', $player_id);
     * 
     * @param int|string $player_id the player id whose cards you want to get
     * @return array @see Deck::getCardsInLocation
     */
    abstract public function getPlayerHand($player_id): array;

    /**
     * Get the card on top of the given pile location. This function won't trigger auto-reshuffle functionality
     * 
     * @param string $location the location of which you want to get the top card of
     * @return array @see Deck::getCard
     */
    abstract public function getCardOnTop(string $location): ?array;

    /**
     * @see Deck::getCardOnTop but multiple cards on top. This also won't trigger auto-reshuffle functionality
     * 
     * @param int $nbr number of cards to get the information for
     * @param string $location the location of which you want to get the top card of
     * @return array array of @see Deck::getCard for at most $nbr cards (fewer than $nbr entries if location does not have enough cards)
     */
    abstract public function getCardsOnTop(int $nbr, string $location): array;

    /**
     * Get the location_arg of the card either on top or on bottom of the given pile location
     * 
     * @param bool $bGetMax if true, returns the location_arg for the top card, otherwise for the bottom card
     * @param string $location the location of which you want to get the top card of
     * @return int the location_arg of the card
     */
    abstract public function getExtremePosition(bool $bGetMax, $location): int;

    /**
     * Get all cards with the specified type (and type_arg)
     * 
     * @param string $type the type of the cards you want to get information for
     * @param int|null $type_arg if given, only return cards with the specified type_arg
     * @return array @see getCards
     */
    abstract public function getCardsOfType(string $type, ?int $type_arg = null): array;

    /**
     * Get all cards with the specified type (and type_arg) in the specified location (and $location_arg)
     * 
     * @param string $type the type of the cards you want to get information for
     * @param int|null $type_arg if given, only return cards with the specified type_arg
     * @param string $location where the cards are
     * @param int|null $location_arg if not null, only get info for cards with specified location_arg
     * @return array @see getCards
     */
    abstract public function getCardsOfTypeInLocation(string $type, ?int $type_arg = null, string $location, ?int $location_arg = null): array;

    /**
     * Shuffle all cards in specific location (this only works when the location is treated as a pile,
     * as this will change the location_arg to reflect the order of the pile)
     * 
     * @param string $location where the cards to shuffle are
     */
    abstract public function shuffle(string $location);
}