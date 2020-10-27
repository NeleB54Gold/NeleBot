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

# Connessione a Redis
if ($config['usa_redis']) {
    // Configurazioni di Redis per l'accesso ai Database
    $redisc = $config['redis'];
    // Connessione a Redis
    try {
        $redis = new Redis();
        $redis->connect($redisc['host'], $redisc['port']);
    } catch (Exception $e) {
        call_error($e->getMessage(), $f['database']);
        die();
    }
    // Autenticazione
    if ($redisc['password'] !== false) {
        try {
            $redis->auth($redisc['password']);
        } catch (Exception $e) {
            call_error($e);
            die();
        }
    }
    // Selezione del database Redis
    if ($redisc['database'] !== false) {
        try {
            $redis->select($redisc['database']);
        } catch (Exception $e) {
            call_error($e);
            die();
        }
    }
    //Test funzionamento
    if ($cmd == "redis" and $isadmin) {
        $test = $redis->ping();
        if ($test == "+PONG") {
            $res = "Positivo";
        } else {
            $res = "Negativo";
        }
        sm($chatID, bold("Risultato del Test: ") . $res);
    }

    require($f['anti-flood']);
}

# Connessione al Database
if (isset($config['usa_il_db'])) {
    if (strtolower($database['type']) == 'mysql') {
        try {
            $PDO = new PDO("mysql:host=" . $database['host'] . ";dbname=" . $database['nome_database'],
                $database['utente'], $database['password']);
        } catch (PDOException $e) {
            call_error($e->getMessage(), $f['database']);
            die;
        }
        $query = "CREATE TABLE IF  NOT EXISTS utenti (
		user_id BIGINT(50)  NOT NULL ,
		nome VARCHAR(50)  NOT NULL ,
		cognome VARCHAR(50)  NOT NULL ,
		username VARCHAR(50)  NOT NULL ,
		lang VARCHAR(50)  NOT NULL ,
		page VARCHAR(50)  NOT NULL ,
		status VARCHAR(50)  NOT NULL);";
        $PDO->query($query);
        $err = $PDO->errorInfo();
        if ($err[0] !== "00000") {
            call_error("PDO Error\n<b>INPUT:</> <code>$query</>\n<b>OUTPUT:</> <code>" . json_encode($err) . "</>");
            die;
        }
    } elseif (strtolower($database['type']) == 'postgre') {
        try {
            $PDO = new PDO("pgsql:host=" . $database['host'] . ";dbname=" . $database['nome_database'],
                $database['utente'], $database['password']);
        } catch (PDOException $e) {
            call_error($e->getMessage(), $f['database']);
            die;
        }
        $query = "CREATE TABLE IF  NOT EXISTS utenti (
		user_id BIGINT NOT NULL ,
		nome VARCHAR NOT NULL ,
		cognome VARCHAR NOT NULL ,
		username VARCHAR NOT NULL ,
		lang VARCHAR NOT NULL ,
		page VARCHAR NOT NULL ,
		status VARCHAR NOT NULL);";
        $PDO->query($query);
        $err = $PDO->errorInfo();
        if ($err[0] !== "00000") {
            call_error("PDO Error\n<b>INPUT:</> <code>$query</>\n<b>OUTPUT:</> <code>" . json_encode($err) . "</>");
            die;
        }
    } else {
        call_error("Errore: tipo di database sconosciuto.", $f['database']);
        die;
    }

    $q = $PDO->prepare("SELECT * FROM utenti WHERE user_id = ?");
    $q->execute([$userID]);
    $err = $q->errorInfo();
    if ($err[0] !== "00000") {
        call_error("PDO Error\n<b>INPUT:</> <code>" . json_encode($q) . "</>\n<b>OUTPUT:</> <code>" . json_encode($err) . "</>");
    }

    if (isset($q) && $q) {
        $u = $q->fetch(\PDO::FETCH_ASSOC);
    } else {
        $u = false;
    }

    if (!$u and $exists_user) {
        if (!$cognome) {
            $cognome = "";
        }
        if (!$username) {
            $username = "";
        }
        if (!$lingua) {
            $lingua = "en-US";
        }
        $q = $PDO->prepare("INSERT INTO utenti (user_id, nome, cognome, username, lang, page, status) VALUES (?,?,?,?,?,?,?)");
        $q->execute([$userID, $nome, $cognome, $username, $lingua, '', '[]']);
        $err = $q->errorInfo();
        if ($err[0] !== "00000") {
            call_error("PDO Error\n<b>INPUT:</> <code>" . json_encode($q) . "</>\n<b>OUTPUT:</> <code>" . json_encode($err) . "</>");
        }
        $u = db_query("SELECT * FROM utenti WHERE user_id = $userID");
    }
    $u['status'] = json_decode($u['status'], true);

    if ($cmd == "database" and $isadmin) {
        sm($chatID, code(json_encode($u, JSON_PRETTY_PRINT)));
        die;
    }

}