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

# Sistema delle informazioni sull'invio di una richiesta da Telegram
# Controlla le variabili già esistenti per utilizzare al meglio il Framework
if (isset($update)) {
	
	# Sistemazione update del Bot in base alla configurazione
	if (isset($update['channel_post'])) { // Post inviato su un canale
		if ($config['post_canali']) {
			$update['message'] = $update['channel_post'];
			$update['message']['chat']['typechat'] = 'channel';
		} else {
			die;
		}
	}
	if (isset($update['edited_channel_post'])) { // Post modificato su un canale
		if ($config['post_canali']) {
			$modificato = true;
			$update['message'] = $update['edited_channel_post'];
			$update['message']['chat']['typechat'] = 'channel';
		} else {
			die;
		}
	}
	if (isset($update['edited_message'])) {// Messaggio modificato
		if ($config['modificato']) {
			$modificato = true;
			$update['message'] = $update['edited_message'];
		} else {
			die;
		}
	}

	# Sistemazione delle variabili
	
	if (isset($update['message']['author_signature']) and $config['post_canali']) { // Informazioni utente su un canale
		$firma = $update['message']['author_signature']; // Firma del Post su un canale
	}
	if (isset($update['message']['forward_signature']) and $config['post_canali']) { // Informazioni utente su un messaggio inoltrato dal canale
		$ffirma = $update['message']['forward_signature']; // Firma del Post su un canale
	}

	# Informazioni utente
	if (isset($update['message']['from'])) {
		$exists_user = true;
		$userID = $update['message']['from']['id'];
		$nome = $update['message']['from']['first_name'];
		$cognome = $update['message']['from']['last_name'];
		$username = $update['message']['from']['username'];
		$lingua = $update['message']['from']['language_code'];
		$is_bot = $update['message']['from']['is_bot'];
	}

	# Informazioni utente inoltrato
	if (isset($update['message']['forward_from'])) {
		$exists_fuser = true;
		$fuserID = $update['message']['forward_from']['id'];
		$fnome = $update['message']['forward_from']['first_name'];
		$fcognome = $update['message']['forward_from']['last_name'];
		$fusername = $update['message']['forward_from']['username'];
		$flingua = $update['message']['forward_from']['language_code'];
		$fis_bot = $update['message']['forward_from']['is_bot'];
	}

	# Messaggio sulla risposta
	if (isset($update['message']['reply_to_message'])) {
		$reply = true;
		$rmsg = $update['message']['reply_to_message']['text']; // Testo del messaggio al quale si risponde
		$rcaption = $update['message']['reply_to_message']['caption']; // Testo del messaggio al quale si risponde
		if ($rmsg) {
			$rentities = $update['message']['reply_to_message']['entities']; // Entità del messaggio al quale si risponde
		} else {
			$rentities = $update['message']['reply_to_message']['caption_entities']; // Entità della didascalia al quale si risponde
		}
		$rmsgID = $update['message']['reply_to_message']['message_id']; // ID del messaggio al quale si risponde
		$rmenu = $update['message']['reply_to_message']['reply_markup']; // Testo del messaggio al quale si risponde
		$rdata = $update['message']['reply_to_message']['date']; // Data del messaggio in reply
		
		# Informazioni utente sulla reply
		if (isset($update['message']['reply_to_message']['from'])) {
			$exists_ruser = true;
			$ruserID = $update['message']['reply_to_message']['from']['id'];
			$rnome = $update['message']['reply_to_message']['from']['first_name'];
			$rcognome = $update['message']['reply_to_message']['from']['last_name'];
			$rusername = $update['message']['reply_to_message']['from']['username'];
			$rlingua = $update['message']['reply_to_message']['from']['language_code'];
			$ris_bot = $update['message']['reply_to_message']['from']['is_bot'];
		} 
	
		# Informazioni utente inoltrato sulla reply
		if (isset($update['message']['reply_to_message']['forward_from'])) {
			$exists_rfuser = true;
			$rfuserID = $update['message']['reply_to_message']['forward_from']['id'];
			$rfnome = $update['message']['reply_to_message']['forward_from']['first_name'];
			$rfcognome = $update['message']['reply_to_message']['forward_from']['last_name'];
			$rfusername = $update['message']['reply_to_message']['forward_from']['username'];
			$rflingua = $update['message']['reply_to_message']['forward_from']['language_code'];
			$rfis_bot = $update['message']['reply_to_message']['forward_from']['is_bot'];
		} 
	}

	# Messaggio inviato
	$msg = $update['message']['text']; // Testo del messaggio inviato (Vale anche per quelli inoltrati)
	$entities = $update['message']['entities']; // Entità del messaggio inviato (Vale anche per quelli inoltrati)
	$msgID = $update['message']['message_id']; // ID del messaggio inviato
	$caption = $update['message']['caption']; // Testo che si trova nei file media

	# Date e orari [Timestamp]
	$data = $update['message']['date']; // Data dell'invio del Messaggio (Vale anche per quelli inoltrati)
	if (isset($modificato)) {
		$edata = $update['message']['edit_date']['date']; // Data dell'ultima modifica sul messagio
	}
	$fdata = $update['message']['forward_date']; // Data del messaggio inoltrato

	# Gruppi e Canali
	$chatID = $update['message']['chat']['id'];  // ID del gruppo/canale
	$typechat = $update['message']['chat']['type']; // Tipo di chat (private, group, supergroup, channel)
	if ($typechat !== "private") {
		$title = $update['message']['chat']['title']; // Titolo del gruppo/canale
		$usernamechat = $update['message']['chat']['username']; // Username del gruppo/canale
	}
	
	# Informazioni chat inoltrate
	if (isset($update['message']['forward_from_chat'])) {
		$fchatID = $update['message']['forward_from_chat']['id']; // ID del gruppo/canale del messaggio inoltrato
		$ftypechat = $update['message']['forward_from_chat']['type']; // Tipo ci chat (private, group, supergroup, channel) (In base all' inoltro)
		if ($ftypechat !== "private") {
			$ftitle = $update['message']['forward_from_chat']['title']; // Titolo del canale da cui è stato inoltrato
			$fusernamechat = $update['message']['forward_from_chat']['username']; // Username del canale da cui è stato inoltrato
		}
	}

	# CallBack Query
	if (isset($update["callback_query"])) {
		$cbid = $update["callback_query"]["id"]; // ID della query
		$cbdata = $update["callback_query"]["data"]; // Messaggio della query
		$data = $update["callback_query"]['message']['date']; // Data dell'invio del Messaggio (Vale anche per quelli inoltrati)
		$messageType = "callback_query";
		$caption = $update["callback_query"]['message']['caption']; // Didascalia dei file media sui callback

		# Informazioni Chat
		$chatID = $update["callback_query"]['message']['chat']['id'];  // ID del gruppo/canale
		$typechat = $update["callback_query"]['message']['chat']['type']; // Tipo di chat (private, group, supergroup, channel)
		if ($typechat !== "private") {
			$title = $update["callback_query"]['message']['chat']['title']; // Titolo del gruppo/canale
			$usernamechat = $update["callback_query"]['message']['chat']['username']; // Username del gruppo/canale
		}
		
		# Informazioni utente
		if (isset($update['callback_query']['from'])) {
			$exists_user = true;
			$userID = $update['callback_query']['from']['id'];
			$nome = $update['callback_query']['from']['first_name'];
			$cognome = $update['callback_query']['from']['last_name'];
			$username = $update['callback_query']['from']['username'];
			$lingua = $update['callback_query']['from']['language_code'];
		} 
		
		if (isset($update["callback_query"]["inline_message_id"])) { // CallBack per i messaggi inline
			$cbmid = $update["callback_query"]["inline_message_id"]; // ID del messaggio mandato inline nella query
		} else {
			$cbmid = $update["callback_query"]["message"]["message_id"]; // ID del messaggio nella query
			$chatID = $update["callback_query"]["message"]["chat"]["id"]; // ID della Chat sulla query
		}
	}

	# Media
	if (isset($update["message"]["dice"])) {// La cosa più inutile su Telegram
		$messageType = "dice";
		$dice = $update["message"]["dice"];
		$dice_v = $update["message"]["dice"]['value']; // Valore numerico della cosa più inutile su Telegram
		$dice_e = $update["message"]["dice"]['emoji']; // Emoji della cosa più inutile su Telegram
	} elseif (isset($update["message"]["venue"])) { // Posto
		$messageType = "venue";
		$venue = true;
		$posto = $update['message']['venue']['title']; // Titolo della posizione
		$address = $update['message']['venue']['address']; // Indirizzo della posizione
		$posizione = $update['message']['venue']['location']; // Posizione del GPS inviata
	} elseif (isset($update["message"]["location"])) { // Posizione
		$messageType = "location";
		$posizione = $update["message"]['location']; // Posizione del GPS inviata
	} elseif (isset($update["message"]["voice"])) { // Messaggio vocale
		$messageType = "voice";
		$vocale = $update["message"]["voice"]; // Array del messaggio vocale inviato
		$vocale_id = $update["message"]["voice"]["file_id"]; // ID del messaggio vocale inviato
		$vocale_uid = $update["message"]["voice"]["file_unique_id"]; // ID unico del messaggio vocale inviato
	} elseif (isset($update["message"]["animation"])) { // GIF
		$messageType = "gif";
		$gif = $update["message"]["animation"]; // Array della GIF inviata
		$file_id = $update["message"]["animation"]["file_id"]; // ID della GIF inviata
		$gif_uid = $update["message"]["animation"]["file_unique_id"]; // ID unico della GIF inviato
	} elseif (isset($update["message"]["photo"])) { // Foto
		$messageType = "photo";
		$foto = $update["message"]["photo"]; // ID della foto inviata a minima qualità
		$foto_id = $update["message"]["photo"][0]["file_id"]; // ID della foto inviata a minima qualità
		$foto_uid = $update["message"]["photo"][0]["file_unique_id"]; // ID unico della foto inviata a minima qualità
	} elseif (isset($update["message"]["video"])) { // Video
		$messageType = "video";
		$video = $update["message"]["video"]['file_id']; // Array del video inviato
		$video_id = $update["message"]["video"]['file_id']; // ID del video inviato
		$video_uid = $update["message"]["video"]['file_unique_id']; // ID unico del video inviato
	} elseif (isset($update["message"]["video_note"])) { // Video rotondo
		$messageType = "video_note";
		$video_note = $update["message"]["video_note"]; // Array del video rotondo inviato
		$video_note_id = $update["message"]["video_note"]['file_id']; // ID del video rotondo inviato
		$video_note_uid = $update["message"]["video_note"]['file_unique_id']; // ID unico del video rotondo inviato
	} elseif (isset($update["message"]["audio"])) { // File audio
		$messageType = "audio";
		$audio = $update["message"]["audio"];
		$audio_id = $update["message"]["audio"]["file_id"]; // ID del file audio inviato
		$audio_uid = $update["message"]["audio"]["file_unique_id"]; // ID unico del file audio inviato
	} elseif (isset($update["message"]["sticker"])){ // Sticker
		$messageType = "sticker";
		$s_setname = $update["message"]["sticker"]["set_name"]; // Nome del Pacchetto Sticker
		$sticker = $update["message"]["sticker"]["file_id"]; // ID dello Sticker inviato
		$sticker_uid = $update["message"]["sticker"]["file_unique_id"]; // ID unico dello Sticker inviato
		$is_animated = $update["message"]["sticker"]["is_animated"]; // Definisce se è una sticker animata o meno
		if ($is_animated) $messageType = "animated sticker";
		$s_emoji = $update["message"]["sticker"]["emoji"]; // Emoji attribuito allo Sticker inviato
		$s_x = $update["message"]["sticker"]["width"]; // Larghezza dell'immagine Sticker
		$s_y = $update["message"]["sticker"]["height"]; // Altezza dell'immagine Sticker
		$s_bytes = $update["message"]["sticker"]["file_size"]; // Peso dello Sticker espresso in byte
	} elseif (isset($update["message"]["contact"])) { // Contatto
		$messageType = "contact";
		$contact = $update['message']['contact']['phone_number']; // Numero del contatto
		$cnome = $update['message']['contact']['first_name']; // Nome del Contatto
		$ccognome = $update['message']['contact']['last_name']; // Cognome del Contatto
		$cuserID = $update['message']['contact']['user_id']; // ID dell'utente del contatto
	} elseif (isset($update["message"]["document"])) { // Documento
		$messageType = "document";
		$file = $update["message"]["document"];
		$file_id = $update["message"]["document"]["file_id"]; // ID del file inviato
		$file_uid = $update["message"]["document"]["file_unique_id"]; // ID unico del file inviato
	} elseif (isset($update["inline_query"])) { // Inline mode
		$messageType = "inline";
		$chatID = $userID = $update["inline_query"]["from"]["id"];
		$nome = $update["inline_query"]["from"]["first_name"];
		$cognome = $update["inline_query"]["from"]["last_name"];
		$username = $update["inline_query"]["from"]["username"];
		$lingua = $update["inline_query"]["from"]["language_code"];
		$exists_user = true;
		$inline = true;
	} elseif (!isset($messageType)) { // Messaggio testuale (per esclusione)
		$messageType = "text message";
	}

	# Comandi
	if (in_array($msg[0], $config['operatori_comandi']) and $messageType == "text message") {
		$messageType = "command";
		$cmd = substr($msg, 1, strlen($msg));
		$cmd = str_replace("@" . $config['username_bot'], '', $cmd); // Fix del Tag al Bot nei gruppi
	}

	# Fine gestione variabili
}

