<?php

/*
NeleBotFramework
	Copyright (C) 2018	PHP-Coders

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.	If not, see <http://www.gnu.org/licenses/>.
*/

# Connessione a Redis
$times['redis'] = microtime(true);
if ($config['usa_redis']) {
	// Configurazioni di Redis per l'accesso ai Database
	$redisc = $config['redis'];
	// Connessione a Redis
	try {
		$redis = new Redis();
		$redis->connect($redisc['host'], $redisc['port']);
	} catch (Exception $e) {
		botlog($e->getMessage(), $f['database'], 'redis');
		die();
	}
	// Autenticazione
	if ($redisc['password'] !== false) {
		try {
			$redis->auth($redisc['password']);
		} catch (Exception $e) {
			botlog($e, 'redis');
			die();
		}
	}
	// Selezione del database Redis
	if ($redisc['database'] !== false) {
		try {
			$redis->select($redisc['database']);
		} catch (Exception $e) {
			botlog($e, 'redis');
			die();
		}
	}
	// Anti-Ripetizione delle stesse update
	if ($redis->get($update['update_id'] . "-$botID")) {
		botlog("L'Update " . $update['update_id'] . "-$botID" . " è stata ripetuta.", ['framework', 'tg_updates']);
		die;
	} else {
		$redis->set($update['update_id'] . "-$botID", true);
	}
	// Test funzionamento
	if ($cmd == "redis" and $isadmin) {
		$redisping_start = microtime(true);
		$test = $redis->ping();
		$redisping_end = microtime(true);
		if ($test == "+PONG") {
			$res = "✅";
		} else {
			$res = "⚠";
		}
		sm($chatID, bold("Risultato del Test: ") . $res . "\n<b>Ping:</b> " . number_format($redisping_end - $redisping_start, 10));
		die;
	}
	// Elimina una variabile su Redis
	if (strpos($cmd, "redis_del ") === 0 and $isadmin) {
		$obj = str_replace("redis_del ", '', $cmd);
		$redis->del($obj);
		sm($chatID, "Fatto");
		die;
	}
	// Flush Redis
	if ($cmd == "redis_flush" and $isadmin) {
		$redis->flushDb();
		sm($chatID, "✅ Fatto");
		die;
	}
	// Anti-Flood del Bot
	if ($config['usa_il_db']) require($f['anti-flood']);
} else {
	if ($cmd == "redis" and $isadmin) {
		sm($chatID, bold("Risultato del Test: ") . "❌\nRedis è disattivato!");
		die;
	}
}

