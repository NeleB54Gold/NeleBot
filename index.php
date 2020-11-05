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

# Nomi dei file
//Aumenta la sicurezza del tuo Bot modificando i nomi ai file
$f = array(
    'config' => 'config.php',
    'logfile' => 'logs.json', //File JSON
    'functions' => 'functions.php',
    'anti-flood' => 'antiflood.php',
    'database' => 'database.php',
    'plugin_manager' => 'plugin_manager.php',
    'plugins.dir' => 'plugins', //Cartella dei plugins
    'plugins' => 'plugins.json', //File JSON
    '404_page' => 'index.html', //File HTML con una pagina con errore 404 Not Found
);

require $f['config'];

if (!isset($_GET['key'])) {
    if ($config['devmode']) {
        echo "<b>Bot Error:</b> la chiave delle BotAPI sul parametro 'key' non è stata trovata";
    } else {
        echo file_get_contents($f['404_page']);
    }
    die;
}

# Informazioni del Bot
$api = $_GET['key']; // Chiave di accesso alle BotAPI
$admins = $config['admins']; // Amministratori del Bot
$password = $_GET['password']; //Password delle request

# Autenticazione del Bot
if ($config['password']) {
    if ($password !== $config['password']) {
        if ($config['devmode']) {
            echo "<br><b>Bot Error:</b> Password delle request Errata<br>";
        } else {
            echo file_get_contents($f['404_page']);
        }
        die;
    }
} else {
    if (!$config['json_payload']) {
        echo "<br><b>Bot Warning:</b> Ti consiglio di impostare una password al tuo Bot per avere una maggiore sicurezza!<br>";
    }
}

# Telegram Json Update
$content = file_get_contents("php://input");
if (!$content) {
    $update = false;
    if ($config['devmode']) {
        echo "<br><b>Bot Warning</b>: Telegram non ha inviato nessun contenuto.<br>";
    } else {
        echo file_get_contents($f['404_page']);
        die;
    }
} else {
    $update = json_decode($content, true);
}

# Gestione Logs
if ($config['logs'] and $update !== false) {
    if ($config['logs'] !== 'ultimo') {
        if (file_exists($f['logfile'])) {
            $log = fopen($f['logfile'], 'a');
            fwrite($log, ', 
' . json_encode($update, JSON_PRETTY_PRINT));
            fclose($log);
        } else {
            file_put_contents($f['logfile'], json_encode($update, JSON_PRETTY_PRINT));
        }
    } else {
        file_put_contents($f['logfile'], json_encode($update, JSON_PRETTY_PRINT));
    }
}

# Funzioni
require $f['functions'];

# Gestione Permessi
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
        sm($chatID, "Bot in Whitelist");
        die;
    }
}

# Database (Opzionale)
if ($config['usa_il_db'] or $config['usa_redis']) {
    require $f['database'];
    # Ban dal Bot
    if ($u['status'] == "ban") {
        die;
    }
}

# Gestione Plugin
$plugins = json_decode(file_get_contents($f['plugins']), true);
foreach ($plugins as $pl => $on) {
    if ($on) {
        if (file_exists($f['plugins.dir'] . '/' . $pl)) {
            $pluginp = $pl;
            if ($config['exec']) {
                $check = exec("php -l " . $f['plugins.dir'] . '/' . $pl);
            } else {
                $check = 'No syntax errors detected in ' . $f['plugins.dir'] . '/' . $pl;
            }
            if ($check == 'No syntax errors detected in ' . $f['plugins.dir'] . '/' . $pl) {
                include($f['plugins.dir'] . '/' . $pl);
            } else {
                if ($config['devmode']) {
                    call_error(bold("Plugins errato") . "\n$pl: $check");
                } else {
                    call_error("Plugin disattivato\n<b>$pl</b>: $check");
                    $pls = json_decode(file_get_contents($f['plugins']), true);
                    $pls[$plugin] = false;
                    file_put_contents($f['plugins'], json_encode($pls, JSON_PRETTY_PRINT));
                }
            }
        } else {
            if ($config['devmode']) {
                call_error("Il plugin '" . code($pl) . "' non è stato trovato in " . code($f['plugins.dir']));
            } else {
                call_error("Il plugin '" . code($pl) . "' non è stato trovato in " . code($f['plugins.dir']) . "\nPlugin disattivato");
                $pls = json_decode(file_get_contents($f['plugins']), true);
                $pls[$plugin] = false;
                file_put_contents($f['plugins'], json_encode($pls, JSON_PRETTY_PRINT));
            }
        }
    }
}