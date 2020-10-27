<?php

/*
    NeleBotWebHookInstaller
    Copyright (C) 2018  NeleBot Framework

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
# Consulta la nostra guida prima di modificare questo file!
# Guida: https://telegra.ph/NeleBot--PHP-Framework-per-Bot-Telegram-07-20#config.php

$config = array(
    # Configurazione del bot
    'username_bot' => "NeleBotWebHookInstallerBot",
    // Metti qui l'username del Bot (senza @)
    'password' => false,
    // Password delle request | Lascia false per disabilitarla oppure scrivi una password
    'admins' => [244432022, 426538970],
    // ID degli Amministratori del Bot
    'devmode' => false,
    // Developer Mode | Attivala quando il Bot è in fase di testing
    'method' => 'post',
    // Scegli get o post come metodo delle request cURL | Uso Default sulle funzioni
    'response' => false,
    // Risposta alla request | Uso Default sulle funzioni
    'json_payload' => false,
    //Json Payload è valido solo per le request senza response
    'usa_il_db' => true,
    // Decidi se usare il Database | Consigliato l'uso del Database solo dopo i primi test
    'usa_redis' => false,
    // Decidi se usare Redis
    'logs' => "ultimo",
    // Logs delle Update su un file
    'console' => 24154625,
    // Inserisci l'ID della chat in cui vuoi mandare i log importanti e gli errori (Cambia quello già inserito) oppure metti false per non utilizzarlo.
    'whitelist_users' => false,
    // Blocca gli utenti sconosciuti | Admin auto-inclusi
    'exec' => false,
    // Verifica la sintassi dei plugins con la funzione exec

    # Impostazioni messaggi
    'post_canali' => false,
    // Decidi se il Bot funziona anche per i messaggi dai canali
    'modificato' => false,
    // Decidi se il Bot funziona anche per i messaggi modificati
    'azioni' => false,
    // Sta scrivendo... prima di inviare un messaggio | Uso Default sulle funzioni
    'parse_mode' => "HTML",
    // Scegli HTML o Markdown | Uso Default sulle funzioni
    'disabilita_anteprima_link' => true,
    // Disabilità l'anteprima dei link sui messaggi | Uso Default sulle funzioni
);

# Dati di accesso al database
$database = array(
    'type' => "mysql",
    // Inserisci 'mysql' o 'postgre' per scegliere il tipo di Database
    'nome_database' => "",
    // Nome del database
    'utente' => "",
    // Se usi un' altro utente, sostituisci root con il nome.
    'password' => "",
    // Password del utente di accesso
    'host' => "localhost"
    // IP/dominio del server mysql (lascia invariato se non sai di cosa si tratta)
);

//Dati di accesso a Redis
$config['redis'] = array(
    'database' => 4745,
    // Database da selezionare per l'uso di Redis. Lascia false per usare root.
    'host' => "localhost",
    // Host di connessione al Server Redis.
    'port' => 6379,
    // Porta di connessione al Server Redis.
    'password' => false,
    // Password di autenticazione al Server Redis. Lascia false per disattivarlo.
);