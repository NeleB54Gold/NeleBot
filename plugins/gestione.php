<?php 

/*
NeleBotFramework
	Copyright (C) 2018-2019  PHP-Coders

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

if ($isadmin) {
	function finish_request() {
		fastcgi_finish_request();
	}
	
	function progressbar($now, $tot) {
		$p = round(($now / $tot) * 100);
		if ($p <= 10) {
			return "🕑";
		} elseif ($p >=11 and $p <= 20) {
			return "🕒";
		} elseif ($p >= 21 and $p <= 30) {
			return "🕓";
		} elseif ($p >= 31 and $p <= 40) {
			return "🕔";
		} elseif ($p >= 41 and $p <= 50) {
			return "🕕";
		} elseif ($p >= 51 and $p <= 60) {
			return "🕖";
		} elseif ($p >= 61 and $p <= 70) {
			return "🕗";
		} elseif ($p >= 71 and $p <= 80) {
			return "🕘";
		} elseif ($p >= 81 and $p <= 90) {
			return "🕙";
		} elseif ($p >= 91 and $p <= 100) {
			return "🕛";
		} else {
			return $p;
		}
		return false;
	}
	
	if ($cmd == "system") {
		$testo = bold("Statistiche di sistema 🗄");
		$disco_libero = disk_free_space("/");
		$disco_totale = disk_total_space("/");
		$disco_utilizzato = $disco_totale - $disco_libero;
		$testo .= "\n\nUtilizzo disco: " . round($disco_utilizzato / 1024 / 1024) . " su " . round($disco_totale / 1024 / 1024) . " MB (" . round($disco_utilizzato / $disco_totale * 100) . "%)";
		$ram_utilizzata = memory_get_usage(true);
		$ram_totale = $phpinfo['PHP Core']['memory_limit'];
		if ($ram_totale) {
			$testo .= "\nUtilizzo RAM: " . round($ram_utilizzata / 1024 / 1024) . " su " . round($ram_totale / 1024 / 1024) . " MB (" . round($ram_utilizzato / $ram_totale * 100) . "%)";
		} else {
			$testo .= "\nUtilizzo RAM: " . round($ram_utilizzata / 1024 / 1024, 1) . " MB";
		}
		$cpu = sys_getloadavg();
		$testo .= "\nLoad: " . json_encode($cpu);
		sm($chatID, $testo);
		die;
	}
	
	if ($config['usa_il_db']) {
		
		if (strpos($cmd, "info ") === 0) {
			$config['json_payload'] = false;
			$msid = sm($chatID, "Verifico i dati sui vari database...")['result']['message_id'];
			$id = str_replace("@", '', explode(" ", $cmd, 2)[1]);
			if (isset($id)) {
				$q = db_query("SELECT * FROM utenti WHERE user_id = ? or username = ?", [round($id), $id], true);
				if (!$q['user_id']) {
					$q = db_query("SELECT * FROM gruppi WHERE chat_id = ? or username = ?", [round($id), $id], true);
					if (!$q['chat_id']) {
						$q = db_query("SELECT * FROM canali WHERE chat_id = ? or username = ?", [round($id), $id], true);
						if (!$q['chat_id']) {
							editMsg($chatID, "Chat non trovata nel database...", $msid);
							die;
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
				editMsg($chatID, "Nessun ID o username inserito...", $msid);
				die;
			}
			$q['status'] = json_decode($q['status'], true);
			if (!is_array($q['status'])) $q['status'] = [];
			if ($type == "utente") {
				if ($q['cognome']) $qcognome = "\nCognome: " . htmlspecialchars($q['cognome']);
				if ($q['username']) $qusername = "\nUsername: @" . htmlspecialchars($q['username']);
				$ulanguage = "\nLingua: " . $q['lang'];
				$stati_user = [
					'blocked' => "Bloccato dall'utente",
					'deleted' => "Account eliminato",
					'bot' => "Bot",
					'avviato' => "Avviato",
					'attivo' => "Non avviato",
					'visto' => "Mai incontrato",
					'ban' => "Bannato fino a tempo indefinito"
				];
				if ($stati_user[$q['status'][$botID]]) {
					$stat = $stati_user[$q['status'][$botID]];
				} elseif (strpos($q['status'][$botID], "ban") === 0) {
					$dateban = str_replace("ban", '', $q['status'][$botID]);
					$stat = "Bannato fino al " . date("j M Y", $dateban) . " alle " . date("H:i", $dateban);
				} else {
					$stat = json_encode($q['status']);
				}
				$ustato = "\nStato: $stat";
				$menu[] = [
					[
						"text" => "👮🏻‍♂️ Amministrazione",
						"callback_data" => "useradmins_$id"
					]
				];
				$menu[] = [
					[
						"text" => "🔄 Aggiorna dati",
						"callback_data" => "updateuser_$id"
					],
					[
						"text" => "🗑 Elimina",
						"callback_data" => "deluser_$id"
					]
				];
				editMsg($chatID, bold("Informazioni utente") . "\nID: $id \nNome: " . htmlspecialchars($q['nome']) . $qcognome . $qusername . $ulanguage . $ustato, $msid, $menu);
			} elseif ($type == "gruppo") {
				if ($q['username']) $qusername = "\nUsername: @" . htmlspecialchars($q['username']);
				if ($q['description']) $qdescrizione = "\nDescrizione: " . htmlspecialchars($q['description']);
				$stati_chat = [
					'avviato' => "Avviato",
					'attivo' => "Bot non membro",
					'inattivo' => "Bot rimosso dalla chat",
					'kicked' => "Bot bannato dalla chat",
					'visto' => "Bot mai entrato",
					'ban' => "Bannato fino a tempo indefinito"
				];
				if ($stati_chat[$q['status'][$botID]]) {
					$stat = $stati_chat[$q['status'][$botID]];
				} elseif (strpos($q['status'][$botID], "ban") === 0) {
					$dateban = str_replace("ban", '', $q['status'][$botID]);
					$stat = "Bannato fino al " . date("j M Y", $dateban) . " alle " . date("H:i", $dateban);
				} else {
					$stat = json_encode($q['status']);
				}
				$ustato = "\nStato: $stat";
				$menu[] = [
					[
						"text" => "👮🏻‍♂️ Amministratori",
						"callback_data" => "chatadmins_$id"
					],
					[
						"text" => "🚮 Lascia chat",
						"callback_data" => "leavechat_$id"
					]
				];
				$menu[] = [
					[
						"text" => "🔄 Aggiorna dati",
						"callback_data" => "updatechat_$id"
					],
					[
						"text" => "🗑 Elimina",
						"callback_data" => "delchat_$id"
					]
				];
				editMsg($chatID, bold("Informazioni gruppo") . "\nID: $id \nTitolo: " . htmlspecialchars($q['title']) . $qdescrizione . $qusername . $ustato, $msid, $menu);
			} elseif ($type == "canale") {
				if ($q['username']) $qusername = "\nUsername: @" . htmlspecialchars($q['username']);
				if ($q['description']) $qdescrizione = "\nDescrizione: " . htmlspecialchars($q['description']);
				$stati_chat = [
					'avviato' => "Avviato",
					'attivo' => "Bot non membro",
					'inattivo' => "Bot rimosso dalla chat",
					'kicked' => "Bot bannato dalla chat",
					'visto' => "Bot mai entrato",
					'ban' => "Bannato fino a tempo indefinito"
				];
				if ($stati_chat[$q['status'][$botID]]) {
					$stat = $stati_chat[$q['status'][$botID]];
				} elseif (strpos($q['status'][$botID], "ban") === 0) {
					$dateban = str_replace("ban", '', $q['status'][$botID]);
					$stat = "Bannato fino al " . date("j M Y", $dateban) . " alle " . date("H:i", $dateban);
				} else {
					$stat = json_encode($q['status']);
				}
				$ustato = "\nStato: $stat";
				$menu[] = [
					[
						"text" => "👮🏻‍♂️ Amministratori",
						"callback_data" => "chatadmins_$id"
					],
					[
						"text" => "🚮 Lascia chat",
						"callback_data" => "leavechat_$id"
					]
				];
				$menu[] = [
					[
						"text" => "🔄 Aggiorna dati",
						"callback_data" => "updatechat_$id"
					],
					[
						"text" => "🗑 Elimina",
						"callback_data" => "delchat_$id"
					]
				];
				editMsg($chatID, bold("Informazioni canale") . "\nID: $id \nTitolo: " . htmlspecialchars($q['title']) . $qdescrizione . $qusername . $ustato, $msid, $menu);
			} else {
				editMsg($chatID, "Chat sconosciuta", $msid);
			}
			die;
		}
		
		if ($cmd == "gestione" or $cbdata == "gestione") {
			$menu[] = [
				[
					'text' => "Utenti 👤",
					'callback_data' => 'gestione_utenti-1'
				],
			];
			$menu[] = [
				[
					'text' => "Gruppi 👥",
					'callback_data' => 'gestione_gruppi-1'
				],
				[
					'text' => "Canali 📢",
					'callback_data' => 'gestione_canali-1'
				],
			];
			$menu[] = [
				[
					'text' => "Fatto ✅",
					'callback_data' => 'fatto'
				],
			];
			$testo = "Cosa vuoi gestire?";
			if ($cbdata) {
				cb_reply($cbid, '', false, $cbmid, $testo, $menu);
			} else {
				sm($chatID, $testo, $menu);
				dm($chatID, $msgID);
			}
			die;
		}
		
		if (strpos($cbdata, "gespag_") === 0) {
			$idb = ['utenti', 'gruppi', 'canali'];
			$e = explode("_", $cbdata);
			$db = $e[1];
			$page = $e[2];
			if (in_array($db, $idb)) {
				$cosi = db_query("SELECT * FROM $db LIMIT 100000", false, false);
				$quanti = count($cosi);
				$pages = round($quanti / 5);
				if ($page == 0) {
					$prim = 1;
				} else {
					$prim = (25 * $page) + 1;
				}
				$fin = $prim + 25;
				$range = range($prim, $pages);
				foreach ($range as $num) {
					if ($prim + 4 < $num) {
						$prim = $num;
					}
					if ($num < $fin) {
						$menu[$prim][] = [
							"text" => "$num",
							"callback_data" => "gestione_$db" . "-$num"
						];
					} else {
						$dopo = true;
					}
				}
				if ($page != 0) {
					$menufrecce[] = [
						"text" => "⏮",
						"callback_data" => "gespag_$db" . "_" . round($page - 1)
					];
				}
				if (isset($dopo)) {
					$menufrecce[] = [
						"text" => "⏭",
						"callback_data" => "gespag_$db" . "_" . round($page + 1)
					];
				}
				if (isset($menufrecce)) $menu[] = $menufrecce;
				$menu[] = [
					[
						"text" => "🔙 Indietro",
						"callback_data" => "gestione"
					],
				];
				$menu = array_values($menu);
				cb_reply($cbid, "$pages pagine", false, $cbmid, bold("Gestione $db") . " \n\nSeleziona la pagina da visualizzare.", $menu);
			}
			die;
		}
		
		if (strpos($cbdata, "useradmins_") === 0) {
			$e = explode("_", str_replace("useradmins_", '', $cbdata));
			$user_id = $e[0];
			$mtt = $e[1];
			$q = db_query("SELECT * FROM utenti WHERE user_id = ?", [$user_id], true);
			if (!isset($q['user_id'])) {
				cb_reply($cbid, "Utente non trovato nel database...", true);
				die;
			}
			if ($database['type'] == "postgre") {
				$querytdc = "SELECT * FROM canali WHERE strpos(admins, ?) != 0";
				$querytdg = "SELECT * FROM gruppi WHERE strpos(admins, ?) != 0";
			} elseif ($database['type'] == "mysql") {
				$querytdc = "SELECT * FROM canali WHERE locate(?, admins) > 2";
				$querytdg = "SELECT * FROM gruppi WHERE locate(?, admins) > 2";
			}
			$cadmins = array_merge(db_query($querytdc, [$user_id], false), db_query($querytdg, [$user_id], false));
			if ($cadmins['error']) {
				cb_reply($cbid, "Database error: " . $cadmins['error'][2]);
			} elseif (!$cadmins) {
				cb_reply($cbid, 'Nessuna chat in cui sia amministratore...', true);
			} else {
				$t = bold("👮🏻‍♂️ Chat di " . $q['nome'] . " " . $q['cognome']);
				if ($mtt) {
					$plus = "➖";
					$p = false;
				} else {
					$plus = "➕";
					$p = true;
				}
				foreach($cadmins as $chat) {
					unset($statush);
					$chat['admins'] = json_decode($chat['admins'], true);
					foreach($chat['admins'] as $ad) {
						if ($ad['user']['id'] == $user_id) {
							$statush = $ad['status'];
						}
					}
					if ($statush) {
						if ($mtt) {
							$menu[] = [
								[
									"text" => $chat['title'],
									"callback_data" => "updatechat_" . $chat['chat_id']
								]
							];
						} else {
							if ($chat['username']) {
								$title = text_link($chat['title'], "https://t.me/" . $chat['username']);
							} else {
								$title = $chat['title'];
							}
							$t .= "\n• " . $title . " [" . code($chat['chat_id']) . "] \n" . $statush;
						}
					}
				}
				$menu[] = [
					[
						"text" => "🔄",
						"callback_data" => $cbdata
					],
					[
						"text" => $plus,
						"callback_data" => "useradmins_$user_id" . "_$p"
					]
				];
				$menu[] = [
					[
						"text" => "🔙 Indietro",
						"callback_data" => "updateuser_$user_id"
					]
				];
				cb_reply($cbid, '', false, $cbmid, $t, $menu);
			}
			die;
		}
		
		if (strpos($cbdata, "chatadmins_") === 0) {
			$e = explode("_", str_replace("chatadmins_", '', $cbdata));
			$chat_id = $e[0];
			$mtt = $e[1];
			$q = db_query("SELECT * FROM canali WHERE chat_id = ?", [$chat_id], true);
			$type = "channel";
			if (!isset($q['chat_id'])) {
				$q = db_query("SELECT * FROM gruppi WHERE chat_id = ?", [$chat_id], true);
				$type = "supergroup";
				if (!isset($q['chat_id'])) {
					cb_reply($cbid, "Chat non trovata nel database...", true);
					die;
				}
			}
			$q['admins'] = json_decode($q['admins'], true);
			if (!$q['admins']) {
				cb_reply($cbid, 'Lista amministratori non disponibile...', true);
			} else {
				$t = bold("👮🏻‍♂️ Amministratori di " . $q['title']);
				$q['admins'] = array_reverse($q['admins'], true);
				if ($mtt) {
					$plus = "➖";
					$p = false;
				} else {
					$plus = "➕";
					$p = true;
				}
				foreach($q['admins'] as $ad) {
					if (!isset(db_query("SELECT user_id FROM utenti WHERE user_id = ?", [$ad['user']['id']], true)['user_id'])) {
						unset($new);
						if ($ad['user']['is_bot']) {
							$new[$botID] = "bot";
						} else {
							$new[$botID] = "visto";
						}
						db_query("INSERT INTO utenti (user_id, nome, cognome, username, lang, page, status, first_update, last_update) VALUES (?,?,?,?,?,?,?,?,?)", [$ad['user']['id'], $ad['user']['first_name'], $ad['user']['last_name'], $ad['user']['username'], 'en', '', json_encode($new), time(), time()], "no");
					}
					if ($mtt) {
						$menu[] = [
							[
								"text" => $ad['user']['first_name'] . " " . $ad['user']['last_name'],
								"callback_data" => "updateuser_" . $ad['user']['id']
							]
						];
					} else {
						if ($ad['is_anonymous']) {
							$isanon = italic("(Anonimo)");
						} else {
							unset($isanon);
						}
						$t .= "\n• " . tag($ad['user']['id'], $ad['user']['first_name'] ,$ad['user']['last_name']) . " [" . code($ad['user']['id']) . "]\n" . $ad['status'] . " $isanon";
					}
				}
				$menu[] = [
					[
						"text" => "🔄",
						"callback_data" => $cbdata
					],
					[
						"text" => $plus,
						"callback_data" => "chatadmins_$chat_id" . "_$p"
					]
				];
				$menu[] = [
					[
						"text" => "🔙 Indietro",
						"callback_data" => "updatechat_$chat_id"
					]
				];
				cb_reply($cbid, '', false, $cbmid, $t, $menu);
			}
			die;
		}
		
		if (strpos($cbdata, "updateuser_") === 0) {
			$e = explode("_",$cbdata);
			$user_ID = $e[1];
			$q = db_query("SELECT * FROM utenti WHERE user_id = ?", [$user_ID], true);
			if (!isset($q['user_id'])) {
				cb_reply($cbid, "Utente non trovato nel database...", true);
				die;
			}
			if ($e[2] and $e[3] or $e[2]) {
				if ($config['usa_redis']) {
					if ($redis->get($cbdata) >= time()) {
						cb_reply($cbid, '⚠️ Please wait a second and try again...', false);
					} else {
						$redis->set($cbdata, time() + 10);
					}
				}
				$config['response'] = true;
				$user = getChat($user_ID);
				if ($user['ok'] === false) {
					cb_reply($cbid, "Non sono riuscito a ricevere le sue info!\n" . $user['description'], true);
				} else {
					$user = $user['result'];
					if (!$user['first_name']) {
						setStatus($user_ID, "deleted");
						$user['first_name'] = "Deleted account";
					}
					if (!$user['last_name']) {
						$user['last_name'] = "";
					}
					if (!$user['username']) {
						$user['username'] = "";
					}
					db_query("UPDATE utenti SET nome = ?, cognome = ?, username = ? WHERE user_id = ?", [$user['first_name'], $user['last_name'], $user['username'], $user_ID]);
					cb_reply($cbid, "Aggiornato", false);
				}
			} else {
				if ($cbid) cb_reply($cbid);				
			}
			if ($e[2] and $e[3]) {
				$cbdata = "gestione_" . $e[2] . "-" . $e[3];
			} else {
				if ($q['cognome']) $qcognome = "\nCognome: " . htmlspecialchars($q['cognome']);
				if ($q['username']) $qusername = "\nUsername: @" . htmlspecialchars($q['username']);
				$q['status'] = json_decode($q['status'], true);
				if (!is_array($q['status'])) $q['status'] = [];
				$q['settings'] = json_decode($q['settings'], true);
				if (!is_array($q['settings'])) $q['settings'] = [];
				$ulanguage = "\nLingua: " . $q['lang'];
				$stati_user = [
					'blocked' => "Bloccato dall'utente",
					'deleted' => "Account eliminato",
					'bot' => "Bot",
					'avviato' => "Avviato",
					'attivo' => "Non avviato",
					'visto' => "Mai incontrato",
					'ban' => "Bannato fino a tempo indefinito"
				];
				if ($stati_user[$q['status'][$botID]]) {
					$stat = $stati_user[$q['status'][$botID]];
				} elseif (strpos($q['status'][$botID], "ban") === 0) {
					$dateban = str_replace("ban", '', $q['status'][$botID]);
					$stat = "Bannato fino al " . date("j M Y", $dateban) . " alle " . date("H:i", $dateban);
				} else {
					$stat = json_encode($q['status']);
				}
				$ustato = "\nStato: $stat";
				$menu[] = [
					[
						"text" => "👮🏻‍♂️ Amministrazione",
						"callback_data" => "useradmins_$user_ID"
					]
				];
				$menu[] = [
					[
						"text" => "🔄 Aggiorna dati",
						"callback_data" => "updateuser_$user_ID"
					],
					[
						"text" => "🗑 Elimina",
						"callback_data" => "deluser_$user_ID"
					]
				];
				editMsg($chatID, bold("Informazioni utente") . "\nID: $user_ID \nNome: " . htmlspecialchars($q['nome']) . $qcognome . $qusername . $ulanguage . $ustato, $cbmid, $menu);
				die;
			}
		}
		
		if (strpos($cbdata, "updatechat_") === 0) {
			$e = explode("_", $cbdata);
			$chat_ID = $e[1];
			$config['response'] = true;
			$q = db_query("SELECT * FROM canali WHERE chat_id = ?", [$chat_ID], true);
			$type = "channel";
			if (!isset($q['chat_id'])) {
				$q = db_query("SELECT * FROM gruppi WHERE chat_id = ?", [$chat_ID], true);
				$type = "supergroup";
				if (!isset($q['chat_id'])) {
					cb_reply($cbid, "Chat non trovata nel database", true);
					die;
				}
			}
			if ($e[2] and $e[3] or $e[2]) {
				if ($config['usa_redis']) {
					if ($redis->get($cbdata) >= time()) {
						cb_reply($cbid, '⚠️ Hai aggiornato già meno di 10 secondi fa...', false);
						die;
					} else {
						$redis->set($cbdata, time() + 10);
					}
				}
				$chat = getChat($chat_ID);
				if ($chat['ok'] === false) {
					cb_reply($cbid, "Non sono riuscito a ricevere le informazioni di questa chat! \n" . $chat['description'], true);
				} else {
					$chat = $chat['result'];
					$title = $chat['title'];
					$type = $chat['type'];
					if (isset($chat['username'])) $usernamechat = $chat['username'];
					else $usernamechat = "";
					$descrizione = $chat['description'];
					if (!isset($descrizione)) {
						$descrizione = "";
					}
					$admins = getAdmins($chat_ID);
					if (isset($admins['ok'])) {
						$adminsg = json_encode($admins['result']);
					} else {
						$adminsg = "[]";
					}
					if ($type == "channel") {
						db_query("UPDATE canali SET title = ?, username = ?, admins = ?, description = ? WHERE chat_id = ?", [$title, $usernamechat, $adminsg, $descrizione, $chat_ID]);
						$q = db_query("SELECT * FROM canali WHERE chat_id = ?", [$chat_ID], true);
					} else {
						if (isset($chat['permissions'])) {
							$perms = $chat['permissions'];
						} else {
							$perms = ["can_send_messages" => true, "can_send_media_messages" => true, "can_send_polls" => true, "can_send_other_messages" => true, "can_add_web_page_previews" => true, "can_change_info" => false, "can_invite_users" => false, "can_pin_messages" => false];
						}
						db_query("UPDATE gruppi SET title = ?, username = ?, admins = ?, description = ?, permissions = ? WHERE chat_id = ?", [$title, $usernamechat, $adminsg, $descrizione, json_encode($perms), $chat_ID]);
						$q = db_query("SELECT * FROM gruppi WHERE chat_id = ?", [$chat_ID], true);
					}
					cb_reply($cbid, "Aggiornato", false);
				}
			} else {
				if ($cbid) cb_reply($cbid);				
			}
			if ($e[2] and $e[3]) {
				$cbdata = "gestione_" . $e[2] . "-" . $e[3];
			} else {
				$q['status'] = json_decode($q['status'], true);
				if (!is_array($q['status'])) $q['status'] = [];
				if ($type == "channel") {
					if ($q['username']) $qusername = "\nUsername: @" . htmlspecialchars($q['username']);
					if ($q['description']) $qdescrizione = "\nDescrizione: " . htmlspecialchars($q['description']);
					$stati_chat = [
						'avviato' => "Avviato",
						'attivo' => "Bot non membro",
						'inattivo' => "Bot rimosso dalla chat",
						'kicked' => "Bot bannato dalla chat",
						'visto' => "Bot mai entrato",
						'ban' => "Bannato fino a tempo indefinito"
					];
					if ($stati_chat[$q['status'][$botID]]) {
						$stat = $stati_chat[$q['status'][$botID]];
					} elseif (strpos($q['status'][$botID], "ban") === 0) {
						$dateban = str_replace("ban", '', $q['status'][$botID]);
						$stat = "Bannato fino al " . date("j M Y", $dateban) . " alle " . date("H:i", $dateban);
					} else {
						$stat = json_encode($q['status']);
					}
					$ustato = "\nStato: $stat";
					$menu[] = [
						[
							"text" => "👮🏻‍♂️ Amministratori",
							"callback_data" => "chatadmins_$chat_ID"
						],
						[
							"text" => "🚮 Lascia chat",
							"callback_data" => "leavechat_$chat_ID"
						]
					];
					$menu[] = [
						[
							"text" => "🔄 Aggiorna dati",
							"callback_data" => "updatechat_$chat_ID"
						],
						[
							"text" => "🗑 Elimina",
							"callback_data" => "delchat_$chat_ID"
						]
					];
					editMsg($chatID, bold("Informazioni canale") . "\nID: $chat_ID \nTitolo: " . htmlspecialchars($q['title']) . $qdescrizione . $qusername . $ustato, $cbmid, $menu);
				} else {
					if ($q['username']) $qusername = "\nUsername: @" . htmlspecialchars($q['username']);
					if ($q['description']) $qdescrizione = "\nDescrizione: " . htmlspecialchars($q['description']);
					$stati_chat = [
						'avviato' => "Avviato",
						'attivo' => "Bot non membro",
						'inattivo' => "Bot rimosso dalla chat",
						'kicked' => "Bot bannato dalla chat",
						'visto' => "Bot mai entrato",
						'ban' => "Bannato fino a tempo indefinito"
					];
					if ($stati_chat[$q['status'][$botID]]) {
						$stat = $stati_chat[$q['status'][$botID]];
					} elseif (strpos($q['status'][$botID], "ban") === 0) {
						$dateban = str_replace("ban", '', $q['status'][$botID]);
						$stat = "Bannato fino al " . date("j M Y", $dateban) . " alle " . date("H:i", $dateban);
					} else {
						$stat = json_encode($q['status']);
					}
					$ustato = "\nStato: $stat";
					$menu[] = [
						[
							"text" => "👮🏻‍♂️ Amministratori",
							"callback_data" => "chatadmins_$chat_ID"
						],
						[
							"text" => "🚮 Lascia chat",
							"callback_data" => "leavechat_$chat_ID"
						]
					];
					$menu[] = [
						[
							"text" => "🔄 Aggiorna dati",
							"callback_data" => "updatechat_$chat_ID"
						],
						[
							"text" => "🗑 Elimina",
							"callback_data" => "delchat_$chat_ID"
						]
					];
					editMsg($chatID, bold("Informazioni gruppo") . "\nID: $chat_ID \nTitolo: " . htmlspecialchars($q['title']) . $qdescrizione . $qusername . $ustato, $cbmid, $menu);
				}
				die;
			}
		}	
		
		if (strpos($cbdata, "infouser_") === 0) {
			$e = explode("_", $cbdata);
			$id = $e[1];
			$user = db_query("SELECT * FROM utenti WHERE user_id = ?", [$id], true);
			$user['status'] = json_decode($user['status'], true);
			$testo = "Nome: " . $user['nome'];
			if ($user['cognome']) $testo .= " ".$user['cognome'];
			if ($user['username']) $testo .= " \nUsername: @".$user['username'];
			$testo .= " \nPage: '" . $user['page'] . "'";
			$stati_user = [
				'blocked' => "Bloccato dall'utente",
				'deleted' => "Account eliminato",
				'bot' => "Bot",
				'avviato' => "Avviato",
				'attivo' => "Non avviato",
				'visto' => "Mai incontrato",
				'ban' => "Bannato fino a tempo indefinito"
			];
			if (!isset($user['status'][$botID])) {
				$stat = $stati_user['visto'];
			} elseif ($stati_user[$user['status'][$botID]]) {
				$stat = $stati_user[$user['status'][$botID]];
			} elseif (strpos($user['status'][$botID], "ban") === 0) {
				$dateban = str_replace("ban", '', $user['status'][$botID]);
				$stat = "Bannato fino al " . date("j M Y", $dateban) . " alle " . date("H:i", $dateban);
			} else {
				$stat = json_encode($user['status']);
			}
			$testo .= "\nStato: $stat";
			$user['settings'] = json_decode($user['settings'], true);
			cb_reply($cbid, "Informazioni Utente \n$testo", true);
			if ($e[2] and $e[3]) {
				unset($testo);
				unset($cbid);
				$cbdata = "gestione_" . $e[2] . "-" . $e[3];
			} else {
				die;
			}
		}
		
		if (strpos($cbdata, "infochat_") === 0) {
			$e = explode("_", $cbdata);
			$id = $e[1];
			$chat = db_query("SELECT * FROM canali WHERE chat_id = ?", [$id], true);
			if (!isset($chat['chat_id'])) {
				$chat = db_query("SELECT * FROM gruppi WHERE chat_id = ?", [$id], true);
				if (!isset($chat['chat_id'])) {
					cb_reply($cbid, "Chat non trovata nel database", true);
					die;
				}
			}
			$chat['status'] = json_decode($chat['status'], true);
			$testo = "Titolo: " . $chat['title'];
			if ($chat['username']) $testo .= " \nUsername: @".$chat['username'];
			$admi = json_decode($chat['admins'], true);
			if (isset($admi['result'])) $admi = $admi['result'];
			foreach ($admi as $adminsa) {
				if ($adminsa['status'] == 'creator') {
					$founder = $adminsa['user']['first_name'] . " [" . $adminsa['user']['id'] . "]";
				}
			}
			$testo .= "\nCreatore: ".$founder;
			$testo .= " \nPage: '" . $chat['page'] . "'";
			$stati_chat = [
				'avviato' => "Avviato",
				'attivo' => "Bot non membro",
				'inattivo' => "Bot rimosso dalla chat",
				'kicked' => "Bot bannato dalla chat",
				'visto' => "Bot mai entrato",
				'ban' => "Bannato fino a tempo indefinito"
			];
			if (!isset($chat['status'][$botID])) {
				$stat = $stati_chat['visto'];
			} elseif ($stati_chat[$chat['status'][$botID]]) {
				$stat = $stati_chat[$chat['status'][$botID]];
			} elseif (strpos($chat['status'][$botID], "ban") === 0) {
				$dateban = str_replace("ban", '', $chat['status'][$botID]);
				$stat = "Bannato fino al " . date("j M Y", $dateban) . " alle " . date("H:i", $dateban);
			} else {
				$stat = json_encode($chat['status']);
			}
			$testo .= "\nStato: $stat";
			cb_reply($cbid, "Informazioni Chat \n$testo", true);
			if ($e[2] and $e[3]) {
				$cbdata = "gestione_" . $e[2] . "-" . $e[3];
			} else {
				die;
			}
		}
		
		if (strpos($cbdata, "banchat_") === 0) {
			$e = explode("_", $cbdata);
			$id = $e[1];
			$chat = db_query("SELECT * FROM canali WHERE chat_id = ?", [$id], true);
			$db = "canali";
			if (!isset($chat['chat_id'])) {
				$chat = db_query("SELECT * FROM gruppi WHERE chat_id = ?", [$id], true);
				$db = "gruppi";
				if (!isset($chat['chat_id'])) {
					cb_reply($cbid, "Chat non trovata nel database", true);
					die;
				}
			}
			$chat['status'] = json_decode($chat['status'], true);
			if (strpos($chat['status'][$botID], "ban") === 0) {
				$ban = "unban";
				db_query("UPDATE $db SET status = ? WHERE chat_id = ?", [json_encode([$botID => 'attivo']), $id]);
				if ($config['usa_redis']) {
					$redis->del($id);
				}
			} else {
				$ban = "ban";
				foreach (array_keys($config['cloni']) as $idBot) {
					$new[$idBot] = "ban";
				}
				db_query("UPDATE $db SET status = ? WHERE chat_id = ?", [json_encode($new), $id]);
			}
			cb_reply($cbid, "$ban dai $db", true);
			$tag = $chat['title'];
			if ($chat['username']) $tag = text_link($tag, "https://t.me/" . $chat['username']);
			botlog("Gestione database\n" . bold("Chat bannata: ") . $tag . " [" . code($chat['chat_id']) . "] " . bold("\nData: ") . date("d/m/Y h:i:s") . bold("\nBan di: ") .tag(), [$ban, "id$userID"]);
			if ($e[2] and $e[3]) {
				$cbdata = "gestione_" . $e[2] . "-" . $e[3];
			} else {
				die;
			}
		}
		
		if (strpos($cbdata, "banuser_") === 0) {
			$e = explode("_", $cbdata);
			$id = $e[1];
			$user = db_query("SELECT * FROM utenti WHERE user_id = ?", [$id], true);
			$user['status'] = json_decode($user['status'], true);
			if (strpos($user['status'][$botID], "ban") === 0) {
				$ban = "sbannato";
				db_query("UPDATE utenti SET status = ? WHERE user_id = ?", [json_encode([$botID => 'attivo']), $id], "no");
			} else {
				$ban = "bannato";
				foreach (array_keys($config['cloni']) as $idBot) {
					$new[$idBot] = "ban";
				}
				db_query("UPDATE utenti SET status = ? WHERE user_id = ?", [json_encode($new), $id], "no");
			}
			cb_reply($cbid, "Utente $ban", true);
			if ($e[2] and $e[3]) {
				$cbdata = "gestione_" . $e[2] . "-" . $e[3];
			} else {
				die;
			}
		}
		
		if (strpos($cbdata, "leavechat_") === 0) {
			$e = explode("_", $cbdata);
			$id = $e[1];
			$chat = db_query("SELECT * FROM canali WHERE chat_id = ?", ["$id"], true);
			$db = "canali";
			if (!isset($chat['chat_id'])) {
				$chat = db_query("SELECT * FROM gruppi WHERE chat_id = ?", ["$id"], true);
				$db = "gruppi";
				if (!isset($chat['chat_id'])) {
					cb_reply($cbid, "Chat non trovata nel database", true);
					die;
				}
			}
			$config['json_payload'] = false;
			$lc = lc($chat['chat_id']);
			if ($lc['ok']) {
				cb_reply($cbid, "Ho abbandonato questa chat", false);
			} else {
				cb_reply($cbid, "Errore: " . $lc['description']);
			}
			if ($e[2] and $e[3]) {
				$cbdata = "gestione_" . $e[2] . "-" . $e[3];
			} else {
				die;
			}
		}

		if (strpos($cbdata, "deluser_") === 0) {
			$e = explode("_", $cbdata);
			$id = $e[1];
			$user = db_query("SELECT * FROM utenti WHERE user_id = ?", [$id], true);
			if (!isset($user['user_id'])) {
				cb_reply($cbid, "Utente non trovato nel database", true);
				die;
			}
			db_query("DELETE FROM utenti WHERE user_id = ?", [$id], 'no');
			cb_reply($cbid, "Ho eliminato questo utente dal database", false);
			if ($e[2] and $e[3]) {
				$cbdata = "gestione_" . $e[2] . "-" . $e[3];
			} else {
				cb_reply($cbid);
				dm($chatID, $cbmid);
				die;
			}
		}
		
		if (strpos($cbdata, "delchat_") === 0) {
			$e = explode("_", $cbdata);
			$id = $e[1];
			$chat = db_query("SELECT * FROM canali WHERE chat_id = ?", [$id], true);
			$db = "canali";
			if (!isset($chat['chat_id'])) {
				$chat = db_query("SELECT * FROM gruppi WHERE chat_id = ?", [$id], true);
				$db = "gruppi";
				if (!isset($chat['chat_id'])) {
					cb_reply($cbid, "Chat non trovata nel database", true);
					die;
				}
			}
			db_query("DELETE FROM $db WHERE chat_id = ?", [$id]);
			cb_reply($cbid, "Ho eliminato questa chat dal database", false);
			if ($e[2] and $e[3]) {
				$cbdata = "gestione_" . $e[2] . "-" . $e[3];
			} else {
				cb_reply($cbid);
				dm($chatID, $cbmid);
				die;
			}
		}
		
		if (strpos($cbdata, "gestione_") === 0) {
			$idb = ['utenti', 'gruppi', 'canali'];
			$e = explode("-", str_replace("gestione_", '', $cbdata));
			$db = $e[0];
			$page = $e[1];
			if ($page == 1) {
				$limit = 5;
			} elseif (is_numeric($page)) {
				$limit = 5 * $page;
			} else {
				$limit = 1000;
			}
			if (in_array($db, $idb)) {
				$limit = $limit + 1;
				$cosi = db_query("SELECT * FROM $db LIMIT $limit", false, false);
				$ultimo = (5 * $page) - 1;
				$primo = $ultimo - 4;
				$range = range($primo, $ultimo);
				unset($testo);
				foreach ($range as $num) {
					$coso = $cosi[$num];
					if (isset($coso['user_id'])) {
						if ($cognome) $coso['nome'] .= " " . $coso['cognome'];
						$num = $num + 1;
						if ($num < 10) $num .= "️⃣";
						$coso['status'] = json_decode($coso['status'], true);
						if ($coso['status'][$botID] == "bot") {
							$emo = "🤖 ";
						} else {
							$emo = "👤 ";
						}
						$testo .=  "\n$emo" . textspecialchars($coso['nome']) . " [".code($coso['user_id']) . "]";
						$leggenda = italic("🔄 Aggiorna informazioni \nℹ️ Informazioni utente \n🗑 Elimina l'utente \n🚷 Banna utente");
						$menu[] = [
							[
								"text" => $num,
								"callback_data" => "updateuser_" . $coso['user_id']
							],
							[
								"text" => "🔄",
								"callback_data" => "updateuser_" . $coso['user_id'] . "_$db" . "_$page"
							],
							[
								"text" => "ℹ️",
								"callback_data" => "infouser_" . $coso['user_id'] . "_$db" . "_$page"
							],
							[
								"text" => "🗑",
								"callback_data" => "deluser_" . $coso['user_id'] . "_$db" . "_$page"
							],
							[
								"text" => "🚷",
								"callback_data" => "banuser_" . $coso['user_id'] . "_$db" . "_$page"
							]
						];
					} elseif (isset($coso['chat_id'])) {
						$leggenda = italic("🔄 Aggiorna informazioni \nℹ️ Informazioni sulla chat \n🚮 Lascia la chat \n🗑 Elimina la chat \n🚷 Banna chat");
						$num = $num + 1;
						if ($num < 10) $num .= "️⃣";
						$menu[] = [
							[
								"text" => $num,
								"callback_data" => "updatechat_" . $coso['chat_id']
							],
							[
								"text" => "🔄",
								"callback_data" => "updatechat_" . $coso['chat_id'] . "_$db" . "_$page"
							],
							[
								"text" => "ℹ️",
								"callback_data" => "infochat_" . $coso['chat_id'] . "_$db" . "_$page"
							],
							[
								"text" => "🚮",
								"callback_data" => "leavechat_" . $coso['chat_id'] . "_$db" . "_$page"
							],
							[
								"text" => "🗑",
								"callback_data" => "delchat_" . $coso['chat_id'] . "_$db" . "_$page"
							],
							[
								"text" => "🚷",
								"callback_data" => "banchat_" . $coso['chat_id'] . "_$db" . "_$page"
							]
						];
						$testo .=  "\n".textspecialchars($coso['title']) . " [".code($coso['chat_id']) . "]";
					}
				}
				if ($page !== "1") {
					$menufrecce[] = [
						"text" => "⏪",
						"callback_data" => "gestione_$db-" . ($page - 1)
					];
				}
				if (isset($cosi[$ultimo + 1])) {
					$menufrecce[] = [
						"text" => "⏺",
						"callback_data" => "gespag_$db"
					];
					$menufrecce[] = [
						"text" => "⏩",
						"callback_data" => "gestione_$db-" . ($page + 1)
					];
				}
				if (isset($menufrecce)) $menu[] = $menufrecce;
				$menu[] = [
					[
						"text" => "🔙 Indietro",
						"callback_data" => "gestione"
					]
				];
				cb_reply($cbid, '', false, $cbmid, bold("Gestione $db\n") . $testo . "\n\n$leggenda", $menu);
			} else {
				cb_reply($cbid, "Errore: database sconosciuto");
			}
			die;
		}
		
		if ($cmd == "post" and $typechat == "private") {
			$config['json_payload'] = false;
			$cbmid = sm($chatID, "Carico...")['result']['message_id'];
			db_query("UPDATE utenti SET page = ? WHERE user_id = ?", ["post", $userID], 'no');
			$cbdata = "post_settings";
		}
		
		if (strpos($cbdata, "post_") === 0) {
			$command = str_replace("post_", '', $cbdata);
			if (strpos($command, 'format') === 0) {
				if (strpos($command, 'format_') === 0) {
					if (str_replace("format_", '', $command) !== 0) {
						$u['settings']['global_post']['format'] = str_replace("format_", '', $command);
					} else {
						unset($u['settings']['global_post']['format']);
					}
					db_query("UPDATE utenti SET settings = ? WHERE user_id = ?", [json_encode($u['settings']), $userID], false);
				}
				$select[$u['settings']['global_post']['format']] = "✅";
				$menu[] = [
					[
						"text" => "HTML " . $select['html'],
						"callback_data" => "post_format_html"
					],
					[
						"text" => "Markdown " . $select['markdown'],
						"callback_data" => "post_format_markdown"
					],
					[
						"text" => "MarkdownV2 " . $select['markdownv2'],
						"callback_data" => "post_format_markdownv2"
					]
				];
				$menu[] = [
					[
						"text" => "Niente " . $select['noformat'],
						"callback_data" => "post_format_noformat"
					],
					[
						"text" => "Default " . $select[0],
						"callback_data" => "post_format_0"
					]
				];
				$menu[] = [
					[
						"text" => "🔙 Ritorna al post",
						"callback_data" => "post_settings"
					]
				];
				cb_reply($cbid, '', false, $cbmid, bold("#️⃣ Formattazione") . "\nScegli la formattazione del post globale:", $menu);
			} elseif (strpos($command, 'silenzioso') === 0) {
				if (strpos($command, 'silenzioso_') === 0) {
					if (str_replace("silenzioso_", '', $command) !== 0) {
						$u['settings']['global_post']['silenzioso'] = str_replace("silenzioso_", '', $command);
					} else {
						unset($u['settings']['global_post']['silenzioso']);
					}
					db_query("UPDATE utenti SET settings = ? WHERE user_id = ?", [json_encode($u['settings']), $userID], false);
				}
				$select[$u['settings']['global_post']['silenzioso']] = "✅";
				$menu[] = [
					[
						"text" => "🔈 " . $select[1],
						"callback_data" => "post_silenzioso_1"
					],
					[
						"text" => "🔊 " . $select[0],
						"callback_data" => "post_silenzioso_0"
					]
				];
				$menu[] = [
					[
						"text" => "Default " . $select[$config['disabilita_notifica']],
						"callback_data" => "post_silenzioso_" . $config['disabilita_notifica']
					]
				];
				$menu[] = [
					[
						"text" => "🔙 Ritorna al post",
						"callback_data" => "post_settings"
					]
				];
				cb_reply($cbid, '', false, $cbmid, bold("🔔 Silenzioso") . "\nScegli se far partire il tono di notifica nel post globale:", $menu);
			} elseif (strpos($command, 'anteprima') === 0) {
				if (strpos($command, 'anteprima_') === 0) {
					if (str_replace("anteprima_", '', $command) !== 0) {
						$u['settings']['global_post']['anteprima'] = str_replace("anteprima_", '', $command);
					} else {
						unset($u['settings']['global_post']['anteprima']);
					}
					db_query("UPDATE utenti SET settings = ? WHERE user_id = ?", [json_encode($u['settings']), $userID], false);
				}
				$select[$u['settings']['global_post']['anteprima']] = "✅";
				$menu[] = [
					[
						"text" => "Mostra " . $select[0],
						"callback_data" => "post_anteprima_0"
					],
					[
						"text" => "Nascondi " . $select[1],
						"callback_data" => "post_anteprima_1"
					]
				];
				$menu[] = [
					[
						"text" => "Default " . $select[$config['disabilita_anteprima_link']],
						"callback_data" => "post_anteprima_" . $config['disabilita_anteprima_link']
					]
				];
				$menu[] = [
					[
						"text" => "🔙 Ritorna al post",
						"callback_data" => "post_settings"
					]
				];
				cb_reply($cbid, '', false, $cbmid, bold("ℹ️ Anteprima link") . "\nScegli se mostrare o meno l'anteprima link nel post globale:", $menu);
			} elseif (strpos($command, 'db') === 0) {
				if (strpos($command, 'db_') === 0) {
					$dbs = str_replace("db_", '', $command);
					if (in_array($dbs, $u['settings']['global_post']['db_selected'])) {
						$u['settings']['global_post']['db_selected'] = array_diff($u['settings']['global_post']['db_selected'], [$dbs]);
					} else {
						$u['settings']['global_post']['db_selected'][] = str_replace("db_", '', $command);
					}
					db_query("UPDATE utenti SET settings = ? WHERE user_id = ?", [json_encode($u['settings']), $userID], false);
				}
				if (in_array('utenti', $u['settings']['global_post']['db_selected'])) $select['utenti'] = "✅";
				if (in_array('gruppi', $u['settings']['global_post']['db_selected'])) $select['gruppi'] = "✅";
				if (in_array('canali', $u['settings']['global_post']['db_selected'])) $select['canali'] = "✅";
				$menu[] = [
					[
						"text" => "Utenti " . $select['utenti'],
						"callback_data" => "post_db_utenti"
					]
				];
				$menu[] = [
					[
						"text" => "Gruppi " . $select['gruppi'],
						"callback_data" => "post_db_gruppi"
					],
					[
						"text" => "Canali " . $select['canali'],
						"callback_data" => "post_db_canali"
					]
				];
				$menu[] = [
					[
						"text" => "🔙 Ritorna al post",
						"callback_data" => "post_settings"
					]
				];
				cb_reply($cbid, '', false, $cbmid, bold("🗃 Seleziona database") . "\nScegli su quali tipo di chat vuoi mandare il post globale:", $menu);
			} elseif ($command == "settings") {
				if ($u['settings']['global_post']['silenzioso']) {
					$emojis = "🔈";
				} else {
					$emojis = "🔊";
				}
				if ($u['settings']['global_post']['anteprima']) {
					$emojia = "❎";
				} else {
					$emojia = "✅";
				}
				$forms = [0 => "Default", 'html' => "HTML", 'markdown' => "Markdown", 'markdownv2' => "MarkdownV2", 'noformat' => "Niente"];
				$format = $forms[$u['settings']['global_post']['format']];
				if ($u['settings']['global_post']['db_selected']) {
					foreach ($u['settings']['global_post']['db_selected'] as $tdb) {
						if (!$db) {
							$db = $tdb;
						} else {
							$db .= ", $tdb";
						}
					}
				} else {
					$db = "Nessun database selezionato.";
				}
				$menu[] = [
					[
						"text" => "🗃 Database",
						"callback_data" => "post_db"
					]
				];
				$menu[] = [
					[
						"text" => "🔔 Silenzioso",
						"callback_data" => "post_silenzioso"
					],
					[
						"text" => "ℹ️ Anteprima link",
						"callback_data" => "post_anteprima"
					]
				];
				$menu[] = [
					[
						"text" => "#️⃣ Formattazione",
						"callback_data" => "post_format"
					]
				];
				cb_reply($cbid, '', false, $cbmid, "📟 Seleziona dove inviare il post: \nDatabase: $db\nForamttazione: $format \nAnteprima Link: $emojia \nNotifica: $emojis \n" . italic("Dopo la configurazione del post inviami il messaggio da distribuire."), $menu);
			} else {
				cb_reply($cbid, 'Campo inesistente sul codice, forse non hai aggiornato gestione.php...', true);
			}
			die;
		}
		
		if ($u['page'] == "post" and !$cmd and !$cbdata and $typechat == "private") {
			db_query("UPDATE utenti SET page = ? WHERE user_id = ?", ['', $userID], 'no');
			$config['json_payload'] = false;
			$cbmid = sm($chatID, italic("Questa operazione potrebbe richiedere diverso tempo per essere completato..."))['result']['message_id'];
			$menuc[] = [
				[
					"text" => "⛔️ Annulla ⛔️",
					"callback_data" => "ferma"
				]
			];
			$users = [];
			if (isset($u['settings']['global_post']['silenzioso'])) {
				$config['disabilita_notifica'] = $u['settings']['global_post']['silenzioso'];
			}
			if (isset($u['settings']['global_post']['anteprima'])) {
				$config['disabilita_anteprima_link'] = $u['settings']['global_post']['anteprima'];
			}
			if (isset($u['settings']['global_post']['format'])) {
				if ($u['settings']['global_post']['format'] !== "noformat") {
					$config['parse_mode'] = $u['settings']['global_post']['format'];
				} else {
					$config['parse_mode'] = '';
				}
			}
			if (!$u['settings']['global_post']['db_selected']) {
				editMsg($chatID, "Non hai selezionato alcun database...", $cbmid);
				die;
			}
			foreach($u['settings']['global_post']['db_selected'] as $db) {
				if ($db == "utenti") {
					$chats = db_query("SELECT * FROM $db", false, false);
				} else {
					if ($config['console']) $chats = db_query("SELECT * FROM $db WHERE chat_id != ?", [$config['console']], false);
					else db_query("SELECT * FROM $db", false, false);
				}
				$users = array_merge_recursive($users, $chats);
			}
			if (!$users) {
				editMsg($chatID, "Non sono riuscito ad ottenere chat dove inviare il post.", $cbmid);
				die;
			}
			$config['console'] = false;
			if (file_exists('ferma')) unlink("ferma");
			editMsg($chatID, "Invio del post iniziato...", $cbmid, $menu);
			$time = time();
			$time_start = microtime(true);
			foreach ($users as $user) {
				if (!file_exists("ferma")) {
					$user['status'] = json_decode($user['status'], true);
					if (!in_array($user['status'][$botID], ['deleted', 'ban']) or strpos("ban", $user['status']) !== 0) {
						if (isset($user['user_id'])) {
							$chat = $user['user_id'];
							$title = $user['nome'] . " ".$user['cognome'];
						} else {
							$chat = $user['chat_id'];
							$title = $user['title'];
						}
						if ($messageType == "text message") {
							$m = sm($chat, $msg);
						} elseif ($messageType == "sticker" or $messageType == "animated sticker") {
							$m = ss($chat, $sticker);
						} elseif ($messageType == "gif") {
							$m = sgif($chat, $file_id, $caption);
						} elseif ($messageType == "photo") {
							$m = sp($chat, $foto_id, $caption);
						} elseif ($messageType == "video") {
							$m = sv($chat, $video_id, $caption);
						} elseif ($messageType == "video_note") {
							$m = svr($chat, $video_note_id);
						} elseif ($messageType == "voice") {
							$m = sav($chat, $vocale_id, $caption);
						} elseif ($messageType == "audio") {
							$m = sa($chat, $audio_id, $caption);
						} elseif ($messageType == "contact") {
							$m = sc($chat, $contact, $cnome, $ccognome);
						} elseif ($messageType == "venue") {
							$m = sven($chat, $posizione['latitude'], $posizione['longitude'], $posto, $address);
						} elseif ($messageType == "location") {
							$m = sendLocation($chat, $posizione['latitude'], $posizione['longitude']);
						} elseif ($messageType == "document") {
							$m = sd($chat, $file_id, $caption);
						} else {
							sm($chatID, "Tipo di messaggio non supportato per il post globale.");
							die;
						}
						if ($m['ok']) {
							$cstati['avviato'][] = $chat;
							setStatus($chat, 'avviato');
						} else {
							if ($m['description'] == "Forbidden: bot can't send messages to bots") {
								setStatus($chat, 'bot');
								$cstati['bot'][] = $chat;
							} elseif ($m['description'] == "Forbidden: bot can't send messages to the user") {
								setStatus($chat, 'visto');
								$cstati['visto'][] = $chat;
							} elseif ($m['description'] == "Forbidden: bot was blocked by the user") {
								setStatus($chat, 'blocked');
								$cstati['blocked'][] = $chat;
							} elseif ($m['description'] == "Forbidden: bot can't initiate conversation with a user") {
								setStatus($chat, 'attivo');
								$cstati['attivo'][] = $chat;
							} elseif ($m['description'] == "Forbidden: user is deactivated") {
								setStatus($chat, 'deleted');
								$cstati['deleted'][] = $chat;
							} elseif (strpos($m['description'], "Forbidden: bot was kicked from the") === 0) {
								setStatus($chat, 'kicked');
								$cstati['kicked'][] = $chat;
							} elseif (strpos($m['description'], "Forbidden: bot is not a member of the") === 0) {
								setStatus($chat, 'attivo');
								$cstati['attivo'][] = $chat;
							} elseif (strpos($m['description'], "Bad Request: chat not found") === 0) {
								setStatus($chat, 'visto');
								$cstati['visto'][] = $chat;
							} else {
								$config['parse_mode'] = "html";
								sm($chatID, "[Fatal Error] Errore " . $m['error_code'] . " per $chat: " . code($m['description']), false, 'def');
								file_put_contents("ferma", 'fermati');
							}
						}
						$timet = time();
						if (isset($somma)) $somma = 0;
						foreach ($cstati as $cstato => $cschats) {
							$somma += count($cschats);
						}
						if ($thistime + 2 < time()) {
							$thistime = time();
							editMsg($chatID, bold("Invio del post") . "\nMessaggi inviati: " . count($cstati['avviato']) . "/$somma \n" . italic(date("d/m/Y H:i:s", $thistime)), $cbmid, $menuc);
						}
					}
				}
			}
			$time_now = microtime(true);
			$time_tot = round($time_now - $time_start, 2);
			editMsg($chatID, bold("Invio del post terminato!") . "\nMessaggi inviati: " . count($cstati['avviato']) . "/$somma \n" . italic(date("d/m/Y H:i:s", $thistime)), $cbmid);
			sm($chatID, "Finito di inviare il tuo post! \n" . bold("Tempo impiegato: ") . $time_tot);
			die;
		}
		
		# Gestione iscritti
		if ($cmd == "iscritti" or $cbdata == 'iscritti') {
			if ($cbdata) {
				cb_reply($cbid, 'Carico...', false);
				if ($config['devmode']) editMenu($chatID, $cbmid);
			} else {
				$config['json_payload'] = false;
				$m = sm($chatID, "Carico...");
				$cbmid = $m['result']['message_id'];
			}
			$menu[] = [
				[
					'text' => "Più informazioni ➕",
					'callback_data' => 'controllo_iscritti'
				]
			];
			$menu[] = [
				[
					'text' => "Controllo Inattivi 🔄",
					'callback_data' => 'controllo_inattivi'
				],
				[
					'text' => "Attività 🚸",
					'callback_data' => 'online_users'
				]
			];
			$menu[] = [
				[
					'text' => "Fatto ✅",
					'callback_data' => 'fatto'
				]
			];
			if ($config['devmode']) editMsg($chatID, 'Controllo il db utenti...', $cbmid);
			$substot = db_query("SELECT status FROM utenti", false, false);
			if ($substot) {
				$subs = 0;
				foreach ($substot as $user) {
					$user['status'] = json_decode($user['status'], true);
					if (!is_array($user['status'])) $user['status'] = [];
					if (!$user['status'][$botID]) {
						
					} elseif (strpos($user['status'][$botID], "ban") === 0) {
						
					} elseif (in_array($user['status'][$botID], ["deleted", "ban", "visto", "bot", "blocked"])) {
						
					} else {
						$subs += 1;
					}
				}
			}
			if ($config['devmode']) editMsg($chatID, 'Controllo il db gruppi...', $cbmid);
			$gruppitot = db_query("SELECT status FROM gruppi", false, false);
			if ($gruppitot) {
				$gruppi = 0;
				foreach ($gruppitot as $gruppo) {
					$gruppo['status'] = json_decode($gruppo['status'], true);
					if (!is_array($gruppo['status'])) $gruppo['status'] = [];
					if (!$gruppo['status'][$botID]) {
						
					} elseif (strpos($gruppo['status'][$botID], "ban") === 0) {
						
					} elseif (in_array($gruppo['status'][$botID], ["ban", "visto", "blocked"])) {
						
					} else {
						$gruppi += 1;
					}
				}
			}
			if ($config['post_canali']) {
				if ($config['devmode']) editMsg($chatID, 'Controllo il db canali...', $cbmid);
				$canalitot = db_query("SELECT status FROM canali", false, false);
				$canali = 0;
				if ($canalitot) {
					foreach ($canalitot as $canale) {
						$canale['status'] = json_decode($canale['status'], true);
						if (!is_array($canale['status'])) $canale['status'] = [];
						if (!$canale['status'][$botID]) {
							
						} elseif (strpos($canale['status'][$botID], "ban") === 0) {
							
						} elseif (in_array($canale['status'][$botID], ["ban", "visto", "blocked"])) {
							
						} else {
							$canali += 1;
						}
					}
				}
				$canali = "\n" . bold("📢 Canali: ") . round($canali) . "/" . count($canalitot);
			} else {
				$canali = "\n" . italic("📢 Canali non disponibili");
			}
			$testo = bold("ISCRITTI 👥") . "\n" . bold("👤 Utenti: ") . round($subs) . "/" . count($substot) . "\n" . bold("👥 Gruppi: ") . round($gruppi) . "/" . count($gruppitot) . $canali;
			editMsg($chatID, $testo, $cbmid, $menu);
			if ($cmd) dm($chatID, $msgID);
			die;
		}
		
		# Comando per vedere l'utenza attiva sul database
		if ($cbdata == "online_users") {
			cb_reply($cbid, "Carico...", false);
			if ($config['devmode']) editMenu($chatID, $cbmid);
			$config['response'] = true;
			$time = time();
			$res = db_query("SELECT * FROM utenti ORDER BY last_update DESC", false, false);
			foreach ($res as $dati) {
				if ($dati['last_update'] > $time - 60) {
					$onlinen['1min'] = $onlinen['1min'] + 1;
				}
				if ($dati['last_update'] > $time - 60 * 60) {
					$onlinen['1h'] = $onlinen['1h'] + 1;
				}
				if ($dati['last_update'] > $time - 24 * 60 * 60) {
					$onlinen['24h'] = $onlinen['24h'] + 1;
				}
				if ($dati['last_update'] > $time - 30 * 24 * 60 * 60) {
					$onlinen['30d'] = $onlinen['30d'] + 1;
				}
			}
			$menu[] = [
				[
					"text" => "Aggiorna 🔄",
					"callback_data" => $cbdata
				]
			];
			$menu[] = [
				[
					'text' => "🔙 Indietro",
					'callback_data' => 'iscritti'
				]
			];
			$testo = "<b>Iscritti online su</> @" . $config['username_bot'] . "\n";
			$testo .= $onlinen['1min'] . " utenti online nell'ultimo minuto\n";
			$testo .= $onlinen['1h'] . " utenti online nell'ultima ora\n";
			$testo .= $onlinen['24h'] . " utenti online nelle ultime 24 ore\n";
			$testo .= $onlinen['30d'] . " utenti online negli ultimi 30 giorni\n";
			$testo .= count($res) . " utenti totali.\n\n";
			$testo .= italic("🔄 Aggiornato il " . date("d/m/Y") . " alle " . date("h:i:s"));
			editMsg($chatID, $testo, $cbmid, $menu);
			die;
		}
		
		# Controllo dello stato degli utenti
		if ($cbdata == "controllo_iscritti") {
			cb_reply($cbid, "Carico...", false);
			if (file_exists('ferma')) unlink("ferma");
			$menuc[] = [
				[
					'text' => "⛔️ Annulla ⛔️",
					'callback_data' => 'ferma'
				]
			];
			if ($config['devmode']) editMenu($chatID, $cbmid, $menuc);
			$menu[] = [
				[
					'text' => "🔙 Indietro",
					'callback_data' => 'iscritti'
				]
			];
			$stati_user = [
				'blocked' => "Bloccato dall'utente",
				'deleted' => "Account eliminato",
				'bot' => "Bot",
				'avviato' => "Avviato",
				'attivo' => "Non avviato",
				'visto' => "Mai incontrato",
				'ban' => "Bannato a tempo indeterminato"
			];
			$stati_chat = [
				'avviato' => "Avviato",
				'attivo' => "Bot non membro",
				'inattivo' => "Bot rimosso dalla chat",
				'visto' => "Bot mai entrato",
				'ban' => "Bannato a tempo indeterminato"
			];
			$dbs = ["utenti", "gruppi"];
			if ($config['post_canali']) $dbs[] = "canali";
			foreach ($dbs as $db) {
				if (!file_exists("ferma")) {	
					$r = db_query("SELECT status FROM $db", false, false);
					if ($db == 'utenti') {
						$type = "user_id";
						$stati = $stati_user;
					} else {
						$type = "chat_id";
						$stati = $stati_chat;
					}
					unset($chats);
					if (isset($r[0]['status'])) {
						foreach ($r as $chat) {
							if (file_exists("ferma")) {
								
							} else {
								$chat['status'] = json_decode($chat['status'], true);
								if (!$chat['status'][$botID]) {
									$chat['status'][$botID] = "visto";
								}
								if ($stati[$chat['status'][$botID]]) {
									$stat = $stati[$chat['status'][$botID]];
								} elseif (strpos($chat['status'][$botID], "ban") === 0) {
									$dateban = str_replace("ban", '', $chat['status'][$botID]);
									$stat = "Bannato fino al " . date("j M Y", $dateban) . " alle " . date("H:i", $dateban);
								} else {
									$stat = "visto";
								}
								$chat = $chat[$type];
								$types[$db][$stat][] = $chat;
							}
						}
						foreach ($types[$db] as $type => $c) {
							$chats .= "\n$type: " . count($c);
						}
					}
					$db[0] = strtoupper($db[0]);
					$testo .= "\n\n" . bold($db) . " (" . count($r) . ")" . $chats;
				}
			}
			if (file_exists("ferma")) {
				editMsg($chatID, "Annullato...", $cbmid, $menu);
				unlink("ferma");
			} else {
				editMsg($chatID, bold("DATABASE ISCRITTI 👥") . $testo, $cbmid, $menu);
			}
			die;
		}
		
		# Aggiornamento stato utenti globale (può richiedere molto tempo)
		if ($cbdata == "controllo_inattivi") {
			$menuc[] = [
				[
					'text' => "❌ Annulla ❌",
					'callback_data' => 'ferma'
				]
			];
			cb_reply($cbid, 'Attendi', false, $cbmid, bold("Controllo...🕔") . italic("\nAttendi, questa operazione puó durare più di un minuto.") . "\n", $menuc);
			$dbs = ["utenti", "gruppi", "canali"];
			$nope = ["inattivo", "ban"];
			$config['console'] = false;
			$config['response'] = true;
			$config['disabilita_notifica'] = true;
			if (file_exists('ferma')) unlink("ferma");
			$stati_user = [
				'blocked' => "Bloccato dall'utente",
				'deleted' => "Account eliminato",
				'bot' => "Bot",
				'avviato' => "Avviato",
				'attivo' => "Non avviato",
				'visto' => "Mai incontrato",
				'ban' => "Bannato a tempo indeterminato"
			];
			$stati_chat = [
				'avviato' => "Avviato",
				'attivo' => "Bot non membro",
				'inattivo' => "Bot non membro",
				'kicked' => "Bot rimosso dalla chat",
				'visto' => "Bot mai entrato",
				'ban' => "Bannato a tempo indeterminato"
			];
			$message = [
				'utenti' => bold("Utenti") . italic("\nDatabase vuoto"),
				'gruppi' => bold("Gruppi") . italic("\nDatabase vuoto"),
				'canali' => bold("Canali") . italic("\nDatabase vuoto")
			];
			foreach ($dbs as $db) {
				if ($exdb) {
					$message[$exdb] = $messageup;
					unset($messageup);
					unset($contati);
					unset($cstati);
				}
				$r = db_query("SELECT * FROM $db", false, false);
				$tot = count($r);
				$exdb = $db;
				if ($db == 'utenti') {
					$type = "user_id";
					$stati = $stati_user;
				} else {
					$type = "chat_id";
					$stati = $stati_chat;
				}
				if ($r[0][$type]) {
					$tbname = $db;
					$tbname[0] = strtoupper($tbname);
					foreach ($r as $chat) {
						if (!file_exists('ferma')) {
							$ts = json_decode($chat['status'], true);
							if (!in_array($ts[$botID], ['deleted', 'bot', 'ban']) and strpos($ts[$botID], "ban") !== 0) {
								$chat = $chat[$type];
								$m = sm($chat, "Messaggio temporaneo ⏱ \nStiamo controllando gli utenti attivi su questo Bot...");
								if ($m['ok']) {
									dm($chat, $m['result']['message_id']);
									setStatus($chat, 'avviato');
									$cstati['avviato'][] = $chat;
								} else {
									if ($m['description'] == "Forbidden: bot can't send messages to bots") {
										setStatus($chat, 'bot');
										$cstati['bot'][] = $chat;
									} elseif ($m['description'] == "Forbidden: bot was blocked by the user") {
										setStatus($chat, 'blocked');
										$cstati['blocked'][] = $chat;
									} elseif ($m['description'] == "Forbidden: bot can't initiate conversation with a user") {
										setStatus($chat, 'attivo');
										$cstati['attivo'][] = $chat;
									} elseif ($m['description'] == "Forbidden: user is deactivated") {
										setStatus($chat, 'deleted');
										$cstati['deleted'][] = $chat;
									} elseif (strpos($m['description'], "Forbidden: bot was kicked from the") === 0) {
										setStatus($chat, 'kicked');
										$cstati['kicked'][] = $chat;
									} elseif (strpos($m['description'], "Forbidden: bot is not a member of the") === 0) {
										setStatus($chat, 'attivo');
										$cstati['inattivo'][] = $chat;
									} elseif (strpos($m['description'], "Bad Request: chat not found") === 0) {
										$cstati['attivo'][] = $chat;
									} elseif (strpos($m['description'], "Forbidden: bot can't send messages to the user") === 0) { 
										$cstati['visto'][] = $chat;
									} else {
										sm($chatID, "[Fatal Error] Errore sconosciuto per $chat: " . json_encode($m));
										file_put_contents("ferma", 'fermati');
									}
								}
								$contati[] = $chat;
								$num = count($contati);
								$messageup = bold($tbname);
								if ($cstati['avviato']) $messageup .= "\n" . $stati['avviato'] . ": " . count($cstati['avviato']);
								if ($cstati['attivo']) $messageup .= "\n" . $stati['attivo'] . ": " . count($cstati['attivo']);
								if ($cstati['blocked']) $messageup .= "\n" . $stati['blocked'] . ": " . count($cstati['blocked']);
								if ($cstati['visto']) $messageup .= "\n" . $stati['visto'] . ": " . count($cstati['visto']);
								if ($cstati['bot']) $messageup .= "\n" . $stati['bot'] . ": " . count($cstati['bot']);
								if ($cstati['deleted']) $messageup .= "\n" . $stati['deleted'] . ": " . count($cstati['deleted']);
								if ($thistime + 1 < time()) {
									$thistime = time();
									editMsg($chatID, bold("Aggiorno gli status di $db ") . round($num / $tot * 100) . "%" . progressbar($num, $tot) . "\n\n" . $messageup . "\n\n" . italic(date("d/m/Y H:i:s", $thistime)), $cbmid, $menuc);
								}
							}
						} else {
							if (!$messageup) $messageup = bold($tbname) . italic("\nControllo non eseguito su questo database...");
							$annullato = true;
						}
					}
				} else {
					$messageup = $message[$db];
				}
			}
			if ($exdb) {
				$message[$exdb] = $messageup;
			}
			$menu[] = [
				[
					'text' => "🔙 Indietro",
					'callback_data' => 'iscritti'
				]
			];
			if ($annullato) {
				editMsg($chatID, bold("Operazione annullata") . "\n\n" . $message['utenti']. "\n\n" . $message['gruppi']. "\n\n" . $message['canali'] . "\n\n" . italic(date("d/m/Y H:i:s")), $cbmid, $menu);
				unlink("ferma");
			} else {
				editMsg($chatID, bold("Finito il controllo!") . "\n\n" . $message['utenti']. "\n\n" . $message['gruppi']. "\n\n" . $message['canali'] . "\n\n" . italic(date("d/m/Y H:i:s")), $cbmid, $menu);
			}
			die;
		}
		
		# Ferma l'operazione in corso
		if ($cbdata == "ferma") {
			cb_reply($cbid, 'Richiesto annullamento del comando in esecuzione...');
			file_put_contents("ferma", 'fermati');
			die;
		}
	}
}
