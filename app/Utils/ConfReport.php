<?php

/**
 * Conf report class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Utils;

/**
 * Conf report.
 */
class ConfReport
{
	/** @var string System URL. */
	private static $crmUrl;

	/** @var string System URL. */
	public static $testCli = false;

	/** @var array Optional database configuration for offline use. */
	public static $dbConfig = [
		'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=yetiforce;',
		'user' => '',
		'password' => '',
		'options' => [],
	];

	/** @var array Urls to check in request. */
	public static $urlsToCheck = ['root' => 'token.php', 'js' => 'layouts/resources/Tools.js', 'css' => 'layouts/resources/fonts/fonts.css'];

	/**
	 * List all variables.
	 *
	 * @var string[]
	 */
	public static $types = ['stability', 'security', 'libraries', 'database', 'performance', 'environment', 'writableFilesAndFolders', 'functionalVerification', 'headers', 'publicDirectoryAccess', 'pathVerification'];

	/**
	 * List all container.
	 *
	 * @var string[]
	 */
	public static $container = ['php', 'env', 'ext', 'request', 'db', 'writableFilesAndFolders'];

	/**
	 * Stability variables map.
	 *
	 * @var array
	 */
	public static $stability = [
		'phpVersion' => ['recommended' => '7.4.x, 8.0.x, 8.1.x (dev)', 'type' => 'Version', 'container' => 'env', 'testCli' => true, 'label' => 'PHP'],
		'protocolVersion' => ['recommended' => '2.0, 1.x', 'type' => 'Version', 'container' => 'env', 'testCli' => false, 'label' => 'PROTOCOL_VERSION'],
		'error_reporting' => ['recommended' => 'E_ALL & ~E_NOTICE', 'type' => 'ErrorReporting', 'container' => 'php', 'testCli' => true],
		'output_buffering' => ['recommended' => 'On', 'type' => 'OnOffInt', 'container' => 'php', 'testCli' => true],
		'max_execution_time' => ['recommended' => 600, 'type' => 'Greater', 'container' => 'php', 'testCli' => true],
		'max_input_time' => ['recommended' => 600, 'type' => 'Greater', 'container' => 'php', 'testCli' => true],
		'default_socket_timeout' => ['recommended' => 600, 'type' => 'Greater', 'container' => 'php', 'testCli' => true],
		'memory_limit' => ['recommended' => '1 GB', 'type' => 'GreaterMb', 'container' => 'php', 'testCli' => true],
		'log_errors' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'file_uploads' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'short_open_tag' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'post_max_size' => ['recommended' => '50 MB', 'type' => 'GreaterMb', 'container' => 'php', 'testCli' => true],
		'upload_max_filesize' => ['recommended' => '100 MB', 'type' => 'GreaterMb', 'container' => 'php', 'testCli' => true],
		'max_input_vars' => ['recommended' => 10000, 'type' => 'Greater', 'container' => 'php', 'testCli' => true],
		'zlib.output_compression' => ['recommended' => 'Off', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'session.auto_start' => ['recommended' => 'Off', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'session.gc_maxlifetime' => ['recommended' => 1440, 'type' => 'Greater', 'container' => 'php', 'testCli' => true],
		'session.gc_divisor' => ['recommended' => 500, 'type' => 'Greater', 'container' => 'php', 'testCli' => true],
		'session.gc_probability' => ['recommended' => 1, 'type' => 'Equal', 'container' => 'php', 'testCli' => true],
		'suhosin.session.encrypt' => ['recommended' => 'Off', 'type' => 'Equal', 'container' => 'php', 'testCli' => true], //Roundcube
		'mbstring.func_overload' => ['recommended' => 0, 'type' => 'Equal', 'container' => 'php', 'testCli' => true], //Roundcube
		'pcre.backtrack_limit' => ['recommended' => 100000, 'type' => 'Greater', 'container' => 'php', 'testCli' => true], //Roundcube
		'date.timezone' => ['type' => 'TimeZone', 'container' => 'php', 'testCli' => true], //Roundcube
		'allow_url_fopen' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true], //Roundcube
		'auto_detect_line_endings' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true], //CSVReader
		'httpMethods' => ['recommended' => 'GET, POST, PUT, OPTIONS, PATCH, PROPFIND, REPORT, LOCK, DELETE, COPY, MOVE', 'type' => 'HttpMethods', 'container' => 'request', 'testCli' => true, 'label' => 'HTTP_METHODS'],
		'request_order' => ['recommended' => 'GP', 'type' => 'Equal', 'container' => 'php', 'testCli' => true],
		'variables_order' => ['recommended' => 'GPCS', 'type' => 'Equal', 'container' => 'php', 'testCli' => true],
		'opcache.jit' => ['container' => 'php', 'testCli' => true],
		'opcache.jit_buffer_size' => ['container' => 'php', 'testCli' => true],
		'mysqli.allow_persistent' => ['container' => 'php', 'testCli' => true],
		'mysqli.max_persistent' => ['container' => 'php', 'testCli' => true],
	];
	/**
	 * Security variables map.
	 *
	 * @var array
	 */
	public static $security = [
		'HTTPS' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'env', 'testCli' => false],
		'public_html' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'env', 'testCli' => false],
		'display_errors' => ['recommended' => 'Off', 'type' => 'OnOff', 'container' => 'php', 'demoMode' => true, 'testCli' => true],
		'session.use_strict_mode' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'session.use_trans_sid' => ['recommended' => 'Off', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'session.cookie_httponly' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => false],
		'session.use_only_cookies' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => false],
		'session.cookie_secure' => ['recommended' => '', 'type' => 'CookieSecure', 'container' => 'php', 'testCli' => false],
		'session.cookie_samesite' => ['recommended' => '', 'type' => 'CookieSamesite', 'container' => 'php', 'testCli' => false],
		'session.name' => ['recommended' => 'YTSID', 'container' => 'php', 'type' => 'Equal', 'testCli' => false],
		'expose_php' => ['recommended' => 'Off', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'session_regenerate_id' => ['recommended' => 'On', 'type' => 'SessionRegenerate', 'testCli' => true],
		'disable_functions' => ['recommended' => 'shell_exec, exec, system, passthru, popen', 'type' => 'In', 'container' => 'php', 'testCli' => false],
		'allow_url_include' => ['recommended' => 'Off', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
	];

	/**
	 * Headers variables map.
	 *
	 * @var array
	 */
	public static $headers = [
		'Header: server' => ['recommended' => '', 'type' => 'HeaderServer', 'container' => 'request', 'testCli' => false],
		'Header: x-powered-by' => ['recommended' => '', 'type' => 'Header', 'contaiuse_only_cookiesner' => 'request', 'testCli' => false],
		'Header: access-control-allow-methods' => ['recommended' => 'GET, POST', 'type' => 'Header', 'container' => 'request', 'testCli' => false, 'onlyPhp' => true],
		'Header: access-control-allow-origin' => ['recommended' => '*', 'type' => 'Header', 'container' => 'request', 'testCli' => false, 'onlyPhp' => true],
		'Header: referrer-policy' => ['recommended' => 'no-referrer', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: expect-ct' => ['recommended' => 'enforce; max-age=3600', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: x-frame-options' => ['recommended' => 'sameorigin', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: x-xss-protection' => ['recommended' => '1; mode=block', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: x-content-type-options' => ['recommended' => 'nosniff', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: x-robots-tag' => ['recommended' => 'none', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: x-permitted-cross-domain-policies' => ['recommended' => 'none', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: strict-transport-security' => ['recommended' => 'max-age=31536000; includeSubDomains; preload', 'type' => 'Header', 'container' => 'request', 'testCli' => false, 'httpsRequired' => true],
		'Header: content-security-policy' => ['recommended' => '', 'type' => 'HeaderCsp', 'container' => 'request', 'testCli' => false],
	];

	/**
	 * Libraries map.
	 *
	 * @var array
	 */
	public static $libraries = [
		'imap' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'imap', 'container' => 'ext', 'testCli' => true],
		'PDO' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'PDO', 'container' => 'ext', 'testCli' => true],
		'pdo_mysql' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'pdo_mysql', 'container' => 'ext', 'testCli' => true],
		'mysqlnd' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'mysqlnd', 'container' => 'ext', 'testCli' => true],
		'openssl' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'openssl', 'container' => 'ext', 'testCli' => true],
		'curl' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'curl', 'container' => 'ext', 'testCli' => true],
		'gd' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'gd', 'container' => 'ext', 'testCli' => true],
		'pcre' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'pcre', 'container' => 'ext', 'testCli' => true],
		'xml' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'xml', 'container' => 'ext', 'testCli' => true],
		'json' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'json', 'container' => 'ext', 'testCli' => true],
		'session' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'session', 'container' => 'ext', 'testCli' => true],
		'dom' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'dom', 'container' => 'ext', 'testCli' => true],
		'zip' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'zip', 'container' => 'ext', 'testCli' => true],
		'mbstring' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'mbstring', 'container' => 'ext', 'testCli' => true],
		'soap' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'soap', 'container' => 'ext', 'testCli' => true],
		'fileinfo' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'fileinfo', 'container' => 'ext', 'testCli' => true],
		'iconv' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'iconv', 'container' => 'ext', 'testCli' => true],
		'intl' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'intl', 'container' => 'ext', 'testCli' => true],
		'SPL' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'SPL', 'container' => 'ext', 'testCli' => true],
		'Reflection' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'Reflection', 'container' => 'ext', 'testCli' => true],
		'SimpleXML' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'SimpleXML', 'container' => 'ext', 'testCli' => true],
		'bcmath' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'bcmath', 'container' => 'ext', 'testCli' => true],
		'filter' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'filter', 'container' => 'ext', 'testCli' => true],
		'ctype' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'ctype', 'container' => 'ext', 'testCli' => true],
		'hash' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'hash', 'container' => 'ext', 'testCli' => true],
		'exif' => ['mandatory' => false, 'type' => 'ExtExist', 'extName' => 'exif', 'container' => 'ext', 'testCli' => true, 'mode' => 'showWarnings'],
		'ldap' => ['mandatory' => false, 'type' => 'ExtExist', 'extName' => 'ldap', 'container' => 'ext', 'testCli' => true, 'mode' => 'showWarnings'],
		'OPcache' => ['mandatory' => false, 'type' => 'ExtExist', 'extName' => 'Zend OPcache', 'container' => 'ext', 'testCli' => true, 'mode' => 'showWarnings'],
		'apcu' => ['mandatory' => false, 'type' => 'ExtExist', 'extName' => 'apcu', 'container' => 'ext', 'testCli' => true, 'mode' => 'showWarnings'],
		'imagick' => ['mandatory' => false, 'type' => 'ExtExist', 'extName' => 'imagick', 'container' => 'ext', 'testCli' => true, 'mode' => 'showWarnings'],
		'allExt' => ['container' => 'ext', 'type' => 'AllExt', 'testCli' => true, 'label' => 'EXTENSIONS'],
	];

	/**
	 * Database map.
	 *
	 * @var array
	 */
	public static $database = [
		'driver' => ['recommended' => 'mysql', 'type' => 'Equal', 'container' => 'db', 'testCli' => true, 'label' => 'DB_DRIVER'],
		'typeDb' => ['container' => 'db', 'testCli' => true, 'label' => 'DB_VERSION_TYPE'],
		'serverVersion' => ['recommended' => ['MariaDb' => '10.x', 'MySQL' => '5.6.x'], 'type' => 'VersionDb', 'container' => 'db', 'testCli' => true, 'label' => 'DB_SERVER_VERSION'],
		'clientVersion' => ['container' => 'db', 'testCli' => true, 'label' => 'DB_CLIENT_VERSION'],
		'version_comment' => ['container' => 'db', 'testCli' => true, 'label' => 'DB_VERSION_COMMENT'],
		'connectionStatus' => ['container' => 'db', 'testCli' => true, 'label' => 'DB_CONNECTION_STATUS'],
		'serverInfo' => ['container' => 'db', 'testCli' => true, 'label' => 'DB_SERVER_INFO'],
		'maximumMemorySize' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true, 'label' => 'DB_MAXIMUM_MEMORY_SIZE', 'showHelp' => true],
		'key_buffer_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'have_query_cache' => ['container' => 'db', 'testCli' => true],
		'query_cache_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'query_cache_type' => ['container' => 'db', 'testCli' => true],
		'table_cache' => ['container' => 'db', 'testCli' => true],
		'table_open_cache_instances' => ['container' => 'db', 'testCli' => true],
		'table_open_cache' => ['recommended' => 1000, 'type' => 'Greater', 'container' => 'db', 'testCli' => true, 'mode' => 'showWarnings'],
		'table_definition_cache' => ['type' => 'DbTableDefinitionCache', 'container' => 'db', 'testCli' => true, 'mode' => 'showWarnings'],
		'open_files_limit' => ['container' => 'db', 'testCli' => true],
		'tmp_table_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'innodb_buffer_pool_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'innodb_additional_mem_pool_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'innodb_log_buffer_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'max_connections' => ['container' => 'db', 'testCli' => true],
		'sort_buffer_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'myisam_sort_buffer_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'read_buffer_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'read_rnd_buffer_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'join_buffer_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'thread_stack' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'binlog_cache_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'bulk_insert_buffer_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'max_heap_table_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'innodb_log_file_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'innodb_lock_wait_timeout' => ['recommended' => 600, 'type' => 'Greater', 'container' => 'db', 'testCli' => true],
		'wait_timeout' => ['recommended' => 600, 'type' => 'Greater', 'container' => 'db', 'testCli' => true],
		'interactive_timeout' => ['recommended' => 600, 'type' => 'Greater', 'container' => 'db', 'testCli' => true],
		'sql_mode' => ['recommended' => '', 'type' => 'NotIn', 'container' => 'db', 'testCli' => true, 'exclusions' => ['STRICT_ALL_TABLES', 'STRICT_TRANS_TABLE']],
		'max_allowed_packet' => ['recommended' => '10 MB', 'type' => 'GreaterMb', 'container' => 'db', 'testCli' => true],
		'thread_cache_size' => ['container' => 'db', 'testCli' => true],
		'tx_isolation' => ['container' => 'db', 'testCli' => true],
		'transaction_isolation' => ['container' => 'db', 'testCli' => true],
		'ft_min_word_len' => ['container' => 'db', 'testCli' => true],
		'innodb_ft_min_token_size' => ['container' => 'db', 'testCli' => true],
		'innodb_default_row_format' => ['recommended' => 'dynamic', 'type' => 'Equal', 'container' => 'db', 'testCli' => true],
		'innodb_strict_mode' => ['container' => 'db', 'testCli' => true],
		'innodb_large_prefix' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'db', 'testCli' => true], //Roundcube
		'innodb_file_per_table' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'db', 'testCli' => true], //Roundcube
		'innodb_stats_on_metadata' => ['recommended' => 'Off', 'type' => 'OnOff', 'container' => 'db', 'testCli' => true],
		'innodb_buffer_pool_instances' => ['container' => 'db', 'testCli' => true],
		'innodb_buffer_pool_load_at_startup' => ['container' => 'db', 'testCli' => true],
		'innodb_io_capacity' => ['container' => 'db', 'testCli' => true],
		'innodb_io_capacity_max' => ['container' => 'db', 'testCli' => true],
		'innodb_file_format' => ['container' => 'db', 'testCli' => true],
		'innodb_file_format_check' => ['container' => 'db', 'testCli' => true],
		'innodb_file_format_max' => ['container' => 'db', 'testCli' => true],
		'character_set_server' => ['recommended' => 'utf8', 'values' => ['utf8', 'utf8mb3', 'utf8mb4'], 'type' => 'OneOf', 'container' => 'db', 'testCli' => true],
		'character_set_database' => ['recommended' => 'utf8', 'values' => ['utf8', 'utf8mb3', 'utf8mb4'], 'type' => 'OneOf', 'container' => 'db', 'testCli' => true],
		'character_set_client' => ['recommended' => 'utf8', 'values' => ['utf8', 'utf8mb3', 'utf8mb4'], 'type' => 'OneOf', 'container' => 'db', 'testCli' => true],
		'character_set_connection' => ['recommended' => 'utf8', 'values' => ['utf8', 'utf8mb3', 'utf8mb4'], 'type' => 'OneOf', 'container' => 'db', 'testCli' => true],
		'character_set_results' => ['recommended' => 'utf8', 'values' => ['utf8', 'utf8mb3', 'utf8mb4'], 'type' => 'OneOf', 'container' => 'db', 'testCli' => true],
		'character_set_system' => ['container' => 'db', 'testCli' => true],
		'character_set_filesystem' => ['container' => 'db', 'testCli' => true],
		'datadir' => ['container' => 'db', 'testCli' => true],
		'connect_timeout' => ['container' => 'db', 'testCli' => true],
		'lock_wait_timeout' => ['container' => 'db', 'testCli' => true],
		'net_read_timeout' => ['container' => 'db', 'testCli' => true],
		'net_write_timeout' => ['container' => 'db', 'testCli' => true],
		'aria_recover_options' => ['container' => 'db', 'testCli' => true],
		'aria_recover' => ['container' => 'db', 'testCli' => true],
		'hostname' => ['container' => 'db', 'testCli' => true],
		'innodb_checksum_algorithm' => ['container' => 'db', 'testCli' => true],
		'innodb_flush_method' => ['container' => 'db', 'testCli' => true],
		'innodb_thread_sleep_delay' => ['container' => 'db', 'testCli' => true],
		'innodb_thread_concurrency' => ['container' => 'db', 'testCli' => true],
		'innodb_adaptive_max_sleep_delay' => ['container' => 'db', 'testCli' => true],
		'innodb_read_ahead_threshold' => ['container' => 'db', 'testCli' => true],
		'innodb_max_dirty_pages_pct_lwm' => ['container' => 'db', 'testCli' => true],
		'innodb_open_files' => ['container' => 'db', 'testCli' => true],
		'thread_pool_max_threads' => ['container' => 'db', 'testCli' => true],
		'innodb_read_io_threads' => ['container' => 'db', 'testCli' => true],
		'innodb_write_io_threads' => ['container' => 'db', 'testCli' => true],
		'lower_case_file_system' => ['container' => 'db', 'testCli' => true],
		'lower_case_table_names' => ['container' => 'db', 'testCli' => true],
		'system_time_zone' => ['container' => 'db', 'testCli' => true],
		'use_stat_tables' => ['container' => 'db', 'testCli' => true],
		'thread_handling' => ['container' => 'db', 'testCli' => true],
		'host_cache_size' => ['container' => 'db', 'testCli' => true],
		'optimizer_search_depth' => ['container' => 'db', 'testCli' => true],
		'version_compile_machine' => ['container' => 'db', 'testCli' => true],
		'version_compile_os' => ['container' => 'db', 'testCli' => true],
		'socket' => ['container' => 'db', 'testCli' => true],
		'back_log' => ['container' => 'db', 'testCli' => true],
		'log_bin' => ['container' => 'db', 'testCli' => true],
		'log_bin_basename' => ['container' => 'db', 'testCli' => true],
		'log_slave_updates' => ['container' => 'db', 'testCli' => true],
		'binlog_format' => ['container' => 'db', 'testCli' => true],
		'max_binlog_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => true],
		'slow_query_log' => ['container' => 'db', 'testCli' => true],
		'slow_query_log_file' => ['container' => 'db', 'testCli' => true],
		'log_slow_admin_statements' => ['container' => 'db', 'testCli' => true],
		'general_log' => ['container' => 'db', 'testCli' => true],
		'general_log_file' => ['container' => 'db', 'testCli' => true],
		'log_error' => ['container' => 'db', 'testCli' => true],
		'log_warnings' => ['container' => 'db', 'testCli' => true],
		'log_output' => ['container' => 'db', 'testCli' => true],
	];

	/**
	 * Performance map.
	 *
	 * @var array
	 */
	public static $performance = [
		'xdebug' => ['recommended' => 'Off', 'type' => 'ExtNotExist', 'extName' => 'xdebug', 'container' => 'ext', 'testCli' => true],
		'opcache.enable' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'opcache.enable_cli' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'opcache.max_accelerated_files' => ['recommended' => 40000, 'type' => 'Greater', 'container' => 'php', 'testCli' => true],
		'opcache.interned_strings_buffer' => ['recommended' => 100, 'type' => 'Greater', 'container' => 'php', 'testCli' => true],
		'opcache.validate_timestamps' => ['recommended' => 1, 'type' => 'Equal', 'container' => 'php', 'testCli' => true],
		'opcache.revalidate_freq' => ['recommended' => 0, 'type' => 'Equal', 'container' => 'php', 'testCli' => true],
		'opcache.save_comments' => ['recommended' => 0, 'type' => 'Equal', 'container' => 'php', 'testCli' => true],
		'opcache.file_update_protection' => ['recommended' => 0, 'type' => 'Equal', 'container' => 'php', 'testCli' => true],
		'opcache.memory_consumption' => ['container' => 'php', 'testCli' => true],
		'realpath_cache_size' => ['recommended' => '256k', 'type' => 'GreaterMb', 'container' => 'php', 'testCli' => true],
		'realpath_cache_ttl' => ['recommended' => 600, 'type' => 'Greater', 'container' => 'php', 'testCli' => true],
		'mysqlnd.collect_statistics' => ['recommended' => 'Off', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'mysqlnd.collect_memory_statistics' => ['recommended' => 'Off', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'apc.enabled' => ['container' => 'php', 'testCli' => true],
		'apc.enable_cli' => ['container' => 'php', 'testCli' => true],
		'apc.preload_path' => ['container' => 'php', 'testCli' => true],
		'apc.ttl' => ['container' => 'php', 'testCli' => true],
		'apc.user_ttl' => ['container' => 'php', 'testCli' => true],
		'apc.num_files_hint' => ['container' => 'php', 'testCli' => true],
		'apc.stat' => ['container' => 'php', 'testCli' => true],
		'apc.optimization' => ['container' => 'php', 'testCli' => true],
		'apc.cache_by_default' => ['container' => 'php', 'testCli' => true],
		'apc.mmap_file_mask' => ['container' => 'php', 'testCli' => true],
		'apc.shm_segments' => ['container' => 'php', 'testCli' => true],
	];

	/**
	 * Environment map.
	 *
	 * @var array
	 */
	public static $environment = [
		'crmVersion' => ['container' => 'env', 'testCli' => false, 'label' => 'CRM_VERSION'],
		'crmDate' => ['container' => 'env', 'testCli' => false, 'label' => 'CRM_DATE'],
		'companySize' => ['container' => 'env', 'testCli' => false, 'label' => 'COMPANY_SIZE'],
		'operatingSystem' => ['container' => 'env', 'testCli' => true, 'label' => 'OPERATING_SYSTEM'],
		'serverSoftware' => ['container' => 'env', 'testCli' => false, 'label' => 'SERVER_SOFTWARE'],
		'currentUser' => ['container' => 'env', 'type' => 'CronEqual', 'testCli' => true, 'label' => 'SCRIPT_USER'],
		'tempDir' => ['container' => 'env', 'testCli' => true, 'label' => 'TMP_DIR'],
		'crmDir' => ['container' => 'env', 'testCli' => false, 'label' => 'CRM_DIR'],
		'sapi' => ['container' => 'env', 'testCli' => true, 'label' => 'PHP_SAPI'],
		'zendVersion' => ['container' => 'env', 'testCli' => true, 'label' => 'ZEND_VERSION'],
		'locale' => ['container' => 'env', 'testCli' => true, 'label' => 'LOCALE'],
		'error_log' => ['type' => 'ErrorLog', 'container' => 'php', 'testCli' => true, 'label' => 'LOG_FILE'],
		'phpIni' => ['container' => 'env', 'testCli' => true, 'label' => 'PHPINI'],
		'phpIniAll' => ['container' => 'env', 'testCli' => true, 'label' => 'PHPINIS'],
		'spaceRoot' => ['container' => 'env', 'type' => 'Space', 'testCli' => false, 'label' => 'SPACE_ROOT'],
		'spaceStorage' => ['container' => 'env', 'type' => 'Space', 'testCli' => false, 'label' => 'SPACE_STORAGE'],
		'spaceTemp' => ['container' => 'env', 'type' => 'Space', 'testCli' => false, 'label' => 'SPACE_TEMP'],
		'spaceBackup' => ['container' => 'env', 'type' => 'Space', 'testCli' => false, 'label' => 'SPACE_BACKUP'],
		'lastCronStart' => ['container' => 'env', 'testCli' => false, 'label' => 'LAST_CRON_START', 'isHtml' => true],
		'crmProvider' => ['container' => 'env', 'testCli' => true, 'label' => 'CRM_PROVIDER'],
		'appId' => ['container' => 'env', 'testCli' => true, 'label' => 'APP_ID'],
		'open_basedir' => ['container' => 'php',  'type' => 'OpenBasedir', 'testCli' => true, 'mode' => 'showWarnings'],
		'caCertBundle' => ['recommended' => 'On', 'container' => 'env', 'type' => 'OnOff', 'testCli' => true, 'label' => 'CACERTBUNDLE'],
		'caCertBundlePath' => ['recommended' => 'On', 'container' => 'env', 'testCli' => true, 'label' => 'CACERTBUNDLE_PATH'],
		'SSL_CERT_FILE' => ['container' => 'env', 'testCli' => true, 'label' => 'SSL_CERT_FILE'],
		'SSL_CERT_DIR' => ['container' => 'env', 'testCli' => true, 'label' => 'SSL_CERT_DIR'],
		'openssl.cafile' => ['container' => 'php',  'type' => 'NotEmpty', 'testCli' => true, 'mode' => 'showWarnings'],
		'openssl.capath' => ['container' => 'php',  'type' => 'NotEmpty', 'testCli' => true, 'mode' => 'showWarnings'],
	];

	/**
	 * Directory permissions map.
	 *
	 * @var array
	 */
	public static $publicDirectoryAccess = [
		'config' => ['type' => 'NotExistsUrl', 'container' => 'request', 'testCli' => false],
		'cache' => ['type' => 'NotExistsUrl', 'container' => 'request', 'testCli' => false],
		'app_data' => ['type' => 'NotExistsUrl', 'container' => 'request', 'testCli' => false],
		'storage' => ['type' => 'NotExistsUrl', 'container' => 'request', 'testCli' => false],
		'user_privileges' => ['type' => 'NotExistsUrl', 'container' => 'request', 'testCli' => false],
	];
	/**
	 * Path verification.
	 *
	 * @var array
	 */
	public static $pathVerification = [
		'webservice/WebserviceStandard/' => ['type' => 'Webservice', 'container' => 'request', 'testCli' => false],
		'.well-known/carddav' => ['type' => 'ExistsUrl', 'container' => 'request', 'testCli' => false],
		'.well-known/caldav' => ['type' => 'ExistsUrl', 'container' => 'request', 'testCli' => false],
		'robots.txt' => ['type' => 'ExistsUrl', 'container' => 'request', 'testCli' => false],
		'install/index.php' => ['type' => 'NotExistsUrl', 'container' => 'request', 'testCli' => false],
	];

	/**
	 * Writable files and folders permissions map.
	 *
	 * @var array
	 */
	public static $writableFilesAndFolders = [
		'app_data/cron.php' => ['type' => 'IsWritable', 'testCli' => true],
		'app_data/registration.php' => ['type' => 'IsWritable', 'testCli' => true],
		'app_data/moduleHierarchy.php' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'app_data/shop.php' => ['type' => 'IsWritable', 'testCli' => true],
		'app_data/icons.php' => ['type' => 'IsWritable', 'testCli' => true],
		'app_data/LanguagesUpdater.json' => ['type' => 'IsWritable', 'testCli' => true],
		'app_data/SystemUpdater.json' => ['type' => 'IsWritable', 'testCli' => true],
		'app_data/libraries.json' => ['type' => 'IsWritable', 'testCli' => true],
		'user_privileges/tabdata.php' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'user_privileges/menu_0.php' => ['type' => 'IsWritable', 'testCli' => true],
		'user_privileges/user_privileges_1.php' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/logs/system.log' => ['type' => 'IsWritable', 'testCli' => true],
		'app_data/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'app_data/shop/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'cache/addressBook/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/images/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/import/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/mail/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/pdf/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/logs/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'cache/logs/cron/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/session/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'cache/templates_c/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'cache/upload/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'cache/vtlib/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'cache/vtlib/HTML' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'config/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'config/Components' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'config/Modules' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'user_privileges/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'cron/modules/' => ['type' => 'IsWritable', 'testCli' => true],
		'languages/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'install/' => ['type' => 'IsWritable', 'testCli' => true],
		'modules/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'storage/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'storage/Products/' => ['type' => 'IsWritable', 'testCli' => true],
		'storage/Users/' => ['type' => 'IsWritable', 'testCli' => true],
		'storage/Contacts/' => ['type' => 'IsWritable', 'testCli' => true],
		'storage/OSSMailView/' => ['type' => 'IsWritable', 'testCli' => true],
		'public_html/modules/OSSMail/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'public_html/libraries/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
		'public_html/layouts/resources/Logo/' => ['type' => 'IsWritable', 'testCli' => true, 'mustExist' => true],
	];
	/**
	 * Functionality test map.
	 *
	 * @var array
	 */
	public static $functionalVerification = [
		'branding' => ['type' => 'Branding',  'testCli' => false, 'label' => 'FOOTER', 'mode' => 'onlyText'],
		'shop' => ['type' => 'ShopProducts',  'testCli' => false, 'label' => 'PREMIUM_MODULES', 'mode' => 'onlyText'],
		'watchdog' => ['type' => 'Watchdog',  'testCli' => true, 'label' => 'WATCHDOG', 'mode' => 'onlyText'],
		'register' => ['type' => 'Register',  'testCli' => true, 'label' => 'REGISTER', 'mode' => 'onlyText'],
		'shopCache' => ['type' => 'ShopCache',  'testCli' => true, 'label' => 'SHOP_CACHE', 'mode' => 'onlyText'],
	];
	/**
	 * Php variables.
	 *
	 * @var mixed[]
	 */
	private static $php = [];

	/**
	 * Environment variables.
	 *
	 * @var mixed[]
	 */
	private static $env = [];

	/**
	 * Database variables.
	 *
	 * @var mixed[]
	 */
	private static $db = [];

	/**
	 * Extensions.
	 *
	 * @var mixed[]
	 */
	private static $ext = [];

	/**
	 * Request request.
	 *
	 * @var mixed[]
	 */
	private static $request = [];

	/**
	 * Sapi name.
	 *
	 * @var string
	 */
	public static $sapi = 'www';
	/**
	 * Errors.
	 *
	 * @var string[]
	 */
	public static $errors = [];

	/**
	 * Get all configuration values.
	 *
	 * @return array
	 */
	public static function getAll(): array
	{
		return static::getByType(static::$types, true);
	}

	/**
	 * Get configuration values by type.
	 *
	 * @param array $types
	 * @param bool  $initAll
	 *
	 * @return array
	 */
	public static function getByType(array $types, bool $initAll = false): array
	{
		if ($initAll) {
			static::init('all');
		}
		$returnVal = [];
		foreach ($types as $type) {
			if (!\in_array($type, static::$types)) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
			}
			if (!$initAll) {
				static::init($type);
			}
			$returnVal[$type] = static::validate($type);
		}
		return $returnVal;
	}

	/**
	 * Initializing variables.
	 *
	 * @param string $type
	 */
	private static function init(string $type)
	{
		if (\App\Config::main('site_URL')) {
			static::$crmUrl = \App\Config::main('site_URL');
		} elseif (isset(\App\Process::$requestMode) && 'Install' === \App\Process::$requestMode) {
			static::$crmUrl = str_replace('/install/', '/', \App\RequestUtil::getBrowserInfo()->siteUrl);
		}
		$types = static::$container;
		if (isset(static::${$type})) {
			$types = \array_unique(\array_column(static::${$type}, 'container'));
		}
		$conf = static::getConfig();
		foreach ($types as $item) {
			switch ($item) {
				case 'php':
					static::$php = $conf['php'];
					break;
				case 'env':
					static::$env = $conf['env'];
					break;
				case 'ext':
					static::$ext = get_loaded_extensions();
					break;
				case 'request':
					static::$request = static::getRequest();
					break;
				case 'db':
					$db = \App\Db::getInstance();
					if ($db->getMasterPdo()) {
						static::$db = $db->getInfo();
					}
					break;
				case 'writableFilesAndFolders':
					if ($tmp = sys_get_temp_dir()) {
						self::$writableFilesAndFolders[$tmp] = ['type' => 'IsWritable', 'testCli' => true, 'absolutePaths' => true, 'mustExist' => true];
					}
					if ($tmp = ini_get('upload_tmp_dir')) {
						self::$writableFilesAndFolders[$tmp] = ['type' => 'IsWritable', 'testCli' => true, 'absolutePaths' => true, 'mustExist' => true];
					}
					break;
				default:
					break;
			}
		}
	}

	/**
	 * Get environment variables.
	 *
	 * @return array
	 */
	public static function getConfig()
	{
		$php = [];
		foreach (ini_get_all() as $key => $value) {
			$php[$key] = $value['local_value'];
		}
		$locale = '';
		if (\function_exists('locale_get_default')) {
			$locale = print_r(locale_get_default(), true);
		}
		$cron = static::getCronVariables('last_start');
		$lastCronStart = '-';
		$lastCronStartText = '-';
		if ($cron) {
			$lastCronStart = date('Y-m-d H:i:s', $cron);
			$lastCronStartText = \App\Fields\DateTime::formatToViewDate($lastCronStart);
		}
		$caCertBundlePath = realpath(\Composer\CaBundle\CaBundle::getSystemCaRootBundlePath());
		if (0 === strpos($caCertBundlePath, ROOT_DIRECTORY)) {
			$caCertBundlePath = str_replace(ROOT_DIRECTORY, '__CRM_PATH__', $caCertBundlePath);
		}
		return [
			'php' => $php,
			'env' => [
				'phpVersion' => PHP_VERSION,
				'sapi' => \PHP_SAPI,
				'zendVersion' => zend_version(),
				'phpIni' => php_ini_loaded_file() ?: '-',
				'phpIniAll' => php_ini_scanned_files() ?: '-',
				'locale' => $locale,
				'https' => \App\RequestUtil::isHttps(),
				'caCertBundle' => \is_file(\Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()) ? 'On' : 'Off',
				'caCertBundlePath' => $caCertBundlePath,
				'public_html' => IS_PUBLIC_DIR ? 'On' : 'Off',
				'crmVersion' => \App\Version::get(),
				'crmDate' => \App\Version::get('patchVersion'),
				'companySize' => \App\Config::main('application_unique_key') ? \App\Company::getSize() : '-',
				'crmDir' => ROOT_DIRECTORY,
				'operatingSystem' => 'demo' === \App\Config::main('systemMode') ? php_uname('s') : php_uname(),
				'serverSoftware' => $_SERVER['SERVER_SOFTWARE'] ?? '-',
				'currentUser' => (\function_exists('get_current_user') ? get_current_user() : '') . ((\function_exists('getmyuid') && getmyuid()) ? ' (uid:' . getmyuid() . ')' : ''),
				'tempDir' => \App\Fields\File::getTmpPath(),
				'spaceRoot' => '',
				'spaceStorage' => '',
				'spaceTemp' => '',
				'spaceBackup' => '',
				'crmProvider' => \App\YetiForce\Register::getProvider(),
				'appId' => substr(\App\YetiForce\Register::getInstanceKey(), -15),
				'lastCronStart' => $lastCronStartText,
				'lastCronStartDateTime' => $lastCronStart,
				'protocolVersion' => isset($_SERVER['SERVER_PROTOCOL']) ? substr($_SERVER['SERVER_PROTOCOL'], strpos($_SERVER['SERVER_PROTOCOL'], '/') + 1) : '-',
				'SSL_CERT_FILE' => getenv('SSL_CERT_FILE') ?? '',
				'SSL_CERT_DIR' => getenv('SSL_CERT_DIR') ?? '',
			],
		];
	}

	/**
	 * Get variable for cron.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public static function getCronVariables(string $type)
	{
		$data = [];
		$filePath = ROOT_DIRECTORY . '/app_data/cron.php';
		if (file_exists($filePath)) {
			try {
				$cron = include $filePath;
				$data = $cron[$type] ?? null;
			} catch (\Throwable $e) {
				unlink($filePath);
				throw $e;
			}
		}
		return $data;
	}

	/**
	 * Get request request.
	 *
	 * @return array
	 */
	private static function getRequest()
	{
		$requestUrl = static::$crmUrl;
		if (\PHP_SAPI !== 'cli' && !IS_PUBLIC_DIR) {
			$requestUrl .= 'public_html/';
		}
		$request = [];
		try {
			foreach (static::$urlsToCheck as $type => $url) {
				$urlAddress = $requestUrl . $url;
				\App\Log::beginProfile("GET|ConfReport::getRequest|{$urlAddress}", __NAMESPACE__);
				$res = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('HEAD', $urlAddress, ['timeout' => 1, 'verify' => false]);
				\App\Log::endProfile("GET|ConfReport::getRequest|{$urlAddress}", __NAMESPACE__);
				foreach ($res->getHeaders() as $key => $value) {
					$request[strtolower($key)][$type] = \is_array($value) ? implode(',', $value) : $value;
				}
			}
		} catch (\Throwable $e) {
			self::$errors[__FUNCTION__] = $e->getMessage();
		}
		return $request;
	}

	/**
	 * Validating configuration values.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	private static function validate(string $type)
	{
		$main = static::parse($type);
		$cron = static::getCronVariables($type);
		foreach (static::${$type} as $key => &$item) {
			if (!isset($item['status'])) {
				$item['status'] = true;
				if (isset($main[$key])) {
					$item[static::$sapi] = $main[$key];
				}
				if (isset($cron[$key]['cron']) && (self::$testCli || ($item['testCli'] && 'www' === static::$sapi))) {
					$item['cron'] = $cron[$key]['cron'];
				}
				if (isset($item['type'])) {
					$methodName = 'validate' . $item['type'];
					if (\method_exists(__CLASS__, $methodName)) {
						if ('www' === static::$sapi) {
							$item = static::$methodName($key, $item, 'www');
						}
						if (self::$testCli || ($item['testCli'] && !empty($cron))) {
							$item = static::$methodName($key, $item, 'cron');
						}
					}
					if (isset($item['mode']) && (('whenError' === $item['mode'] && !$item['status']) || 'skipParam' === $item['mode'])) {
						unset(static::${$type}[$key]);
					}
				}
			}
		}
		return static::${$type};
	}

	/**
	 * Parser of configuration values.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	private static function parse(string $type)
	{
		$values = [];
		foreach (static::${$type} as $key => $item) {
			if ('cron' === static::$sapi && !$item['testCli']) {
				continue;
			}
			if (isset($item['type']) && ($methodName = 'parser' . $item['type']) && \method_exists(__CLASS__, $methodName)) {
				$values[$key] = \call_user_func_array([__CLASS__, $methodName], [$key, $item]);
			} elseif (isset($item['container'])) {
				$container = $item['container'];
				if (isset(static::${$container}[\strtolower($key)]) || isset(static::${$container}[$key])) {
					$values[$key] = static::${$container}[\strtolower($key)] ?? static::${$container}[$key];
				}
			}
		}
		return $values;
	}

	/**
	 * Get configuration values by type of map.
	 *
	 * @param string $type
	 * @param bool   $onlyError
	 *
	 * @return mixed
	 */
	public static function get(string $type, bool $onlyError = false)
	{
		static::init($type);
		if ($onlyError) {
			$values = [];
			foreach (static::validate($type) as $key => $item) {
				if (!$item['status']) {
					$values[$key] = $item;
				}
			}
			return $values;
		}
		return static::validate($type);
	}

	/**
	 * Check open_basedir restrictions.
	 *
	 * @param string $dir
	 *
	 * @return bool
	 */
	public static function validatePath(string $dir): bool
	{
		$paths = [];
		if (\ini_get('open_basedir')) {
			$paths = explode(PATH_SEPARATOR, \ini_get('open_basedir'));
		}

		return !$paths || array_filter($paths, fn ($v) => 0 === strpos(rtrim($dir, '/') . '/', rtrim($v, '/') . '/')) ? is_dir($dir) && is_readable($dir) : true;
	}

	/**
	 * Validate php version.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return mixed
	 */
	private static function validateVersion(string $name, array $row, string $sapi)
	{
		unset($name);
		$phpVersions = explode(',', $row['recommended']);
		$row['status'] = false;
		foreach ($phpVersions as $phpVersion) {
			if (!empty($row[$sapi]) && \App\Version::compare($row[$sapi], trim($phpVersion))) {
				$row['status'] = true;
				break;
			}
		}
		if ((isset(\App\Process::$requestMode) && 'Install' === \App\Process::$requestMode) && isset($row['Install']) && !$row['Install']) {
			$row['mode'] = 'skipParam';
		}
		return $row;
	}

	/**
	 * Validate database version.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return bool
	 */
	private static function validateVersionDb(string $name, array $row, string $sapi)
	{
		unset($name);
		$recommended = \is_string($row['recommended']) ? $row['recommended'] : $row['recommended'][static::$db['typeDb']];
		$row['status'] = false;
		if (!empty($row[$sapi]) && \App\Version::compare($row[$sapi], $recommended, '>=')) {
			$row['status'] = true;
		}
		$row['recommended'] = $recommended;
		return $row;
	}

	/**
	 * Validate error reporting.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateErrorReporting(string $name, array $row, string $sapi)
	{
		unset($name);
		$current = $row[$sapi];
		$errorReporting = false === stripos($current, '_') ? \App\ErrorHandler::error2string($current) : $current;
		if ('E_ALL & ~E_NOTICE' === $row['recommended'] && ((E_ALL & ~E_NOTICE) === (int) $current || 'E_ALL & ~E_NOTICE' === $errorReporting)) {
			$row[$sapi] = $row['recommended'];
		} else {
			$row['status'] = false;
			if (\is_array($errorReporting)) {
				$row[$sapi] = implode(' | ', $errorReporting) . " ({$current})";
			}
		}
		return $row;
	}

	/**
	 * Validate on, off and int values.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateOnOffInt(string $name, array $row, string $sapi)
	{
		unset($name);
		if ('cron' !== $sapi && 'on' !== strtolower($row[$sapi])) {
			$row['status'] = false;
		}
		return $row;
	}

	/**
	 * Validate number greater than recommended.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateGreater(string $name, array $row, string $sapi)
	{
		unset($name);
		if (isset($row[$sapi])) {
			if ((int) $row[$sapi] > 0 && (int) $row[$sapi] < (int) $row['recommended']) {
				$row['status'] = false;
			}
		} else {
			$row['noParameter'] = true;
		}
		return $row;
	}

	/**
	 * Validate number greater than another parameter.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateDbTableDefinitionCache(string $name, array $row, string $sapi)
	{
		unset($name);
		$tableOpenCache = (self::$db['table_open_cache'] > self::$database['table_open_cache']['recommended']) ? self::$db['table_open_cache'] : self::$database['table_open_cache']['recommended'];
		$row['recommended'] = $tableOpenCache + 400;
		if (isset($row[$sapi]) && (int) $row[$sapi] < $row['recommended']) {
			$row['status'] = false;
		}
		if (!isset($row[$sapi])) {
			$row['noParameter'] = true;
		}
		return $row;
	}

	/**
	 * Validate number in bytes greater than recommended.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateGreaterMb(string $name, array $row, string $sapi)
	{
		unset($name);
		if (isset($row[$sapi])) {
			if ('-1' !== $row[$sapi] && \vtlib\Functions::parseBytes($row[$sapi]) < \vtlib\Functions::parseBytes($row['recommended'])) {
				$row['status'] = false;
			}
			$row[$sapi] = \vtlib\Functions::showBytes($row[$sapi]);
		} else {
			$row['noParameter'] = true;
		}
		return $row;
	}

	/**
	 * Display number in bytes.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateShowBytes(string $name, array $row, string $sapi)
	{
		unset($name);
		if (isset($row[$sapi])) {
			$row[$sapi] = \vtlib\Functions::showBytes($row[$sapi]);
		} else {
			$row['noParameter'] = true;
		}
		return $row;
	}

	/**
	 * Validate equal value "recommended == current".
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateEqual(string $name, array $row, string $sapi)
	{
		unset($name);
		if (isset($row[$sapi])) {
			if (strtolower((string) $row[$sapi]) !== strtolower((string) $row['recommended'])) {
				$row['status'] = false;
			}
		} else {
			$row['noParameter'] = true;
		}
		return $row;
	}

	/**
	 * Validate equal value "cron == www".
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateCronEqual(string $name, array $row, string $sapi)
	{
		unset($name);
		if ('www' === $sapi && isset($row['cron']) && strtolower($row['www'] ?? '') !== strtolower($row['cron'] ?? '')) {
			$row['status'] = false;
		}
		return $row;
	}

	/**
	 * Validate date timezone.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateTimeZone(string $name, array $row, string $sapi)
	{
		unset($name);
		$row[$sapi] = \App\Fields\DateTime::getTimeZone();
		try {
			$test = new \DateTimeZone($row[$sapi]);
			if ($test->getName() === $row[$sapi]) {
				return $row;
			}
			$row['status'] = false;
			return $row;
		} catch (\Throwable $e) {
			$row[$sapi] = \App\Language::translate('LBL_INVALID_TIME_ZONE', 'Settings::ConfReport') . $row[$sapi];
			$row['status'] = false;
		}
		return $row;
	}

	/**
	 * Validate on or off value.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateOnOff(string $name, array $row, string $sapi)
	{
		unset($name);
		if (isset($row[$sapi])) {
			if ($row[$sapi] !== $row['recommended'] && !(isset($row['demoMode']) && 'prod' !== \App\Config::main('systemMode'))) {
				$row['status'] = false;
			}
		} else {
			$row['noParameter'] = true;
		}

		return $row;
	}

	/**
	 * Validate function exist.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateFnExist(string $name, array $row, string $sapi)
	{
		unset($name);
		$row['status'] = \function_exists($row['fnName']);
		$row[$sapi] = $row['status'] ? 'LBL_YES' : 'LBL_NO';
		return $row;
	}

	/**
	 * Validate extension loaded.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateExtExist(string $name, array $row, string $sapi)
	{
		unset($name);
		$row['status'] = \in_array($row['extName'], static::$ext);
		$row[$sapi] = $row['status'] ? 'LBL_YES' : 'LBL_NO';
		if ($row['status'] && 'www' === $sapi) {
			$ext = new \ReflectionExtension($row['extName']);
			ob_start();
			$ext->info();
			if ($i = ob_get_contents()) {
				$info = $i;
			}
			ob_end_clean();
			$row[$sapi . '_info'] = $info;
		}
		return $row;
	}

	/**
	 * Validate extension loaded.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateExtNotExist(string $name, array $row, string $sapi)
	{
		unset($name);
		if (\in_array($row['extName'], static::$ext)) {
			$row['status'] = false;
		}
		$row[$sapi] = $row['status'] ? 'Off' : 'On';
		return $row;
	}

	/**
	 * Validate session.cookie_secure.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateCookieSecure(string $name, array $row, string $sapi)
	{
		$row[$sapi] = static::parserOnOff($name, $row);
		$row['recommended'] = static::$env['https'] ? 'On' : 'Off';
		$row['status'] = $row[$sapi] === $row['recommended'];
		return $row;
	}

	/**
	 * Validate session.cookie_samesite.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateCookieSamesite(string $name, array $row, string $sapi)
	{
		$row['recommended'] = \Config\Security::$cookieSameSite;
		$row['status'] = ($row[$sapi] ?? '') === $row['recommended'];
		return $row;
	}

	/**
	 * Parser on or off value.
	 *
	 * @param string $name
	 * @param array  $row
	 *
	 * @return array
	 */
	private static function parserOnOff(string $name, array $row)
	{
		$container = $row['container'];
		$current = static::${$container}[\strtolower($name)] ?? static::${$container}[$name] ?? '';
		static $map = ['on' => 'On', 'true' => 'On', 'off' => 'Off', 'false' => 'Off'];
		return isset($map[strtolower($current)]) ? $map[strtolower($current)] : ($current ? 'On' : 'Off');
	}

	/**
	 * Validate session_regenerate_id.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateSessionRegenerate(string $name, array $row, string $sapi)
	{
		unset($name);
		if ('Install' !== \App\Process::$requestMode) {
			$row[$sapi] = \Config\Security::$loginSessionRegenerate ? 'On' : 'Off';
			$row['status'] = \Config\Security::$loginSessionRegenerate;
		} else {
			$row['mode'] = 'skipParam';
		}
		return $row;
	}

	/**
	 * Validate header.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateHeader(string $name, array $row, string $sapi)
	{
		unset($sapi);
		$header = strtolower(\str_replace('Header: ', '', $name));
		if (!empty($row['httpsRequired']) && !\App\RequestUtil::isHttps()) {
			$row['recommended'] = '';
		}
		$onlyPhp = empty($row['onlyPhp']);
		if (isset(static::$request[$header])) {
			$row['www'] = static::$request[$header]['root'] ?? '-';
			$row['js'] = static::$request[$header]['js'] ?? '-';
			$row['css'] = static::$request[$header]['css'] ?? '-';
			$row['status'] = strtolower($row['www']) === strtolower($row['recommended']);
			if ($onlyPhp) {
				$row['status'] = $row['status'] && strtolower($row['js']) === strtolower($row['recommended']) && strtolower($row['css']) === strtolower($row['recommended']);
			}
		} elseif ('' === $row['recommended']) {
			$row['status'] = true;
		} else {
			$row['status'] = false;
		}
		return $row;
	}

	/**
	 * Validate header CSP.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateHeaderServer(string $name, array $row, string $sapi)
	{
		unset($sapi);
		$header = strtolower(\str_replace('Header: ', '', $name));
		$onlyPhp = empty($row['onlyPhp']);
		if (isset(static::$request[$header])) {
			$row['www'] = static::$request[$header]['root'] ?? '-';
			$row['js'] = static::$request[$header]['js'] ?? '-';
			$row['css'] = static::$request[$header]['css'] ?? '-';
			$row['status'] = empty($row['www']);
			if ($onlyPhp) {
				$row['status'] = $row['status'] && empty($row['js']) && empty($row['css']);
			}
			if (!$row['status'] && \App\Validator::standard($row['www'])) {
				$row['mode'] = 'showWarnings';
			}
		}
		$row['status'] = false;

		return $row;
	}

	/**
	 * Validate header CSP.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateHeaderCsp(string $name, array $row, string $sapi)
	{
		unset($sapi);
		$header = strtolower(\str_replace('Header: ', '', $name));
		$row['recommended'] = trim(\App\Controller\Headers::getInstance()->getCspHeader());
		if (isset(static::$request[$header])) {
			$row['www'] = static::$request[$header]['root'] ?? '-';
			$row['status'] = strtolower($row['www']) === strtolower($row['recommended']);
			if (!$row['status']) {
				$www = [];
				foreach (explode(';', $row['www']) as $value) {
					if ($value) {
						$name = explode(' ', trim($value))[0];
						$www[$name] = $value;
					}
				}
				foreach (explode(';', $row['recommended']) as $value) {
					if ($value) {
						$name = explode(' ', trim($value))[0];
						if ($www[$name] !== $value) {
							$row['recommended'] = str_replace($value, "<b class=\"text-danger\">$value</b>", $row['recommended']);
							$row['isHtml'] = true;
						}
					}
				}
			}
		} else {
			$row['status'] = false;
		}
		return $row;
	}

	/**
	 * Validate not in array.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateNotIn(string $name, array $row, string $sapi)
	{
		unset($name);
		if (isset($row[$sapi])) {
			$value = $row[$sapi];
			if (!\is_array($row[$sapi])) {
				$value = \explode(',', $row[$sapi]);
			}
			$recommended = (array) $row['exclusions'];
			foreach ($recommended as $item) {
				if (\in_array($item, $value)) {
					$row['status'] = false;
					break;
				}
			}
		} else {
			$row['noParameter'] = true;
		}
		return $row;
	}

	/**
	 * Validate in array.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateIn(string $name, array $row, string $sapi)
	{
		unset($name);
		$value = $row[$sapi] ?? '';
		if (!isset($row[$sapi])) {
			$row['noParameter'] = true;
		}
		if (!\is_array($value)) {
			$value = \explode(',', $value);
		}
		$value = \array_map('trim', $value);
		$recommended = \array_map('trim', \explode(',', $row['recommended']));
		foreach ($recommended as &$item) {
			if (!\in_array($item, $value)) {
				$row['status'] = false;
				$item = "<b class=\"text-danger\">$item</b>";
				$row['isHtml'] = true;
			}
		}
		$row['recommended'] = \implode(', ', $recommended);
		return $row;
	}

	/**
	 * Validate one of array.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateOneOf(string $name, array $row, string $sapi)
	{
		unset($name);
		if (isset($row['values'])) {
			$recommended = $row['values'];
		} else {
			$recommended = \array_map('trim', \explode(',', strtolower($row['recommended'])));
		}
		if (isset($row[$sapi])) {
			if (!\in_array((string) $row[$sapi], $recommended)) {
				$row['status'] = false;
			}
		} else {
			$row['noParameter'] = true;
		}
		return $row;
	}

	/**
	 * Validate exists url.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateExistsUrl(string $name, array $row, string $sapi)
	{
		unset($sapi);
		$row['status'] = \App\Fields\File::isExistsUrl(static::$crmUrl . $name);
		return $row;
	}

	/**
	 * Validate not exists url.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateNotExistsUrl(string $name, array $row, string $sapi)
	{
		unset($sapi);
		$row['status'] = !\App\Fields\File::isExistsUrl(static::$crmUrl . $name);
		return $row;
	}

	/**
	 * Parser all extensions value.
	 *
	 * @param string $name
	 * @param array  $row
	 *
	 * @return array
	 */
	private static function parserAllExt(string $name, array $row)
	{
		unset($name, $row);
		sort(static::$ext, SORT_NATURAL | SORT_FLAG_CASE);
		return \implode(', ', static::$ext);
	}

	/**
	 * Validate exists url.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateAllExt(string $name, array $row, string $sapi)
	{
		unset($name);
		$forbidden = ['uopz'];
		if (isset($row[$sapi])) {
			foreach (array_intersect($forbidden, \explode(', ', $row[$sapi])) as $type) {
				$row[$sapi] = \str_replace($type, "<b class=\"text-danger\">$type</b>", $row[$sapi]);
				$row['isHtml'] = true;
				$row['status'] = false;
			}
		}
		return $row;
	}

	/**
	 * Validate disc space value.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateSpace(string $name, array $row, string $sapi)
	{
		$dir = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR;
		switch ($name) {
			case 'spaceStorage':
				$dir .= 'storage';
				break;
			case 'spaceTemp':
				$dir = static::$env['tempDir'];
				break;
			case 'spaceBackup':
				$dir = \App\Utils\Backup::getBackupCatalogPath();
				break;
			default:
				break;
		}
		if (empty($dir) || !is_dir($dir)) {
			return $row;
		}
		$free = disk_free_space($dir);
		$total = disk_total_space($dir);
		$row['spaceTotal'] = $total;
		$row['spaceFree'] = $free;
		$row[$sapi] = round((($total - $free) / $total) * 100) . '% | ';
		$row[$sapi] .= \App\Language::translate('LBL_SPACE_FREE', 'Settings::ConfReport') . ': ';
		$row[$sapi] .= \vtlib\Functions::showBytes($free) . ' | ';
		$row[$sapi] .= \App\Language::translate('LBL_SPACE_USED', 'Settings::ConfReport') . ': ';
		$row[$sapi] .= \vtlib\Functions::showBytes($total - $free);
		if ($free < 1024 * 1024 * 1024) {
			$row['status'] = false;
		}
		return $row;
	}

	/**
	 * Parser http methods value.
	 *
	 * @param string $name
	 * @param array  $row
	 *
	 * @return array
	 */
	private static function parserHttpMethods(string $name, array $row)
	{
		unset($name);
		$supported = [];
		$requestUrl = static::$crmUrl . 'token.php';
		foreach (\explode(', ', $row['recommended']) as $type) {
			try {
				$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request($type, $requestUrl, ['timeout' => 1, 'verify' => false]);
				if (200 === $response->getStatusCode()) {
					$supported[] = $type;
				}
			} catch (\Throwable $e) {
			}
		}
		return \implode(', ', $supported);
	}

	/**
	 * Validate http methods.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateHttpMethods(string $name, array $row, string $sapi)
	{
		unset($name);
		foreach (array_diff(\explode(',', $row['recommended']), \explode(',', $row[static::$sapi])) as $type) {
			$row['recommended'] = \str_replace($type, "<b class=\"text-danger\">$type</b>", $row['recommended']);
			$row['isHtml'] = true;
		}
		return $row;
	}

	/**
	 * Validate realpath cache size.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateIsWritable(string $name, array $row, string $sapi)
	{
		$absolutePaths = $row['absolutePaths'] ?? false;
		$path = $name;
		if (!$absolutePaths) {
			$path = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . $path;
		}
		$exists = file_exists($path);
		if (empty($row['mustExist']) && !$exists) {
			$row['mode'] = 'skipParam';
		} else {
			$row['status'] = \App\Fields\File::isWriteable($path, true);
			$row[$sapi] = $row['status'] ? 'LBL_YES' : 'LBL_NO';
			$row['owner'] = $exists ? fileowner($path) : '';
			$row['perms'] = $exists ? substr(sprintf('%o', fileperms($path)), -4):'';
		}
		return $row;
	}

	/**
	 * Validate branding value.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateBranding(string $name, array $row, string $sapi)
	{
		$view = new \Vtiger_Viewer();
		$view->assign('APPTITLE', \App\Language::translate('APPTITLE'));
		$view->assign('YETIFORCE_VERSION', \App\Version::get());
		$view->assign('MODULE_NAME', 'Base');
		$view->assign('USER_MODEL', \Users_Record_Model::getCurrentUserModel());
		$view->assign('ACTIVITY_REMINDER', 0);
		$view->assign('FOOTER_SCRIPTS', []);
		$view->assign('SHOW_FOOTER', true);
		$view->assign('SHOW_FOOTER_BAR', true);
		$html = $view->view('PageFooter.tpl', '', true);
		$row['status'] = true;
		if (!\App\YetiForce\Shop::check('YetiForceDisableBranding')) {
			$row['status'] = false !== \strpos($html, '&copy; YetiForce.com All rights reserved') || !empty(\App\Config::component('Branding', 'footerName'));
		}
		unset($name);
		$row[$sapi] = \App\Language::translate($row['status'] ? 'LBL_YES' : 'LBL_NO');
		return $row;
	}

	/**
	 * Validate shop products.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	public static function validateShopProducts(string $name, array $row, string $sapi)
	{
		unset($name);
		$row['status'] = true;
		$status = '';
		foreach (\App\YetiForce\Shop::getProducts() as $name => $product) {
			$verify = $product->verify();
			if (!$verify['status']) {
				$status .= $name . '(' . \strlen($verify['message']) . '), ';
				$row['status'] = false;
			}
		}
		$row[$sapi] = $status ? trim($status, ', ') : ('shop' === $sapi ? '' : \App\Language::translate('LBL_YES'));
		return $row;
	}

	/**
	 * Validate open_basedir.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateOpenBasedir(string $name, array $row, string $sapi)
	{
		unset($name);
		$row['status'] = true;
		if ('cron' === $sapi) {
			if (!empty($row[$sapi])) {
				$row['status'] = false;
			}
		} else {
			if (empty($row[$sapi])) {
				$row['status'] = false;
			}
		}
		return $row;
	}

	/**
	 * Validate check if value is empty.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateNotEmpty(string $name, array $row, string $sapi)
	{
		unset($name);
		if (empty($row[$sapi])) {
			$row['status'] = false;
		}
		return $row;
	}

	/**
	 * Validate check error_log.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateErrorLog(string $name, array $row, string $sapi)
	{
		$row = self::validateNotEmpty($name, $row, $sapi);
		if ($row['status']) {
			$row['status'] = self::validatePath(\dirname($row[$sapi]));
		}

		return $row;
	}

	/**
	 * Validate watchdog.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	public static function validateWatchdog(string $name, array $row, string $sapi)
	{
		unset($name);
		$row['status'] = true;
		$row[$sapi] = \App\Language::translate('LBL_YES');
		if (!\App\Cron::$watchdogIsActive) {
			$row['status'] = false;
			$row[$sapi] = \App\Language::translate('LBL_NO');
		}
		return $row;
	}

	/**
	 * Validate register.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	public static function validateRegister(string $name, array $row, string $sapi)
	{
		unset($name);
		$row['status'] = true;
		$row[$sapi] = \App\Language::translate('LBL_YES');
		if (!\App\Cron::$registerIsActive) {
			$row['status'] = false;
			$row[$sapi] = \App\Language::translate('LBL_NO');
		}
		return $row;
	}

	/**
	 * Validate shop cache.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	public static function validateShopCache(string $name, array $row, string $sapi)
	{
		unset($name);
		$row['status'] = true;
		$row[$sapi] = \App\Language::translate('LBL_YES');
		if (!\App\Cron::$shopIsActive) {
			$row['status'] = false;
			$row[$sapi] = \App\Language::translate('LBL_NO');
		}
		return $row;
	}

	/**
	 * Validate webservice.
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateWebservice(string $name, array $row, string $sapi)
	{
		unset($sapi);
		try {
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('OPTIONS', static::$crmUrl . $name . 'Modules', ['timeout' => 1, 'connect_timeout' => 1, 'verify' => false, 'http_errors' => false, 'allow_redirects' => false]);
			$row['status'] = 200 === $response->getStatusCode();
		} catch (\Throwable $th) {
			$row['status'] = false;
		}
		if (!\in_array('webservice', \Config\Api::$enabledServices)) {
			$row['mode'] = 'showWarnings';
		}
		return $row;
	}

	/**
	 * Get all configuration error values.
	 *
	 * @param bool $cache
	 *
	 * @return array
	 */
	public static function getAllErrors(bool $cache = false)
	{
		$fileCache = ROOT_DIRECTORY . '/app_data/ConfReport_AllErrors.php';
		if ($cache && file_exists($fileCache) && filemtime($fileCache) > strtotime('-15 minute')) {
			return require $fileCache;
		}
		$result = [];
		foreach (static::getAll() as $category => $params) {
			foreach ($params as $param => $data) {
				if (!$data['status']) {
					if (!isset($data['www']) && !isset($data['cron'])) {
						$val = $data['status'];
					} else {
						$val = $data['www'] ?? $data['cron'];
					}
					$result[$category][$param] = $val;
				}
			}
		}
		if ($cache) {
			\App\Utils::saveToFile($fileCache, $result, '', 0, true);
		}
		return $result;
	}

	/**
	 * Get configuration error values.
	 *
	 * @param string $type
	 * @param bool   $returnMore
	 *
	 * @return array
	 */
	public static function getErrors(string $type, bool $returnMore = false): array
	{
		$result = [];
		foreach (static::get($type, true) as $param => $data) {
			if (!$data['status'] && (empty($data['mode']) || 'showErrors' === $data['mode'])) {
				if (!isset($data['www']) && !isset($data['cron'])) {
					$val = $data['status'];
				} else {
					$tmp = [];
					if (isset($data['www'])) {
						$tmp[] = 'www: ' . $data['www'];
					}
					if (isset($data['cron'])) {
						$tmp[] = 'cron: ' . $data['cron'];
					}
					$val = \implode(' | ', $tmp) ?? '';
				}
				if ($returnMore) {
					$data['val'] = $val;
					$result[$param] = $data;
				} else {
					$result[$param] = $val;
				}
			}
		}
		return $result;
	}

	/**
	 * Get actual version of PHP.
	 *
	 * @return string[]
	 */
	public static function getNewestPhpVersion()
	{
		if (!\App\RequestUtil::isNetConnection()) {
			return false;
		}
		$data = [];
		$versions = explode(',', self::$stability['phpVersion']['recommended']);
		foreach ($versions as $version) {
			$version = explode('.', $version, 3);
			array_pop($version);
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->get('http://php.net/releases/index.php?json&max=1&version=' . implode('.', $version));
			$response = array_keys((array) \App\Json::decode($response->getBody()));
			$data = array_merge($data, $response);
		}
		natsort($data);
		$ver = [];
		foreach (array_reverse($data) as $row) {
			$t = explode('.', $row);
			array_pop($t);
			$short = implode('.', $t);
			if (!isset($ver[$short])) {
				$ver[$short] = \App\Purifier::encodeHtml($row);
			}
		}
		return $ver;
	}

	/**
	 * Save environment variables.
	 *
	 * @return void
	 */
	public static function saveEnv(): void
	{
		$data = self::getEnv();
		$key = \PHP_SAPI !== 'cli' ? 'www' : 'cli';
		$data[$key]['sapi'] = \PHP_SAPI;
		$data[$key]['operatingSystem'] = [
			'machineType' => php_uname('m'),
			'hostName' => php_uname('n'),
			'release' => php_uname('r'),
			'operatingSystem' => php_uname('s'),
			'version' => php_uname('v'),
		];
		if (($db = \App\Db::getInstance()) && $db->getMasterPdo() && ($dbInfo = $db->getInfo())) {
			$data[$key]['sql'] = [
				'clientVersion' => $dbInfo['clientVersion'],
				'serverVersion' => $dbInfo['serverVersion'],
				'typeDb' => $dbInfo['typeDb'],
				'version' => $dbInfo['version'] ?? '',
				'versionComment' => $dbInfo['version_comment'] ?? '',
				'versionSslLibrary' => $dbInfo['version_ssl_library'] ?? '',
			];
		}
		if (isset($_SERVER['SERVER_SOFTWARE'])) {
			$data[$key]['serverSoftware'] = $_SERVER['SERVER_SOFTWARE'];
		}
		if (isset($_SERVER['GATEWAY_INTERFACE'])) {
			$data[$key]['gatewayInterface'] = $_SERVER['GATEWAY_INTERFACE'];
		}
		\App\Utils::saveToFile(ROOT_DIRECTORY . '/app_data/ConfReport_Env.php', $data, '', 0, true);
	}

	/**
	 * Get environment variables.
	 *
	 * @return array
	 */
	public static function getEnv(): array
	{
		$path = ROOT_DIRECTORY . '/app_data/ConfReport_Env.php';
		return file_exists($path) ? (require $path) : [];
	}
}
