<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @author SkyKnight
 * @author Dmitry Plyonkin
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

$lang = array();
$lang['title'] = 'AJAX Chat';
$lang['userName'] = 'Имя';
$lang['password'] = 'Пароль';
$lang['login'] = 'Login';
$lang['logout'] = 'Выйти';
$lang['channel'] = 'Канал';
$lang['style'] = 'Стиль';
$lang['language'] = 'Язык';
$lang['inputLineBreak'] = 'Нажмите SHIFT+ENTER, чтобы перейти на новую строку';
$lang['messageSubmit'] = 'Отправить';
$lang['registeredUsers'] = 'Только для зарегистрированных пользователей';
$lang['onlineUsers'] = 'Пользователи онлайн';
$lang['toggleAutoScroll'] = 'Вкл/выкл авто-прокрутку';
$lang['toggleAudio'] = 'Вкл/выкл звук';
$lang['toggleHelp'] = 'Показать/скрыть подсказку';
$lang['toggleSettings'] = 'Показать/скрыть настройки';
$lang['toggleOnlineList'] = 'Показать/скрыть список участников';
$lang['bbCodeLabelBold'] = 'b';
$lang['bbCodeLabelItalic'] = 'i';
$lang['bbCodeLabelUnderline'] = 'u';
$lang['bbCodeLabelQuote'] = 'Цитата';
$lang['bbCodeLabelCode'] = 'Код';
$lang['bbCodeLabelURL'] = 'URL';
$lang['bbCodeLabelImg'] = 'Image';
$lang['bbCodeLabelColor'] = 'Цвет шрифта';
$lang['bbCodeTitleBold'] = 'Жирный текст: [b]текст[/b]';
$lang['bbCodeTitleItalic'] = 'Курсив: [i]текст[/i]';
$lang['bbCodeTitleUnderline'] = 'Подчеркнутый текст: [u]текст[/u]';
$lang['bbCodeTitleQuote'] = 'Цитата: [quote]текст[/quote] или [quote=автор]текст[/quote]';
$lang['bbCodeTitleCode'] = 'Код: [code]текст[/code]';
$lang['bbCodeTitleURL'] = 'Вставить ссылку: [url]http://example.org[/url] или [url=http://example.org]текст[/url]';
$lang['bbCodeTitleImg'] = 'Insert image: [img]http://example.org/image.jpg[/img]';
$lang['bbCodeTitleColor'] = 'Цвет шрифта: [color=red]текст[/color]';
$lang['help'] = 'Помощь';
$lang['helpItemDescJoin'] = 'Присоедениться к каналу:';
$lang['helpItemCodeJoin'] = '/join имя_канала';
$lang['helpItemDescJoinCreate'] = 'Создать частный (приватный) канал:';
$lang['helpItemCodeJoinCreate'] = '/join';
$lang['helpItemDescInvite'] = 'Пригласить кого-либо (например в частный канал):';
$lang['helpItemCodeInvite'] = '/invite имя_пользователя';
$lang['helpItemDescUninvite'] = 'Отозвать приглашение:';
$lang['helpItemCodeUninvite'] = '/uninvite имя_пользователя';
$lang['helpItemDescLogout'] = 'Выйти из чата:';
$lang['helpItemCodeLogout'] = '/quit';
$lang['helpItemDescPrivateMessage'] = 'Приватное сообщение:';
$lang['helpItemCodePrivateMessage'] = '/msg Имя_пользователся Текст';
$lang['helpItemDescQueryOpen'] = 'Открыть приватный канал:';
$lang['helpItemCodeQueryOpen'] = '/query Имя_пользователя';
$lang['helpItemDescQueryClose'] = 'Закрыть приватный канал:';
$lang['helpItemCodeQueryClose'] = '/query';
$lang['helpItemDescAction'] = 'Описать действия:';
$lang['helpItemCodeAction'] = '/action Текст';
$lang['helpItemDescDescribe'] = 'Описать действия в приватном сообщении:';
$lang['helpItemCodeDescribe'] = '/describe Имя_пользователя Текст';
$lang['helpItemDescIgnore'] = 'Игнорировать/принимать сообщения от пользователей:';
$lang['helpItemCodeIgnore'] = '/ignore Имя_пользователся';
$lang['helpItemDescIgnoreList'] = 'List ignored users:';
$lang['helpItemCodeIgnoreList'] = '/ignore';
$lang['helpItemDescWhereis'] = 'Display user channel:';
$lang['helpItemCodeWhereis'] = '/whereis Username';
$lang['helpItemDescKick'] = 'Выгнать пользователся (Kick) (только модераторы и администраторы):';
$lang['helpItemCodeKick'] = '/kick Имя_пользователя [Минуты]';
$lang['helpItemDescUnban'] = 'Разбанить пользователя (Только модераторы и администраторы):';
$lang['helpItemCodeUnban'] = '/unban Имя_пользователя';
$lang['helpItemDescBans'] = 'Список забаненных пользователей (Только для модераторов):';
$lang['helpItemCodeBans'] = '/bans';
$lang['helpItemDescWhois'] = 'Показать IP пользователся (Только модераторы и администраторы):';
$lang['helpItemCodeWhois'] = '/whois Имя_пользователя';
$lang['helpItemDescWho'] = 'Список доступных пользователей:';
$lang['helpItemCodeWho'] = '/who [Имя_канала]';
$lang['helpItemDescList'] = 'Список доступных каналов:';
$lang['helpItemCodeList'] = '/list';
$lang['helpItemDescRoll'] = 'Бросить кубики:';
$lang['helpItemCodeRoll'] = '/roll [количесво]d[количество сторон]';
$lang['helpItemDescNick'] = 'Change username:';
$lang['helpItemCodeNick'] = '/nick Username';
$lang['settings'] = 'Настройки';
$lang['settingsBBCode'] = 'Разрешить BBCode:';
$lang['settingsBBCodeImages'] = 'Enable image BBCode:';
$lang['settingsBBCodeColors'] = 'Enable font color BBCode:';
$lang['settingsHyperLinks'] = 'Разрешить ссылки:';
$lang['settingsLineBreaks'] = 'Разрешить переводы строк:';
$lang['settingsEmoticons'] = 'Разрешить смайлики:';
$lang['settingsAutoFocus'] = 'Автоматически ставить курсор в поле ввода:';
$lang['settingsMaxMessages'] = 'Маскимальное количество сообщений, отображающихся в чате:';
$lang['settingsWordWrap'] = 'Переносить длинные слова:';
$lang['settingsMaxWordLength'] = 'Максимально допустимая длина слова, которе не будет разбито для переноса:';
$lang['settingsDateFormat'] = 'Формат даты и времени:';
$lang['settingsPersistFontColor'] = 'Постоянный цвет шрифта:';
$lang['settingsAudioVolume'] = 'Громкость:';
$lang['settingsSoundReceive'] = 'Звук для входящих сообщений:';
$lang['settingsSoundSend'] = 'Звук для исходящих сообщений:';
$lang['settingsSoundEnter'] = 'Звук для оповещения о новом участнике в чате:';
$lang['settingsSoundLeave'] = 'Звук для оповещения об уходе участника из чата:';
$lang['settingsSoundChatBot'] = 'Звук для сообщений бота:';
$lang['settingsSoundError'] = 'Звук для оповещений об ошибках:';
$lang['settingsBlink'] = '"Моргать" заголовком окна при новом сообщении:';
$lang['settingsBlinkInterval'] = 'Интервал "моргания" (в миллисекундах):';
$lang['settingsBlinkIntervalNumber'] = 'Количество "морганий":';
$lang['playSelectedSound'] = 'Воспроизвести выбранный звук';
$lang['requiresJavaScript'] = 'Для корректной работы чата должен быть разрешен JavaScript.';
$lang['errorInvalidUser'] = 'Неверное имя пользователя.';
$lang['errorUserInUse'] = 'Имя занято.';
$lang['errorBanned'] = 'Пользователь или IP забанены.';
$lang['errorMaxUsersLoggedIn'] = 'В чате максимальное количество пользователей. Попробуйте зайти позднее.';
$lang['errorChatClosed'] = 'Чат закрыт в настоящий момент.';
$lang['logsTitle'] = 'AJAX Chat - История';
$lang['logsDate'] = 'Дата';
$lang['logsTime'] = 'Время';
$lang['logsSearch'] = 'Поиск';
$lang['logsPrivateChannels'] = 'Приватные каналы';
$lang['logsPrivateMessages'] = 'Приватные сообщения';
?>