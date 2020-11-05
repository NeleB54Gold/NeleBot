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

# Sistema delle informazioni sull'invio di una request da Telegram
# Controlla le variabili già esistenti per utilizzare al meglio il Framework
if (isset($update)) {
    # Sistemazione update del Bot in base alla configurazione
    if (isset($update['edited_channel_post'])) // Post modificato su un canale
    {
        if ($config['post_canali']) {
            $modificato = true;
            $update['message'] = $update['edited_channel_post'];
        } else {
            exit;
        }
    }

    if (isset($update['channel_post'])) // Post inviato su un canale
    {
        if ($config['post_canali']) {
            $update['message'] = $update['channel_post'];
        } else {
            exit;
        }
    }

    if (isset($update['edited_message'])) // Messaggio modificato
    {
        if ($config['modificato']) {
            $update['message'] = $update['edited_message'];
            $modificato = true;
        } else {
            exit;
        }
    }


    # Imformazioni utente su un canale
    if ($update['message']['author_signature'] and $config['post_canali']) {
        $firma = $update['message']['author_signature']; // Firma del Post su un canale
    }

    # Imformazioni utente su un messaggio inoltrato dal canale
    if ($update['message']['forward_signature'] and $config['post_canali']) {
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
    } else {
        $exists_user = false;
    }

    # Informazioni utente inoltrato
    if (isset($update['message']['forward_from'])) {
        $exists_fuser = true;
        $fuserID = $update['message']['forward_from']['id'];
        $fnome = $update['message']['forward_from']['first_name'];
        $fcognome = $update['message']['forward_from']['last_name'];
        $fusername = $update['message']['forward_from']['username'];
        $flingua = $update['message']['forward_from']['language_code'];
    } else {
        $exists_fuser = false;
    }

    # Informazioni utente sulla reply
    if (isset($update['message']['reply_to_message']['from'])) {
        $exists_ruser = true;
        $ruserID = $update['message']['reply_to_message']['from']['id'];
        $rnome = $update['message']['reply_to_message']['from']['first_name'];
        $rcognome = $update['message']['reply_to_message']['from']['last_name'];
        $rusername = $update['message']['reply_to_message']['from']['username'];
        $rlingua = $update['message']['reply_to_message']['from']['language_code'];
    } else {
        $exists_ruser = false;
    }

    # Informazioni utente inoltrato sulla reply
    if (isset($update['message']['reply_to_message']['forward_from'])) {
        $exists_rfuser = true;
        $rfuserID = $update['message']['reply_to_message']['forward_from']['id'];
        $rfnome = $update['message']['reply_to_message']['forward_from']['first_name'];
        $rfcognome = $update['message']['reply_to_message']['forward_from']['last_name'];
        $rfusername = $update['message']['reply_to_message']['forward_from']['username'];
        $rflingua = $update['message']['reply_to_message']['forward_from']['language_code'];
    } else {
        $exists_rfuser = false;
    }

    # Messaggio sulla risposta
    if (isset($update['message']['reply_to_message'])) {
        $reply = true;
        $rmsg = $update['message']['reply_to_message']['text']; // Testo del messaggio al quale si risponde
        $rentities = $update['message']['reply_to_message']['entities']; // Entità del messaggio al quale si risponde
        $rmsgID = $update['message']['reply_to_message']['message_id']; // ID del messaggio al quale si risponde
    }

    # Messaggio inviato
    $msg = $update['message']['text']; // Testo del messaggio inviato (Vale anche per quelli inoltrati)
    $entities = $update['message']['entities']; // Entità del messaggio inviato (Vale anche per quelli inoltrati)
    $msgID = $update['message']['message_id']; // ID del messaggio inviato
    $caption = $update['message']['caption']; // Testo che si trova nei file media

    # Date e orari [Timestamp]
    $data = $update['message']['date']; // Data dell'invio del Messaggio (Vale anche per quelli inoltrati)
    $edata = $update['message']['edit_date']['date']; // Data dell'ultima modifica sul messagio
    $fdata = $update['message']['forward_date']; // Data del messaggio inoltrato
    $rdata = $update['message']['reply_to_message']['date']; // Data del messaggio in reply

    # Gruppi e Canali
    $chatID = $update['message']['chat']['id'];  // ID del gruppo/canale
    $typechat = $update['message']['chat']['type']; // Tipo di chat (private, group, supergroup, channel)
    if ($typechat !== "private") {
        $title = $update['message']['chat']['title']; // Titolo del gruppo/canale
        $chatusername = $update['message']['chat']['username']; // Username del gruppo/canale
    }

    $fchatID = $update['message']['forward_from_chat']['chat']['id']; // ID del gruppo/canale del messaggio inoltrato
    $ftypechat = $update['message']['forward_from_chat']['chat']['type']; // Tipo ci chat (private, group, supergroup, channel) (In base all' inoltro)
    if ($ftypechat !== "private") {
        $ftitle = $update['message']['forward_from_chat']['chat']['title']; // Titolo del canale da cui è stato inoltrato
        $fchatusername = $update['message']['forward_from_chat']['chat']['username']; // Username del canale da cui è stato inoltrato
    }

    # CallBack Query
    if (isset($update["callback_query"])) {
        $cbid = $update["callback_query"]["id"]; // ID della query
        $cbdata = $update["callback_query"]["data"]; // Messaggio della query
        $msg = $cbdata;
        # Informazioni utente
        if (isset($update['callback_query']['from'])) {
            $exists_user = true;
            $userID = $update['callback_query']['from']['id'];
            $nome = $update['callback_query']['from']['first_name'];
            $cognome = $update['callback_query']['from']['last_name'];
            $username = $update['callback_query']['from']['username'];
            $lingua = $update['callback_query']['from']['language_code'];
        } else {
            $exists_user = false;
        }
        if (isset($update["callback_query"]["inline_message_id"])) {
            $cbmid = $update["callback_query"]["inline_message_id"]; // ID del messaggio mandato inline nella query
            $chatID = $userID; // ID della Chat sulla query
        } else {
            $cbmid = $update["callback_query"]["message"]["message_id"]; // ID del messaggio nella query
            $chatID = $update["callback_query"]["message"]["chat"]["id"]; // ID della Chat sulla query
        }
    }

    # Media
    $voice = $update["message"]["voice"]["file_id"]; // ID del audio vocale inviato
    $foto = $update["message"]["photo"][0]["file_id"]; // ID della foto inviata a minima qualità
    $file = $update["message"]["document"]["file_id"]; // ID del file inviato
    $audio = $update["message"]["audio"]["file_id"]; // ID del file audio inviato
    if (isset($update["message"]["sticker"])) // Update per le stickers
    {
        $s_setname = $update["message"]["sticker"]["set_name"]; // Nome del Pacchetto Sticker
        $sticker = $update["message"]["sticker"]["file_id"]; // ID dello Sticker inviato
        $s_emoji = $update["message"]["sticker"]["emoji"]; // Emoji attribuito allo Sticker inviato
        $s_x = $update["message"]["sticker"]["width"]; // Larghezza dell'immagine Sticker
        $s_y = $update["message"]["sticker"]["height"]; // Altezza dell'immagine Sticker
        $s_bytes = $update["message"]["sticker"]["file_size"]; // Peso dello Sticker espresso in byte
    }

    $pecmd = ['/', '!', '.']; // Utilizzo comandi del Bot (Es: /cmd !cmd .cmd)
    if (in_array($msg[0], $pecmd)) {
        $cmd = substr($msg, 1, strlen($msg));
    }
    $cmd = str_replace("@" . $config['username_bot'], '', $cmd);

    # Fine gestione variabili
}