# Funzioni del Bot

# Segnalazione errori php
set_error_handler("errorHandler");
register_shutdown_function("shutdownHandler");
function errorHandler($error_level, $error_message, $error_file, $error_line, $error_context) {
	global $config;
	$error = $error_message . " \nSulla stringa: " . $error_line;
	switch ($error_level) {
		case E_ERROR:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_PARSE:
			if ($config['log_report']['FATAL']) botlog($error, "FATAL", $error_file);
			break;
		case E_USER_ERROR:
		case E_RECOVERABLE_ERROR:
			if ($config['log_report']['ERROR']) botlog($error, "ERROR", $error_file);
			break;
		case E_WARNING:
		case E_CORE_WARNING:
		case E_COMPILE_WARNING:
		case E_USER_WARNING:
			if ($config['log_report']['WARN']) botlog($error, "WARN", $error_file);
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
			if ($config['log_report']['INFO']) botlog($error, "INFO", $error_file);
			break;
		case E_STRICT:
			if ($config['log_report']['DEBUG']) botlog($error, "DEBUG", $error_file);
			break;
		default:
			if ($config['log_report']['WARN']) botlog($error, "WARN", $error_file);
	}
}
function shutdownHandler() {
	global $config;
	$lasterror = error_get_last();
	switch ($lasterror['type']) {
		case E_ERROR:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_USER_ERROR:
		case E_RECOVERABLE_ERROR:
		case E_CORE_WARNING:
		case E_COMPILE_WARNING:
		case E_PARSE:
			if ($config['log_report']['SHUTDOWN']) {
				$error = $lasterror['message'] . " \nSulla stringa: " . $lasterror['line'];
				botlog($error, ["FATAL", "SHUTDOWN"], $lasterror['file']);
			}
	}
}

