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

if ($cmd == 'start') {
	sm($chatID, bold("Bot avviato!") . "\nUsa /help per una lista di comandi.\nℹ️ " . code("Versione {$config['version']}"));
	die;
}

if ($cmd == "type") {
	db_query("UPDATE utenti SET page = ? WHERE user_id = ?", ["testMessage", $userID]);
	sm($chatID, "OK, inviami un messaggio e ti dirò che tipo di messaggio è...");
	die;
}

if ($cmd == "menu" or $cmd == "mediamenu" or $cbdata == "menu") {
	$t = "Menu aperto";
	$menu[] = [
		[
			"text" => "ℹ️ Info",
			"callback_data" => "info"
		]
	];
	if ($cmd == "menu") {
		sm($chatID, $t, $menu);
	} elseif ($cmd == "mediamenu") {
		sp($chatID, "https://telegra.ph/file/5850d536904bb4e8b7dfd.jpg", $t, $menu);
	} else {
		if ($caption) {
			$is_caption = true;
		} else {
			$is_caption = false;
		}
		cb_reply($cbid, '', false, $cbmid, $t, $menu, 'def', 'def', $is_caption);
	}
	die;
}

if ($cbdata == "info") {
	$config['json_payload'] = false;
	$t = bold("Sviluppatore: ") . tag(244432022, "Nele");
	$t .= bold("\nVersione: ") . code($config['version']);
	$menu[] = [
		[
			"text" => "◀️ Torna indietro",
			"callback_data" => "menu"
		]
	];
	if ($caption) {
		$is_caption = true;
	} else {
		$is_caption = false;
	}
	cb_reply($cbid, '', false, $cbmid, $t, $menu, 'def', 'def', $is_caption);
	die;
}

if ($cbdata == "del") {
	dm($chatID, $cbmid);
	cb_reply($cbid);
	die;
}

if ($cmd == 'cancel') {
	db_query("UPDATE utenti SET page = ? WHERE user_id = ?", ["", $userID]);
	sm($chatID, "Comando annullato.", 'nascondi');
	die;
}

if ($u['page'] == "testMessage" and $typechat == "private") {
	sm($chatID, "Tipo di messaggio: $messageType");
	die;
}

if ($cmd == "dice") {
	sdice($chatID, "🏀");
	sm($chatID, tag() . ", vergognati, mi hai appena chiesto di fare la cosa più inutile di Telegram, fai /help e rendimi utile...");
	die;
}

if ($cmd == "tag") {
	sm($chatID, tag());
	die;
}

if ($cmd == "cache") {
	$config['json_payload'] = false;
	$propic = getPropic($botID);
	if (!$propic['ok']) {
		die;
	}
	$file_id = $propic['result']['photos'][0][1]['file_id']; // Qualità 320p
	copy(getFile($file_id), "cache.jpeg");
	sm($chatID, "Fatto");
	die;
}

if ($cmd == 'documents') {
	$menu[] = [
		[
			"text" => "Da locale",
			"callback_data" => "doc_locale"
		],
		[
			"text" => "Da link",
			"callback_data" => "doc_link"
		],
		[
			"text" => "Da file_id",
			"callback_data" => "doc_file_id"
		]
	];
	sm($chatID, "Documenti", $menu);
	die;
}

if (strpos($cbdata, "doc_") === 0) {
	$tipo = str_replace('doc_', '', $cbdata);
	$documents = [
		'locale' => $f['plugins'],
		'link' => "https://t.me/NeleB54GoldBlog/798",
		'file_id' => "BQACAgQAAxkDAAIJ2156hyNuwDGlSfF4-3giJkac5aqtAAJsAwAC7YmIU3fnc3N-71esGAQ" // Ogni Bot ha i propri file ID
	];
	$config['response'] = true;
	cb_reply($cbid, '', false);
	$r = sd($chatID, $documents[$tipo], "test", 'nascondi', 'def', false, true, "cache.jpeg");
	if (!$r['ok']) {
		sm($chatID, "Error: " . json_encode($r));
	}
	die;
}

if ($cmd == 'buttons') {
	$menu[] = [
		[
			'text' => "Mostra",
			'callback_data' => 'mostra'
		],
		[
			'text' => "Mostra URL",
			'callback_data' => 'mostraurl'
		]
	];
	$menu[] = [
		[
			'text' => "Inline",
			'switch_inline_query_current_chat' => 'messaggio'
		],
		[
			'text' => "Condividi Inline",
			'switch_inline_query' => 'messaggio'
		]
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
		]
	];
	sm($chatID, "Bottoni", $menu);
	die;
}

if ($cbdata == 'mostra') {
	cb_reply($cbid, 'Ciao', true);
	die;
}

if ($cbdata == 'mostraurl') {
	cb_url($cbid, "https://t.me/" . $config['username_bot'] . "?start=Test");
	die;
}

if ($cmd == "start Test") {
	sm($chatID);
	die;
}