#Funzioni del Bot	

// Metodo di request (cURL e Json Payload)
function sendRequest($url = false, $args = false, $response = 'def', $metodo = 'def')
{
    global $config;
    if (!defined('json_payload')) {
        define('json_payload', true);
    }
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
    if (!defined('json_payload') and $config['json_payload'] and !$response and strpos($url,
            'api.telegram.org') !== false) {
        $method = explode('/', $url);
        $ult = count($method) - 1;
        $method = $method[$ult];
        $args['method'] = $method;
        $json = json_encode($args);
        if (!defined('header')) {
            define('header', false);
            ignore_user_abort(true);
            header('Content-Length: ' . strlen($json));
            header('Content-Type: application/json');
            echo $json;
            fastcgi_finish_request();
        }
        define('json_payload', false);
        return true;
    } else {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => $post,
            CURLOPT_POSTFIELDS => $args,
            CURLOPT_RETURNTRANSFER => $response
        ]);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}

//Avvisi Errori
if ($config['console'] !== false) {
    function call_error($error, $plugin = 'no', $chat = 'def')
    {
        global $api;
        global $config;
        global $pluginp;
        $userbot = $config['username_bot'];
        if ($chat == 'def') {
            $chat = $config['console'];
        }
        if (!$pluginp) {
        } elseif ($plugin == 'no') {
            $plugin = $pluginp;
        }
        $text = "\n<b>Error:</b> $error \n<b>Plugin</b>: $plugin \n@$userbot\n";
        $args = array(
            'chat_id' => $chat,
            'text' => $text,
            'parse_mode' => 'html',
        );
        sendRequest("https://api.telegram.org/$api/sendMessage", $args);
        if ($config['devmode']) {
            echo "<br><b>Bot Error</b>: $error in $plugin<br>";
        }
    }
} else {
    function call_error($error, $plugin = 'no', $chat = 'def')
    {
        return "Console non settata";
    }
}

//Query semplice per PDO
if ($config['usa_il_db'] !== false) {
    function db_query($query, $prepare = false)
    {
        global $PDO;
        $q = $PDO->prepare($query);
        if ($prepare !== false and is_array($prepare)) {
            $q->execute($prepare);
        } else {
            $q->execute();
        }
        $err = $PDO->errorInfo();
        if ($err[0] !== "00000") {
            call_error("PDO Error\n<b>INPUT:</> <code>$q</>\n<b>OUTPUT:</> <code>" . json_encode($err) . "</>");
            $rr = $err;
        } else {
            $rr = $q->fetchAll();
        }
        return $rr;
    }
} else {
    function db_query($query)
    {
        return "Database disattivato";
    }
}