// Metodo di richieste (cURL e Json Payload)
$ch = curl_init();
function sendRequest($url = false, $args = false, $response = 'def', $metodo = 'def') {
	global $config;
	global $api;
	global $f;
	global $ch;
	
	if (!$url) {
		return false;
	}
	if ($response === 'def') {
		$response = $config['response'];
	}
	if ($metodo === 'def') {
		if (strtolower($config['method']) == 'post') {
			$post = true;
		} else {
			$post = false;
		}
	} elseif (strtolower($metodo) == 'post') {
		$post = true;
	} else {
		$post = false;
	}
	if (!defined('json_payload') and $config['json_payload'] and !$response and strpos($url, $config['telegram-bot-api']) !== false) {
		define('json_payload', false);
		ignore_user_abort(true);
		$method = explode('/', $url);
		$method = $method[count($method)-1];
		$args['method'] = $method;
		$json = json_encode($args);
		header('Content-Type: application/json');
		$error = error_get_last();
		if (strpos($error['message'], "Cannot modify header information") !== false) {
			unset($args['method']);
			sendRequest($url, $args, 0);
			return json_encode(['result' => "Json Payload failed", 'error' => $error, "contents" => $ob_contents]);
		} else {
			echo $json;
			if (function_exists("fastcgi_finish_request")) {
				fastcgi_finish_request();
			} else {
				ob_end_flush();
				ob_flush();
				flush();
			}
			if (json_decode($obcontents, true)) {
				ob_end_clean();
				ob_start();
				return $obcontents;
			}
		}
		ob_end_clean();
		ob_start();
		return json_encode(['result' => "Json Payload"]);
	} else {
		define('json_payload', false);
		if (!isset($ch)) $ch = curl_init();
		if (!$post) {
			if ($args) { $url .= "?" . http_build_query($args); }
			curl_setopt_array($ch, [
				CURLOPT_URL				=> $url,
				CURLOPT_POST			=> 0,
				CURLOPT_TIMEOUT			=> $config['request_timeout'],
				CURLOPT_RETURNTRANSFER	=> $response
			]);
		} else {
			curl_setopt_array($ch, [
				CURLOPT_URL				=> $url,
				CURLOPT_POST			=> 1,
				CURLOPT_POSTFIELDS		=> $args,
				CURLOPT_TIMEOUT			=> $config['request_timeout'],
				CURLOPT_RETURNTRANSFER	=> $response
			]);
		}
		ob_end_clean();
		ob_start();
		$output = curl_exec($ch);
		if (!$response) {
			$obcontents = ob_get_contents();
			ob_end_clean();
			ob_start();
			if (json_decode($obcontents, true)) {
				return $obcontents;
			}
		}
		return $output;
	}
}

// Avvisi Errori
function botlog($message = "Errore", $groups = false, $plugin = 'no', $chat = 'def') {
	global $f;
	global $botID;
	global $config;
	global $pluginp;
	global $api;
	if ($chat == 'def') {
		if ($chat = $config['console']) {
			$chat = $config['console'];
		} else {
			$chat = false;
		}
	}
	if (!$groups) {
		$groups = ["message"];
	} else {
		if (!is_array($groups)) $groups = [$groups];
	}
	if (!empty($config['not_log_report'])) {
		foreach ($groups as $group) {
			if (in_array($group, $config['not_log_report'])) {
				return false;
			}
		}
	}
	if (!$pluginp) {
	} elseif ($plugin == 'no') {
		$plugin = $pluginp;
	}
	if (count($groups) === 1) {
		$group = "[" . $groups[0] . "]";
	} else {
		foreach ($groups as $agroup) {
			$group .= "[$agroup]";
		}
	}
	if ($config['devmode']) {
		if (ini_get('display_errors')) echo "<br><b>[NeleBotFramework][" . time() . "]$group:</b> " . str_replace("\n", '', $message) . " in $plugin<br>";
	}
	if (file_exists($f['logs'] . "/NBF_$botID.log")) {
		if ($plugin) $inplugins = " on file $plugin";
		$nbftext = "[NeleBotFramework][" . time() . "]$group: " . htmlspecialchars_decode($message) . " $inplugin\n";
		$nbftext = str_replace('<b>', '', str_replace('<i>', '', str_replace('<code>', '', str_replace('</>', '', $nbftext))));
		$nbff = fopen($f['logs'] . "/NBF_$botID.log", "a+");
		fwrite($nbff, $nbftext);
		fclose($nbff);
	}
	if ($chat) {
		unset($group);
		if (count($groups) === 1) {
			$group = "#" . $groups[0];
		} else {
			foreach ($groups as $agroup) {
				$group .= "#$agroup ";
			}
		}
		$text = "$group\n" . bold("Messaggio:") . " $message \n" . bold("Plugin:") . " {$plugin} \n@" . $config['username_bot'];
		$args = [
			'chat_id' => $chat,
			'text' => $text,
			'parse_mode' => 'html',
		];
		define('json_payload', false);
		sendRequest("{$config['telegram-bot-api']}/$api/sendMessage", $args, false, 'get');
	}
}

//Query semplice per PDO
if ($config['usa_il_db'] !== false) {
	function db_query($query = false, $prepare = false, $fetch = true) {
		global $PDO;
		
		if (!$query) return false;
		if (!$PDO) {
			botlog("Query: $query \nDatabase non avviato.", ['framework', 'database', 'pdo']);
			return false;
		}
		$q = $PDO->prepare($query);
		if (!$q) {
			$err = $PDO->errorInfo();
			botlog("Query: " . code($query) . "\nPrepare: " . code(json_encode($prepare)) . "\nError: " . code(json_encode($err)), ['database', 'pdo']);
			return ['error' => $err];
		}
		if ($prepare !== false and is_array($prepare)) {
			$q->execute($prepare);
		} else {
			$q->execute();
		}
		$err = $q->errorInfo();
		if ($err[0] !== "00000") {
			botlog("Query: " . code($query) . "\nPrepare: " . code(json_encode($prepare)) . "\nError: " . code(json_encode($err)), ['database', 'pdo']);
			$rr = ['error' => $err];
		} else {
			if ($fetch === "no") {
				return true;
			} elseif ($fetch) {
				$rr = $q->fetch(\PDO::FETCH_ASSOC);
			} else {
				$rr = $q->fetchAll();
			}
		}
		return $rr;
	}
	function setStatus($id = false, $status = false, $bot = 'def') {
		global $config;
		global $botID;
		
		if (!$id or !$status) return -1;
		if ($bot === 'def') {
			$bot = $botID;
		}
		if (isset($id)) {
			$q = db_query("SELECT * FROM utenti WHERE user_id = ? or username = ?", [round($id), $id], true);
			if (!$q['user_id']) {
				$q = db_query("SELECT * FROM gruppi WHERE chat_id = ? or username = ?", [round($id), $id], true);
				if (!$q['chat_id']) {
					$q = db_query("SELECT * FROM canali WHERE chat_id = ? or username = ?", [round($id), $id], true);
					if (!$q['chat_id']) {
						return false;
					} else {
						$type = "canale";
						$id = $q['chat_id'];
					}
				} else {
					$type = "gruppo";
					$id = $q['chat_id'];
				}
			} else {
				$type = "utente";
				$id = $q['user_id'];
			}
		} else {
			return ["error" => "Chat not found"];
		}
		$q['status'] = json_decode($q['status'], true);
		// Auto-Fix per status non array
		if (!is_array($q['status'])) $q['status'] = [];
		if (in_array($status, ['ban', 'deleted', 'bot'])) {
			foreach($config['cloni'] as $idBot => $bot_username) {
				$q['status'][$idBot] = $status;
			}
		} elseif (strpos($status, "ban") === 0) {
			foreach($config['cloni'] as $idBot => $bot_username) {
				$q['status'][$idBot] = $status;
			}
		} else {
			$q['status'][$bot] = $status;
		}
		if ($id > 0) {
			$r = db_query("UPDATE utenti SET status = ? WHERE user_id = ?", [json_encode($q['status']), $id]);
		} else {
			$r = db_query("UPDATE gruppi SET status = ? WHERE chat_id = ?", [json_encode($q['status']), $id]);
			$r = db_query("UPDATE canali SET status = ? WHERE chat_id = ?", [json_encode($q['status']), $id]);
		}
		return $r;
	}
} else {
	function db_query($query = null, $prepare = null, $fetch = true) {
		botlog("Funzione db_query spenta per database disattivato.");
		return false;
	}
}

