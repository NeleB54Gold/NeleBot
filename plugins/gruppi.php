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

if ($typechat == 'supergroup' or $typechat == 'group') {
	# Qui ci sono tutti i comandi per i Gruppi

	# Aggiornamento delle informazioni manuale (Solo per gli Admin del Gruppo)
	if ($cmd == "riavvia") {
		if (!$isStaff and !empty($g['admins'])) {
			die;
		}
		if (isset($g['chat_id'])) {
			if (!isset($usernamechat)) {
				$usernamechat = "";
			}
			$getchat = getChat($chatID);
			if (isset($getchat['result']['permissions'])) {
				$perms = $getchat['result']['permissions'];
			} else {
				$perms = [
					"can_send_messages" => true,
					"can_send_media_messages" => true,
					"can_send_polls" => true,
					"can_send_other_messages" => true,
					"can_add_web_page_previews" => true,
					"can_change_info" => false,
					"can_invite_users" => false,
					"can_pin_messages" => false
				];
			}
			if ($perms !== $g['permissions']) {
				$altro .= "\n✅ Permessi globali aggiornati";
			} else {
				$altro .= "\n🔄 Permessi globali già aggiornati";
			}
			$perms = json_encode($perms);
			if (isset($getchat['result']['description'])) {
				$descrizione = $getchat['result']['description'];				
			} else {
				$descrizione = "";
			}
			if ($descrizione == $g['description']) {
				$altro .= "\n🔄 Descrizione già aggiornata";
			} else {
				if (empty($descrizione)) {
					$altro .= "\n❌ Descrizione rimossa";
				} else {
					$altro .= "\n✅ Descrizione aggiornata";
				}
			}
			$admins = getAdmins($chatID);
			if (isset($admins['ok'])) {
				if ($admins !== $g['admins']) {
					$altro .= "\n✅ Lista Admins aggiornata";
				} else {
					$altro .= "\n🔄 Lista Admins già aggiornata";
				}
				$adminsg = json_encode($admins['result']);
			} else {
				$altro .= "\n❌ Lista Admins...";
				$adminsg = "[]";
			}
			db_query("UPDATE gruppi SET title = ?, username = ?, admins = ?, description = ?, permissions = ? WHERE chat_id = ?", [$title, $usernamechat, $adminsg, $descrizione, $perms, $chatID], 'no');
		}
		sm($chatID, "✅ Bot riavviato $altro");
		die;
	}
	
	# Quando un utente o il Bot viene aggiunto al Gruppo
	if ($update["message"]["new_chat_member"]) {
		$nomeag = $update["message"]["new_chat_member"]["first_name"];
		$cognomeag = $update["message"]["new_chat_member"]["last_name"];
		$usernameag = $update["message"]["new_chat_member"]["username"];
		$idag = $update["message"]["new_chat_member"]["id"];
		if ($usernameag == $config['username_bot']) {
			sm($chatID, bold("Ciao!") . "\nSono un Bot di Test di " . text_link("NelePHPFramework", 't.me/NelePHPFramework'));
			if ($config['console'] !== false) {
				$text = "#Aggiunto \n" . bold("$nome") . " ha aggiunto @" . $config['username_bot'] . " in " . bold($title);
				if ($usernamechat) {
					$text .= "\n" . bold("Chat:") . " $title (@$usernamechat)";
				} else {
					$text .= "\n" . bold("Chat:") . " $title";
				}
				$text .= "\n" . bold("ID:") . " " . code($chatID);
				$text .= "\n" . bold("Utente:") . " " . textspecialchars("$nome $cognome") . " [" . code($userID) . "]";
				if ($username) {
					$text .= "\n" . bold("Username:") . " @$username";
				}
				$text .= " \n" . bold("Bot:") . " @$usernameag";
				sm($config['console'], $text);
			}
			die;
		}
		if ($idag == $userID) {
			sm($chatID, textspecialchars("$nomeag $cognomeag") . " è entrato.");
		} else {
			sm($chatID, textspecialchars("$nomeag $cognomeag") . " è stato aggiunto da " . textspecialchars("$nome $cognome"));
		}
	}

	# Quando un utente o il Bot viene rimosso dal Gruppo
	if ($update["message"]["left_chat_member"]) {
		$nomeag = $update["message"]["left_chat_member"]["first_name"];
		$cognomeag = $update["message"]["left_chat_member"]["last_name"];
		$usernameag = $update["message"]["left_chat_member"]["username"];
		$idag = $update["message"]["left_chat_member"]["id"];
		if ($usernameag == $config['username_bot']) {
			if ($config['console'] !== false) {
				$text = "#Rimosso \n".bold($nome) . " ha rimosso @" . $config['username_bot'] . " da " . bold($title);
				if ($usernamechat) {
					$text .= "\n" . bold("Chat:") . " $title (@$usernamechat)";
				} else {
					$text .= "\n" . bold("Chat:") . " $title";
				}
				$text .= "\n" . bold("ID:") . " " . code($chatID);
				$text .= "\n" . bold("Utente:") . " " . textspecialchars("$nome $cognome") . " [" . code($userID) . "]";
				if ($username) {
					$text .= "\n" . bold("Username:") . " @$username";
				}
				$text .= " \n" . bold("Bot:") . " @$usernameag";
				sm($config['console'], $text);
			}
			die;
		}
		if ($idag == $userID) {
			sm($chatID, textspecialchars("$nomeag $cognomeag") . " ha abbandonato.");
		} else {
			sm($chatID, textspecialchars("$nomeag $cognomeag") . " è stato rimosso da " . textspecialchars("$nome $cognome"));
		}
	}

	# Vedi la lista dei comandi per i gruppi
	if ($cmd == 'help') {
		$menu[] = [
			[
				'text' => "Source Bot",
				'url' => 'https://t.me/NelePHPFramework'
			]
		];
		sm($chatID, bold("Comandi del Bot sui Gruppi") . "
/start - Avvia il Bot
/help - Ottieni la lista dei comandi per i Gruppi
/jsondump - Ottieni un dump in json della tua update
/staff - Visualizza la lista degli amministratori
/dm - Elimina un messaggio
/setStickers - Setta il Set Sticker del Gruppo
/unsetStickers - Togli il Set Sticker del Gruppo
/fissato - Visualizza il messaggio fissato
/pin - Fissa un messaggio via reply
/unpin - Togli il messaggio fissato
/delpic - Togli la foto profilo del Gruppo
/admin - Rendi amministratoreun utente
/getchat - Prendi le info del Gruppo
/membri - Guarda il numero di membri sul Gruppo
/info - Informazionidi un utente via reply
/setTitle - Modificail nome del Gruppo
/setDescription - Modifica la descrizione del Gruppo
/muta - Silenzia un utente via reply
@" . $config['username_bot'], $menu);
		die;
	}

	# Vedi l'ID della chat (Solo per gli Admin del Gruppo)
	if ($cmd == 'chat_id' and $isStaff) {
		sm($chatID, code($chatID));
		die;
	}
	
	# Visualizza i permessi del Bot (solo per Admin del Bot)
	if ($cmd == 'botperms' and $isadmin) {
		$emoji = [
			0 => "❌",
			1 => "✅"
		];
		if ($botisadmin) {
			$text = "Permessi di @" . $botperms['user']['username'];
			$text .= "\n\nStato: " . code($botperms['status']);
			$active = $botperms['can_change_info'];
			$text .= "\nCambiare le info del gruppo: " . $emoji[$active];
			$active = $botperms['can_delete_messages'];
			$text .= "\nEliminare messaggi: " . $emoji[$active];
			$active = $botperms['can_restrict_members'];
			$text .= "\nBloccare utenti: " . $emoji[$active];
			$active = $botperms['can_invite_users'];
			$text .= "\nInvitare utenti tramite link: " . $emoji[$active];
			$active = $botperms['can_pin_messages'];
			$text .= "\nFissare messaggi: " . $emoji[$active];
			$active = $botperms['can_promote_members'];
			$text .= "\nAggiungere amministratori: " . $emoji[$active];
			sm($chatID, $text);
		} else {
			sm($chatID, "Nessun permesso da Admin.");
		}
		die;
	}

	# Lista Staff di un Gruppo
	if ($cmd == 'staff') {
		foreach ($g['admins'] as $ad) {
			if ($ad['user']['is_bot'] !== true) {
				$nomec = $ad['user']['first_name'];
				if (isset($ad['user']['last_name'])) {
					$nomec .= ' ' . $ad['user']['last_name'];
				}
				if (isset($ad['user']['username'])) {
					$nomec = text_link($nomec, "t.me/" . $ad['user']['username']);
				} else {
					$nomec = bold($nomec);
				}
				if ($ad['status'] == 'creator') {
					$founder = $nomec;
				} else {
					$adminis .= "\n- $nomec";
				}
			}
		}
		sm($chatID, bold("👮‍♂️ Lista Amministratori \n\n👑 Creatore:") . " $founder \n$adminis");
		die;
	}
	
	# Riporta i permessi globali del gruppo ai default (Solo per il Creatore del Gruppo)
	if ($cmd == 'setChatPerms' and $isfounder) {
		$config['response'] = true;
		$r = setChatPerms($chatID, []);
		sm($chatID, "Results: " . code(json_encode($r)));
		die;
	}
	
	# Elimina messaggio via reply (Solo per gli Admin del Gruppo)
	if ($cmd == 'dm' and $isStaff) {
		if ($botisadmin) {
			if ($botperms['can_delete_messages']) {
				dm($chatID, $msgID);
				if ($reply) {
					if ($uPerms['can_delete_messages']) {
						dm($chatID, $rmsgID);
					} else {
						sm($userID, "Non hai il permesso per eliminare i messaggi.");
					}
				} else {
					sm($userID, "Non hai risposto al messaggio da eliminare.");
				}
			} else {
				sm($userID, "Non ho il permesso per eliminare i messaggi.");
			}
		} else {
			sm($userID, "Non sono admin del gruppo " . bold($title) . " per cancellare i messaggi!");
		}
		die;
	}

	# Setta il Pacchetto Stickers (solo per Supergruppi con 100+ membri | Solo per gli Admin del Gruppo)
	if ($cmd == 'setStickers' and $reply and $isStaff) {
		$set = $update['message']['reply_to_message']['sticker']['set_name'];
		if ($botisadmin) {
			if ($botperms['can_change_info']) {
				if ($uPerms['can_change_info']) {
					$config['json_payload'] = false;
					$m = setStickers($chatID, $set);
					if ($m['ok']) {
						sm($chatID, text_link("Set Sticker", "https://t.me/addstickers/$set")." del Gruppo Settati!");
					} else {
						sm($chatID, "Non sono riuscito a modificare il Set Stickers di questo Gruppo:\n" . code($m['description']));
					}
				} else {
					sm($userID, "Non hai il permesso per cambiare le informazioni del Gruppo.");
				}
			} else {
				sm($userID, "Non ho il permesso per cambiare le informazioni del Gruppo.");
			}
		} else {
			sm($userID, "Non sono admin del gruppo " . bold($title) . " per modificare il Set Sticker!");
		}
		die;
	}

	# Togli il Pacchetto Stickers del Gruppo (Solo per gli Admin del Gruppo)
	if ($cmd == 'unsetStickers' and $isStaff) {
		if ($botisadmin) {
			if ($botperms['can_change_info']) {
				if ($uPerms['can_change_info']) {
					$config['json_payload'] = false;
					$m = unsetStickers($chatID);
					if ($m['ok']) {
						sm($chatID, "Stickers del Gruppo rimossi.");
					} else {
						sm($userID, "Impossibile modificare il Set Stickers di questo Gruppo, guarda i Logs per sapere di più.");
					}
				} else {
					sm($userID, "Non hai il permesso per cambiare le informazioni del Gruppo.");
				}
			} else {
				sm($userID, "Non ho il permesso per cambiare le informazioni del Gruppo.");
			}
		} else {
			sm($userID, "Non sono admin del gruppo " . bold($title) . " per eliminare il Set Sticker!");
		}
		die;
	}

	# Visualizza il messaggio fissato
	if ($cmd == 'fissato') {
		$config['json_payload'] = false;
		$res = getChat($chatID);
		if ($res['ok']) {
			if (isset($res['result']['pinned_message'])) {
				$pinID = $res['result']['pinned_message']['message_id'];
				$menu[] = [
					[
						"text" => "Messaggio fissato",
						"url" => "https://t.me/c/" . str_replace('-', '', str_replace('-100', '', $chatID)) . "/$pinID"
					]
				];
				sm($chatID, "Vai al messaggio fissato:", $menu, 'def', $pinID);
			} else {
				sm($chatID, "Non c'è nessun messaggio fissato in questo gruppo.");
			}
		}
		die;
	}
	
	# Fissa un messaggio via reply (Solo per gli Admin del Gruppo)
	if ($cmd == 'pin' and $isStaff) {
		if ($botisadmin) {
			if ($botperms['can_pin_messages']) {
				if ($uPerms['can_pin_messages']) {
					if ($reply) {
						$m = pin($chatID, $rmsgID);
						if ($m['error_code']) {
							sm($userID, "Non sono riuscito a fissare il messaggio sul Gruppo:\n" . code($m['description']));
						}
					} else {
						sm($chatID, "Rispondi a un messaggio per fissarlo", null, null, true);
					}
				} else {
					sm($userID, "Non hai il permesso di fissare messaggi su " . bold($title));
				}
			} else {
				sm($userID, "Non ho il permesso di fissare messaggi su " . bold($title));
			}
		} else {
			sm($userID, "Non sono admin sul gruppo " . bold($title));
		}
		die;
	}

	# Togli il messaggio fissato (Solo per gli Admin del Gruppo)
	if ($cmd == 'unpin' and $isStaff) {
		if ($botisadmin) {
			if ($botperms['can_pin_messages']) {
				if ($uPerms['can_pin_messages']) {
					$m = unpin($chatID);
					if ($m['error_code']) {
						sm($userID, "Non sono riuscito togliere il messaggio fissato dal Gruppo:\n" . code($m['description']));
					}
				} else {
					sm($userID, "Non hai il permesso di fissare messaggi su " . bold($title));
				}
			} else {
				sm($userID, "Non ho il permesso di fissare messaggi su " . bold($title));
			}
		} else {
			sm($userID, "Non sono admin sul gruppo " . bold($title));
		}
		die;
	}

	# Togli l'immagine profilo del Gruppo (Solo per gli Admin del Gruppo)
	if ($cmd == 'delpic' and $isStaff) {
		if ($botisadmin) {
			if ($botperms['can_change_info']) {
				if ($uPerms['can_change_info']) {
					$m = unsetp($chatID);
					if ($m['error_code']) {
						sm($userID, "Non sono riuscito togliere la foto dal Gruppo:\n" . code($m['description']));
					}
				} else {
					sm($userID, "Non hai i permessi per modificare le info in " . bold($title) . ".");
				}
			} else {
				sm($userID, "Non ho i permessi per modificare le info in " . bold($title) . ".");
			}
		} else {
			sm($userID, "Non sono admin sul gruppo " . bold($title));
		}
		die;
	}

	# Esci da un Gruppo (Solo per gli Admin del Bot)
	if ($cmd == 'leave' and $isadmin) {
		lc($chatID);
		die;
	}

	# Rendi amministratore utente (Solo per il Creatore del Gruppo)
	if ($cmd == 'addadmin' and $isfounder and $ruserID) {
		if ($botperms['can_promote_members']) {
			$config['response'] = true;
			promote($chatID, $ruserID, [
				'can_change_info' => false,
				'can_delete_messages' => true,
				'can_invite_users' => true,
				'can_restrict_members' => true,
				'can_pin_messages' => false,
				'can_promote_members' => false,
			]);
			sm($chatID, "Admin aggiunto!");
		} else {
			sm($userID, "Non ho i permessi per aggiungere Amministratori in " . bold($title) . ".");
		}
		die;
	}
	
	# Modifica tag utente (Solo per gli Admin del Gruppo)
	if (strpos($cmd, 'tag ') === 0 and $isStaff and $ruserID and $reply) {
		$tag = str_replace('tag ', '', $cmd);
		$config['json_payload'] = false;
		if ($botperms['can_promote_members']) {
			if (!$isrStaff) {
				$config['response'] = true;
				promote($chatID, $ruserID, [
					'can_change_info' => false,
					'can_delete_messages' => false,
					'can_invite_users' => true,
					'can_restrict_members' => false,
					'can_pin_messages' => false,
					'can_promote_members' => false,
				]);
			}
			setAdminTag($chatID, $ruserID, $tag);
			sm($chatID, tag($ruserID, $rnome, $rcognome) . " è stato promosso a " . bold($tag));
		} else {
			sm($userID, "Non ho i permessi per aggiungere Amministratori in " . bold($title) . ".");
			die;
		}
		die;
	}

	# Informazioni del Gruppo in Json (Solo per gli Admin del Gruppo)
	if ($cmd == 'getchat' and $isadmin) {
		$config['json_payload'] = false;
		$res = getChat($chatID);
		sm($chatID, code(substr(json_encode($res, JSON_PRETTY_PRINT), 0, 4095)));
		die;
	}

	# Ottieni il numero di membri del Gruppo
	if ($cmd == 'membri') {
		sm($chatID, 'Membri: ' . conta($chatID));
		die;
	}

	# Modifica il nome del Gruppo (Solo per gli Admin del Gruppo)
	if (strpos($cmd, 'setTitle') === 0 and $isStaff) {
		if ($botisadmin) {
			if ($botperms['can_change_info']) {
				if ($uPerms['can_change_info']) {
					$title = str_replace('setTitle ', '', $cmd);
					$config['json_payload'] = false;
					$m = setTitle($chatID, $title);
					if ($m['error_code']) {
						sm($userID, "Non sono riuscito a modificare il titolo su " . bold($title) . ":\n" . code($m['description']));
					}
				} else {
					sm($userID, "Non hai i permessi per modificare le informazioni su " . bold($title));
				}
			} else {
				sm($userID, "Non ho i permessi per modificare le informazioni su " . bold($title));
			}
		} else {
			sm($userID, "Non sono admin sul gruppo " . bold($title));
		}
		die;
	}

	# Modifica la descrizione del Gruppo (Solo per il Creatore del Gruppo)
	if (strpos($cmd, 'setDescription') === 0 and $isfounder) {
		if ($botisadmin) {
			if ($botperms['can_change_info']) {
				if ($uPerms['can_change_info']) {
					$desc = str_replace('setDescription ', '', $cmd);
					$m = setDescription($chatID, $desc);
					if ($m['error_code']) {
						sm($userID, "Non sono riuscito a modificare la descrizione su " . bold($title) . ":\n" . code($m['description']));
					}
				} else {
					sm($userID, "Non hai i permessi per modificare le informazioni su " . bold($title));
				}
			} else {
				sm($userID, "Non ho i permessi per modificare le informazioni su " . bold($title));
			}
		} else {
			sm($userID, "Non sono admin sul gruppo " . bold($title));
		}
		die;
	}

	# Prendi le Informazioni di un utente (Solo per gli Admin del Gruppo)
	if ($cmd == 'info' and $isStaff and $reply) {
		$res = getChatMember($chatID, $ruserID);
		if ($res['ok']) {
			$us = $res['result']['user'];
			$status = $res['result']['status'];
			if ($us['is_bot']) {
				$type = 'Bot';
			} else {
				$type = 'Utente';
			}
			$usinfo = "ID: " . code($us['id']);
			$usinfo .= "\nNome: " . htmlspecialchars($us['first_name']);
			if (isset($us['last_name'])) {
				$usinfo .= "\nCognome: " . htmlspecialchars($us['last_name']);
			}
			if (isset($us['username'])) {
				$usinfo .= "\nUsername: @" . htmlspecialchars($us['username']);
			}
			if (isset($us['language_code'])) {
				$usinfo .= "\nLingua: " . htmlspecialchars($us['language_code']);
			}
			$stati = [
				'member' => 'membro',
				'creator' => 'creatore',
				'kicked' => 'bandito',
				'administrator' => 'amministratore',
				'left' => 'fuori dal gruppo'
			];
			$stato = $stati[$status];
			sm($chatID, bold("$type: $stato") . "\n$usinfo");
		}
		die;
	}

	# Silenzia un utente (Solo per gli Admin del Gruppo)
	if ($cmd == 'muta' and $isStaff and $reply) {
		if ($botisadmin) {
			if ($botperms['can_restrict_members']) {
				if ($uPerms['can_restrict_members']) {
					if ($isrStaff) {
						sm($userID, "Non ho i permessi per mutare " . tag($ruserID, $rnome, $rcognome) . " su " . bold($title));
					} else {
						limita($chatID, $ruserID);
						sm($chatID, "Ho mutato " . tag($ruserID, $rnome, $rcognome));
					}
				} else {
					sm($userID, "Non hai i permessi per mutare un utente su " . bold($title));
				}
			} else {
				sm($userID, "Non ho i permessi per mutare un utente su " . bold($title));
			}
		} else {
			sm($userID, "Non sono admin sul gruppo " . bold($title));
		}
		die;
	}
	
	# Rimuovi utente (kick | Solo per gli Admin del Gruppo)
	if ($cmd == 'kick' and $isStaff and $reply) {
		if ($botisadmin) {
			if ($botperms['can_restrict_members']) {
				if ($uPerms['can_restrict_members']) {
					$config['json_payload'] = false;
					$m = ban($chatID, $ruserID);
					unban($chatID, $ruserID);
					if ($m['ok']) {
						sm($chatID, "Ho rimosso " . tag($ruserID, $rnome, $rcognome) . " dal gruppo.");
					} else {
						sm($userID, "Non sono riuscito rimuovere " . tag($ruserID, $rnome, $rcognome) . " su " . bold($title) . ":\n" . code($m['description']));
					}
				} else {
					sm($userID, "Non hai i permessi per limitare un utente su " . bold($title));
				}
			} else {
				sm($userID, "Non ho i permessi per limitare un utente su " . bold($title));
			}
		} else {
			sm($userID, "Non sono admin sul gruppo " . bold($title));
		}
		die;
	}
	
	# Banna utente (ban | Solo per gli Admin del Gruppo)
	if ($cmd == 'ban' and $isStaff and $reply) {
		if ($botisadmin) {
			if ($botperms['can_restrict_members']) {
				if ($uPerms['can_restrict_members']) {
					$config['json_payload'] = false;
					$m = ban($chatID, $ruserID);
					if ($m['ok']) {
						sm($chatID, "Ho bannato " . tag($ruserID, $rnome, $rcognome) . " dal gruppo.");
					} else {
						sm($userID, "Non sono riuscito a bannare " . tag($ruserID, $rnome, $rcognome) . " su " . bold($title) . ":\n" . code($m['description']));
					}
				} else {
					sm($userID, "Non hai i permessi per limitare un utente su " . bold($title));
				}
			} else {
				sm($userID, "Non ho i permessi per limitare un utente su " . bold($title));
			}
		} else {
			sm($userID, "Non sono admin sul gruppo " . bold($title));
		}
		die;
	}
	
	# Sbanna utente (unban | Solo per gli Admin del Gruppo)
	if ($cmd == 'unban' and $isStaff and $reply) {
		if ($botisadmin) {
			if ($botperms['can_restrict_members']) {
				if ($uPerms['can_restrict_members']) {
					$config['json_payload'] = false;
					$m = unban($chatID, $ruserID);
					if ($m['ok']) {
						sm($chatID, "Ho riammesso " . tag($ruserID, $rnome, $rcognome) . " dal gruppo.");
					} else {
						sm($userID, "Non sono riuscito a riammettere " . tag($ruserID, $rnome, $rcognome) . " su " . bold($title) . ":\n" . code($m['description']));
					}
				} else {
					sm($userID, "Non hai i permessi per limitare un utente su " . bold($title));
				}
			} else {
				sm($userID, "Non ho i permessi per limitare un utente su " . bold($title));
			}
		} else {
			sm($userID, "Non sono admin sul gruppo " . bold($title));
		}
		die;
	}
	
	# Fine comandi per supergruppi
}