//Query con risposta in Json
function JsonResponse($link)
{
    $r = sendRequest($link);
    $rr = json_decode($r, true);
    return $rr;
}

# Funzioni Telegram | Method

//Azioni | sendChatAction
function scAction($chatID, $action = 'typing')
{
    global $api;
    $args = array(
        'chat_id' => $chatID,
        'action' => $action
    );
    $rr = sendRequest("https://api.telegram.org/$api/sendChatAction", $args, false);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendChatAction \n<b>INPUT</b>: <code>" . htmlspecialchars(json_encode($args)) . "</code> \n<b>OUTPUT:</b> " . $ar['description']);
    }
    return $ar;
}

//Invio messaggi | sendMessage
function sm($chatID, $text, $rmf = false, $pm = 'def', $reply = false, $dislink = 'def', $inline = true)
{
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

    $args = array(
        'chat_id' => $chatID,
        'text' => $text,
        'parse_mode' => $pm,
        'disable_web_page_preview' => $dislink,
    );

    if ($rmf == 'rispondimi') {
        $rm = array('force_reply' => true, 'selective' => true);
    } elseif ($rmf == 'nascondi') {
        $rm = array('hide_keyboard' => true);
    } elseif (!$inline) {
        $rm = array('keyboard' => $rmf, 'resize_keyboard' => true);
    } else {
        $rm = array('inline_keyboard' => $rmf);
    }

    $rm = json_encode($rm);
    if ($rmf) {
        $args['reply_markup'] = $rm;
    }
    if ($reply) {
        $args['reply_to_message_id'] = $reply;
    }
    $rr = sendRequest("https://api.telegram.org/$api/sendMessage", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendMessage \n<b>INPUT</b>: <code>" . htmlspecialchars(json_encode($args)) . "</code> \n<b>OUTPUT:</b> " . $ar['description']);
    }
    return $ar;
}

//Rispondi CallBack | editMessageText & answerCallbackQuery
function cb_reply(
    $id,
    $text,
    $alert = false,
    $cbmid = false,
    $ntext = false,
    $nmenu = false,
    $pm = 'def',
    $dislink = 'def'
) {
    global $api;
    global $chatID;
    global $config;

    if ($pm === 'def') {
        $pm = $config['parse_mode'];
    }

    if ($dislink === 'def') {
        $dislink = $config['disabilita_anteprima_link'];
    }

    $args = array(
        'callback_query_id' => $id,
        'text' => $text,
        'show_alert' => $alert,
    );
    sendRequest("https://api.telegram.org/$api/answerCallbackQuery", $args, 0);
    if ($cbmid) {
        $c = editMsg($chatID, $ntext, $cbmid, $nmenu, $pm, $dislink);
    } else {
        $c = true;
    }
    return $c;
}

