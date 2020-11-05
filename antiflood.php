<?php

/*
NeleBotFramework
	Copyright (C) 2018  PHP-Coders

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

# Conigurazioni Anti-Flood Bot
$antiflood = [
	'messaggi' => 4,
	// Numero di messaggi che un utente può mandare entro i secondi per poi essere punito
	'secondi' => 1,
	// Secondi disponibili per inviare il numero di messaggi per poi essere punito
	'punizione' => 60 * 60 * 2, //Queste sono 2 ore
	// Punizioni disponibili: 'forever' è per sempre oppure inserisci il tempo di ban in secondi
	'ban-message' => "Bannato dall'Anti-Flood",
	// Messaggio di Ban
	'unban-message' => "",
	// Messaggio di UnBan
	'chat' => [
		'messaggi' => 5,
		// Numero di messaggi che un utente può mandare entro i secondi per poi essere punito
		'secondi' => 1,
		// Secondi disponibili per inviare il numero di messaggi per poi essere punito
		'punizione' => 60 * 60, //Questi sono 60 minuti
		// Punizioni disponibili: 'forever' è per sempre oppure inserisci il tempo di ban in secondi
	],
	// Anti-Flood Chat
];

# Anti-Flood solo per utenti senza permessi da Amministratore, gruppi e canali
if ($update and !$isadmin) {
	$time = time();
	if ($typechat == "private") {
		if ($get = $redis->get($userID)) {
			$json = json_decode($get, true);
			if (isset($json['ban'])) {
				if ($time > $json['tempo'] + $json['ban']) {
					$redis->del($chatID);
				} else {
					die;
				}
			}
			$sec = date('s', time() - $json['tempo']);
			if ($sec >= $antiflood['secondi']) {
				$json = [
					'tempo' => $time,
					'messaggi' => 1,
				];
				$redis->set($userID, json_encode($json));
			} else {
				$json['messaggi'] = $json['messaggi'] + 1;
				$json['tempo'] = $time;
				if ($json['messaggi'] >= $antiflood['messaggi']) {
					$ban = true;
					$json['ban'] = $antiflood['punizione'];
					$banFromAntiflood = $antiflood['punizione'];
				}
				$redis->set($userID, json_encode($json));
				if ($msg or $cmd) {
					botlog("L'utente $nome $cognome [$userID] è stato bannato per " . $antiflood['punizione'] . " secondi", 'antiflood');
					sm($userID, $antiflood['ban-message']);
				} elseif ($cbid) {
					cb_reply($cbid, $antiflood['ban-message'], true);
				}
			}
		} else {
			$json = [
				'tempo' => $time,
				'messaggi' => 1,
			];
			$redis->set($userID, json_encode($json));
		}
	} elseif ($chatID < 0) {
		if ($get = $redis->get($chatID)) {
			$json = json_decode($get, true);
			$sec = date('s', time() - $json['tempo']);
			if (isset($json['ban'])) {
				if ($time > $json['tempo'] + $json['ban']) {
					$redis->del($chatID);
				}
				die;
			}
			if ($sec >= $antiflood['chat']['secondi']) {
				$json = [
					'tempo' => $time,
					'messaggi' => 1,
				];
				$redis->set($chatID, json_encode($json));
			} else {
				$json['messaggi'] = $json['messaggi'] + 1;
				$json['tempo'] = $time;
				if ($json['messaggi'] >= $antiflood['chat']['messaggi']) {
					$ban = true;
					botlog("La chat $title [$chatID] è stato bannato per " . $antiflood['chat']['punizione'] . " secondi.", 'antiflood');
					$json['ban'] = $antiflood['chat']['punizione'];
					$chatbanFromAntiflood = $antiflood['chat']['punizione'];
				}
				$redis->set($chatID, json_encode($json));
			}
		} else {
			$json = [
				'tempo' => $time,
				'messaggi' => 1,
			];
			$redis->set($chatID, json_encode($json));
		}
	}
}
