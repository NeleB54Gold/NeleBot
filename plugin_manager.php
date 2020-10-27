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
// Attenzione: in questo file non è possibile fare query al database

$pluginp = $f['plugin_manager'];
$c = [
	true	=> "✅",
	false	=> "❌",
	'true'	=> "✅",
	'false'	=> "❌"
];

if ($cmd == "devmode") {
	sm($chatID, $c[$config['devmode']]);
	die;
}

if ($cmd == "php") {
	sm($chatID, phpversion ());
	die;
}

if ($cmd == "logs" or $cbdata == "logs") {
	if (file_exists($f['logs'] . "/NBF_$botID.log")) {
		$logs = substr(file_get_contents($f['logs'] . "/NBF_$botID.log"), -2048);
		$logs = explode("\n[NeleBotFramework]", $logs);
		unset($logs[0]);
		foreach ($logs as $tlog) {
			$grs = explode(":", $tlog)[0];
			$log .= str_replace("\n", "\n$grs: ", $tlog);
		}
		$menu[] = [
			[
				"text" => "🔄 Aggiorna",
				"callback_data" => "logs"
			]
		];
		$menu[] = [
			[
				"text" => "🗑 Elimina logs",
				"callback_data" => "clear_logs"
			]
		];
		$t = "Ultimi errori: \n\n" . code($log);
	} else {
		$t = "Il file log di NeleBotFramework non esiste. Crealo con /nbflog";
	}
	if ($cmd) {
		sm($chatID, $t, $menu);
	} else {
		editMsg($chatID, $t, $cbmid, $t, $menu);
		cb_reply($cbid, '', false);
	}
	die;
}

if ($cbdata == "clear_logs") {
	if (file_exists($f['logs'] . "/NBF_$botID.log")) {
		cb_reply($cbid, "File resettato");
		file_put_contents($f['logs'] . "/NBF_$botID.log", '');
		editMenu($chatID, $cbmid);
	} else {
		cb_reply($cbid, "File inesistente...");
	}
	die;
}

if ($cmd == "isadmin") {
	sm($chatID, "Sei un Admin del Bot", false, 'def', $msgID);
	die;
}

if (strpos($cmd, "addpl ") === 0) {
	$pl = str_replace("addpl ", '', $cmd);
	if (!file_exists($f['plugins.dir'] . "/$pl")) {
		sm($chatID, "Il file non esiste!");
		die;
	}
	if (file_exists($f['plugins'])) {
		$pls = json_decode(file_get_contents($f['plugins']), true);
	} else {
		$pls = [];
	}
	if (!is_array($pls)) {
		$tot = "Errore: " . $f['plugins'] . " non è un array. \nFile corrotto: aggiustalo manualmente o ricrealo.";
	} else {
		$tot = bold("Plugin aggiunto!");
		$pls[$pl] = true;
		file_put_contents($f['plugins'], json_encode($pls, JSON_PRETTY_PRINT));
	}
	sm($chatID, $tot);
	die;
}

