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

if ($config['usa_il_db']) {
    if (strpos($msg, "/ban ") === 0 and $typechat == "private") {
        $id = str_replace("@", '', str_replace("/ban ", '', $msg));
        if (isset($id)) {
            $q = $PDO->prepare("UPDATE utenti SET status = ? WHERE user_id = ? or username = ?");
            $q->execute(['ban', $id, $id]);
            sm($chatID, "Ho bannato dal bot " . $id);
        }
    }

    if (strpos($msg, "/unban ") === 0 and $typechat == "private") {
        $id = str_replace("@", '', str_replace("/unban ", '', $msg));
        if (isset($id)) {
            $q = $PDO->prepare("UPDATE utenti SET status = ? WHERE user_id = ? or username = ?");
            $q->execute([' ', $id, $id]);
            sm($chatID, "Ho sbannato dal bot " . $id);
        }
    }
}

$c = array(
    "true" => "✅",
    "false" => "❌"
);

if ($msg == "/plugins" and $isadmin) {
    if (file_exists($f['plugins'])) {
        $pls = json_decode(file_get_contents($f['plugins']), true);
        if (!is_array($pls)) {
            call_error("array non trovato su " . $f['plugins']);
            sm($chatID, "Formato Array sul file JSON errato.");
            exit;
        }
        foreach ($pls as $pl => $act) {
            if ($act) {
                $act = $c['true'];
            } else {
                $act = $c['false'];
            }
            $menupl[] = array(
                array(
                    "text" => "$pl $act",
                    "callback_data" => "/pladd $pl"
                ),
                array(
                    "text" => $c['false'],
                    "callback_data" => "/pld $pl"
                ),
            );
        }
        $menupl[] = array(
            array(
                "text" => "Fatto",
                "callback_data" => "fatto"
            ),
        );
        sm($chatID, bold("Plugins"), $menupl);
    } else {
        sm($chatID, bold("Plugins") . "\nNessun plugin nella lista.\nUsa: " . code("/addpl comandi.php"));
    }
    exit;
}

if (strpos($cbdata, "/pladd") === 0) {
    $plt = str_replace("/pladd ", "", $msg);
    $pls = json_decode(file_get_contents($f['plugins']), true);
    if (is_array($pls)) {
        if ($pls[$plt]) {
            $dis = "dis";
            $pls[$plt] = false;
        } else {
            $pls[$plt] = true;
        }
        file_put_contents($f['plugins'], json_encode($pls, JSON_PRETTY_PRINT));
        $tot = $plt . " $dis" . "attivato";
    } else {
        $tot = "Errore: " . $f['plugins'] . " non è un array. \nFile corrotto: aggiustalo o ricrealo.";
    }
    foreach ($pls as $pl => $act) {
        if ($act === true) {
            $act = $c['true'];
        } else {
            $act = $c['false'];
        }
        $menupl[] = array(
            array(
                "text" => "$pl $act",
                "callback_data" => "/pladd $pl"
            ),
            array(
                "text" => $c['false'],
                "callback_data" => "/pld $pl"
            ),
        );
    }
    $menupl[] = array(
        array(
            "text" => "Fatto",
            "callback_data" => "fatto"
        ),
    );
    cb_reply($cbid, $tot, true, $cbmid, bold("Plugins"), $menupl);
    exit;
}

if (strpos($msg, "/pld") === 0) {
    $pll = str_replace("/pld ", "", $msg);
    $pls = json_decode(file_get_contents($f['plugins']), true);
    if (is_array($pls)) {
        unset($pls[$pll]);
        file_put_contents($f['plugins'], json_encode($pls, JSON_PRETTY_PRINT));
        $tot = "$pll tolto dalla lista";
    } else {
        $tot = "Errore: " . $f['plugins'] . " non è un array. \nFile corrotto: aggiustalo o ricrealo.";
    }
    foreach ($pls as $pl => $act) {
        if ($act) {
            $act = $c['true'];
        } else {
            $act = $c['false'];
        }
        $menupl[] = array(
            array(
                "text" => "$pl $act",
                "callback_data" => "/pladd $pl"
            ),
            array(
                "text" => $c['false'],
                "callback_data" => "/pld $pl"
            ),
        );
    }
    $menupl[] = array(
        array(
            "text" => "Fatto",
            "callback_data" => "fatto"
        ),
    );
    cb_reply($cbid, $tot, true, $cbmid, bold("Plugins"), $menupl);
    exit;
}

if (strpos($msg, "/addpl ") === 0) {
    $pl = str_replace("/addpl ", '', $msg);
    if (file_exists($f['plugins'])) {
        $pls = json_decode(file_get_contents($f['plugins']), true);
    } else {
        $pls = [];
    }
    if (!is_array($pls)) {
        $tot = "Errore: " . $f['plugins'] . " non è un array. \nFile corrotto: aggiustalo o ricrealo.";
    } else {
        $tot = bold("Plugin aggiunto!");
        $pls[$pl] = true;
        file_put_contents($f['plugins'], json_encode($pls, JSON_PRETTY_PRINT));
    }
    sm($chatID, $tot);
    exit;
}

if ($cbdata == "fatto") {
    cb_reply($cbid, '👍', false, $cbmid, "Finito");
    die;
}