# Connessione al Database
$times['database'] = microtime(true);
if ($config['usa_il_db']) {
	if (strtolower($database['type']) == 'mysql') {
		try {
			$PDO = new PDO("mysql:host=" . $database['host'] . ";dbname=" . $database['nome_database'] . ";charset=utf8mb4", $database['utente'], $database['password']);
		} catch (PDOException $e) {
			botlog($e->getMessage(), ['pdo', 'database'], $f['database']);
			die;
		}
		$query = "CREATE TABLE IF NOT EXISTS utenti (
		user_id BIGINT(20) NOT NULL ,
		nome VARCHAR(64) NOT NULL ,
		cognome VARCHAR(64) ,
		username VARCHAR(32) ,
		lang VARCHAR(10) NOT NULL ,
		page VARCHAR(512) ,
		settings VARCHAR(512) DEFAULT '[]',
		first_update VARCHAR(64)	NOT NULL ,
		last_update VARCHAR(64) NOT NULL ,
		status VARCHAR(1024) DEFAULT '[]');";
		$PDO->query($query);
		$err = $PDO->errorInfo();
		if ($err[0] !== "00000") {
			botlog("PDO Error: errore nella creazione della tabella utenti, OUTPUT: " . json_encode($err), ['pdo', 'database'], $f['database']);
			die;
		}
		$query = "CREATE TABLE IF NOT EXISTS gruppi (
		chat_id BIGINT(20) NOT NULL ,
		title VARCHAR(64) NOT NULL ,
		description VARCHAR(256) ,
		username VARCHAR(32) ,
		admins VARCHAR(4096) DEFAULT '[]',
		permissions VARCHAR(1024) DEFAULT '[]',
		page VARCHAR(512) ,
		status VARCHAR(1024) DEFAULT '[]');";
		$PDO->query($query);
		$err = $PDO->errorInfo();
		if ($err[0] !== "00000") {
			botlog("PDO Error: errore nella creazione della tabella gruppi, OUTPUT: " . json_encode($err), ['pdo', 'database'], $f['database']);
			die;
		}
		if ($config['post_canali']) {
			$query = "CREATE TABLE IF NOT EXISTS canali (
			chat_id BIGINT(20) NOT NULL ,
			title VARCHAR(64) NOT NULL ,
			description VARCHAR(256) ,
			username VARCHAR(32) ,
			admins VARCHAR(4096) DEFAULT '[]' ,
			page VARCHAR(512) ,
			status VARCHAR(50) DEFAULT '[]');";
			$PDO->query($query);
			$err = $PDO->errorInfo();
			if ($err[0] !== "00000") {
				botlog("PDO Error: errore nella creazione della tabella gruppi, OUTPUT: " . json_encode($err), ['pdo', 'database'], $f['database']);
				die;
			}
		}
	} elseif (strtolower($database['type']) == 'sqlite') {
		try {
			$PDO = new PDO("sqlite:" . $database['nome_database'] . ";charset=utf8mb4");
		} catch (PDOException $e) {
			botlog($e->getMessage(), ['pdo', 'database'], $f['database']);
			die;
		}
		$query = "CREATE TABLE IF NOT EXISTS utenti (
		user_id BIGINT(20) NOT NULL ,
		nome VARCHAR(64) NOT NULL ,
		cognome VARCHAR(64) ,
		username VARCHAR(32) ,
		lang VARCHAR(10) NOT NULL ,
		page VARCHAR(512) ,
		settings VARCHAR(512) DEFAULT '[]',
		first_update VARCHAR(64)	NOT NULL ,
		last_update VARCHAR(64)	NOT NULL ,
		status VARCHAR(1024)	DEFAULT '[]');";
		$PDO->query($query);
		$err = $PDO->errorInfo();
		if ($err[0] !== "00000") {
			botlog("PDO Error: errore nella creazione della tabella utenti, OUTPUT: " . json_encode($err), ['pdo', 'database'], $f['database']);
			die;
		}
		$query = "CREATE TABLE IF NOT EXISTS gruppi (
		chat_id BIGINT(20) NOT NULL ,
		title VARCHAR(64) NOT NULL ,
		description VARCHAR(256) ,
		username VARCHAR(32) ,
		admins VARCHAR(4096) DEFAULT '[]',
		permissions VARCHAR(1024) DEFAULT '[]',
		page VARCHAR(512) ,
		status VARCHAR(1024) DEFAULT '[]');";
		$PDO->query($query);
		$err = $PDO->errorInfo();
		if ($err[0] !== "00000") {
			botlog("PDO Error: errore nella creazione della tabella gruppi, OUTPUT: " . json_encode($err), ['pdo', 'database'], $f['database']);
			die;
		}
		if ($config['post_canali']) {
			$query = "CREATE TABLE IF NOT EXISTS canali (
			chat_id BIGINT(20) NOT NULL ,
			title VARCHAR(64) NOT NULL ,
			description VARCHAR(256) ,
			username VARCHAR(32) ,
			admins VARCHAR(4096) DEFAULT '[]' ,
			page VARCHAR(512) ,
			status VARCHAR(50) DEFAULT '[]');";
			$PDO->query($query);
			$err = $PDO->errorInfo();
			if ($err[0] !== "00000") {
				botlog("PDO Error: errore nella creazione della tabella canali, OUTPUT: " . json_encode($err), ['pdo', 'database'], $f['database']);
				die;
			}
		}
	} elseif (strtolower($database['type']) == 'postgre') {
		try {
			$PDO = new PDO("pgsql:host=" . $database['host'] . ";dbname=" . $database['nome_database'] . ";charset=utf8mb4", $database['utente'], $database['password']);
		} catch (PDOException $e) {
			botlog($e->getMessage(), ['pdo', 'database'], $f['database']);
			die;
		}
		$query = "CREATE TABLE IF NOT EXISTS utenti (
		user_id BIGINT NOT NULL ,
		nome VARCHAR NOT NULL ,
		cognome VARCHAR ,
		username VARCHAR ,
		lang VARCHAR NOT NULL ,
		page VARCHAR ,
		settings VARCHAR DEFAULT '[]',
		first_update VARCHAR NOT NULL ,
		last_update VARCHAR NOT NULL ,
		status VARCHAR DEFAULT '[]');";
		$PDO->query($query);
		$err = $PDO->errorInfo();
		if ($err[0] !== "00000") {
			botlog("PDO Error: errore nella creazione della tabella utenti, OUTPUT: " . json_encode($err), ['pdo', 'database'], $f['database']);
			die;
		}
		$query = "CREATE TABLE IF NOT EXISTS gruppi (
		chat_id BIGINT NOT NULL ,
		title VARCHAR NOT NULL ,
		description VARCHAR ,
		username VARCHAR ,
		admins VARCHAR DEFAULT '[]' ,
		permissions VARCHAR DEFAULT '[]' ,
		page VARCHAR NOT NULL ,
		status VARCHAR DEFAULT '[]');";
		$PDO->query($query);
		$err = $PDO->errorInfo();
		if ($err[0] !== "00000") {
			botlog("PDO Error: errore nella creazione della tabella gruppi, OUTPUT: " . json_encode($err), ['pdo', 'database'], $f['database']);
			die;
		}
		if ($config['post_canali']) {
			$query = "CREATE TABLE IF NOT EXISTS canali (
			chat_id BIGINT NOT NULL ,
			title VARCHAR NOT NULL ,
			description VARCHAR ,
			username VARCHAR ,
			admins VARCHAR  DEFAULT '[]' ,
			page VARCHAR ,
			status VARCHAR  DEFAULT '[]');";
			$PDO->query($query);
			$err = $PDO->errorInfo();
			if ($err[0] !== "00000") {
				botlog("PDO Error: errore nella creazione della tabella canali, OUTPUT: " . json_encode($err), ['pdo', 'database'], $f['database']);
				die;
			}
		}
	} else {
		botlog("Errore: tipo di database sconosciuto.", ['database', 'framework'], $f['database']);
		die;
	}
	
	if ($isadmin and strtolower($cmd) == $database['type']) {
		$dbping_start = microtime(true);
		$test = db_query("SELECT * FROM utenti WHERE user_id = ?", [$userID], true);
		$dbping_end = microtime(true);
		if ($test['user_id']) {
			$res = "✅";
		} else {
			$res = "⚠";
		}
		sm($chatID, bold("Risultato del Test: ") . "$res\n<b>Ping:</b> " . number_format($dbping_end - $dbping_start, 10));
		die;
	}
	
	# Database di Utenti
	if ($userID) {
		# Ban dal Bot
		if (is_int($banFromAntiflood)) {
			$dateban = time() + $banFromAntiflood;
			foreach (array_keys($config['cloni']) as $idBot) {
				$new[$idBot] = "ban$dateban";
			}
			db_query("UPDATE utenti SET status = ? WHERE user_id = ?", [json_encode($new), $userID], "no");
			die;
		}
		$u = db_query("SELECT * FROM utenti WHERE user_id = ? LIMIT 1", [$userID], true);
		if (!$cognome) {
			$cognome = "";
		}
		if (!$username) {
			$username = "";
		} else {
			$hausername = "\n<b>Username:</> @$username";
		}
		if (!isset($lingua)) {
			$lingua = "en";
		} else {
			$lingua = explode("-", $lingua)[0];
			$rest = [
				'root' => 'en',
				'rus' => 'ru',
				'uzb' => 'uz',
				'zh' => 'zh_TW',
				'nb_NO' => 'nb',
				'gsw' => 'de'
			];
			if (in_array($lingua, array_keys($rest))) {
				$lingua = $rest[$lingua];
			}
		}
		if (!$u['user_id']) {
			$first_start = true;
			unset($new);
			if ($is_bot) {
				foreach (array_keys($config['cloni']) as $idBot) {
					$new[$idBot] = "bot";
				}
			} else {
				$new[$botID] = "attivo";
			}
			db_query("INSERT INTO utenti (user_id, nome, cognome, username, lang, page, status, last_update, first_update) VALUES (?,?,?,?,?,?,?,?,?)", [$userID, $nome, $cognome, $username, $lingua, '', json_encode($new), time(), time()], 'no');
			$u = db_query("SELECT * FROM utenti WHERE user_id = ? LIMIT 1", [$userID], true);
		}
		if ($u) {
			if ($nome !== $u['nome'] or $cognome !== $u['cognome'] or $username !== $u['username']) {
				$u['nome'] = $nome;
				$u['cognome'] = $cognome;
				$u['username'] = $username;
				db_query("UPDATE utenti SET nome = ?, cognome = ?, username = ? WHERE user_id = ?", [$nome, $cognome, $username, $userID], 'no');
			}
			$u['settings'] = json_decode($u['settings'], true);
			if (!is_array($u['settings'])) {
				$u['settings'] = [];
				$update_settings = true;
			}
			$u['status'] = json_decode($u['status'], true);
			if (!is_array($u['status'])) {
				unset($new);
				if ($is_bot) {
					foreach (array_keys($config['cloni']) as $idBot) {
						$new[$idBot] = "bot";
					}
				} else {
					$new[$botID] = "attivo";
				}
				$u['status'] = $new;
				db_query("UPDATE utenti SET status = ? WHERE user_id = ?", [json_encode($u['status']), $userID], "no");
			}
			if ($typechat == "private" and strpos($u['status'][$botID], "ban") !== 0 and $u['status'][$botID] !== "bot") {
				if (in_array($u['status'][$botID], ["attivo", "wait", "inattesa", "blocked"])) {
					$is_new = true;
					$u['status'][$botID] = "avviato";
					db_query("UPDATE utenti SET status = ? WHERE user_id = ?", [json_encode($u['status']), $userID], "no");
				}
			}
			if ($u['last_update'] + 60 < time()) {
				$u['last_update'] = time();
				db_query("UPDATE utenti SET last_update = ? WHERE user_id = ?", [$u['last_update'], $userID], "no");
			}
		} else {
			botlog("Utente non caricato sul database: $userID", ['database'], $f['database']);
			die;
		}
	}
	
	# Datbase di Gruppi e Canali
	if ($chatID < 0) {
		if (in_array($typechat, ["supergroup", "group"])) {
			if ($chatbanFromAntiflood) {
				$dateban = time() + $chatbanFromAntiflood;
				foreach (array_keys($config['cloni']) as $idBot) {
					$new[$idBot] = "ban$dateban";
				}
			}
			$g = db_query("SELECT * FROM gruppi WHERE chat_id = ?", [$chatID]);
			if (!isset($usernamechat)) {
				$usernamechat = "";
			}
			if (!$g) {
				$getchat = getChat($chatID);
				if (isset($getchat['result']['permissions'])) {
					$perms = $getchat['result']['permissions'];
				} else {
					$perms = ["can_send_messages" => true, "can_send_media_messages" => true, "can_send_polls" => true, "can_send_other_messages" => true, "can_add_web_page_previews" => true, "can_change_info" => false, "can_invite_users" => false, "can_pin_messages" => false];
				}
				if (isset($getchat['result']['description'])) {
					$descrizione = $getchat['result']['description'];				
				} else {
					$descrizione = "";
				}
				$admins = getAdmins($chatID);
				if (isset($admins['ok'])) {
					$adminsg = json_encode($admins['result']);
				} else {
					$adminsg = "[]";
				}
				db_query("INSERT INTO gruppi (chat_id, title, description, username, admins, page, permissions, status) VALUES (?,?,?,?,?,?,?,?)", [$chatID, $title, $descrizione, $usernamechat, $adminsg, '', json_encode($perms), 'attivo'], 'no');
				$g = db_query("SELECT * FROM gruppi WHERE chat_id = ?", [$chatID]);
			}
			if ($g) {
				// Aggiornamento automatico della chat
				if ($title !== $g['title'] or $usernamechat !== $g['username']) {
					$getchat = getChat($chatID);
					if (isset($getchat['result']['permissions'])) {
						$perms = $getchat['result']['permissions'];
					} else {
						$perms = ["can_send_messages" => true, "can_send_media_messages" => true, "can_send_polls" => true, "can_send_other_messages" => true, "can_add_web_page_previews" => true, "can_change_info" => false, "can_invite_users" => false, "can_pin_messages" => false];
					}
					if (isset($getchat['result']['description'])) {
						$descrizione = $getchat['result']['description'];				
					} else {
						$descrizione = "";
					}
					$admins = getAdmins($chatID);
					if (isset($admins['ok'])) {
						$adminsg = json_encode($admins['result']);
					} else {
						$adminsg = "[]";
					}
					db_query("UPDATE gruppi SET title = ?, username = ?, admins = ?, description = ?, page = ' ' WHERE chat_id = $chatID", [$title, $usernamechat, $adminsg, $descrizione]);
					sm($chatID, "✅ Dati aggiornati automaticamente!");
				}
				// Auto-Fix per correggere l'array mancante o il json errato dello stato
				if (!$g['status'] or !is_array($g['status'])) {
					$g['status'] = [$botID => 'attivo'];
					db_query("UPDATE gruppi SET status = ? WHERE chat_id = ?", [json_encode($g['status']), $chatID], "no");
				}
				$g['permissions'] = json_decode($g['permissions'], true);
				// Auto-Fix per correggere l'array mancante o il json errato dei permessi globali
				if (!is_array($g['permissions'])) {
					$g['permissions'] = ["can_send_messages" => true, "can_send_media_messages" => true, "can_send_polls" => true, "can_send_other_messages" => true, "can_add_web_page_previews" => true, "can_change_info" => false, "can_invite_users" => false, "can_pin_messages" => false];
					db_query("UPDATE gruppi SET permissions = ? WHERE chat_id = ?", [json_encode($g['permissions']), $chatID]);
				}
				// Attivazione della chat
				if (in_array($g['status'][$botID], ["inattivo", "inattesa"])) {
					if (in_array($typechat, ["group", "supergroup"])) {
						$g['status'][$botID] = 'avviato';
					} else {
						$g['status'][$botID] = 'attivo';
					}
					db_query("UPDATE gruppi SET status = ? WHERE chat_id = ?", [json_encode($g['status']), $chatID], 'no');
				} elseif ($g['status'][$botID] == "attivo" and in_array($typechat, ["group", "supergroup"])) {
					$g['status'][$botID] = 'avviato';
					db_query("UPDATE gruppi SET status = ? WHERE chat_id = ?", [json_encode($g['status']), $chatID], 'no');
				}
				# Permessi dello Staff di un Gruppo
				$g['admins'] = json_decode($g['admins'], true);
				if (!empty($g['admins'])) {
					foreach ($g['admins'] as $adminsa) {
						// Verifica se l'utente è un amministratore
						if ($adminsa['user']['id'] == $userID) {
							$isStaff = true;
							$uPerms = $adminsa['permissions'];
						}
						// Verifica se l'utente in reply è un amministratore
						if ($adminsa['user']['id'] == $ruserID) {
							$isrStaff = true;
							$urPerms = $adminsa['permissions'];
						}
						// Verifica se l'utente del messaggio inoltrato è un amministratore
						if ($adminsa['user']['id'] == $fuserID) {
							$isfStaff = true;
							$ufPerms = $adminsa['permissions'];
						}
						// Verifica se il bot è un amministratore
						if ($adminsa['user']['id'] == $botID) {
							$botisadmin = true;
							$botperms = $adminsa;
						}
						// Verifica se l'utente è il creatore
						if ($adminsa['user']['id'] == $userID and $adminsa['status'] == 'creator') {
							$isfounder = true;
							$isStaff = true;
							$uPerms = [
								'can_send_messages'			=> true,
								'can_send_media_messages'	=> true,
								'can_send_polls'			=> true,
								'can_send_other_messages'	=> true,
								'can_add_web_page_previews'	=> true,
								'can_change_info'			=> true,
								'can_invite_users'			=> true,
								'can_pin_messages'			=> true,
								'can_change_info'			=> true,
								'can_delete_messages'		=> true,
								'can_invite_users'			=> true,
								'can_restrict_members'		=> true,
								'can_pin_messages'			=> true,
								'can_promote_members'		=> true
							];
						}
					}
				}
			} else {
				botlog("Gruppo non caricato sul database: $chatID", ['database'], $f['database']);
				die;
			}	
		} elseif ($typechat == "channel") {
			if ($chatbanFromAntiflood) {
				$dateban = time() + $chatbanFromAntiflood;
				foreach (array_keys($config['cloni']) as $idBot) {
					$new[$idBot] = "ban$dateban";
				}
				db_query("UPDATE canali SET status = ? WHERE chat_id = ?", [json_encode($new), $chatID], "no");
				die;
			}
			$c = db_query("SELECT * FROM canali WHERE chat_id = ?", [$chatID]);
			if (!isset($usernamechat)) {
				$usernamechat = "";
			}
			if (!$c) {
				$descrizione = getChat($chatID);
				$descrizione = $descrizione['result']['description'];
				if (!isset($descrizione)) {
					$descrizione = "";
				}
				$adminsg = getAdmins($chatID);
				$adminsg = json_encode($adminsg['result']);
				db_query("INSERT INTO canali (chat_id, title, description, username, admins, page, status) VALUES (?,?,?,?,?,?,?)", [$chatID, $title, $descrizione, $usernamechat, $adminsg, '', 'attivo']);
				$c = db_query("SELECT * FROM canali WHERE chat_id = ?", [$chatID]);
			}
			if ($c) {
				// Aggiornamento automatico dei dati del canale
				if ($title !== $c['title'] or $usernamechat !== $c['username']) {
					$descrizione = getChat($chatID);
					$descrizione = $descrizione['result']['description'];
					if (!isset($descrizione)) {
						$descrizione = "";
					}
					$adminsg = getAdmins($chatID);
					$adminsg = json_encode($adminsg['result']);
					db_query("UPDATE canali SET title = ?, username = ?, admins = ?, description = ?, page = ? WHERE chat_id = ?", [$title, $usernamechat, $adminsg, $descrizione, ' ', $chatID]);
				}
				// Auto-Fix per correggere l'array mancante o il json errato dello stato
				if (!$c['status'] or !is_array($c['status'])) {
					$c['status'] = [$botID => 'attivo'];
					db_query("UPDATE gruppi SET status = ? WHERE chat_id = ?", [json_encode($c['status']), $chatID], "no");
				}
				// Attivazione della chat
				if (in_array($c['status'][$botID], ["inattivo", "inattesa"])) {
					if (in_array($typechat, ["channel"])) {
						$c['status'][$botID] = 'avviato';
					} else {
						$c['status'][$botID] = 'attivo';
					}
					db_query("UPDATE canali SET status = ? WHERE chat_id = ?", [json_encode($c['status']), $chatID], 'no');
				} elseif ($c['status'][$botID] == "attivo" and in_array($typechat, ["channel"])) {
					$c['status'][$botID] = 'avviato';
					db_query("UPDATE canali SET status = ? WHERE chat_id = ?", [json_encode($c['status']), $chatID], 'no');
				}
			} else {
				botlog("Gruppo non caricato sul database: $chatID", ['database'], $f['database']);
				die;
			}
		}
	}
	
	# Comandi sul Database solo per Amministratori del Bot
	if($isadmin) {
		# Comando per il Ban di un utente dal Bot
		if (strpos($cmd, "ban ") === 0 and $typechat == "private") {
			$ex = explode(" ", $cmd, 3);
			$id = str_replace("@", '', $ex[1]);
			if (isset($id)) {
				if (isset($ex[2])) {
					$dat = $ex[2];
					if (strpos($dat, "/") !== false) {
						$exp = explode(" ", $dat);
						$data = explode("/", $exp[0]);
						if (isset($exp[1])) {
							$ora = explode(":", $exp[1]);
						} else {
							$ora = "000";
						}
						$dateban = mktime($ora[0], $ora[1], $ora[2], $data[1], $data[0], $data[2]);
						if ($dateban < time()) {
							sm($chatID, "Data non valida. \nLa tua data sarebbe destinata al " . date("d/m/Y", $dateban) . " alle " . date("H:i:s", $dateban));
							die;
						}
					} elseif (strpos($dat, "for ") !== false) {
						$exp = strtolower(str_replace("for ", '', $dat));
						$dateban = strtotime($exp);
						if ($dateban <= time()) {
							sm($chatID, "Data non valida. \nLa tua data sarebbe destinata al " . date("d/m/Y", $dateban) . " alle " . date("H:i:s", $dateban));
							die;
						}
					} else {
						sm($chatID, "Invalid date.");
						die;
					}
					$al = "al " . date("d/m/Y", $dateban) . " alle " . date("H:i:s", $dateban);
					$stato = 'ban' . $dateban;
				} else {
					$al = "tempo indefinito.";
					$stato = 'ban';
				}
				$q = db_query("SELECT * FROM utenti WHERE user_id = ? or username = ? LIMIT 1", [round($id), $id], true);
				$db = "utenti";
				if (!$q['user_id']) {
					$q = db_query("SELECT * FROM canali WHERE chat_id = ? or username = ? LIMIT 1", [round($id), $id], true);
					$db = "canali";
					if (!isset($q['chat_id'])) {
						$q = db_query("SELECT * FROM gruppi WHERE chat_id = ? or username = ? LIMIT 1", [round($id), $id], true);
						$db = "gruppi";
						if (!isset($q['chat_id'])) {
							cb_reply($cbid, "Chat non trovata nel database", true);
							die;
						} elseif (strpos(json_decode($q['status'], true)[$botID], "ban") === 0) {
							sm($chatID, "Questa chat è già bannata...");
							die;
						} else {
							$id = $q['chat_id'];
						}
					} elseif (strpos(json_decode($q['status'], true)[$botID], "ban") === 0) {
						sm($chatID, "Questa chat è già bannata...");
						die;
					} else {
						$id = $q['chat_id'];
					}
				} elseif (strpos(json_decode($q['status'], true)[$botID], "ban") === 0) {
					sm($chatID, "Questo utente è già bannato...");
					die;
				} else {
					$id = $q['user_id'];
				}
				unset($new);
				foreach (array_keys($config['cloni']) as $idBot) {
					$new[$idBot] = "ban$dateban";
				}
				if ($db == "utenti") {
					$tag = tag($id, $q['nome'], $q['cognome']);
					db_query("UPDATE utenti SET status = ? WHERE user_id = ?", [json_encode($new), round($id)], "no");
				} else {
					$tag = $q['title'];
					if ($q['username']) $tag = text_link($tag, "https://t.me/" . $q['username']);
					db_query("UPDATE $db SET status = ? WHERE chat_id = ?", [json_encode($new), round($id)], "no");
				}
				sm($chatID, "Ho bandito " . $tag . " dall'utilizzo del Bot fino a $al");
				db_query("UPDATE utenti SET status = ? WHERE user_id = ?", [json_encode($new), $id], "no");
				botlog("Gestione database\n" . bold("Chat bannata: ") . $tag . " [" . code($id) . "] " . bold("\nData: ") . date("d/m/Y h:i:s") . bold("\nBan di: ") .tag(), ["ban", "id$userID"]);
			}
			die;
		}
		# Comando per l'Unban di un utente dal Bot
		if (strpos($cmd, "unban ") === 0 and $typechat == "private") {
			$ex = explode(" ", $cmd, 2);
			$id = str_replace("@", '', $ex[1]);
			if (isset($id)) {
				if ($config['usa_redis']) {
					$redis->del($id);
				}
				$q = db_query("SELECT * FROM utenti WHERE user_id = ? or username = ? LIMIT 1", [round($id), $id], true);
				$db = "utenti";
				if (!$q['user_id']) {
					$q = db_query("SELECT * FROM canali WHERE chat_id = ? or username = ? LIMIT 1", [round($id), $id], true);
					$db = "canali";
					if (!isset($q['chat_id'])) {
						$q = db_query("SELECT * FROM gruppi WHERE chat_id = ? or username = ? LIMIT 1", [round($id), $id], true);
						$db = "gruppi";
						if (!isset($q['chat_id'])) {
							cb_reply($cbid, "Chat non trovata nel database", true);
							die;
						} elseif (strpos(json_decode($q['status'], true)[$botID], "ban") !== 0) {
							sm($chatID, "Questa chat non è bannata...");
							die;
						} else {
							$id = $q['chat_id'];
						}
					} elseif (strpos(json_decode($q['status'], true)[$botID], "ban") !== 0) {
						sm($chatID, "Questa chat non è bannata...");
						die;
					} else {
						$id = $q['chat_id'];
					}
				} elseif (strpos(json_decode($q['status'], true)[$botID], "ban") !== 0) {
					sm($chatID, "Questo utente non è bannato...");
					die;
				} else {
					$id = $q['user_id'];
				}
				if ($db == "utenti") {
					$tag = tag($id, $q['nome'], $q['cognome']);
					db_query("UPDATE utenti SET status = ? WHERE user_id = ?", [json_encode([$botID => 'attivo']), round($id)], "no");
				} else {
					$tag = $q['title'];
					if ($q['username']) $tag = text_link($tag, "https://t.me/" . $q['username']);
					db_query("UPDATE $db SET status = ? WHERE chat_id = ?", [json_encode([$botID => 'attivo']), round($id)], "no");
				}
				if ($config['usa_redis']) {
					$redis->del($id);
				}
				sm($chatID, "Ho riammesso " . $tag . " all'utilizzo del Bot.");
				botlog("Gestione database\n" . bold("Chat sbannata: ") . $tag . " [" . code($q['chat_id']) . "] " . bold("\nData: ") . date("d/m/Y h:i:s") . bold("\nUnBan di: ") .tag(), ["unban", "id$userID"]);
			}
			die;
		}
		# JSON del Utente sul Database
		if ($cmd == "database") {
			if (in_array($typechat, ["supergroup", "group"])) {
				sm($chatID, code(json_encode($g, JSON_PRETTY_PRINT)));
			} else {
				sm($chatID, code(json_encode($u, JSON_PRETTY_PRINT)));
			}
			die;
		}
		# Test degli errori sul Database
		if ($cmd == "dberror") {
			$error = db_query("TEST ERRORI AL DB", false, false);
			sm($chatID, json_encode($error));
			die;
		}
		# Query manuali
		if (strpos($cmd, "query ") === 0) {
			$query = str_replace("query ", '', $cmd);
			$r = db_query($query, false, false);
			sm($chatID, bold("Query: ") . code($query) . "\n" . bold("Risultato: ") . code(substr(json_encode($r), 0, 2047)));
			die;
		}
	}
	
	# Ban dal Bot
	if (!$isadmin) {
		if (strpos($u['status'][$botID], "ban") === 0) {
			if ($u['status'][$botID] == "ban") {
				// Ban a durata non specificata
			} else {
				$time = str_replace("ban", '', $u['status'][$botID]);
				if ($time < time()) {
					if ($config['usa_redis']) {
						$redis->del($userID);
					}
					$u['status'][$botID] = 'attivo';
					db_query("UPDATE utenti SET status = ? WHERE user_id = ?", [json_encode($u['status']), $userID]);
					sm($config['console'], "#UnBan #id$userID \n" . bold("Utente unbannato: ") . tag() . " [" . code($userID) . "] \n" . bold("Data: ") . date("d/m/Y h:i:s") . bold("\nUnban automatico"));
				}
			}
			die; // Ban per un utente
		}
		if (strpos($g['status'][$botID], "ban") === 0) {
			if ($g['status'][$botID] == "ban") {
				// Ban a durata non specificata
			} else {
				$time = str_replace("ban", '', $g['status'][$botID]);
				if ($time < time()) {
					if ($config['usa_redis']) {
						$redis->del($chatID);
					}
					$g['status'][$botID] = 'attivo';
					db_query("UPDATE gruppi SET status = ? WHERE chat_id = ?", [json_encode($g['status']), $chatID]);
					sm($chatID, "Gruppo sbannato dal Bot");
					sm($config['console'], "Gruppo sbannato: " . bold("$title") . " [" . code($chatID) . "]");
				}
			}
			lc($chatID);
			die; // Ban per un gruppo
		}
		if (strpos($c['status'][$botID], "ban") === 0) {
			if ($c['status'][$botID] == "ban") {
				// Ban a durata non specificata
			} else {
				$time = str_replace("ban", '', $g['status'][$botID]);
				if ($time < time()) {
					if ($config['usa_redis']) {
						$redis->del($chatID);
					}
					$c['status'][$botID] = 'attivo';
					db_query("UPDATE canali SET status = ? WHERE chat_id = ?", [json_encode($c['status']), $chatID]);
					sm($config['console'], "Canale sbannato: " . bold("$title") . " [" . code($chatID) . "]");
				}
			}
			lc($chatID);
			die; // Ban per un canale
		}
	}
	
	# Comando Riavvia
	if ($cmd == "riavvia" and in_array($typechat, ['channel', 'private'])) {
		if (isset($c['chat_id'])) {
			# Aggiornamento canale
			if (!isset($usernamechat)) {
				$usernamechat = "";
			}
			$getchat = getChat($chatID);
			if (isset($getchat['result']['description'])) {
				$descrizione = $getchat['result']['description'];				
			} else {
				$descrizione = "";
			}
			if ($descrizione == $c['description']) {
				$altro .= "\n🔄 Descrizione già aggiornata";
			} else {
				if (!$descrizione) {
					$altro .= "\n🗑 Descrizione rimossa";
				} else {
					$altro .= "\n✅ Descrizione aggiornata";
				}
			}
			$admins = getAdmins($chatID);
			if (isset($admins['ok'])) {
				$adminsg = json_encode($admins['result']);
				if ($adminsg !== $c['admins']) {
					$altro .= "\n✅ Lista Admins aggiornata";
				} else {
					$altro .= "\n🔄 Lista Admins già aggiornata";
				}
			} else {
				$altro .= "\n❌ Lista Admins...";
				$adminsg = "[]";
			}
			db_query("UPDATE canali SET title = ?, username = ?, admins = ?, description = ? WHERE chat_id = ?", [$title, $usernamechat, $adminsg, $descrizione, $chatID], 'no');
		} else {
			# Aggiornamento dati utente
			if ($u['page']) {
				db_query("UPDATE utenti SET page = ? WHERE user_id = ?", ["", $userID], 'no');
				$altro .= "\n✅ Comando in esecuzione annullato";
			}
			// Qui potete mettere aggiornamenti dei dati secondari presenti nel database
		}
		sm($chatID, "✅ Bot riavviato $altro");
		die;
	}

} else {
	if ($cmd == "database" and $isadmin) {
		sm($chatID, "Database non attivo");
		die;
	}
}