if ($cmd == "plugins" or strpos($cbdata, 'selectPlugins') === 0 or strpos($cbdata, 'editPlugins') === 0 or strpos($cbdata, 'orderPlugins') === 0 or strpos($cbdata, 'removePlugins') === 0) {
	$file = $f['plugins'];
	$numtobool = [
		1 => true,
		0 => false
	];
	if (file_exists($file)) {
		$json = json_decode(file_get_contents($file), true);
	} else {
		$json = [];
		file_put_contents($file, json_encode($json, JSON_PRETTY_PRINT));
	}
	if (strpos($cbdata, 'editPlugins_') === 0) {
		cb_reply($cbid, '', false);
		$e = explode("-", str_replace("editPlugins_", '', $cbdata));
		$type = $e[0];
		$selected = $type;
		$set = round($e[1]);
		if (!$set) {
			$dis = "dis";
		} else {
			$dis = "";
		}
		if ($type == "all") {
			$sfile = "Tutti i file";
		} else {
			$sfile = "Il file '$type'";
		}
		cb_reply($cbid, "$sfile è stato $dis" . "attivato dalla lista plugins.", false);
		if ($type == "all") {
			foreach ($json as $m => $s) {
				$json[$m] = $numtobool[$set];
			}
		} else {
			$json[$type] = $numtobool[$set];
		}
		file_put_contents($file, json_encode($json, JSON_PRETTY_PRINT));
	} elseif (strpos($cbdata, 'orderPlugins_') === 0) {
		$e = explode("-", str_replace("orderPlugins_", '', $cbdata));
		$type = $e[0];
		$position = round($e[1]) - 1;
		cb_reply($cbid);
		$plugins = array_keys($json);
		foreach ($plugins as $num => $tpl) {
			if ($tpl == $type) $exposition = $num;
		}
		$copy = $plugins[$position];
		$plugins[$position] = $type;
		$plugins[$exposition] = $copy;
		foreach ($plugins as $num => $tpl) {
			$newjson[$tpl] = $json[$tpl];
		}
		$json = $newjson;
		file_put_contents($file, json_encode($json, JSON_PRETTY_PRINT));
		$selected = $type;
	} elseif (strpos($cbdata, 'removePlugins_') === 0) {
		$type = str_replace("removePlugins_", '', $cbdata);
		cb_reply($cbid, "Il file '$type' è stato rimosso dalla lista plugins.", false);
		unset($json[$type]);
		file_put_contents($file, json_encode($json, JSON_PRETTY_PRINT));
	} elseif (strpos($cbdata, 'selectPlugins_') === 0) {
		$selected = str_replace("selectPlugins_", '', $cbdata);
	}
	$rnum = 0;
	foreach ($json as $art => $active) {
		$rnum = $rnum + 1;
		if ($active) {
			$e = false;
			$active = true;
		} else {
			$e = true;
			$active = false;
		}
		$emo = $c[json_encode($active)];
		$b = $art;
		if ($selected !== $art) {
			$b .= $emo;
			$bcb = "selectPlugins_$art";
		} else {
			$bcb = "selectPlugins";
		}
		$menu[] = [
			[
				"text" => $b,
				"callback_data" => $bcb
			]
		];
		if ($selected == $art) {
			$sottomenu[] = [
				"text" => "$emo",
				"callback_data" => "editPlugins_$art-$e"
			];
			if (array_keys($json)[0] !== $art) {
				$sottomenu[] = [
					"text" => "⬆️",
					"callback_data" => "orderPlugins_$art-" . ($rnum - 1)
				];
			}
			if (array_keys($json)[count($json) - 1] !== $art){
				$sottomenu[] = [
					"text" => "⬇️",
					"callback_data" => "orderPlugins_$art-" . ($rnum + 1)
				];
			}
			$sottomenu[] = [
				"text" => "🗑",
				"callback_data" => "removePlugins_$art"
			];
			$menu[] = $sottomenu;
			unset($sottomenu);
		}
	}
	$menu[] = [
		[
			"text" => "Bot Off",
			"callback_data" => "editPlugins_all-0"
		],
		[
			"text" => "Bot On",
			"callback_data" => "editPlugins_all-1"
		]
	];
	$menu[] = [
		[
			"text" => "Fatto",
			"callback_data" => "fatto"
		]
	];
	if ($messageType == "command") {
		dm($chatID, $msgID);
		sm($chatID, "Attiva/Disattiva plugins:", $menu);
	} else {
		editMenu($chatID, $cbmid, $menu);
		cb_reply($cbid, '', false);
	}
	die;
}

if ($cbdata == "fatto") {
	cb_reply($cbid, '👍', false);
	dm($chatID, $cbmid);
	die;
}

if ($cmd == "ping") {
	$config['json_payload'] = true;
	$config['response'] = false;
	$time_start = microtime(true);
	$dev[0] = sm($chatID, code("Pong 0"));
	$time_end = microtime(true);
	$execution_time = number_format($time_end - $time_start, 3);
	$time_start1 = microtime(true);
	$dev[1] = sm($chatID, code("Pong 1"));
	$time_end1 = microtime(true);
	$execution_time1 = number_format($time_end1 - $time_start1, 3);
	$total_time = number_format($time_end1 - $times['start'], 3);
	sm($chatID, bold("Performance del Bot🤖 \n") . "<code>sendMessage:  $execution_time secondi\nsendMessage1: $execution_time1 secondi\nTotale:	      $total_time secondi </>\n\n" . code(json_encode($dev, JSON_PRETTY_PRINT)));
	die;
}

if ($cmd == "nbflog") {
	if (!file_exists($f['logs'] . "/NBF_$botID.log")){
		file_put_contents($f['logs'] . "/NBF_$botID.log", '');
		if (file_exists($f['logs'] . "/NBF_$botID.log")) {
			$t = "File creato!";
		} else {
			$t = "Non sono riuscito a creare il file su " . code($f['logs'] . "/NBF_$botID.log");
		}
	} else {
		$t = "Il file log esiste già";
	}
	sm($chatID, $t);
	die;
}

if ($cmd == "getlasterror") {
	$config['json_payload'] = false;
	$m = sm($chatID, "Ultimo errore: carico...");
	$get = getWhInfo()['result'];
	editMsg($chatID, "Ultimo errore: \n[" . date("c", $get['last_error_date']) . "]: " . $get['last_error_message'], $m['result']['message_id']);
	die;
}

if ($cmd == "error") {
	botlog("Errore di prova", ['framework']);
	die;
}
