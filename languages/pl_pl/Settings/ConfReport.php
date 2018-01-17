<?php
/**
 * ConfReport polish translation
 * @package YetiForce.Languages
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
$languageStrings = [
	'ConfReport' => 'Konfiguracja serwera',
	'LBL_CONFIGURATION' => 'Konfiguracja serwera',
	'LBL_CONFREPORT_DESCRIPTION' => 'Pozwala zweryfikować czy najważniejsze ustawienia serwera są zgodne z zalecanymi.',
	'LBL_PARAMETER' => 'Parametr',
	'LBL_VALUE' => 'Aktualna konfiguracja',
	'LBL_RECOMMENDED' => 'Zalecana konfiguracja',
	'LBL_SYSTEM_STABILITY' => 'Konfiguracja stabilności systemu',
	'LBL_SYSTEM_SECURITY' => 'Konfiguracja bezpieczeństwa systemu',
	'LBL_SECURITY_ADVISORIES_CHECKER' => 'Raport podatności w używanych bibliotekach',
	'LBL_FILES_PERMISSIONS' => 'Pliki/Foldery',
	'LBL_FILE' => 'Nazwa',
	'LBL_PATH' => 'Ścieżka',
	'LBL_PERMISSION' => 'Dostęp',
	'LBL_TRUE_PERMISSION' => 'Dostępne',
	'LBL_FAILED_PERMISSION' => 'Brak uprawnień',
	'On' => 'Włączony',
	'Off' => 'Wyłączony',
	'NOT RECOMMENDED' => 'Niezalecany',
	'LBL_YETIFORCE_ENGINE' => 'Niezalecany',
	'LBL_YETIFORCE_ENGINE' => 'YetiForce Engine',
	'LBL_CHECK_CONFIG' => 'RoundCube Engine',
	'LBL_MANDATORY' => 'Obowiązkowe',
	'LBL_OPTIONAL' => 'Opcjonalne',
	'LBL_LIBRARY' => 'Wsparcie dla bibliotek',
	'LBL_INSTALLED' => 'Zainstalowana',
	'LBL_PDO_SUPPORT' => 'PDO',
	'LBL_OPEN_SSL' => 'openssl',
	'LBL_CURL' => 'cURL',
	'LBL_ZLIB_SUPPORT' => 'Zlib',
	'LBL_IMAP_SUPPORT' => 'IMAP',
	'LBL_GD_LIBRARY' => 'GD',
	'LBL_LDAP_LIBRARY' => 'LDAP',
	'LBL_PCRE_LIBRARY' => 'PCRE',
	'LBL_XML_LIBRARY' => 'XML',
	'LBL_JSON_LIBRARY' => 'JSON',
	'LBL_MYSQLND_LIBRARY' => 'MySQL Native Driver (mysqlnd)',
	'LBL_SESSION_LIBRARY' => 'SESSION',
	'LBL_DOM_LIBRARY' => 'DOM',
	'LBL_MBSTRING_LIBRARY' => 'Mbstring (wymagane dla mPDF)',
	'LBL_EXIF_LIBRARY' => 'Exif (poprawia bezpieczeństwo wgrywanych plików)',
	'LBL_ZIP_ARCHIVE' => 'ZIP',
	'LBL_FILEINFO_LIBRARY' => 'FileInfo',
	'LBL_LIBICONV_LIBRARY' => 'Libiconv',
	'LBL_SPACE' => 'Pojemność',
	'LBL_SPACE_TOTAL' => 'Całkowita',
	'LBL_SPACE_FREE' => 'Wolnych',
	'LBL_SPACE_USED' => 'Zajętych',
	'LBL_VALUE' => 'Wartość',
	'LBL_PHPINI' => 'Konfiguracja PHP',
	'LBL_LOG_FILE' => 'Logi błedów',
	'LBL_CRM_DIR' => 'Katalog lokalny CRM',
	'LBL_SOAP_LIBRARY' => 'SOAP',
	'LBL_PHP_SAPI' => 'Server API',
	'LBL_APCU_LIBRARY' => 'APCu',
	'LBL_OPCACHE_LIBRARY' => 'OPcache',
	'LBL_CRON_PHP' => 'Cron - Wersja PHP',
	'LBL_CRON_PHPINI' => 'Cron - Konfiguracja PHP',
	'LBL_CRON_LOG_FILE' => 'Cron - Logi błedów',
	'LBL_ENVIRONMENTAL_INFORMATION' => 'Informacje o środowisku',
	'LBL_CRON_PHP_SAPI' => 'Cron - Server API',
	'LBL_OPERATING_SYSTEM' => 'System operacyjny',
	'BTN_SERVER_SPEED_TEST' => 'Testuj szybkość serwera',
	'LBL_LIB_NAME' => 'Nazwa biblioteki',
	'LBL_VULNERABILITY_NAME' => 'Nazwa podatności',
	'LBL_VULNERABILITY_URL' => 'Adres',
	'HTTPS_HELP_TEXT' => 'SSL to technologia zabezpieczeń służąca do tworzenia zaszyfrowanego połączenia między serwerem internetowym a przeglądarką. Zalecamy włączenie jej aby uniemożliwić podsłuchiwanie komunikacji pomiędzy Tobą a serwerem.',
	'DISPLAY_ERRORS_HELP_TEXT' => 'Oznacza to, czy komunikaty o błędach i ostrzeżeniach będą wysyłane do przeglądarki. Informacje te często zawierają poufne techniczne informacje o systemie i nigdy nie powinny być pokazywane niezaufanym źródłom. Tą opcję włącza się tylko podczas rozwijania systemu w celu zobaczenia błędów i ostrzeżeń.',
	'SESSION_COOKIE_HTTPONLY_HELP_TEXT' => 'Oznacza to, że pliki cookie są dostępne tylko za pośrednictwem protokołu HTTP. Innymi słowy, plik cookie nie będzie dostępny dla języków skryptowych, takich jak JavaScript. To ustawienie może skutecznie pomóc w ograniczeniu kradzieży tożsamości poprzez ataki XSS.',
	'SESSION_USE_ONLY_COOKIES_HELP_TEXT' => 'Oznacza to, czy system będzie używał tylko ciasteczek do przechowywania identyfikatora sesji po stronie klienta. Włączenie tego ustawienia zapobiega atakom polegającym na przekazywaniu identyfikatorów sesji w adresach URL.',
	'EXPOSE_PHP_HELP_TEXT' => 'Określa, czy serwer PHP będzie wysyłał w nagłówkach informację o numerze wersji PHP. Ta informacja może ułatwić atak na serwer, dlatego zaleca się wyłączenie tej opcji.',
	'HTACCESS_HELP_TEXT' => 'Plik .htaccess jest nieaktywny, jest on potrzebny do prawidłowej obsługi wszystkich integracji oraz do zabezpieczenia dostępu do katalogów z danymi użytkownika.',
	'PUBLIC_HTML_HELP_TEXT' => 'Zalecamy aby domena kierowała do katalogu public_html, dzięki temu pliki systemu oraz twoje dane nie będą publiczne i nikt nie będzie mógł ich pobrać.',
	'SESSION_REGENERATE_HELP_TEXT' => 'Zalecamy aby regenerowanie identyfikatora sesji było włączone, opcja ta utrudnia przejęcie sesji użytkowników. Zmiany należy dokonać w głównym pliku konfiguracyjnym.',
	'SESSION_USE_STRICT_MODE_HELP_TEXT' => 'Oznacza, czy serwer będzie odrzucał niezainicjowaną sesję. Ze względów bezpieczeństwa zaleca się włączyć tą opcję.',
	'SESSION_USE_TRANS_SID_HELP_TEXT' => 'Oznacza, czy serwer będzie przekazywał identyfikator sesji w adresie URL. Ze względów bezpieczeństwa zaleca się wyłączyć tą opcję.',
	'HEADER_X_FRAME_OPTIONS_HELP_TEXT' => 'Dla opcji "SAMEORIGIN" oznacza możliwość ładowania strony w "ramce" tylko z tej samej domeny. Opcja ta chroni przed atakiem "clickjacking".',
	'HEADER_X_XSS_PROTECTION_HELP_TEXT' => 'Chroni przed atakiem XSS. Dla opcji "1; mode=block" w przypadku wykrycia ataku XSS, renderowanie strony zostanie wstrzymane.',
	'HEADER_X_CONTENT_TYPE_OPTIONS_HELP_TEXT' => 'Opcja ta oznacza, że typy MIME przekazywane w nagłówkach Content-Type nie powinny być zmieniane. Opcja ta chroni przed atakiem "MIME".',
	'HEADER_X_ROBOTS_TAG_HELP_TEXT' => 'Opcja ta określa zachowanie robotów sieciowych. Zalecana jest opcja "none", która oznacza brak indeksowania strony oraz nie podążanie za linkami przez roboty sieciowe.',
	'HEADER_X_PERMITTED_CROSS_DOMAIN_POLICIES_HELP_TEXT' => 'Określa sposób traktowania "plików strategii" na serwerze np. plik "crossdomain.xml". Zalecana opcja to brak obsługi "plików strategii".',
	'HEADER_X_POWERED_BY_HELP_TEXT' => 'Ten nagłówek przekazuje informacje o serwerze PHP. Ze względu bezpieczeństwa zaleca się, aby był pusty (nie zawierał żadnych informacji).',
	'HEADER_SERVER_HELP_TEXT' => 'Ten nagłówek przekazuje ogólne informacje o serwerze. Ze względu bezpieczeństwa zaleca się, aby był pusty (nie zawierał żadnych informacji).',
	'HEADER_REFERRER_POLICY_HELP_TEXT' => 'Określa kontrolę nad danymi pojawiającymi się w nagłówku Referer. Domyślna opcja to "same-origin", która oznacza pojawianie się tego nagłówka tylko z tej samej domeny.',
	'HEADER_EXPECT_CT_HELP_TEXT' => 'Nagłówek określa jak traktowany jest certyfikat na stronie. Zalecana opcja to "enforce; max-age=3600" która oznacza egzekwowanie poprawności certyfikatu i ważność tego certyfikatu wynosi 3600 sekund.',
	'HEADER_STRICT_TRANSPORT_SECURITY_HELP_TEXT' => 'Oznacza, że przeglądarka internetowa powinna się komunikować tylko po protokole HTTPS, a nie przez protokół HTTP. Zalecany czas dla takiej komunikacji tylko po HTTPS wynosi 31536000 sekund.',
	'LBL_TMP_DIR' => 'Tymczasowy katalog',
	'LBL_INVALID_TIME_ZONE' => 'Nieprawidłowa strefa czasowa: ',
	'LBL_DATABASE_INFORMATION' => 'Informacje o bazie danych',
	'LBL_DB_DRIVER' => 'Silnik',
	'LBL_DB_CLIENT_VERSION' => 'Wersja klienta',
	'LBL_DB_SERVER_VERSION' => 'Wersja silnika',
	'LBL_DB_CONNECTION_STATUS' => 'Status połączenia',
	'LBL_DB_SERVER_INFO' => 'Infomracje o serwerze',
];
$jsLanguageStrings = [
	'JS_SPEED_TEST_START' => 'Trwa sprawdzanie szybkości...',
];
