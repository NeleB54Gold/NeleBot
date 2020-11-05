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

# Impostazioni e Configurazioni del Bot

$config = [
	# Configurazione del bot
	'cloni' => [
		123456789 => "UsernameBot"
	],
	// Metti qui l'ID che indica l'username di ogni Bot (senza @)
	'password' => false,
	// Password delle request | Lascia false per disabilitarla oppure scrivi una password
	'admins' => [
		244432022 // Nele
	],
	// ID degli Amministratori del Bot
	'devmode' => false,
	// Developer Mode | Attivala quando il Bot è in fase di testing
	'telegram-bot-api' => "https://api.telegram.org",
	// URL per le BotAPI (Se usi quella locale sostituiscilo con http://localhost:8081)
	'method' => 'post',
	// Scegli get o post come metodo delle request cURL | Uso Default sulle funzioni
	'response' => true,
	// Risposta alla request | Uso Default sulle funzioni
	'request_timeout' => 2,
	// Timeout dell richiesta
	'json_payload' => false,
	// Json Payload è valido solo per le request senza response
	'usa_il_db' => false,
	// Decidi se usare il Database | Consigliato l'uso del Database solo dopo i primi test
	'usa_redis' => false,
	// Decidi se usare Redis
	'logs' => false,
	// Memorizza le updates del tuo Bot (sconsigliato l'utilizzo)
	'log_report' => [
		'SHUTDOWN' => true,
		'FATAL' => true,
		'ERROR' => true,
		'WARN' => true,
		'INFO' => false,
		'DEBUG' => false
	],
	// Segnalazione errori
	'not_log_report' => [],
	// Array di Log da non reportare (Esempio: ['redis'])
	'console' => 244432022,
	// Inserisci l'ID della chat in cui vuoi mandare i log importanti e gli errori (Cambia quello già inserito) oppure metti false per non utilizzarlo.
	'whitelist_users' => false,
	// Blocca gli utenti sconosciuti | Admins auto-inclusi
	'whitelist_chats' => false,
	// Blocca le chat sconosciute (gruppi, supergruppi e canali)
	// false: Whitelist spenta
	// Array: Whitelist accesa
	
	# Impostazioni messaggi
	'operatori_comandi' => ['/', '!', '.', '>'], 
	// Utilizzo comandi del Bot (Es: /cmd !cmd .cmd)
	'post_canali' => true,
	// Decidi se i comandi funzionano anche per i messaggi dai canali
	'modificato' => true,
	// Decidi se i comandi funzionano anche per i messaggi modificati
	'azioni' => false,
	// Sta scrivendo... prima di inviare un messaggio | Uso Default sulle funzioni
	'parse_mode' => "HTML",
	// Scegli HTML o Markdown | Uso Default sulle funzioni
	'disabilita_notifica' => false,
	// Disabilita la notifica dei messaggi | Uso Default sulle funzioni
	'disabilita_anteprima_link' => true,
	// Disabilita l'anteprima dei link | Uso Default sulle funzioni
	'send_without_reply' => true,
	// Invia messaggi anche senza reply (se specificata)
	
	'version' => "2.6.5"
	// Versione di NeleBot
];
if (!isset($config['cloni'][$botID])) {
	if ($config['devmode']) {
		die("Bot non autorizzato.");
	} else {
		header("Content-Type: application/json");
		echo json_encode(['method' => 'deleteWebhook']);
		die;
	}
} else {
	$config['username_bot'] = $config['cloni'][$botID];
}

# Dati di accesso al database
$database = [
	'type' => "mysql",
	// Inserisci 'sqlite', 'mysql' o 'postgre' per scegliere il tipo di Database
	'nome_database' => "",
	// Nome del database (o nome del file per SQLite)
	'utente' => "",
	// Se usi un' altro utente, sostituisci root con il nome.
	'password' => "",
	// Password del utente di accesso
	'host' => "localhost"
	// IP/dominio del server mysql (lascia invariato se non sai di cosa si tratta)
];

# Dati di accesso a Redis
$config['redis'] = [
	'database' => 1,
	// Database da selezionare per l'uso di Redis. Lascia false per usare root.
	'host' => "localhost",
	// Host di connessione al Server Redis.
	'port' => 6379,
	// Porta di connessione al Server Redis.
	'password' => false
	// Password di autenticazione al Server Redis. Lascia false per disattivarlo.
];