function cb_url($cbid, $url)
{
    global $api;

    $args = array(
        'callback_query_id' => $cbid,
        'url' => $url
    );

    $rr = sendRequest("https://api.telegram.org/$api/answerCallbackQuery", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("answerCallbackQuery\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Modifica il testo di un messaggio | editMessageText
function editMsg($chatID, $msg, $cbmid, $editKeyBoard = false, $pm = 'html', $dislink = 'def')
{
    global $api;
    global $config;

    if ($pm === 'def') {
        $pm = $config['parse_mode'];
    }

    if ($dislink === 'def') {
        $dislink = $config['disabilita_anteprima_link'];
    }

    $args = array(
        'text' => $msg,
        'parse_mode' => $pm,
        'disable_web_page_preview' => $dislink
    );
    if (is_numeric($cbmid)) {
        $args['chat_id'] = $chatID;
        $args['message_id'] = $cbmid;
    } else {
        $args['inline_message_id'] = $cbmid;
    }

    if ($editKeyBoard) {
        $rm = array('inline_keyboard' => $editKeyBoard);
        $rm = json_encode($rm);
        $args["reply_markup"] = $rm;
    }

    $rr = sendRequest("https://api.telegram.org/$api/editMessageText", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("editMessageText \n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Modifica il testo di un file media | editMessageCaption
function editMsgc($chatID, $msg, $cbmid, $editKeyBoard = false, $pm = 'html', $dislink = 'def')
{
    global $api;
    global $config;

    if ($pm === 'def') {
        $pm = $config['parse_mode'];
    }

    if ($dislink === 'def') {
        $dislink = $config['disabilita_anteprima_link'];
    }

    $args = array(
        'chat_id' => $chatID,
        'text' => $msg,
        'message_id' => $cbmid,
        'parse_mode' => $pm,
        'disable_web_page_preview' => $dislink
    );

    if ($editKeyBoard) {
        $rm = array('inline_keyboard' => $editKeyBoard);
        $rm = json_encode($rm);
        $args["reply_markup"] = $rm;
    }

    $rr = sendRequest("https://api.telegram.org/$api/editMessageCaption", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("editMessageCaption \n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Elimina un messaggio | deleteMessage
function dm($chatID, $msgID)
{
    global $api;

    $args = array(
        'chat_id' => $chatID,
        'message_id' => $msgID
    );

    $rr = sendRequest("https://api.telegram.org/$api/deleteMessage", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("deleteMessage\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Inoltra un messaggio | forwardMessage
function fw($chatID, $fromID, $msgID)
{
    global $api;

    $args = array(
        'chat_id' => $chatID,
        'from_chat_id' => $fromID,
        'message_id' => $msgID
    );

    $rr = sendRequest("https://api.telegram.org/$api/forwardMessage", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("forwardMessage\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Invia una foto | sendPhoto
function sp($chatID, $photo, $caption = null, $rmf = false, $pm = 'def', $reply = false, $inline = true)
{
    global $api;
    global $config;


    if ($pm === 'def') {
        $pm = $config['parse_mode'];
    }

    if ($config['azioni']) {
        scAction($chatID, 'upload_photo');
    }

    $args = array(
        'chat_id' => $chatID,
        'photo' => $photo,
        'caption' => $caption,
        'parse_mode' => $pm
    );

    if ($rmf == 'rispondimi') {
        $rm = array('force_reply' => true, 'selective' => true);
    } elseif ($rmf == 'nascondi') {
        $rm = array('hide_keyboard' => true);
    } elseif (!$inline) {
        $rm = array('keyboard' => $rmf, 'resize_keyboard' => true);
    } else {
        $rm = array('inline_keyboard' => $rmf);
    }

    $rm = json_encode($rm);
    if ($rmf) {
        $args['reply_markup'] = $rm;
    }
    if ($reply) {
        $args['reply_to_message_id'] = $reply;
    }
    $rr = sendRequest("https://api.telegram.org/$api/sendPhoto", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendPhoto\n<b>INPUT</>: <code>" . htmlspecialchars(json_encode($args)) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Invia un file audio | sendAudio
function sa($chatID, $audio, $caption = null, $rmf = false, $pm = 'def', $reply = false, $inline = true)
{
    global $api;
    global $config;


    if ($pm === 'def') {
        $pm = $config['parse_mode'];
    }

    if ($config['azioni']) {
        scAction($chatID, 'upload_audio');
    }

    $args = array(
        'chat_id' => $chatID,
        'audio' => $audio,
        'caption' => $caption,
        'parse_mode' => $pm
    );

    if ($rmf == 'rispondimi') {
        $rm = array('force_reply' => true, 'selective' => true);
    } elseif ($rmf == 'nascondi') {
        $rm = array('hide_keyboard' => true);
    } elseif (!$inline) {
        $rm = array('keyboard' => $rmf, 'resize_keyboard' => true);
    } else {
        $rm = array('inline_keyboard' => $rmf);
    }

    $rm = json_encode($rm);
    if ($rmf) {
        $args['reply_markup'] = $rm;
    }
    if ($reply) {
        $args['reply_to_message_id'] = $reply;
    }
    $rr = sendRequest("https://api.telegram.org/$api/sendAudio", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendAudio\n<b>INPUT</>: <code>" . htmlspecialchars(json_encode($args)) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Invia un audio vocale | sendVoice
function sav($chatID, $audio, $caption = null, $rmf = false, $pm = 'def', $reply = false, $inline = true)
{
    global $api;
    global $config;


    if ($pm === 'def') {
        $pm = $config['parse_mode'];
    }

    if ($config['azioni']) {
        scAction($chatID, 'record_audio');
    }

    $args = array(
        'chat_id' => $chatID,
        'voice' => $audio,
        'caption' => $caption,
        'parse_mode' => $pm
    );

    if ($rmf == 'rispondimi') {
        $rm = array('force_reply' => true, 'selective' => true);
    } elseif ($rmf == 'nascondi') {
        $rm = array('hide_keyboard' => true);
    } elseif (!$inline) {
        $rm = array('keyboard' => $rmf, 'resize_keyboard' => true);
    } else {
        $rm = array('inline_keyboard' => $rmf);
    }

    $rm = json_encode($rm);
    if ($rmf) {
        $args['reply_markup'] = $rm;
    }
    if ($reply) {
        $args['reply_to_message_id'] = $reply;
    }
    $rr = sendRequest("https://api.telegram.org/$api/sendVoice", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendVoice\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Invia un video | sendVideo
function sv($chatID, $documento, $caption = null, $rmf = false, $pm = 'def', $reply = false, $inline = true)
{
    global $api;
    global $config;


    if ($pm === 'def') {
        $pm = $config['parse_mode'];
    }

    if ($config['azioni']) {
        scAction($chatID, 'upload_video');
    }

    $args = array(
        'chat_id' => $chatID,
        'video' => $documento,
        'caption' => $caption,
        'parse_mode' => $pm
    );

    if ($rmf == 'rispondimi') {
        $rm = array('force_reply' => true, 'selective' => true);
    } elseif ($rmf == 'nascondi') {
        $rm = array('hide_keyboard' => true);
    } elseif (!$inline) {
        $rm = array('keyboard' => $rmf, 'resize_keyboard' => true);
    } else {
        $rm = array('inline_keyboard' => $rmf);
    }

    $rm = json_encode($rm);
    if ($rmf) {
        $args['reply_markup'] = $rm;
    }
    if ($reply) {
        $args['reply_to_message_id'] = $reply;
    }
    $rr = sendRequest("https://api.telegram.org/$api/sendVideo", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendVideo\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Invia un video rotondo | sendVideoNote
function svr($chatID, $documento, $rmf = false, $reply = true, $inline = true)
{
    global $api;
    global $config;

    if ($config['azioni']) {
        scAction($chatID, 'upload_video_note');
    }

    $args = array(
        'chat_id' => $chatID,
        'video_note' => $documento,
    );

    if ($rmf == 'rispondimi') {
        $rm = array('force_reply' => true, 'selective' => true);
    } elseif ($rmf == 'nascondi') {
        $rm = array('hide_keyboard' => true);
    } elseif (!$inline) {
        $rm = array('keyboard' => $rmf, 'resize_keyboard' => true);
    } else {
        $rm = array('inline_keyboard' => $rmf);
    }

    $rm = json_encode($rm);
    if ($rmf) {
        $args['reply_markup'] = $rm;
    }
    if ($reply) {
        $args['reply_to_message_id'] = $reply;
    }
    $rr = sendRequest("https://api.telegram.org/$api/sendVideoNote", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendVideoNote\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Invia un contatto | sendContact
function sc($ID, $numero, $firstname = "Sconosciuto", $lastname = " ")
{
    global $api;

    $args = array(
        'chat_id' => $ID,
        'phone_number' => $numero,
        'first_name' => $firstname,
        'last_name' => $lastname
    );

    $rr = sendRequest("https://api.telegram.org/$api/sendContact", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendContact\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Invia un file | sendDocument
function sd($chatID, $documento, $caption = null, $rmf = false, $pm = 'def', $reply = false, $inline = true)
{
    global $api;
    global $config;

    if ($pm === 'def') {
        $pm = $config['parse_mode'];
    }

    if ($config['azioni']) {
        scAction($chatID, 'upload_document');
    }

    $args = array(
        'chat_id' => $chatID,
        'document' => $documento,
        'caption' => $caption,
        'parse_mode' => $pm
    );

    if ($rmf == 'rispondimi') {
        $rm = array('force_reply' => true, 'selective' => true);
    } elseif ($rmf == 'nascondi') {
        $rm = array('hide_keyboard' => true);
    } elseif (!$inline) {
        $rm = array('keyboard' => $rmf, 'resize_keyboard' => true);
    } else {
        $rm = array('inline_keyboard' => $rmf);
    }

    $rm = json_encode($rm);
    if ($rmf) {
        $args['reply_markup'] = $rm;
    }
    if ($reply) {
        $args['reply_to_message_id'] = $reply;
    }
    $rr = sendRequest("https://api.telegram.org/$api/sendDocument", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendDocument\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Invia una GIF | sendAnimation
function sgif($chatID, $file, $caption = null, $rmf = false, $pm = 'def', $reply = false, $inline = true)
{
    global $api;
    global $config;


    if ($pm === 'def') {
        $pm = $config['parse_mode'];
    }

    if ($config['azioni']) {
        scAction($chatID, 'upload_video');
    }

    $args = array(
        'chat_id' => $chatID,
        'animation' => $file,
        'caption' => $caption,
        'parse_mode' => $pm
    );

    if ($rmf == 'rispondimi') {
        $rm = array('force_reply' => true, 'selective' => true);
    } elseif ($rmf == 'nascondi') {
        $rm = array('hide_keyboard' => true);
    } elseif (!$inline) {
        $rm = array('keyboard' => $rmf, 'resize_keyboard' => true);
    } else {
        $rm = array('inline_keyboard' => $rmf);
    }

    $rm = json_encode($rm);
    if ($rmf) {
        $args['reply_markup'] = $rm;
    }
    if ($reply) {
        $args['reply_to_message_id'] = $reply;
    }
    $rr = sendRequest("https://api.telegram.org/$api/sendAnimation", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendAnimation\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Invia uno sticker | sendSticker
function ss($chatID, $sticker, $rmf = false, $reply = false, $inline = true)
{
    global $api;

    $args = array(
        'chat_id' => $chatID,
        'sticker' => $sticker
    );

    if ($rmf == 'rispondimi') {
        $rm = array('force_reply' => true, 'selective' => true);
    } elseif ($rmf == 'nascondi') {
        $rm = array('hide_keyboard' => true);
    } elseif (!$inline) {
        $rm = array('keyboard' => $rmf, 'resize_keyboard' => true);
    } else {
        $rm = array('inline_keyboard' => $rmf);
    }

    $rm = json_encode($rm);
    if ($rmf) {
        $args['reply_markup'] = $rm;
    }
    if ($reply) {
        $args['reply_to_message_id'] = $reply;
    }
    $rr = sendRequest("https://api.telegram.org/$api/sendSticker", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendSticker\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Modifica un file media | editMessageMedia
function editMedia($chatID, $msgID, $file_id, $type, $caption = false, $editKeyBoard = false, $pm = 'def')
{
    global $api;
    global $config;

    if ($pm === 'def') {
        $pm = $config['parse_mode'];
    }

    $media = array(
        'type' => $type,
        'media' => $file_id,
        'parse_mode' => $pm
    );

    if ($caption) {
        $media['caption'] = $caption;
    }

    $args = array(
        'chat_id' => $chatID,
        'message_id' => $msgID,
        'media' => json_encode($media)
    );

    if ($editKeyBoard) {
        $rm = array('inline_keyboard' => $editKeyBoard);
        $rm = json_encode($rm);
        $args['reply_markup'] = $rm;
    }

    $rr = sendRequest("https://api.telegram.org/$api/editMessageMedia", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("editMessageMedia\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Invia delle immagini/video in gruppo | sendMediaGroup
function smg($chatID, $documenti = array(), $caption = 'def', $rmf = false, $pm = 'def', $reply = null, $inline = true)
{
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
        $documenti[0]['parse_mode'] = $pm;
    }

    $args = array(
        'chat_id' => $chatID,
        'media' => json_encode($documenti)
    );

    if ($rmf == 'rispondimi') {
        $rm = array('force_reply' => true, 'selective' => true);
    } elseif ($rmf == 'nascondi') {
        $rm = array('hide_keyboard' => true);
    } elseif (!$inline) {
        $rm = array('keyboard' => $rmf, 'resize_keyboard' => true);
    } else {
        $rm = array('inline_keyboard' => $rmf);
    }

    $rm = json_encode($rm);
    if ($rmf) {
        $args['reply_markup'] = $rm;
    }
    if ($reply) {
        $args['reply_to_message_id'] = $reply;
    }
    $rr = sendRequest("https://api.telegram.org/$api/sendMediaGroup", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendMediaGroup\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Invia una posizione | sendLocation
function sendLocation($chatID, $lati, $long, $rmf = false, $time = null, $reply = false, $inline = true)
{
    global $api;
    global $config;

    if ($config['azioni']) {
        scAction($chatID, 'find_location');
    }

    $args = array(
        'chat_id' => $chatID,
        'latitude' => $lati,
        'longitude' => $long,
        'disable_notification' => false
    );

    if ($rmf == 'rispondimi') {
        $rm = array('force_reply' => true, 'selective' => true);
    } elseif ($rmf == 'nascondi') {
        $rm = array('hide_keyboard' => true);
    } elseif (!$inline) {
        $rm = array('keyboard' => $rmf, 'resize_keyboard' => true);
    } else {
        $rm = array('inline_keyboard' => $rmf);
    }

    $rm = json_encode($rm);
    if ($rmf) {
        $args['reply_markup'] = $rm;
    }
    if ($reply) {
        $args['reply_to_message_id'] = $reply;
    }
    if ($time) {
        $args['live_period'] = $time;
    }
    $rr = sendRequest("https://api.telegram.org/$api/sendLocation", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendLocation \n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Modifica una posizione in live | editMessageLiveLocation
function editLocation($chatID, $lati, $long, $msgID, $rmf = false)
{
    global $api;

    $args = array(
        'chat_id' => $chatID,
        'message_id' => $msgID,
        'latitude' => $lati,
        'longitude' => $long,
        'disable_notification' => false
    );

    if ($rmf) {
        $rm = array('inline_keyboard' => $rmf);
        $rm = json_encode($rm);
        $args['reply_markup'] = $rm;
    }

    $rr = sendRequest("https://api.telegram.org/$api/editMessageLiveLocation", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("editMessageLiveLocation \n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Termina una posizione in live | stopMessageLiveLocation
function stopLocation($chatID, $msgID, $rmf = false)
{
    global $api;

    $args = array(
        'chat_id' => $chatID,
        'message_id' => $msgID,
        'disable_notification' => false
    );

    if ($rmf) {
        $rm = array('inline_keyboard' => $rmf);
        $rm = json_encode($rm);
        $args['reply_markup'] = $rm;
    }

    $rr = sendRequest("https://api.telegram.org/$api/stopMessageLiveLocation", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("stopMessageLiveLocation \n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Invia la posizione di un posto | sendVenue
function sven($chatID, $lati, $long, $title, $ind, $rmf = false, $reply = false, $inline = true)
{
    global $api;
    global $config;

    if ($config['azioni']) {
        scAction($chatID, 'find_location');
    }

    $args = array(
        'chat_id' => $chatID,
        'latitude' => $lati,
        'longitude' => $long,
        'title' => $title,
        'addres' => $ind,
        'disable_notification' => false
    );

    if ($rmf == 'rispondimi') {
        $rm = array('force_reply' => true, 'selective' => true);
    } elseif ($rmf == 'nascondi') {
        $rm = array('hide_keyboard' => true);
    } elseif (!$inline) {
        $rm = array('keyboard' => $rmf, 'resize_keyboard' => true);
    } else {
        $rm = array('inline_keyboard' => $rmf);
    }

    $rm = json_encode($rm);
    if ($rmf) {
        $args['reply_markup'] = $rm;
    }
    if ($reply) {
        $args['reply_to_message_id'] = $reply;
    }
    $rr = sendRequest("https://api.telegram.org/$api/sendVenue", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendVenue \n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Informazioni di un gruppo/canale | getChat
function getChat($chatID)
{
    global $api;

    $args = array('chat_id' => $chatID);

    $rr = sendRequest("https://api.telegram.org/$api/getChat", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("getChat \n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Lista Admins di un gruppo | getChatAdministrators
function getAdmins($chatID)
{
    global $api;

    $args = array('chat_id' => $chatID);

    $rr = sendRequest("https://api.telegram.org/$api/getChatAdministrators", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("getChatAdministrators \n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Banna un utente | kickChatMember
function ban($chatID, $userID)
{
    global $api;

    $args = array(
        'chat_id' => $chatID,
        'user_id' => $userID
    );

    $rr = sendRequest("https://api.telegram.org/$api/kickChatMember", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("kickChatMember\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Sbanna un utente | unbanChatMember
function unban($chatID, $userID)
{
    global $api;

    $args = array(
        'chat_id' => $chatID,
        'user_id' => $userID
    );

    $rr = sendRequest("https://api.telegram.org/$api/unbanChatMember", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("unbanChatMember\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Limita utente (per i gruppi) | restrictChatMember
function limita($chatID, $userID, $durata = null)
{
    global $api;

    if ($durata === 'def') {
        $duratas = time();
    } else {
        $duratas = time() + $durata;
    }

    $args = array(
        'chat_id' => $chatID,
        'user_id' => $userID,
        'until_date' => $duratas,
        'can_send_messages' => false,
        'can_send_media_messages' => false,
        'can_send_other_messages' => false,
        'can_add_web_page_previews' => false,
    );

    $rr = sendRequest("https://api.telegram.org/$api/restrictChatMember", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("restrictChatMember\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Rendi admin un utente (per i gruppi) | promoteChatMember
function promote($chatID, $userID)
{
    global $api;

    $args = array(
        'chat_id' => $chatID,
        'user_id' => $userID,
        'can_change_info' => false,
        'can_delete_messages' => true,
        'can_invite_users' => false,
        'can_restrict_members' => true,
        'can_pin_messages' => false,
        'can_promote_members' => false,
    );
    $rr = sendRequest("https://api.telegram.org/$api/promoteChatMember", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("promoteChatMember\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Abbandona la chat | leaveChat
function lc($chatID)
{
    global $api;

    $args = array('chat_id' => $chatID);

    $rr = sendRequest("https://api.telegram.org/$api/leaveChat", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("leaveChat\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Cambia il nome di una Chat (gruppo/canale) | setChatTitle
function setTitle($chatID, $title)
{
    global $api;

    $args = array(
        'title' => $title,
        'chat_id' => $chatID
    );

    $rr = sendRequest("https://api.telegram.org/$api/setChatTitle", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("setChatTitle\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Cambia la descrizione di una Chat (gruppo/canale) | setChatDescription
function setDescription($chatID, $desc)
{
    global $api;

    $args = array(
        'description' => $desc,
        'chat_id' => $chatID
    );

    $rr = sendRequest("https://api.telegram.org/$api/setChatDescription", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("setChatDescription\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Setta la foto di una chat (gruppo/canale) | setChatPhoto
function setp($chatID, $photo)
{
    global $api;

    $args = array(
        'chat_id' => $chatID,
        'photo' => $photo
    );

    $rr = sendRequest("https://api.telegram.org/$api/setChatPhoto", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("setChatPhoto \n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Elimina la foto di una chat (gruppo/canale) | deleteChatPhoto
function unsetp($chatID)
{
    global $api;

    $args = array('chat_id' => $chatID);

    $rr = sendRequest("https://api.telegram.org/$api/deleteChatPhoto", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("deleteChatPhoto \n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Setta il set Sticker di un gruppo | setChatStickerSet
function setStickers($chatID, $set)
{
    global $api;

    $args = array(
        'chat_id' => $chatID,
        'sticker_set_name' => $set
    );

    $rr = sendRequest("https://api.telegram.org/$api/setChatStickerSet", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("setChatStickerSet \n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Rimuovi il set Sticker di un gruppo | deleteChatStickerSet
function unsetStickers($chatID)
{
    global $api;

    $args = array('chat_id' => $chatID);

    $rr = sendRequest("https://api.telegram.org/$api/deleteChatStickerSet", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("deleteChatStickerSet \n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Fissa un messaggio (gruppo/canale) | pinChatMessage
function pin($chatID, $rmsgID, $notify = true)
{
    global $api;

    $args = array(
        'chat_id' => $chatID,
        'message_id' => $rmsgID,
        'disable_notification' => false
    );

    if (!$notify) {
        $args['disable_notification'] = true;
    }
    $rr = sendRequest("https://api.telegram.org/$api/pinChatMessage", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("pinChatMessage\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Togli il messaggio fissato (gruppo/canale) | unpinChatMessage
function unpin($chatID, $notify)
{
    global $api;

    $args = array('chat_id' => $chatID);

    if (!$notify) {
        $args['disable_notification'] = true;
    }
    $rr = sendRequest("https://api.telegram.org/$api/unpinChatMessage", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("unpinChatMessage\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Scarica un file da Telegram tramite fileID | getFile
function getFile($fileID)
{
    global $api;

    $args = array('file_id' => $fileID);

    $rr = sendRequest("https://api.telegram.org/$api/getFile", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("sendPhoto\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
        return null;
    }
    return sendRequest("https://api.telegram.org/file/$api/" . $ar['result']['file_path']);
}

//Ottieni il numero di membri di un Gruppo/Canale | getChatMembersCount
function conta($chatID)
{
    global $api;

    $args = array('chat_id' => $chatID);

    $rr = sendRequest("https://api.telegram.org/$api/getChatMembersCount", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("getChatMembersCount \n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar["result"];
}

//Esporta il link di un Gruppo | exportChatInviteLink
function getLink($chatID)
{
    global $api;

    $args = array('chat_id' => $chatID);

    $rr = sendRequest("https://api.telegram.org/$api/exportChatInviteLink", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("exportChatInviteLink\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
        return $ar;
    } else {
        return $ar['result'];
    }
}

//Visualizza lo stato di un utente in un gruppo| getChatMember
function getChatMember($chatID, $userID)
{
    global $api;

    $args = array(
        'chat_id' => $chatID,
        'user_id' => $userID
    );

    $rr = sendRequest("https://api.telegram.org/$api/getChatMember", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("getChatMember\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

//Visualizza le foto di un Utente | getUserProfilePhotos
function getPropic($userID)
{
    global $api;

    $args = array(
        'user_id' => $userID
    );

    $rr = sendRequest("https://api.telegram.org/$api/getUserProfilePhotos", $args);
    $ar = json_decode($rr, true);
    if (isset($ar["error_code"])) {
        call_error("getUserProfilePhotos\n<b>INPUT</>: <code>" . json_encode($args) . "</> \n<b>OUTPUT:</> " . $ar['description']);
    }
    return $ar;
}

#Formattazioni del Bot
function textspecialchars($text, $format = 'def')
{
    global $config;
    if ($format === 'def') {
        $format = $config['parse_mode'];
    }
    if (strtolower($format) == 'html') {
        return htmlspecialchars($text);
    } elseif (strtolower($format) == 'markdown') {
        return mdspecialchars($text);
    } else {
        call_error("Formattazione sconosciuta per textspecialchars: $format");
    }
    return $text;
}

function mdspecialchars($text)
{
    # Caratteri come "*", "_" e "`" visibili in markdown
    $text = str_replace("_", "\_", $text);
    $text = str_replace("*", "\*", $text);
    $text = str_replace("`", "\`", $text);
    return str_replace("[", "\[", $text);
}

function code($text)
{
    global $config;
    if (strtolower($config['parse_mode']) == 'html') {
        return "<code>" . htmlspecialchars($text) . "</>";
    } else {
        return "`" . mdspecialchars($text) . "`";
    }
}

function bold($text)
{
    global $config;
    if (strtolower($config['parse_mode']) == 'html') {
        return "<b>" . htmlspecialchars($text) . "</>";
    } else {
        return "*" . mdspecialchars($text) . "*";
    }
}

function italic($text)
{
    global $config;
    if (strtolower($config['parse_mode']) == 'html') {
        return "<i>" . htmlspecialchars($text) . "</>";
    } else {
        return "_" . mdspecialchars($text) . "_";
    }
}

function text_link($text, $link)
{
    global $config;
    if (strtolower($config['parse_mode']) == 'html') {
        return "<a href='$link'>" . htmlspecialchars($text) . "</>";
    } else {
        return "[" . mdspecialchars($text) . "]($link)";
    }
}

function tag()
{
    global $nome;
    global $cognome;
    global $userID;
    if ($cognome) {
        $nome .= " $cognome";
    }
    return text_link($nome, "tg://user?id=$userID");
}

function getWebhookInfo($chiavi)
{
    $add = new HttpRequest('post', 'https://api.telegram.org/' . $chiavi . '/getWebhookInfo');
    $r = $add->getResponse();
    $rr = json_decode($r, true);
    return $rr;
}

function getMe($chiavi)
{
    $r = sendRequest('https://api.telegram.org/' . $chiavi . '/getMe', false, true);
    $rr = json_decode($r, true);
    return $rr;
}

function deleteWebhook($chiavi)
{
    $r = sendRequest('https://api.telegram.org/' . $chiavi . '/deleteWebhook', false, true);
    $rr = json_decode($r, true);
    return $rr;
}

function setWebhook($chiavi, $webhook)
{
    $args = array(
        'url' => $webhook,
        'max_connections' => 40
    );
    $r = sendRequest('https://api.telegram.org/' . $chiavi . '/setWebhook', $args, true);
    $rr = json_decode($r, true);
    return $rr;
}