if ($cmd == 'help' and $typechat == "private") {
	$menu[] = [
		[
			'text' => "Funzioni Inline",
			'switch_inline_query_current_chat' => ''
		]
	];
	$menu[] = [
		[
			'text' => "Source Bot",
			'url' => 't.me/NelePHPFramework'
		]
	];
	$config['json_payload'] = false;
	$commands = getBotCommands();
	if (isset($commands['result'])) {
		foreach ($commands['result'] as $comando) {
			$comandi .= "\n/{$comando['command']}: {$comando['description']}";
		}
	} else {
		$comandi = italic("\nNessun comando disponibile...");
	}
	sm($chatID, bold("Comandi del Bot") . "$comandi\n@" . $config['username_bot'], $menu);
	die;
}

if ($cmd == "setcommands" and $isadmin) {
	sm($chatID, "OK. Inviami la lista di comandi per questo Bot. Usa questo formato:\n\ncomando1 - Descrizione\ncomando2 - Altra descrizione");
	db_query("UPDATE utenti SET page = ? WHERE user_id = ?", ['setcommands', $userID], 'no');
	die;
}

if ($u['page'] == 'setcommands' and $isadmin) {
	db_query("UPDATE utenti SET page = ? WHERE user_id = ?", ['', $userID], 'no');
	$config['json_payload'] = false;
	$com = explode("\n", $msg);
	foreach ($com as $ecom) {
		$e = explode(" - ", $ecom, 2);
		$commands[] = [
			'command' => $e[0],
			'description' => $e[1]
		];
	}
	$s = setBotCommands($commands);
	if ($s['ok']) {
		sm($chatID, "✅ Lista comandi aggiornata!");
	}
	die;
}

if ($cmd == "reply") {
	sm($chatID, "Risposta. \nAdesso rispondi tu.", 'rispondimi', '', $msgID);
	die;
}

if ($cmd == 'jsondump') {
	sm($chatID, code(substr(json_encode($original_update, JSON_PRETTY_PRINT), 0, 4095)));
	die;
}

if (in_array($cmd, ['html', 'markdown', 'markdownv2'])) {
	$config['parse_mode'] = $cmd;
	$testo = textspecialchars(bold("Bold") . " = ") . bold("Bold") . "\n";
	$testo .= textspecialchars(italic("Italic") . " = ") . italic("Italic") . "\n";
	$testo .= textspecialchars(code("Fixed") . " = ") . code("Fixed") . "\n";
	$testo .= textspecialchars(underl("Underlined") . " = ") . underl("Underlined") . "\n";
	$testo .= textspecialchars(striket("Strikethrough") . " = ") . striket("Strikethrough") . "\n";
	$testo .= textspecialchars(text_link("Text Link", 'http://www.example.com') . " = ") . text_link("Text Link", 'http://www.example.com');
	sm($chatID, $testo);
	die;
} elseif ($cmd == "entities") {
	$testo = "Bold\n";
	$m_entities[] = ['type' => 'bold', 'offset' => 0, 'length' => mb_strlen($testo, "UTF-16") * 2];
	$offset = mb_strlen($testo);
	$testo .= "Italic\n";
	$length = mb_strlen($testo) - $offset;
	$m_entities[] = ['type' => 'italic', 'offset' => $offset, 'length' => $length];
	$offset = mb_strlen($testo);
	$testo .= "Fixed\n";
	$length = mb_strlen($testo) - $offset;
	$m_entities[] = ['type' => 'code', 'offset' => $offset, 'length' => $length];
	$offset = mb_strlen($testo);
	$testo .= "Underlined\n";
	$length = mb_strlen($testo) - $offset;
	$m_entities[] = ['type' => 'underline', 'offset' => $offset, 'length' => $length];
	$offset = mb_strlen($testo);
	$testo .= "Strikethrough\n";
	$length = mb_strlen($testo) - $offset;
	$m_entities[] = ['type' => 'bold', 'offset' => $offset, 'length' => $length];
	$offset = mb_strlen($testo);
	$testo .= "Text Link";
	$length = mb_strlen($testo) - $offset;
	$m_entities[] = ['type' => 'text_link', 'offset' => $offset, 'length' => $length, 'url' => 'http://www.example.com'];
	sm($chatID, $testo, false, $m_entities);
	die;
}

if ($cmd == 'db' and $typechat === "channel") {
	sm($chatID, code(json_encode($c, JSON_PRETTY_PRINT)));
	die;
}

if ($cmd == "copy" and $reply) {
	if ($rmsg) {
		$t = $rmsg;
	} elseif ($rcaption) {
		$t = $rcaption;
	} else {
		$t = false;
	}
	copyMsg($chatID, $chatID, $rmsgID, $t, $rentities);
	die;
}

if ($cmd == 'mylocation' and $typechat == "private") {
	$config['json_payload'] = false;
	$loc[] = [
		[
			"text" => "Invia posizione [Test]", 
			'request_location' => true
		]
	];
	sm($chatID, "Dove ti trovi?", $loc, 'def', false, false, false);
	die;
}