//Query con risposta in Json
function JsonResponse($link, $method = 'def', $args = false)  {
	$r = sendRequest($link, $args, 'def', $method);
	$rr = json_decode($r, true);
	return $rr;
}

# Funzioni Telegram | Method

// Azioni | sendChatAction
function scAction($chatID, $action = 'typing', $response = false) {
	global $api;
	global $config;
	
	$args = [
		'chat_id' => $chatID,
		'action' => $action
	];
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendChatAction", $args, $response);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendChatAction \n<b>INPUT</b>: " . code(json_encode($args)) . " \n<b>OUTPUT:</b> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Invio messaggi | sendMessage
function sm($chatID, $text = "ᅠ", $rmf = false, $pm = 'def', $reply = false, $dislink = 'def', $inline = true) {
	global $api;
	global $config;

	if ($pm === 'def') {
		$pm = $config['parse_mode'];
	}
	if ($dislink === 'def') {
		$dislink = $config['disabilita_anteprima_link'];
	}
	if ($config['azioni']) {
		scAction($chatID, 'typing');
	}
	$args = [
		'chat_id' => $chatID,
		'text' => $text,
		'disable_web_page_preview' => $dislink,
	];
	if (is_array($pm) and !empty($pm)) {
		$args['entities'] = json_encode($pm);
	} else {
		$args['parse_mode'] = $pm;
	}
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}
	if ($rmf == 'rispondimi') {
		$rm = [
			'force_reply' => true,
			'selective' => true
		];
	} elseif ($rmf == 'nascondi') {
		$rm = [
			'hide_keyboard' => true
		];
	} elseif (!$inline) {
		$rm = [
			'keyboard' => $rmf,
			'resize_keyboard' => true
		];
	} else {
		$rm = [
			'inline_keyboard' => $rmf
		];
	}
	if ($rmf) {
		$args['reply_markup'] = json_encode($rm);
	}
	if ($reply) {
		$args['reply_to_message_id'] = $reply;
		$args['allow_sending_without_reply'] = $config['send_without_reply'];
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendMessage", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendMessage \n<b>INPUT</b>: " . code(json_encode($args)) . " \n<b>OUTPUT:</b> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Rispondi CallBack | editMessageText & answerCallbackQuery
function cb_reply($id, $text = null, $alert = false, $cbmid = false, $ntext = false, $nmenu = false, $pm = 'def', $dislink = 'def', $is_caption = false) {
	global $api;
	global $chatID;
	global $config;

	if ($pm === 'def') {
		$pm = $config['parse_mode'];
	}
	if ($dislink === 'def') {
		$dislink = $config['disabilita_anteprima_link'];
	}
	
	if (!$text) {
		$text = " ";
		if ($cbmid) {
			if ($is_caption) {
				$c[1] = editMsgc($chatID, $ntext, $cbmid, $nmenu, $pm);
			} else {
				$c[1] = editMsg($chatID, $ntext, $cbmid, $nmenu, $pm, $dislink);
			}
		}
		$args = [
			'callback_query_id' => $id,
			'text' => $text,
			'show_alert' => $alert,
		];
		$c[0] = sendRequest("{$config['telegram-bot-api']}/$api/answerCallbackQuery", $args, false);
	} else {
		$args = [
			'callback_query_id' => $id,
			'text' => $text,
			'show_alert' => $alert,
		];
		$c[0] = sendRequest("{$config['telegram-bot-api']}/$api/answerCallbackQuery", $args, false);
		if ($cbmid) {
			if ($is_caption) {
				$c[1] = editMsgc($chatID, $ntext, $cbmid, $nmenu, $pm);
			} else {
				$c[1] = editMsg($chatID, $ntext, $cbmid, $nmenu, $pm, $dislink);
			}
		}
		return $c;
	}
}

function cb_url($cbid, $url) {
	global $api;
	global $config;

	$args = [
		'callback_query_id' => $cbid,
		'url' => $url
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/answerCallbackQuery", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("answerCallbackQuery\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

function media_cb_reply($id, $text = null, $alert = false, $cbmid = false, $file_id = false, $type = 'def', $caption = '', $nmenu = false, $pm = 'def') {
	global $api;
	global $chatID;
	global $config;
	
	if ($pm === 'def') {
		$pm = $config['parse_mode'];
	}
	$args = [
		'callback_query_id' => $id,
		'text' => $text,
		'show_alert' => $alert,
	];
	
	if (!$text) {
		if ($cbmid) {
			$c[1] = editMedia($chatID, $cbmid, $file_id, $type, $caption, $nmenu, $pm);
		}
		$c[0] = sendRequest("{$config['telegram-bot-api']}/$api/answerCallbackQuery", $args, false);
	} else {
		$c[0] = sendRequest("{$config['telegram-bot-api']}/$api/answerCallbackQuery", $args, false);
		if ($cbmid) {
			$c[1] = editMedia($chatID, $cbmid, $file_id, $type, $caption, $nmenu, $pm);
		}
	}
	return $c;
}

// Modifica solo il menu | editMessageReplyMarkup
function editMenu($chatID, $cbmid, $editKeyBoard = []) {
	global $api;
	global $config;
	
	$args = [];
	if (is_numeric($cbmid)) {
		$args['chat_id'] = $chatID;
		$args['message_id'] = $cbmid;
	} else {
		$args['inline_message_id'] = $cbmid;
	}
	$args['reply_markup'] = json_encode(['inline_keyboard' => $editKeyBoard]);
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/editMessageReplyMarkup", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		if ($ar['description'] != "Bad Request: message is not modified: specified new message content and reply markup are exactly the same as a current content and reply markup of the message") botlog("editMessageReplyMarkup \n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Modifica il testo di un messaggio | editMessageText
function editMsg($chatID, $msg, $cbmid, $editKeyBoard = false, $pm = 'def', $dislink = 'def') {
	global $api;
	global $config;

	if ($pm === 'def') {
		$pm = $config['parse_mode'];
	}
	if ($dislink === 'def') {
		$dislink = $config['disabilita_anteprima_link'];
	}
	$args = [
		'text' => $msg,
		'disable_web_page_preview' => $dislink
	];
	if (is_array($pm) and !empty($pm)) {
		$args['entities'] = json_encode($pm);
	} else {
		$args['parse_mode'] = $pm;
	}
	if (is_numeric($cbmid)) {
		$args['chat_id'] = $chatID;
		$args['message_id'] = $cbmid;
	} else {
		$args['inline_message_id'] = $cbmid;
	}
	if ($editKeyBoard) {
		$rm = [
			'inline_keyboard' => $editKeyBoard
		];
		$args["reply_markup"] = json_encode($rm);
	}

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/editMessageText", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		if ($ar['description'] != "Bad Request: message is not modified: specified new message content and reply markup are exactly the same as a current content and reply markup of the message") botlog("editMessageText \n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Modifica il testo di un file media | editMessageCaption
function editMsgc($chatID, $msg, $cbmid, $editKeyBoard = false, $pm = 'def') {
	global $api;
	global $config;

	if ($pm === 'def') {
		$pm = $config['parse_mode'];
	}
	$args = [
		'chat_id' => $chatID,
		'caption' => $msg,
		'message_id' => $cbmid,
		'disable_web_page_preview' => $dislink
	];
	if (is_array($pm) and !empty($pm)) {
		$args['caption_entities'] = json_encode($pm);
	} else {
		$args['parse_mode'] = $pm;
	}
	if ($editKeyBoard) {
		$rm = [
			'inline_keyboard' => $editKeyBoard
		];
		$args["reply_markup"] = json_encode($rm);
	}

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/editMessageCaption", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("editMessageCaption \n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Modifica un file media | editMessageMedia
function editMedia($chatID, $msgID, $file_id, $type = 'def', $caption = false, $editKeyBoard = false, $pm = 'def') {
	global $api;
	global $config;
	global $messageType;
	
	if ($type === 'def') {
		$type = $messageType;
	}
	if ($pm === 'def') {
		$pm = $config['parse_mode'];
	}	
	$media = [
		'type' => $type,
		'media' => $file_id
	];
	if (is_array($pm) and !empty($pm)) {
		$args['caption_entities'] = json_encode($pm);
	} else {
		$args['parse_mode'] = $pm;
	}
	if ($caption) {
		$media['caption'] = $caption;
	}
	$args = [
		'chat_id' => $chatID,
		'message_id' => $msgID,
		'media' => json_encode($media)
	];
	if ($editKeyBoard) {
		$rm = [
			'inline_keyboard' => $editKeyBoard
		];
		$args['reply_markup'] = json_encode($rm);
	}

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/editMessageMedia", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("editMessageMedia\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Copia un messaggio | copyMessage
function copyMsg($chatID, $fromID, $msgID, $caption = '', $pm = 'def') {
	global $api;
	global $config;

	if ($pm === 'def') {
		$pm = $config['parse_mode'];
	}
	if ($config['azioni']) {
		scAction($chatID);
	}

	$args = [
		'chat_id' => $chatID,
		'from_chat_id' => $fromID,
		'message_id' => $msgID
	];
	if ($caption) $args['caption'] = $caption;
	if (is_array($pm) and !empty($pm)) {
		$args['entities'] = json_encode($pm);
		$args['caption_entities'] = json_encode($pm);
	} else {
		$args['parse_mode'] = $pm;
	}
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/copyMessage", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("copyMessage\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Elimina un messaggio | deleteMessage
function dm($chatID, $msgID) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID,
		'message_id' => $msgID
	];
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/deleteMessage", $args, 'get', true);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("deleteMessage\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $rr;
}

// Inoltra un messaggio | forwardMessage
function fw($chatID, $fromID, $msgID) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID,
		'from_chat_id' => $fromID,
		'message_id' => $msgID
	];
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/forwardMessage", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("forwardMessage\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Invia una foto | sendPhoto
function sp($chatID, $photo, $caption = '', $rmf = false, $pm = 'def', $reply = false, $inline = true) {
	global $api;
	global $config;

	if ($pm === 'def') {
		$pm = $config['parse_mode'];
	}
	if ($config['azioni']) {
		scAction($chatID, 'upload_photo');
	}
	$args = [
		'chat_id' => $chatID,
		'photo' => $photo,
		'caption' => $caption
	];	
	if (is_array($pm) and !empty($pm)) {
		$args['caption_entities'] = json_encode($pm);
	} else {
		$args['parse_mode'] = $pm;
	}
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}
	if ($rmf == 'rispondimi') {
		$rm = [
			'force_reply' => true,
			'selective' => true
		];
	} elseif ($rmf == 'nascondi') {
		$rm = [
			'hide_keyboard' => true
		];
	} elseif (!$inline) {
		$rm = [
			'keyboard' => $rmf,
			'resize_keyboard' => true
		];
	} else {
		$rm = [
			'inline_keyboard' => $rmf
		];
	}
	if ($rmf) {
		$args['reply_markup'] = json_encode($rm);
	}
	if ($reply) {
		$args['reply_to_message_id'] = $reply;
		$args['allow_sending_without_reply'] = $config['send_without_reply'];
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendPhoto", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendPhoto\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Invia un file audio | sendAudio
function sa($chatID, $audio, $caption = null, $rmf = false, $pm = 'def', $reply = false, $inline = true) {
	global $api;
	global $config;

	if ($pm === 'def') {
		$pm = $config['parse_mode'];
	}
	if ($config['azioni']) {
		scAction($chatID, 'upload_audio');
	}
	$args = [
		'chat_id' => $chatID,
		'audio' => $audio,
		'caption' => $caption
	];	
	if (is_array($pm) and !empty($pm)) {
		$args['caption_entities'] = json_encode($pm);
	} else {
		$args['parse_mode'] = $pm;
	}
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}
	if ($rmf == 'rispondimi') {
		$rm = [
			'force_reply' => true,
			'selective' => true
		];
	} elseif ($rmf == 'nascondi') {
		$rm = [
			'hide_keyboard' => true
		];
	} elseif (!$inline) {
		$rm = [
			'keyboard' => $rmf,
			'resize_keyboard' => true
		];
	} else {
		$rm = [
			'inline_keyboard' => $rmf
		];
	}
	if ($rmf) {
		$args['reply_markup'] = json_encode($rm);
	}
	if ($reply) {
		$args['reply_to_message_id'] = $reply;
		$args['allow_sending_without_reply'] = $config['send_without_reply'];
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendAudio", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendAudio\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Invia un audio vocale | sendVoice
function sav($chatID, $audio, $caption = null, $rmf = false, $pm = 'def', $reply = false, $inline = true) {
	global $api;
	global $config;

	if ($pm === 'def') {
		$pm = $config['parse_mode'];
	}
	if ($config['azioni']) {
		scAction($chatID, 'record_audio');
	}
	$args = [
		'chat_id' => $chatID,
		'voice' => $audio,
		'caption' => $caption
	];	
	if (is_array($pm) and !empty($pm)) {
		$args['caption_entities'] = json_encode($pm);
	} else {
		$args['parse_mode'] = $pm;
	}
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}
	if ($rmf == 'rispondimi') {
		$rm = [
			'force_reply' => true,
			'selective' => true
		];
	} elseif ($rmf == 'nascondi') {
		$rm = [
			'hide_keyboard' => true
		];
	} elseif (!$inline) {
		$rm = [
			'keyboard' => $rmf,
			'resize_keyboard' => true
		];
	} else {
		$rm = [
			'inline_keyboard' => $rmf
		];
	}
	if ($rmf) {
		$args['reply_markup'] = json_encode($rm);
	}
	if ($reply) {
		$args['reply_to_message_id'] = $reply;
		$args['allow_sending_without_reply'] = $config['send_without_reply'];
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendVoice", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendVoice\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Invia un video | sendVideo
function sv($chatID, $video, $caption = null, $rmf = false, $pm = 'def', $reply = false, $inline = true) {
	global $api;
	global $config;

	if ($pm === 'def') {
		$pm = $config['parse_mode'];
	}
	if ($config['azioni']) {
		scAction($chatID, 'upload_video');
	}
	$args = [
		'chat_id' => $chatID,
		'video' => $video,
		'caption' => $caption
	];
	if (is_array($pm) and !empty($pm)) {
		$args['caption_entities'] = json_encode($pm);
	} else {
		$args['parse_mode'] = $pm;
	}
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}
	if ($rmf == 'rispondimi') {
		$rm = [
			'force_reply' => true,
			'selective' => true
		];
	} elseif ($rmf == 'nascondi') {
		$rm = [
			'hide_keyboard' => true
		];
	} elseif (!$inline) {
		$rm = [
			'keyboard' => $rmf,
			'resize_keyboard' => true
		];
	} else {
		$rm = [
			'inline_keyboard' => $rmf
		];
	}
	if ($rmf) {
		$args['reply_markup'] = json_encode($rm);
	}
	if ($reply) {
		$args['reply_to_message_id'] = $reply;
		$args['allow_sending_without_reply'] = $config['send_without_reply'];
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendVideo", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendVideo\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Invia un video rotondo | sendVideoNote
function svr($chatID, $video_note, $rmf = false, $reply = false, $inline = true) {
	global $api;
	global $config;

	if ($config['azioni']) {
		scAction($chatID, 'upload_video_note');
	}
	$args = [
		'chat_id' => $chatID,
		'video_note' => $video_note,
	];
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}
	if ($rmf == 'rispondimi') {
		$rm = [
			'force_reply' => true,
			'selective' => true
		];
	} elseif ($rmf == 'nascondi') {
		$rm = [
			'hide_keyboard' => true
		];
	} elseif (!$inline) {
		$rm = [
			'keyboard' => $rmf,
			'resize_keyboard' => true
		];
	} else {
		$rm = [
			'inline_keyboard' => $rmf
		];
	}
	if ($rmf) {
		$args['reply_markup'] = json_encode($rm);
	}
	if ($reply) {
		$args['reply_to_message_id'] = $reply;
		$args['allow_sending_without_reply'] = $config['send_without_reply'];
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendVideoNote", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendVideoNote\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Invia un contatto | sendContact
function sc($ID, $numero, $firstname = "Sconosciuto", $lastname = " ") {
	global $api;
	global $config;

	$args = [
		'chat_id' => $ID,
		'phone_number' => $numero,
		'first_name' => $firstname,
		'last_name' => $lastname
	];
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendContact", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendContact\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Invia un file | sendDocument
function sd($chatID, $documento, $caption = false, $rmf = false, $pm = 'def', $reply = false, $inline = true, $thumb = false) {
	global $api;
	global $config;

	if ($pm === 'def') {
		$pm = $config['parse_mode'];
	}

	if ($config['azioni']) {
		scAction($chatID, 'upload_document');
	}

	// File da locale
	if (strpos($documento, "http") === 0) {} else {
		if (file_exists($documento)) {
			$e = explode(".", $documento);
			$ex = $e[count($e) - 1];
			$documento = curl_file_create($documento, "application/$ex");
		}
	}
	
	if (file_exists($thumb)) {
		$e = explode(".", $thumb);
		$ex = $e[count($e) - 1];
		$thumb = curl_file_create($thumb, "application/$ex");
	}
	
	$args = [
		'chat_id' => $chatID,
		'document' => $documento,
		'thumb' => $thumb
	];
	
	if ($caption) {
		$args['caption'] = $caption;
		if (is_array($pm) and !empty($pm)) {
			$args['caption_entities'] = json_encode($pm);
		} else {
			$args['parse_mode'] = $pm;
		}
	}
	
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}

	if ($rmf == 'rispondimi') {
		$rm = [
			'force_reply' => true,
			'selective' => true
		];
	} elseif ($rmf == 'nascondi') {
		$rm = [
			'hide_keyboard' => true
		];
	} elseif (!$inline) {
		$rm = [
			'keyboard' => $rmf,
			'resize_keyboard' => true
		];
	} else {
		$rm = [
			'inline_keyboard' => $rmf
		];
	}
	$rm = json_encode($rm);
	if ($rmf) {
		$args['reply_markup'] = $rm;
	}
	if ($reply) {
		$args['reply_to_message_id'] = $reply;
		$args['allow_sending_without_reply'] = $config['send_without_reply'];
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendDocument", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendDocument\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Invia una GIF | sendAnimation
function sgif($chatID, $file, $caption = null, $rmf = false, $pm = 'def', $reply = false, $inline = true) {
	global $api;
	global $config;

	if ($pm === 'def') {
		$pm = $config['parse_mode'];
	}
	if ($config['azioni']) {
		scAction($chatID, 'upload_video');
	}
	$args = [
		'chat_id' => $chatID,
		'animation' => $file,
		'caption' => $caption
	];
	if (is_array($pm) and !empty($pm)) {
		$args['caption_entities'] = json_encode($pm);
	} else {
		$args['parse_mode'] = $pm;
	}
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}
	if ($rmf == 'rispondimi') {
		$rm = [
			'force_reply' => true,
			'selective' => true
		];
	} elseif ($rmf == 'nascondi') {
		$rm = [
			'hide_keyboard' => true
		];
	} elseif (!$inline) {
		$rm = [
			'keyboard' => $rmf,
			'resize_keyboard' => true
		];
	} else {
		$rm = [
			'inline_keyboard' => $rmf
		];
	}
	if ($rmf) {
		$args['reply_markup'] = json_encode($rm);
	}
	if ($reply) {
		$args['reply_to_message_id'] = $reply;
		$args['allow_sending_without_reply'] = $config['send_without_reply'];
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendAnimation", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendAnimation\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Invia uno sticker | sendSticker
function ss($chatID, $sticker, $rmf = false, $reply = false, $inline = true) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID,
		'sticker' => $sticker
	];
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}
	if ($rmf == 'rispondimi') {
		$rm = [
			'force_reply' => true,
			'selective' => true
		];
	} elseif ($rmf == 'nascondi') {
		$rm = [
			'hide_keyboard' => true
		];
	} elseif (!$inline) {
		$rm = [
			'keyboard' => $rmf,
			'resize_keyboard' => true
		];
	} else {
		$rm = [
			'inline_keyboard' => $rmf
		];
	}
	if ($rmf) {
		$args['reply_markup'] = json_encode($rm);
	}
	if ($reply) {
		$args['reply_to_message_id'] = $reply;
		$args['allow_sending_without_reply'] = $config['send_without_reply'];
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendSticker", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendSticker\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Invia un dado | sendDice
function sdice($chatID, $emoji = "", $rmf = false, $reply = false, $inline = true) {
	global $api;
	global $config;

	$args = [
		'chat_id'	=> $chatID,
		'emoji'		=> $emoji
	];
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}
	if ($rmf == 'rispondimi') {
		$rm = [
			'force_reply' => true,
			'selective' => true
		];
	} elseif ($rmf == 'nascondi') {
		$rm = [
			'hide_keyboard' => true
		];
	} elseif (!$inline) {
		$rm = [
			'keyboard' => $rmf,
			'resize_keyboard' => true
		];
	} else {
		$rm = [
			'inline_keyboard' => $rmf
		];
	}
	if ($rmf) {
		$args['reply_markup'] = json_encode($rm);
	}
	if ($reply) {
		$args['reply_to_message_id'] = $reply;
		$args['allow_sending_without_reply'] = $config['send_without_reply'];
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendDice", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendDice\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Invia delle immagini/video in gruppo | sendMediaGroup
function smg($chatID, $documenti = [], $caption = 'def', $rmf = false, $pm = 'def', $reply = null, $inline = true) {
	global $api;
	global $config;

	if ($pm === 'def') {
		$pm = $config['parse_mode'];
	}
	if ($config['azioni']) {
		scAction($chatID, 'upload_document');
	}
	if ($caption === 'def') {
	} else {
		$range = range(0, count($documenti) - 1);
		foreach ($range as $num) {
			unset($documenti[$num]['caption']);
			unset($documenti[$num]['parse_mode']);
		}
		$documenti[0]['caption'] = $caption;
		if (is_array($pm) and !empty($pm)) {
			$documenti[0]['caption_entities'] = json_encode($pm);
		} else {
			$documenti[0]['parse_mode'] = $pm;
		}
		$documenti[0]['parse_mode'] = $pm;
	}
	$args = [
		'chat_id' => $chatID,
		'media' => json_encode($documenti)
	];
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}
	if ($rmf == 'rispondimi') {
		$rm = [
			'force_reply' => true,
			'selective' => true
		];
	} elseif ($rmf == 'nascondi') {
		$rm = [
			'hide_keyboard' => true
		];
	} elseif (!$inline) {
		$rm = [
			'keyboard' => $rmf,
			'resize_keyboard' => true
		];
	} else {
		$rm = [
			'inline_keyboard' => $rmf
		];
	}
	if ($rmf) {
		$args['reply_markup'] = json_encode($rm);
	}
	if ($reply) {
		$args['reply_to_message_id'] = $reply;
		$args['allow_sending_without_reply'] = $config['send_without_reply'];
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendMediaGroup", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendMediaGroup\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Invia una posizione | sendLocation
function sendLocation($chatID, $lati, $long, $rmf = false, $time = null, $reply = false, $inline = true) {
	global $api;
	global $config;

	if ($config['azioni']) {
		scAction($chatID, 'find_location');
	}
	$args = [
		'chat_id' => $chatID,
		'latitude' => $lati,
		'longitude' => $long,
	];
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}
	if ($rmf == 'rispondimi') {
		$rm = [
			'force_reply' => true,
			'selective' => true
		];
	} elseif ($rmf == 'nascondi') {
		$rm = [
			'hide_keyboard' => true
		];
	} elseif (!$inline) {
		$rm = [
			'keyboard' => $rmf,
			'resize_keyboard' => true
		];
	} else {
		$rm = [
			'inline_keyboard' => $rmf
		];
	}
	if ($rmf) {
		$args['reply_markup'] = json_encode($rm);
	}
	if ($reply) {
		$args['reply_to_message_id'] = $reply;
		$args['allow_sending_without_reply'] = $config['send_without_reply'];
	}
	if ($time) {
		$args['live_period'] = $time;
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendLocation", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendLocation \n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Modifica una posizione in live | editMessageLiveLocation
function editLocation($chatID, $lati, $long, $msgID, $rmf = false) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID,
		'message_id' => $msgID,
		'latitude' => $lati,
		'longitude' => $long,
	];
	if ($rmf) {
		$rm = [
			'inline_keyboard' => $rmf
		];
		$args['reply_markup'] = json_encode($rm);
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/editMessageLiveLocation", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("editMessageLiveLocation \n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Termina una posizione in live | stopMessageLiveLocation
function stopLocation($chatID, $msgID, $rmf = false) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID,
		'message_id' => $msgID,
	];
	if ($rmf) {
		$rm = [
			'inline_keyboard' => $rmf
		];
		$args['reply_markup'] = json_encode($rm);
	}

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/stopMessageLiveLocation", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("stopMessageLiveLocation \n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Invia la posizione di un posto | sendVenue
function sven($chatID, $lati, $long, $title, $ind, $rmf = false, $reply = false, $inline = true) {
	global $api;
	global $config;

	if ($config['azioni']) {
		scAction($chatID, 'find_location');
	}
	$args = [
		'chat_id' => $chatID,
		'latitude' => $lati,
		'longitude' => $long,
		'title' => $title,
		'addres' => $ind,
	];	
	if ($config['disabilita_notifica']) {
		$args['disable_notification'] = true;
	}
	if ($rmf == 'rispondimi') {
		$rm = [
			'force_reply' => true,
			'selective' => true
		];
	} elseif ($rmf == 'nascondi') {
		$rm = [
			'hide_keyboard' => true
		];
	} elseif (!$inline) {
		$rm = [
			'keyboard' => $rmf,
			'resize_keyboard' => true
		];
	} else {
		$rm = [
			'inline_keyboard' => $rmf
		];
	}
	if ($rmf) {
		$args['reply_markup'] = json_encode($rm);
	}
	if ($reply) {
		$args['reply_to_message_id'] = $reply;
		$args['allow_sending_without_reply'] = $config['send_without_reply'];
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/sendVenue", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendVenue \n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Informazioni di un gruppo/canale | getChat
function getChat($chatID) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/getChat", $args, true);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("getChat \n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Lista Admins di un gruppo | getChatAdministrators
function getAdmins($chatID) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID
	];
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/getChatAdministrators", $args, true);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("getChatAdministrators \n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Banna un utente | kickChatMember
function ban($chatID, $userID) {
	global $api;
	global $config;
	
	$args = [
		'chat_id' => $chatID,
		'user_id' => $userID
	];
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/kickChatMember", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("kickChatMember\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Unbanna un utente | unbanChatMember
function unban($chatID, $userID) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID,
		'user_id' => $userID
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/unbanChatMember", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("unbanChatMember\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Limita utente (per i gruppi) | restrictChatMember
function limita($chatID, $userID, $durata = null) {
	global $api;
	global $config;

	if ($durata === 'def') {
		$duratas = time();
	} else {
		$duratas = time() + $durata;
	}
	$args = [
		'chat_id' => $chatID,
		'user_id' => $userID,
		'until_date' => $duratas,
		'can_send_messages' => false,
		'can_send_media_messages' => false,
		'can_send_other_messages' => false,
		'can_add_web_page_previews' => false,
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/restrictChatMember", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("restrictChatMember\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Rendi admin un utente (per i gruppi) | promoteChatMember
function promote($chatID, $userID, $perms = []) {
	global $api;
	global $config;
	
	$args = [
		'chat_id' => $chatID,
		'user_id' => $userID,
	];
	
	$args = array_merge($args, $perms);
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/promoteChatMember", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("promoteChatMember\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Modifica i permessi globali dei gruppi (per i gruppi) | setChatAdministratorCustomTitle
function setAdminTag($chatID, $userID, $tag = "") {
	global $api;
	global $config;
	
	$args = [
		'chat_id' => $chatID,
		'user_id' => $userID,
		'custom_title' => $tag
	];
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/setChatAdministratorCustomTitle", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("setChatAdministratorCustomTitle\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Modifica i permessi globali dei gruppi (per i gruppi) | setChatPermissions
function setchatperms($chatID, $perms = []) {
	global $api;
	global $config;
	
	if (!$perms) {
		$perms = [
			'can_send_messages' => true,
			'can_send_media_messages' => true,
			'can_send_polls' => true,
			'can_send_other_messages' => true,
			'can_add_web_page_previews' => true,
			'can_change_info' => false,
			'can_invite_users' => false,
			'can_pin_messages' => false
		];
	}

	$args = [
		'chat_id' => $chatID,
		'permissions' => json_encode($perms)
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/setChatPermissions", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("setChatPermissions\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Abbandona la chat | leaveChat
function lc($chatID) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/leaveChat", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("leaveChat\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Cambia il nome di una Chat (gruppo/canale) | setChatTitle
function setTitle($chatID, $title) {
	global $api;
	global $config;

	$args = [
		'title' => $title,
		'chat_id' => $chatID
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/setChatTitle", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("setChatTitle\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Cambia la descrizione di una Chat (gruppo/canale) | setChatDescription
function setDescription($chatID, $desc) {
	global $api;
	global $config;

	$args = [
		'description' => $desc,
		'chat_id' => $chatID
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/setChatDescription", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("setChatDescription\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Setta la foto di una chat (gruppo/canale) | setChatPhoto
function setp($chatID, $photo) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID,
		'photo' => $photo
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/setChatPhoto", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("setChatPhoto \n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Elimina la foto di una chat (gruppo/canale) | deleteChatPhoto
function unsetp($chatID) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/deleteChatPhoto", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("deleteChatPhoto \n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Setta il set Sticker di un gruppo | setChatStickerSet
function setStickers($chatID, $set) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID,
		'sticker_set_name' => $set
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/setChatStickerSet", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("setChatStickerSet \n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Rimuovi il set Sticker di un gruppo | deleteChatStickerSet
function unsetStickers($chatID) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/deleteChatStickerSet", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("deleteChatStickerSet \n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Fissa un messaggio (gruppo/canale) | pinChatMessage
function pin($chatID, $rmsgID, $notify = 'def') {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID,
		'message_id' => $rmsgID
	];
	if ($notify === 'def') {
		$args['disable_notification'] = $config['disabilita_notifica'];
	} else {
		$args['disable_notification'] = $notify;
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/pinChatMessage", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("pinChatMessage\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Togli il messaggio fissato | unpinChatMessage
function unpin($chatID, $msgID = false, $notify = 'def') {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID
	];
	if ($msgID) $args['message_id'] = $msgID;
	if ($notify === 'def') {
		$args['disable_notification'] = $config['disabilita_notifica'];
	} else {
		$args['disable_notification'] = $notify;
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/unpinChatMessage", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("unpinChatMessage\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Togli tutti i messaggi fissati | unpinAllChatMessages
function unpinAll($chatID, $notify = 'def') {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID
	];
	if ($notify === 'def') {
		$args['disable_notification'] = $config['disabilita_notifica'];
	} else {
		$args['disable_notification'] = $notify;
	}
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/unpinAllChatMessages", $args);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("unpinAllChatMessages\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Scarica un file da Telegram tramite fileID | getFile
function getFile($fileID) {
	global $api;
	global $config;

	$args = [
		'file_id' => $fileID
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/getFile", $args, true);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("sendPhoto\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
		return null;
	}
	return "{$config['telegram-bot-api']}/file/$api/" . $ar['result']['file_path'];
}

// Ottieni il numero di membri di un Gruppo/Canale | getChatMembersCount
function conta($chatID) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/getChatMembersCount", $args, true);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("getChatMembersCount \n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar["result"];
}

// Esporta il link di un Gruppo | exportChatInviteLink
function getLink($chatID = false) {
	global $api;
	global $config;
	if (!$chatID) return false;
	
	$args = [
		'chat_id' => $chatID
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/exportChatInviteLink", $args, true);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("exportChatInviteLink\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
		return $ar['description'];
	} else {
		return $ar['result'];
	}
}

// Visualizza lo stato di un utente in un gruppo| getChatMember
function getChatMember($chatID, $userID) {
	global $api;
	global $config;

	$args = [
		'chat_id' => $chatID,
		'user_id' => $userID
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/getChatMember", $args, true);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("getChatMember\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Visualizza le foto di un Utente | getUserProfilePhotos
function getPropic($userID) {
	global $api;
	global $config;

	$args = [
		'user_id' => $userID
	];

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/getUserProfilePhotos", $args, true);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("getUserProfilePhotos\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Informazioni del Bot | getMe
function getMe() {
	global $api;
	global $config;

	$rr = sendRequest("{$config['telegram-bot-api']}/$api/getMe", false, true);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("getMe \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar['result'];
}

// Informazioni del Webhook | getWebhookInfo
function getWhInfo($keys = false) {
	global $api;
	global $config;
	
	if (!$keys) $keys = $api;
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$keys/getWebhookInfo", false, true);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("getWebhookInfo\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Informazioni comandi a scomparsa | getMyCommands
function getBotCommands() {
	global $api;
	global $config;
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/getMyCommands", false, true);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("getMyCommands\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

// Setta comandi a scomparsa | setMyCommands
function setBotCommands($commands = []) {
	global $api;
	global $config;
	
	$args = [
		'commands' => json_encode($commands)
	];
	
	$rr = sendRequest("{$config['telegram-bot-api']}/$api/setMyCommands", $args, true);
	$ar = json_decode($rr, true);
	if (isset($ar["error_code"])) {
		botlog("setMyCommands\n<b>INPUT</>: " . code(json_encode($args)) . " \n<b>OUTPUT:</> " . $ar['description'], 'telegram_errors');
	}
	return $ar;
}

# Formattazioni del Bot
function textspecialchars($text, $format = 'def') {
	global $config;
	if ($format === 'def') {
		$format = $config['parse_mode'];
	}
	if (strtolower($format) == 'html') {
		return htmlspecialchars($text);
	} elseif (strtolower($format) == 'markdown') {
		return mdspecialchars($text);
	} elseif (strtolower($format) == 'markdownv2') {
		return md2specialchars($text);
	} else {
		botlog("Unknown formatting for textspecialchars: $format", 'framework');
	}
	return $text;
}

function mdspecialchars($text) {
	# Caratteri come "*", "_" e "`" visibili in markdown
	$text = str_replace("_", "\_", $text);
	$text = str_replace("*", "\*", $text);
	$text = str_replace("`", "\`", $text);
	return str_replace("[", "\[", $text);
}

function md2specialchars($text) {
	# Caratteri come "*", "_" e "`" visibili in markdownV2
	$text = str_replace("_", "\_", $text);
	$text = str_replace("*", "\*", $text);
	$text = str_replace("`", "\`", $text);
	$text =  str_replace("[", "\[", $text);
	$text =  str_replace("]", "\]", $text);
	$text =  str_replace("(", "\(", $text);
	$text =  str_replace(")", "\)", $text);
	$text =  str_replace("~", "\~", $text);
	$text =  str_replace("!", "\!", $text);
	$text =  str_replace("-", "\-", $text);
	$text =  str_replace(".", "\.", $text);
	return str_replace("=", "\=", $text);
}

function code($text) {
	global $config;
	if (strtolower($config['parse_mode']) == 'html') {
		return "<code>" . htmlspecialchars($text) . "</>";
	} elseif (strtolower($config['parse_mode']) == 'markdown') {
		return "`" . mdspecialchars($text) . "`";
	} elseif (strtolower($config['parse_mode']) == 'markdownv2') {
		return "`" . md2specialchars($text) . "`";
	} else {
		return $text;
	}
}

function bold($text) {
	global $config;
	if (strtolower($config['parse_mode']) == 'html') {
		return "<b>" . htmlspecialchars($text) . "</>";
	} elseif (strtolower($config['parse_mode']) == 'markdown') {
		return "*" . mdspecialchars($text) . "*";
	} elseif (strtolower($config['parse_mode']) == 'markdownv2') {
		return "*" . md2specialchars($text) . "*";
	} else {
		return $text;
	}
}

function italic($text) {
	global $config;
	if (strtolower($config['parse_mode']) == 'html') {
		return "<i>" . htmlspecialchars($text) . "</>";
	} elseif (strtolower($config['parse_mode']) == 'markdown') {
		return "_" . mdspecialchars($text) . "_";
	} elseif (strtolower($config['parse_mode']) == 'markdownv2') {
		return "_" . md2specialchars($text) . "_";
	} else {
		return $text;
	}
}

function underl($text) {
	global $config;
	if (strtolower($config['parse_mode']) == 'html') {
		return "<u>" . htmlspecialchars($text) . "</>";
	} elseif (strtolower($config['parse_mode']) == 'markdown') {
		return mdspecialchars($text);
	} elseif (strtolower($config['parse_mode']) == 'markdownv2') {
		return "__" . md2specialchars($text) . "__";
	} else {
		return $text;
	}
}

function striket($text) {
	global $config;
	if (strtolower($config['parse_mode']) == 'html') {
		return "<s>" . htmlspecialchars($text) . "</>";
	} elseif (strtolower($config['parse_mode']) == 'markdown') {
		return mdspecialchars($text);
	} elseif (strtolower($config['parse_mode']) == 'markdownv2') {
		return "~" . md2specialchars($text) . "~";
	} else {
		return $text;
	}
}

function text_link($text, $link) {
	global $config;
	if (strtolower($config['parse_mode']) == 'html') {
		return "<a href='$link'>" . htmlspecialchars($text) . "</>";
	} elseif (strtolower($config['parse_mode']) == 'markdown') {
		return "[" . mdspecialchars($text) . "]($link)";
	} elseif (strtolower($config['parse_mode']) == 'markdownv2') {
		return "[" . md2specialchars($text) . "]($link)";
	} else {
		return $text;
	}
}

function tag($user = false, $name = false, $surname = false) {
	global $nome;
	global $cognome;
	global $userID;
	if (!$user) {
		$user = $userID;
		$name = $nome;
		$surname = $cognome;
	}
	if ($surname) {
		$name .= " $surname";
	}
	return text_link($name, "tg://user?id=$user");
}
