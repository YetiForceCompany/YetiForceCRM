<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @author Asmo Soinio
 * @author Saku Laukkanen
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

$lang = array();
$lang['title'] = 'AJAX Chat';
$lang['userName'] = 'Käyttäjätunnus';
$lang['password'] = 'Salasana';
$lang['login'] = 'Kirjaudu sisään';
$lang['logout'] = 'Kirjaudu ulos';
$lang['channel'] = 'Kanava';
$lang['style'] = 'Tyyli';
$lang['language'] = 'Kieli';
$lang['inputLineBreak'] = 'Paina SHIFT+ENTER saadaksesi rivinvaihdon';
$lang['messageSubmit'] = 'Lähetä';
$lang['registeredUsers'] = 'Rekisteröityneet käyttäjät';
$lang['onlineUsers'] = 'Paikalla olevat käyttäjät';
$lang['toggleAutoScroll'] = 'Automaattinen vieritys päällä/pois';
$lang['toggleAudio'] = 'Äänet päällä/pois';
$lang['toggleHelp'] = 'Näytä/piilota ohje';
$lang['toggleSettings'] = 'Näytä/piilota asetukset';
$lang['toggleOnlineList'] = 'Näytä/piilota paikalla olevat käyttäjät';
$lang['bbCodeLabelBold'] = 'b';
$lang['bbCodeLabelItalic'] = 'i';
$lang['bbCodeLabelUnderline'] = 'u';
$lang['bbCodeLabelQuote'] = 'Lainaus';
$lang['bbCodeLabelCode'] = 'Koodi';
$lang['bbCodeLabelURL'] = 'URL';
$lang['bbCodeLabelImg'] = 'Kuva';
$lang['bbCodeLabelColor'] = 'Fontin väri';
$lang['bbCodeTitleBold'] = 'Lihavoitu teksti: [b]teksti[/b]';
$lang['bbCodeTitleItalic'] = 'Kursivoitu teksti: [i]teksti[/i]';
$lang['bbCodeTitleUnderline'] = 'Alleviivattu teksti: [u]teksti[/u]';
$lang['bbCodeTitleQuote'] = 'Lainattu teksti: [quote]teksti[/quote] tai [quote=kirjoittaja]teksti[/quote]';
$lang['bbCodeTitleCode'] = 'Koodi: [code]koodi[/code]';
$lang['bbCodeTitleURL'] = 'Lisää linkki: [url]http://example.org[/url] tai [url=http://example.org]teksti[/url]';
$lang['bbCodeTitleImg'] = 'Lisää kuva: [img]http://example.org/image.jpg[/img]';
$lang['bbCodeTitleColor'] = 'Kirjaisimen väri: [color=red]teksti[/color]';
$lang['help'] = 'Ohje';
$lang['helpItemDescJoin'] = 'Liity kanavalle:';
$lang['helpItemCodeJoin'] = '/join KanavanNimi';
$lang['helpItemDescJoinCreate'] = 'Tee uusi yksityinen kanava (Ainoastaan rekisteröityneille käyttäjille):';
$lang['helpItemCodeJoinCreate'] = '/join';
$lang['helpItemDescInvite'] = 'Kutsu joku (esim. Yksityiseen kanavaan):';
$lang['helpItemCodeInvite'] = '/invite Käyttäjätunnus';
$lang['helpItemDescUninvite'] = 'Peruuta kutsu:';
$lang['helpItemCodeUninvite'] = '/uninvite Käyttäjätunnus';
$lang['helpItemDescLogout'] = 'Kirjaudu ulos chatista:';
$lang['helpItemCodeLogout'] = '/quit';
$lang['helpItemDescPrivateMessage'] = 'Yksityisviesti:';
$lang['helpItemCodePrivateMessage'] = '/msg Käyttäjätunnus Teksti';
$lang['helpItemDescQueryOpen'] = 'Avaa yksityinen kanava:';
$lang['helpItemCodeQueryOpen'] = '/query Käyttäjänimi';
$lang['helpItemDescQueryClose'] = 'Sulje yksityinen kanava:';
$lang['helpItemCodeQueryClose'] = '/query';
$lang['helpItemDescAction'] = 'Kerro, mitä teet:';
$lang['helpItemCodeAction'] = '/action Teksti';
$lang['helpItemDescDescribe'] = 'Kerro yksityisviestissä mitä teet:';
$lang['helpItemCodeDescribe'] = '/describe Käyttäjätunnus Teksti';
$lang['helpItemDescIgnore'] = 'Estä tai salli viestejä käyttäjältä:';
$lang['helpItemCodeIgnore'] = '/ignore Käyttäjänimi';
$lang['helpItemDescIgnoreList'] = 'Listaa estetyt käyttäjät:';
$lang['helpItemCodeIgnoreList'] = '/ignore';
$lang['helpItemDescWhereis'] = 'Näytä käyttäjän kanavat:';
$lang['helpItemCodeWhereis'] = '/whereis Käyttäjänimi';
$lang['helpItemDescKick'] = 'Potki käyttäjä (Ainoastaan Moderaattoreille):';
$lang['helpItemCodeKick'] = '/kick Käyttäjänimi [Minuutteja potkittuna]';
$lang['helpItemDescUnban'] = 'Poista käyttäjän potkut (Ainoastaan moderaattoreille):';
$lang['helpItemCodeUnban'] = '/unban Käyttäjänimi';
$lang['helpItemDescBans'] = 'Listaa potkitut käyttäjät (Ainoastaan moderaattoreille):';
$lang['helpItemCodeBans'] = '/bans';
$lang['helpItemDescWhois'] = 'Näytä käyttäjän IP (Ainoastaan moderaattoreille):';
$lang['helpItemCodeWhois'] = '/whois Käyttäjänimi';
$lang['helpItemDescWho'] = 'Listaa käyttäjät jotka ovat paikalla:';
$lang['helpItemCodeWho'] = '/who [Kanavatunnus]';
$lang['helpItemDescList'] = 'Listaa käytettävissä olevat kanavat:';
$lang['helpItemCodeList'] = '/list';
$lang['helpItemDescRoll'] = 'Heitä noppaa:';
$lang['helpItemCodeRoll'] = '/roll [kertaa]d[sivuja]';
$lang['helpItemDescNick'] = 'Vaihda käyttäjätunnusta:';
$lang['helpItemCodeNick'] = '/nick Käyttäjänimi';
$lang['settings'] = 'Asetukset';
$lang['settingsBBCode'] = 'BBCode päälle:';
$lang['settingsBBCodeImages'] = 'Salli kuvat:';
$lang['settingsBBCodeColors'] = 'Salli värit:';
$lang['settingsHyperLinks'] = 'Salli hyperlinkit:';
$lang['settingsLineBreaks'] = 'Salli rivinvaihdot:';
$lang['settingsEmoticons'] = 'Salli hymiöt:';
$lang['settingsAutoFocus'] = 'Valitse tekstinsyöttökenttä automaattisesti:';
$lang['settingsMaxMessages'] = 'Suurin määrä viestejä sivulla:';
$lang['settingsWordWrap'] = 'Salli pitkien sanojen rivinvaihto:';
$lang['settingsMaxWordLength'] = 'Sanan enimmäispituus ennen kuin riviä vaihdetaan:';
$lang['settingsDateFormat'] = 'Ajan ja päivämäärän muoto:';
$lang['settingsPersistFontColor'] = 'Pysyvä fontin väri:';
$lang['settingsAudioVolume'] = 'Äänenvoimakkuus:';
$lang['settingsSoundReceive'] = 'Ääni tuleville viesteille:';
$lang['settingsSoundSend'] = 'Ääni lähteville viesteille:';
$lang['settingsSoundEnter'] = 'Ääni sisäänkirjoittautumiseen ja kanavalle tuloon:';
$lang['settingsSoundLeave'] = 'Ääni poistumiseen keskustelusta ja kanavalta:';
$lang['settingsSoundChatBot'] = 'Ääni chatbotin viesteille:';
$lang['settingsSoundError'] = 'Ääni virheilmoituksille:';
$lang['settingsBlink'] = 'Vilkuta ikkunan nimeä uusista viesteistä:';
$lang['settingsBlinkInterval'] = 'Vilkuttamisen aika millisekunneissa:';
$lang['settingsBlinkIntervalNumber'] = 'Vilkutuksen viive:';
$lang['playSelectedSound'] = 'Soita valittu ääni';
$lang['requiresJavaScript'] = 'JavaScript on pakollinen tässä chatissa';
$lang['errorInvalidUser'] = 'Virheellinen käyttäjänimi';
$lang['errorUserInUse'] = 'Käyttäjänimi on käytössä, ole hyvä ja valitse toinen';
$lang['errorBanned'] = 'Tällä IP:llä on porttikielto';
$lang['errorMaxUsersLoggedIn'] = 'Chatissa on jo maksimimäärä käyttäjiä.';
$lang['errorChatClosed'] = 'Chat on poistettu käytöstä toistaiseksi.';
$lang['logsTitle'] = 'AJAX Chat-Lokitiedot';
$lang['logsDate'] = 'Päivä';
$lang['logsTime'] = 'Aika';
$lang['logsSearch'] = 'Etsi';
$lang['logsPrivateChannels'] = 'Näytä yksityiset kanavat';
$lang['logsPrivateMessages'] = 'Yksityiset viestit';
?>
