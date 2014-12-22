<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @author Ettelcar
 * @author Massimiliano Tiraboschi
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

$lang = array();
$lang['title'] = 'AJAX Chat';
$lang['userName'] = 'Nom d’utilisateur';
$lang['password'] = 'Mot de passe';
$lang['login'] = 'Connexion';
$lang['logout'] = 'Déconnexion';
$lang['channel'] = 'Salon';
$lang['style'] = 'Style';
$lang['language'] = 'Langue';
$lang['inputLineBreak'] = 'Taper Maj + Entrer pour avoir une ligne blanche';
$lang['messageSubmit'] = 'Envoyer';
$lang['registeredUsers'] = 'Utilisateurs enregistrés';
$lang['onlineUsers'] = 'Utilisateurs en ligne';
$lang['toggleAutoScroll'] = 'Activer / Désactiver l’autoscroll';
$lang['toggleAudio'] = 'Activer / Désactiver le son';
$lang['toggleHelp'] = 'Afficher / Masquer l’aide';
$lang['toggleSettings'] = 'Afficher / Masquer les préférences';
$lang['toggleOnlineList'] = 'Afficher / Masquer les utilisateurs en ligne';
$lang['bbCodeLabelBold'] = 'b';
$lang['bbCodeLabelItalic'] = 'i';
$lang['bbCodeLabelUnderline'] = 'u';
$lang['bbCodeLabelQuote'] = 'Citation';
$lang['bbCodeLabelCode'] = 'Code';
$lang['bbCodeLabelURL'] = 'URL';
$lang['bbCodeLabelImg'] = 'Image';
$lang['bbCodeLabelColor'] = 'Couleur de police';
$lang['bbCodeTitleBold'] = 'Texte en gras : [b]texte[/b]';
$lang['bbCodeTitleItalic'] = 'Texte en italique : [i]texte[/i]';
$lang['bbCodeTitleUnderline'] = 'Texte souligné : [u]texte[/u]';
$lang['bbCodeTitleQuote'] = 'Citation : [quote]texte[/quote] ou [quote=auteur]texte[/quote]';
$lang['bbCodeTitleCode'] = 'Affichage de code : [code]code[/code]';
$lang['bbCodeTitleURL'] = 'Insérer une URL : [url]http://example.org[/url] ou [url=http://example.org]text[/url]';
$lang['bbCodeTitleImg'] = 'Insérer une image : [img]http://example.org/image.jpg[/img]';
$lang['bbCodeTitleColor'] = 'Couleur de la police: [color=red]texte[/color]';
$lang['help'] = 'Aide';
$lang['helpItemDescJoin'] = 'Rejoindre un salon :';
$lang['helpItemCodeJoin'] = '/join Nom_du_salon';
$lang['helpItemDescJoinCreate'] = 'Créer un salon privé (Utilisateurs enregistrés seulement) :';
$lang['helpItemCodeJoinCreate'] = '/join';
$lang['helpItemDescInvite'] = 'Inviter quelqu’un (exemple : rejoindre un salon privé) :';
$lang['helpItemCodeInvite'] = '/invite Nom_d_utilisateur';
$lang['helpItemDescUninvite'] = 'Annuler l’invitation :';
$lang['helpItemCodeUninvite'] = '/uninvite Nom_d_utilisateur';
$lang['helpItemDescLogout'] = 'Se déconnecter du chat :';
$lang['helpItemCodeLogout'] = '/quit';
$lang['helpItemDescPrivateMessage'] = 'Message privé :';
$lang['helpItemCodePrivateMessage'] = '/msg Nom_d_utilisateur Texte';
$lang['helpItemDescQueryOpen'] = 'Ouvrir un canal privé :';
$lang['helpItemCodeQueryOpen'] = '/query Nom_d_utilisateur';
$lang['helpItemDescQueryClose'] = 'Fermer un canal privé:';
$lang['helpItemCodeQueryClose'] = '/query';
$lang['helpItemDescAction'] = 'Décrire une action :';
$lang['helpItemCodeAction'] = '/action Texte';
$lang['helpItemDescDescribe'] = 'Décrire une action dans un message privé :';
$lang['helpItemCodeDescribe'] = '/describe Nom_d_utilisateur Texte';
$lang['helpItemDescIgnore'] = 'Ignorer / Accepter les messages de l’utilisateur :';
$lang['helpItemCodeIgnore'] = '/ignore Nom_d_utilisateur';
$lang['helpItemDescIgnoreList'] = 'Liste des utilisateurs ignorés :';
$lang['helpItemCodeIgnoreList'] = '/ignore';
$lang['helpItemDescWhereis'] = 'Display user channel :';
$lang['helpItemCodeWhereis'] = '/whereis Username';
$lang['helpItemDescKick'] = 'Éjecter un utilisateur (Modérateurs seulement) :';
$lang['helpItemCodeKick'] = '/kick Nom_d_utilisateur [Temps en minutes de ban]';
$lang['helpItemDescUnban'] = 'Débannir un utilisateur (Modérateurs seulement) :';
$lang['helpItemCodeUnban'] = '/unban Nom_d_utilisateur';
$lang['helpItemDescBans'] = 'Liste des utilisateurs bannis (Modérateurs seulement) :';
$lang['helpItemCodeBans'] = '/bans';
$lang['helpItemDescWhois'] = 'Afficher l’adresse IP de l’utilisateur (Modérateurs seulement) :';
$lang['helpItemCodeWhois'] = '/whois Nom_d_utilisateur';
$lang['helpItemDescWho'] = 'Liste des utilisateurs en ligne :';
$lang['helpItemCodeWho'] = '/who [Nom du salon]';
$lang['helpItemDescList'] = 'Liste des salons disponibles :';
$lang['helpItemCodeList'] = '/list';
$lang['helpItemDescRoll'] = 'Jeter un dé :';
$lang['helpItemCodeRoll'] = '/roll [nombre de dés]d[nombre de faces]';
$lang['helpItemDescNick'] = 'Changer le nom d’utilisateur';
$lang['helpItemCodeNick'] = '/nick Nom_d_utilisateur';
$lang['settings'] = 'Préférences';
$lang['settingsBBCode'] = 'Activer le BBCode :';
$lang['settingsBBCodeImages'] = 'Activer le BBCode des images :';
$lang['settingsBBCodeColors'] = 'Activer le BBCode pour la couleur de la police :';
$lang['settingsHyperLinks'] = 'Activer les liens :';
$lang['settingsLineBreaks'] = 'Activer les lignes blanches :';
$lang['settingsEmoticons'] = 'Activer les émoticons :';
$lang['settingsAutoFocus'] = 'Champ de saisie automatique :';
$lang['settingsMaxMessages'] = 'Maximum de messages dans le chat :';
$lang['settingsWordWrap'] = 'Activer la coupure des mots :';
$lang['settingsMaxWordLength'] = 'Longueur maximum des mots :';
$lang['settingsDateFormat'] = 'Format de la date :';
$lang['settingsPersistFontColor'] = 'Garder la couleur du font :';
$lang['settingsAudioVolume'] = 'Volume du son :';
$lang['settingsSoundReceive'] = 'Son message entrant :';
$lang['settingsSoundSend'] = 'Son message sortant :';
$lang['settingsSoundEnter'] = 'Son pour connexion :';
$lang['settingsSoundLeave'] = 'Son pour déconnexion :';
$lang['settingsSoundChatBot'] = 'Son pour message robot :';
$lang['settingsSoundError'] = 'Son pour les erreurs :';
$lang['settingsBlink'] = 'Clignoter à chaque nouveau message :';
$lang['settingsBlinkInterval'] = 'Intervalle de clignotage :';
$lang['settingsBlinkIntervalNumber'] = 'Numéro de clignotage :';
$lang['playSelectedSound'] = 'Jouer le son sélectionné';
$lang['requiresJavaScript'] = 'Ce chat requiert JavasSript.';
$lang['errorInvalidUser'] = 'Nom d’utilisateur incorrect.';
$lang['errorUserInUse'] = 'Nom d’utilisateur déjà utilisé.';
$lang['errorBanned'] = 'L’utilisateur ou l’adresse IP a été banni(e).';
$lang['errorMaxUsersLoggedIn'] = 'Le chat a atteint le nombre maximal d’utilisateurs en ligne.';
$lang['errorChatClosed'] = 'Le chat est fermé pour le moment.';
$lang['logsTitle'] = 'Chat AJAX - Historique';
$lang['logsDate'] = 'Date';
$lang['logsTime'] = 'Heure';
$lang['logsSearch'] = 'Chercher';
$lang['logsPrivateChannels'] = 'Salons privés';
$lang['logsPrivateMessages'] = 'Messages privés';
?>