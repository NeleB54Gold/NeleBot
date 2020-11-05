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

ob_start();
$times['start'] = microtime(true);

# Nome dei file
$f = [
	'config' => 'config.php', // File PHP
	'logs' => '/var/log/nelebot', // Cartella dei file di log
	'functions' => 'functions.php', // File PHP
	'anti-flood' => 'antiflood.php', //File PHP
	'database' => 'database.php', // File PHP
	'404' => 'index.html', // Pagina HTML di errore 404
	'plugin_manager' => 'plugin_manager.php', // File PHP
	'plugins.dir' => 'plugins', // Cartella dei plugins
	'plugins' => 'plugins.json' // File JSON
];

if (!isset($_GET['key'])) {
	if ($config['devmode']) {
		echo "<b>Bot Error:</b> la chiave delle BotAPI sul parametro 'key' non è stata trovata";
	} else {
		require($f['404']);
	}
	die;
}

# Informazioni del Bot
$api = urldecode($_GET['key']); // Chiave di accesso alle BotAPI
$botID = str_replace('bot', '', explode(":", $api)[0]); // ID del Bot
require $f['config']; // Configurazioni del Bot
$admins = $config['admins']; // Amministratori del Bot
$password = urldecode($_GET['password']); //Password delle request

# Config per gli errori di php
ini_set('memory_limit', -1);
ini_set('display_startup_errors', $config['devmode']);
ini_set('display_errors', $config['devmode']);
ini_set('error_reporting', !E_ALL | E_PARSE | E_WARNING | E_ERROR);
if (!file_exists($f['logs'] . "/bot$botID.log")) file_put_contents($f['logs'] . "/bot$botID.log", "");
ini_set('error_log', $f['logs'] . "/bot$botID.log");
ini_set('ignore_repeated_errors', 1);

# Autenticazione del Bot
if ($config['password']) {
	if ($password !== $config['password']) {
		if ($config['devmode']) {
			echo "<br><b>Bot Error:</b> Password delle request errata!<br>";
		} else {
			require($f['404']);
		}
		die;
	}
} else {
	if (!$config['json_payload']) {
		echo "<br><b>Bot Warning:</b> Ti consiglio di impostare una password al tuo Bot per avere una maggiore sicurezza!<br>";
	}
}

# Telegram Json Update
$times['telegram_update'] = microtime(true);
$content = file_get_contents("php://input");
if (!$content) {
	$update = false;
	if ($config['devmode']) {
	} else {
		require($f['404']);
		die;
	}
} else {
	$update = json_decode($content, true);
	$original_update = $update;
}

# Gestione Logs
$times['logs'] = microtime(true);
if ($config['logs']) {
	// Log degli Updates di Telegram
	if ($config['logs']['tg-updates'] !== 'ultimo') {
		$exup = json_decode(file_get_contents($f['logs'] . "/updates-$botID.json"), true);
		$exup[date("Y")][date("m")][date("d")][date("h")][date("i")] = $update;
		file_put_contents($f['logs'] . "/updates-$botID.json", json_encode($exup, JSON_PRETTY_PRINT));
	} elseif ($config['logs'] == 'ultimo') {
		file_put_contents($f['logs'] . "/updates-$botID.json", json_encode($update, JSON_PRETTY_PRINT));
	}
}

# Funzioni
$times['functions'] = microtime(true);
require $f['functions'];

# Gestione Permessi & Whitelist
if (isset($userID)) {
	if (in_array($userID, $admins)) {
		$isadmin = true;
		include $f['plugin_manager'];
	} else {
		$isadmin = false;
	}
	# Whitelist utenti (modalità manutenzione o beta testing)
	if ($config['whitelist_users'] !== false and is_array($config['whitelist_users'])) {
		$config['whitelist_users'] = array_merge($config['whitelist_users'], $admins);
		if (!in_array($userID, $config['whitelist_users'])) {
			if ($typechat == "private") sm($chatID, "Bot in Whitelist");
			die;
		}
	}
}

# Whitelist chat (modalità di protezione)
if ($config['whitelist_chats'] !== false and is_array($config['whitelist_chats'])) {
	if (!in_array($chatID, $config['whitelist_chats']) and in_array($typechat, ['group', 'supergroup', 'channel'])) {
		if ($cmd == "start") {
			sm($chatID, "Bot in whitelist per le chat");
		}
		die;
	}
}

# Database (Opzionale)
if ($config['usa_il_db'] or $config['usa_redis']) {
	$pluginp = $f['database'];
	require $f['database'];
}

# Gestione Plugin
$times['plugins'] = microtime(true);
$plugins = json_decode(file_get_contents($f['plugins']), true);
if (!is_array($plugins)) {
	botlog("Array non trovato su " . $f['plugins'], ['plugins', 'framework']);
	die;
}
foreach ($plugins as $pluginp => $on) {
	if ($on) {
		if (file_exists($f['plugins.dir'] . '/' . $pluginp)) {
			if ((require $f['plugins.dir'] . '/' . $pluginp) === false) {
				if ($config['devmode']) {
					botlog("Il plugin '" . code($pluginp) . "' non è stato caricato dalla directory " . code($f['plugins.dir']), ['framework', 'plugins']);
				} else {
					botlog("Il plugin '" . code($pluginp) . "' non è stato caricato dalla directory " . code($f['plugins.dir']) . "\n💤 Plugin disattivato", ['framework', 'plugins']);
					$pls = json_decode(file_get_contents($f['plugins']), true);
					$pls[$pluginp] = false;
					file_put_contents($f['plugins'], json_encode($pls, JSON_PRETTY_PRINT));
				}
			}
		} else {
			if ($config['devmode']) {
				botlog("Il plugin '" . code($pluginp) . "' non è stato trovato nella directory " . code($f['plugins.dir']), ['framework', 'plugins']);
			} else {
				botlog("Il plugin '" . code($pluginp) . "' non è stato trovato nella directory " . code($f['plugins.dir']) . "\n💤 Plugin disattivato", ['framework', 'plugins']);
				$pls = json_decode(file_get_contents($f['plugins']), true);
				$pls[$pluginp] = false;
				file_put_contents($f['plugins'], json_encode($pls, JSON_PRETTY_PRINT));
			}
		}
	}
}

# Performance del Bot
$times['finish'] = microtime(true);
if ($cmd == "performance" and $isadmin) {
	foreach($times as $type => $sectime) {
		if ($type !== 'start') {
			$te[$extype] = $sectime - $extime;
			$extype = $type;
			$extime = $sectime;
		} else {
			$extype = $type;
			$extime = $sectime;
		}
	}
	$tot = number_format(microtime(true) - $times['start'], 6, ".", '');
	if ($config['logs']) {
		$tlogs = "\nLogs:     " . number_format($te['logs'], 6, '.', '') . " secondi";
	}
	if ($config['usa_redis']) {
		$tredis = "\nRedis:    " . number_format($te['redis'], 6, '.', '') . " secondi";
	}
	if ($config['usa_il_db']) {
		$tdb = "\nDatabase: " . number_format($te['database'], 6, '.', '') . " secondi";
	}
	sm($chatID, bold("Performance del Bot ⏱") . code("\nUpdate:   " . number_format($te['telegram_update'], 6, '.', '') . " secondi" . $tlogs . "\nFunzioni: " . number_format($te['functions'], 6, '.', '') . " secondi" . $tredis . $tdb . "\nPlugins:  " . number_format($te['plugins'], 6, '.', '') . " secondi" . "\nTotale:   $tot secondi"));
}
