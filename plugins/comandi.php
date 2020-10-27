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

$menustart[0] = array(
    array(
        'text' => "➕ Nuovo Bot ➕",
        'callback_data' => '/newbot'
    ),
);
$menustart[1] = array(
    array(
        'text' => "ℹ️ Informazioni ℹ️",
        'callback_data' => 'stat'
    ),
);

if ($cbdata == 'stat') {
    $testo = "WebHook settati: " . file_get_contents('whs.txt');
    cb_reply($cbid, $testo, true);
}

if ($cmd == 'start' and $u['page'] == ' ') {
    sm($chatID, "<b>Nele WebHook Bot</b>
Setta il Webhook del tuo Bot", $menustart);
}

if ($cmd == 'help' and $typechat == "private") {
    $menu[0] = array(
        array(
            'text' => "Source Bot",
            'url' => 't.me/NelePHPFramework'
        ),
    );
    sm($chatID, "<b>Webhook Bot</b>
Questo Bot ti aiuterà a settare il Webhook al tuo Bot con Base NeleBot.
@" . $config['username_bot'], $menu);
}

if ($cbdata == '/newbot') {
    $menu[0] = array(
        array(
            'text' => "❌ Annulla ❌",
            'callback_data' => '/cancel'
        ),
    );
    db_query("update utenti set page = 'key' where user_id = $userID");
    cb_reply($cbid, '✅', false, $cbmid, "<b>Nuovo Bot</b>
Invia le chiavi API del Bot", $menu);
}

if ($msg == '/cancel') {
    if ($cbdata) {
        if ($u['page'] !== ' ') {
            cb_reply($cbid, 'Annullato', false, $cbmid, "<b>Webhook Bot</b> \nSetta il Webhook del tuo Bot",
                $menustart);
            db_query("update utenti set page = ' ' where user_id = $userID");
        } else {
            cb_reply($cbid, 'Questo comando è già stato annullato', true);
        }
    } else {
        if ($u['page'] !== ' ') {
            db_query("update utenti set page = ' ' where user_id = $userID");
            sm($chatID, "Comando cancellato. Usa /start per riavviare.");
        } else {
            sm($chatID, "Nessun comando in esecuzione. Usa /start per riavviare.");
        }
    }
    db_query("update utenti set status = '[]' where user_id = $userID");
    exit;
}

if ($msg and $typechat == 'private' and $u['page'] == 'key') {
    $e = explode(':', $msg);
    if (strpos($msg, ':') and strlen($msg) < 200 and is_numeric($e[0])) {
        if (strpos($msg, 'bot') === false) {
            $msg = 'bot' . $msg;
        }
        $r = getMe($msg);
        if ($r['ok']) {
            $r = $r['result'];
            $userbot = $r['username'];
            $idbot = $r['id'];
            db_query("update utenti set page = 'password' where user_id = $userID");
            $u['status']['key'] = $msg;
            $st = json_encode($u['status']);
            $query = "update utenti set status = '$st' where user_id = '$userID'";
            $q = $PDO->prepare($query);
            $q->execute();
            $err = $q->errorInfo();
            if ($err[0] !== "00000") {
                call_error("PDO Error\n<b>INPUT:</> <code>" . json_encode($q) . "</>\n<b>OUTPUT:</> <code>" . json_encode($err) . "</>");
            }
            sm($chatID,
                "Chiave API Valida! @$userbot [<code>$idbot</code>] \n<b>Adesso invia la password delle request</b>\n<i>Usa</i> /skip <i>se non hai impostato la password</i>");
        } else {
            sm($chatID, "Chiave API inesistente");
        }
    } else {
        sm($chatID, "Chiave API non valida, riprova! \nEsempio: 467252147:aihTR1WGKcnCglNEVrMYZJPBQ6k_uqtU3XI");
    }
}

if ($msg and $typechat == 'private' and $u['page'] == 'password') {
    if ($cmd == 'skip') {
        db_query("update utenti set page = 'webhookurl' where user_id = $userID");
        sm($chatID, "<b>Ok, password non settata.</b> \nOra inviami il link del Webhook");
    } else {
        db_query("update utenti set page = 'webhookurl' where user_id = $userID");
        $u['status']['password'] = $msg;
        $st = json_encode($u['status']);
        $query = "update utenti set status = '$st' where user_id = '$userID'";
        $q = $PDO->prepare($query);
        $q->execute();
        $err = $q->errorInfo();
        if ($err[0] !== "00000") {
            call_error("PDO Error\n<b>INPUT:</> <code>" . json_encode($q) . "</>\n<b>OUTPUT:</> <code>" . json_encode($err) . "</>");
        }
        sm($chatID, "<b>Ok, password inserita.</b> \nOra inviami il link del Webhook");
    }
}

if ($msg and $typechat == 'private' and $u['page'] == 'webhookurl') {
    $url = $msg . '?' . http_build_query($u['status']);
    $r = setWebhook($u['status']['key'], $url);
    if ($r['ok']) {
        sm($chatID, "<b>Webhook settato!</b>\n\n<code>" . json_encode($r) . '</code>');
        db_query("update utenti set page = ' ' where user_id = $userID");
        db_query("update utenti set status = '[]' where user_id = $userID");
        file_put_contents('whs.txt', file_get_contents('whs.txt') + 1);
    } else {
        sm($chatID,
            "<b>Impossibile impostare l'URL Webhook</b>\nRiprova o usa /cancel per annullare.\n" . json_encode($r) . "\nIn caso setta tu su <code>$url</>");
    }
}