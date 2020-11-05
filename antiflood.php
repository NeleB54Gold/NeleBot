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


# Conigurazioni Anti-Flood Bot
$antiflood = array(
    'messaggi' => 3,
    // Numero di messaggi che un utente può mandare entro i secondi per poi essere punito
    'secondi' => 0.5,
    // Secondi disponibili per inviare il numero di messaggi per poi essere punito
    'punizione' => 30,
    // Punizioni disponibili: 'forever' è per sempre oppure inserisci il tempo di ban in secondi
    'ban-message' => "Bannato dall'Anti-Flood",
    //Messaggio di Ban
    'unban-message' => "Sbannato dall'Anti-Flood"
    //Messaggio di UnBan
);

#UnBan manuale dal Database Redis
if (strpos($cmd, "drop ") === 0 and $isadmin) {
    $id = str_replace('/drop ', '', $msg);
    $redis->delete($id);
    sm($chatID, "Sbannato [" . code($id) . "]");
}

# Anti-Flood solo per utenti senza permessi da Amministratore
if ($update and !$isadmin) {
    if ($typechat == "private" or $cbdata) {
        $time = time();
        if ($get = $redis->get($userID)) {
            $json = json_decode($get, true);
            $sec = date('s', time() - $json['tempo']);
            if (isset($json['ban'])) {
                if ($json['ban'] == 'forever') {
                    die;
                } else {
                    if ($time > $json['tempo'] + $json['ban']) {
                        sm($userID, $antiflood['unban-message']);
                        $redis->delete($userID);
                    }
                    die;
                }
            }
            if ($sec >= $antiflood['secondi']) {
                $json = array(
                    'tempo' => $time,
                    'messaggi' => 1,
                );
                $redis->set($userID, json_encode($json));
            } else {
                $json['messaggi'] = $json['messaggi'] + 1;
                $json['tempo'] = $time;
                if ($json['messaggi'] >= $antiflood['messaggi']) {
                    sm($userID, $antiflood['ban-message']);
                    $json['ban'] = $antiflood['punizione'];
                }
                $redis->set($userID, json_encode($json));
            }
        } else {
            $json = array(
                'tempo' => $time,
                'messaggi' => 1,
            );
            $redis->set($userID, json_encode($json));
        }
    }
}