if ($cmd == 'mynumber' and $typechat == "private") {
	$config['json_payload'] = false;
	$loc[] = [
		[
			"text" => "Invia numero do telefono [Test]", 
			'request_contact' => true
		]
	];
	sm($chatID, "Come ti chiamo?", $loc, 'def', false, false, false);
	die;
}

if ($cmd == 'infome') {
	if ($config['usa_il_db']) {
		$db = "\n\n" . bold("Informazioni Utente Database") . "\nNome: " . code($u['nome']) . "\nCognome: " . code($u['cognome']) . "\nUsername: " . code($u['username']) . "\nID: " . code($u['user_id']) . "\nLingua: " . code($u['lang']);
	}
	sm($chatID, bold("Informazioni Utente Telegram") . "\nNome: " . code($nome) . "\nCognome: " . code($cognome) . "\nUsername: " . code($username) . "\nID: " . code($userID) . "\nLingua: " . code($lingua) . $db);
	die;
}

if ($cmd == 'propic') {
	$config['json_payload'] = false;
	$photos = getPropic($userID);
	if ($photos['ok']) {
		sp($chatID, $photos['result']['photos'][0][count($photos['result']['photos'][0]) - 1]['file_id'], "Hai " . count($photos['result']['photos']) . " foto profilo.");
	} else {
		sm($chatID, bold("Error " . $photos['error_code']) . "\n" . $photos['description']);
	}
	die;
}

if ($cmd == 'contact') {
	sc($chatID, "+13253080284", "Presidente", "VoIP");
	die;
}

if ($cmd == 'editphoto') {
	$menu[] = [
		[
			"text" => "Cambia",
			"callback_data" => "cbEditPhoto"
		]
	];
	sp($chatID, "https://telegra.ph/file/5850d536904bb4e8b7dfd.jpg", 'cia', $menu);
	die;
}

if ($cbdata == "cbEditPhoto") {
	$menu[] = [
		[
			"text" => "Fatto!",
			"url" => "t.me/NeleB54Gold"
		]
	];
	media_cb_reply($cbid, "Text", true, $cbmid, "t.me/NeleB54Gold", 'photo', "Ciao", $menu);
	die;
}

if ($cmd == 'animation') {
	sgif($chatID, "https://t.me/NeleB54GoldBlog/568", 'Rotola');
	die;
}

if ($cmd == 'venue') {
	sven($chatID, 41.90, 12.50, "Capitale d'Italia", 'Roma');
	die;
}

if ($cmd == 'location') {
	$latitudine = 41.9;
	$longitudine = 12.4;
	$menu[] = [
		[
			'text' => "⏺",
			'callback_data' => 'editLocation_' . round($latitudine) . "_" . round($longitudine)
		]
	];
	$menu[] = [
		[
			'text' => "Ferma Location",
			'callback_data' => 'stopLocation'
		]
	];
	sendLocation($chatID, $latitudine, $longitudine, $menu, 7200);
	die;
}

if (strpos($cbdata, 'editLocation_') === 0) {
	$e = explode('_', $cbdata, 3);
	$lati = $e[1];
	$long = $e[2];
	if ($lati > 180) $lati = 180;
	if ($long > 360) $long = 360;
	if ($lati < 0) $lati = 0;
	if ($long < 0) $long = 0;
	$menu[] = [
		[
			'text' => "↖️",
			'callback_data' => 'editLocation_' . round($lati + 1) . "_" . round($long - 1)
		],
		[
			'text' => "⬆️",
			'callback_data' => 'editLocation_' . round($lati + 1) . "_" . round($long)
		],
		[
			'text' => "↗️",
			'callback_data' => 'editLocation_' . round($lati + 1) . "_" . round($long + 1)
		]
	];
	$menu[] = [
		[
			'text' => "⬅️",
			'callback_data' => 'editLocation_' . round($lati) . "_" . round($long - 1)
		],
		[
			'text' => "⏺",
			'callback_data' => 'editLocation_' . round($lati) . "_" . round($long)
		],
		[
			'text' => "➡️",
			'callback_data' => 'editLocation_' . round($lati) . "_" . round($long + 1)
		]
	];
	$menu[] = [
		[
			'text' => "↙️",
			'callback_data' => 'editLocation_' . round($lati - 1) . "_" . round($long - 1)
		],
		[
			'text' => "⬇️",
			'callback_data' => 'editLocation_' . round($lati - 1) . "_" . round($long)
		],
		[
			'text' => "↘️",
			'callback_data' => 'editLocation_' . round($lati - 1) . "_" . round($long + 1)
		]
	];
	$menu[] = [
		[
			'text' => "Ferma Location",
			'callback_data' => 'stopLocation'
		]
	];
	editLocation($chatID, $lati, $long, $cbmid, $menu);
	cb_reply($cbid);
	die;
}

if ($cbdata == 'stopLocation') {
	$menu[] = [
		[
			'text' => "Developer",
			'url' => "t.me/NeleB54Gold"
		]
	];
	cb_reply($cbid, 'Posizionamento terminato', true);
	stopLocation($chatID, $cbmid, $menu);
	die;
}
