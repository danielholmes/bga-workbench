CREATE TABLE `player` (
  `player_no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL COMMENT 'Reference to metagame player id',
  `player_canal` varchar(32) NOT NULL COMMENT 'Player comet d "secret" canal',
  `player_name` varchar(32) NOT NULL,
  `player_avatar` varchar(10) NOT NULL,
  `player_color` varchar(6) NOT NULL,
  `player_score` int(10) NOT NULL DEFAULT '0',
  `player_score_aux` int(10) NOT NULL DEFAULT '0',
  `player_zombie` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = player is a zombie',
  `player_ai` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = player is an AI',
  `player_eliminated` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = player has been eliminated',
  `player_next_notif_no` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Next notification no to be sent to player',
  `player_enter_game` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = player load game view at least once',
  `player_over_time` tinyint(1) NOT NULL DEFAULT '0',
  `player_is_multiactive` tinyint(1) NOT NULL DEFAULT '0',
  `player_start_reflexion_time` datetime DEFAULT NULL COMMENT 'Time when the player reflexion time starts. NULL if its not this player turn',
  `player_remaining_reflexion_time` int(11) DEFAULT NULL COMMENT 'Remaining reflexion time. This does not include reflexion time for current move.',
  `player_beginner` varbinary(32) DEFAULT NULL,
  PRIMARY KEY (`player_no`),
  UNIQUE KEY `player_id` (`player_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE `stats` (
  `stats_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stats_type` smallint(5) unsigned NOT NULL,
  `stats_player_id` int(10) unsigned DEFAULT NULL COMMENT 'if NULL: stat global to table',
  `stats_value` float NOT NULL,
  PRIMARY KEY (`stats_id`),
  UNIQUE KEY `stats_table_id` (`stats_type`,`stats_player_id`),
  KEY `stats_player_id` (`stats_player_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
