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

# Prima di modificare questo file guarda la Documentazione delle Bot API Telegram
# https://core.telegram.org/bots/api#inline-mode
if (isset($inline)) {
	unset($json);
	$inline = $update["inline_query"]["id"];
	$msg = $update["inline_query"]["query"];

	//Preparametri
	$title = "Help Page"; //Questo apparirà in alto su tutte le richieste inline
	$dopostart = "inline"; //Questo sarà valido come "/start inline"

	// Comandi
	if ($msg == "test") {
		// Esempio di molteplici articoli
		$json[] = [
			'type' => 'article',
			'id' => 'esempio1',
			'title' => "Titolo",
			'description' => "Clicca per inviare il tuo messaggio",
			'thumb_url' => "t.me/$username",
			'thumb_width' => 512,
			'thumb_height' => 512,
			'message_text' => bold("Ciao!") . " Questo Bot inline funziona perfettamente!",
			'parse_mode' => $config['parse_mode']
		];
		$menu[] = [
			[
				'text' => "...clicca qui",
				'callback_data' => "comando_inline"
			]
		];
		$json[] = [
			'type' => 'article',
			'id' => 'esempio2',
			'title' => "Titolo 2",
			'description' => "Clicca per inviare il tuo messaggio 2",
			'thumb_url' => "t.me/" . $config['username_bot'],
			'thumb_width' => 512,
			'thumb_height' => 512,
			'message_text' => "Per sas...",
			'parse_mode' => '',
			'reply_markup' => ['inline_keyboard' => $menu]
		];
	}

	if ($msg == "foreach") {
		$range = range(1, 50);
		foreach ($range as $num) {
			$json[] = [
				'type' => 'article',
				'id' => 'esempio' . $num,
				'title' => "Articolo numero " . $num,
				'description' => "Clicca per inviare questo articolo",
				'message_text' => bold("Articolo numero " . $num) . "\nQuesto Bot inline funziona perfettamente!",
				'parse_mode' => $config['parse_mode']
			];
		}
	}
	
	if ($msg == "menu") {
		$menu[] = [
			[
				'text' => "Mostra",
				'callback_data' => 'mostra'
			],
			[
				'text' => "Mostra URL",
				'callback_data' => 'mostraurl'
			],
		];
		$menu[] = [
			[
				'text' => "Inline",
				'switch_inline_query_current_chat' => 'messaggio'
			],
			[
				'text' => "Condividi Inline",
				'switch_inline_query' => 'messaggio'
			],
		];
		$menu[] = [
			[
				'text' => "URL",
				'url' => 'https://t.me/' . $config['username_bot']
			],
			[
				'text' => "Condividi URL",
				'url' => 'https://t.me/share/url?' . http_build_query([
						"text" => "Testo da condividere",
						"url" => "https://t.me/" . $config['username_bot']
					])
			],
		];
		$json[] = [
			'type' => 'article',
			'id' => 'infoutilizzi',
			'title' => 'Test pulsanti al menù',
			'description' => "Clicca per inviare il tuo messaggio",
			'reply_markup' => ['inline_keyboard' => $menu],
			'message_text' => "$msg",
			'parse_mode' => ''
		];
	}
	
	if ($msg and !isset($json)) {
		$json[] = [
			'type' => 'article',
			'id' => 'infoutilizzi',
			'title' => 'Hai scritto',
			'description' => "Clicca per inviare il tuo messaggio",
			'thumb_url' => "t.me/$username",
			'thumb_width' => 512,
			'thumb_height' => 512,
			'message_text' => "$msg",
			'parse_mode' => ''
		];
	}
	
	if (!isset($json)) {
		$json[] = [
			'type' => 'article',
			'id' => 'homebot',
			'title' => '@' . $config['username_bot'],
			'description' => "Questo è un test di NeleBot",
			'message_text' => "Wow",
			'parse_mode' => ''
		];
	}

	$json = json_encode($json);
	$args = [
		'inline_query_id' => $inline,
		'results' => $json,
		'cache_time' => 5,
		'switch_pm_text' => $title,
		'switch_pm_parameter' => $dopostart
	];
	$rr = sendRequest("https://api.telegram.org/$api/answerInlineQuery", $args);
	$ar = json_decode($rr, true);
	if (isset($ar['error_code'])) {
		botlog("answerInlineQuery \n<b>INPUT</b>: " . code(json_encode($args)) . "\n<b>OUTPUT:</b> " . $ar['description'], ['telegram_errors']);
	}
}

//Messaggio CallBack per i messaggi inline
if ($cbdata == "comando_inline") {
	$menu[] = [
		[
			'text' => "Clicca qui",
			'callback_data' => "comando_inline"
		]
	];
	cb_reply($cbid, "Fatto!", false, $cbmid, tag() . " ha cliccato!", $menu);
}

//Inline Info Menu
if ($cmd == "start inline") {
	$menu[] = [
		[
			'text' => 'Provalo',
			'switch_inline_query_current_chat' => 'messaggio'
		],
	];
	sm($chatID, "Usami con la funzione inline di Telegram.\n\nEsempio: " . code("@" . $config['username_bot'] . " Messaggio"), $menu);
}
