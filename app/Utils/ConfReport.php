<?php
/**
 * Conf report class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Utils;

use PDO;

/**
 * Conf report.
 */
class ConfReport
{
	/**
	 * Optional database configuration for offline use.
	 *
	 * @var array
	 */
	public static $dbConfig = [
		'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=yetiforce;',
		'user' => '',
		'password' => '',
		'options' => [],
	];
	/**
	 * List all variables.
	 *
	 * @var string[]
	 */
	public static $types = ['stability', 'security', 'libraries', 'database', 'performance', 'environment', 'publicDirectoryAccess', 'writableFilesAndFolders'];
	/**
	 * List all container.
	 *
	 * @var string[]
	 */
	public static $container = ['php', 'env', 'ext', 'request', 'db'];
	/**
	 * Stability variables map.
	 *
	 * @var array
	 */
	public static $stability = [
		'phpVersion' => ['recommended' => '7.1.x, 7.2.x, 7.3.x (dev)', 'type' => 'Version', 'container' => 'env', 'testCli' => true, 'label' => 'PHP'],
		'protocolVersion' => ['recommended' => '1.x', 'type' => 'Version', 'container' => 'env', 'testCli' => false, 'label' => 'PROTOCOL_VERSION'],
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
		'mbstring.func_overload' => ['recommended' => 'Off', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true], //Roundcube
		'date.timezone' => ['type' => 'TimeZone', 'container' => 'php', 'testCli' => true], //Roundcube
		'allow_url_fopen' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true], //Roundcube
		'auto_detect_line_endings' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true], //CSVReader
		'httpMethods' => ['recommended' => 'GET, POST, PUT, OPTIONS, PATCH, PROPFIND, REPORT, LOCK, DELETE, COPY, MOVE', 'type' => 'HttpMethods', 'container' => 'request', 'testCli' => false, 'label' => 'HTTP_METHODS'],
	];
	/**
	 * Security variables map.
	 *
	 * @var array
	 */
	public static $security = [
		'CaCertBundle' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'env', 'testCli' => true, 'label' => 'CACERTBUNDLE'],
		'HTTPS' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'env', 'testCli' => false],
		'public_html' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'env', 'testCli' => false],
		'display_errors' => ['recommended' => 'Off', 'type' => 'OnOff', 'container' => 'php', 'demoMode' => true, 'testCli' => true],
		'.htaccess' => ['recommended' => 'On', 'type' => 'Htaccess', 'container' => 'php', 'testCli' => false],
		'session.use_strict_mode' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'session.use_trans_sid' => ['recommended' => 'Off', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'session.cookie_httponly' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => false],
		'session.use_only_cookies' => ['recommended' => 'On', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'session.cookie_secure' => ['recommended' => '?', 'type' => 'CookieSecure', 'container' => 'php', 'testCli' => false],
		'session.name' => ['recommended' => 'YTSID', 'container' => 'php', 'type' => 'Equal', 'testCli' => false],
		'expose_php' => ['recommended' => 'Off', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'session_regenerate_id' => ['recommended' => 'On', 'type' => 'SessionRegenerate', 'testCli' => true],
		'disable_functions' => ['recommended' => 'shell_exec, exec, system, passthru, popen', 'type' => 'In', 'container' => 'php', 'testCli' => false],
		'allow_url_include' => ['recommended' => 'Off', 'type' => 'OnOff', 'container' => 'php', 'testCli' => true],
		'Header: server' => ['recommended' => '', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: x-powered-by' => ['recommended' => '', 'type' => 'Header', 'contaiuse_only_cookiesner' => 'request', 'testCli' => false],
		'Header: x-frame-options' => ['recommended' => 'sameorigin', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: x-xss-protection' => ['recommended' => '1; mode=block', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: x-content-type-options' => ['recommended' => 'nosniff', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: x-robots-tag' => ['recommended' => 'none', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: x-permitted-cross-domain-policies' => ['recommended' => 'none', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: expect-ct' => ['recommended' => 'enforce; max-age=3600', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: referrer-policy' => ['recommended' => 'no-referrer', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
		'Header: strict-transport-security' => ['recommended' => 'max-age=31536000; includeSubDomains; preload', 'type' => 'Header', 'container' => 'request', 'testCli' => false],
	];
	/**
	 * Libraries map.
	 *
	 * @var array
	 */
	public static $libraries = [
		'imap' => ['mandatory' => true, 'type' => 'ExtExist', 'extName' => 'imap', 'container' => 'ext', 'testCli' => true],
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
		'exif' => ['mandatory' => false, 'type' => 'ExtExist', 'extName' => 'exif', 'container' => 'ext', 'testCli' => true],
		'ldap' => ['mandatory' => false, 'type' => 'ExtExist', 'extName' => 'ldap', 'container' => 'ext', 'testCli' => true],
		'OPcache' => ['mandatory' => false, 'type' => 'FnExist', 'fnName' => 'opcache_get_configuration', 'container' => 'ext', 'testCli' => true],
		'apcu' => ['mandatory' => false, 'type' => 'ExtExist', 'extName' => 'apcu', 'container' => 'ext', 'testCli' => true],
		'imagick' => ['mandatory' => false, 'type' => 'ExtExist', 'extName' => 'imagick', 'container' => 'ext', 'testCli' => true],
		'allExt' => ['container' => 'ext', 'type' => 'AllExt', 'testCli' => true, 'label' => 'EXTENSIONS'],
	];
	/**
	 * Database map.
	 *
	 * @var array
	 */
	public static $database = [
		'driver' => ['recommended' => 'mysql', 'type' => 'Equal', 'container' => 'db', 'testCli' => false, 'label' => 'DB_DRIVER'],
		'serverVersion' => ['container' => 'db', 'testCli' => false, 'label' => 'DB_CLIENT_VERSION'],
		'clientVersion' => ['container' => 'db', 'testCli' => false, 'label' => 'DB_SERVER_VERSION'],
		'connectionStatus' => ['container' => 'db', 'testCli' => false, 'label' => 'DB_CONNECTION_STATUS'],
		'serverInfo' => ['container' => 'db', 'testCli' => false, 'label' => 'DB_SERVER_INFO'],
		'innodb_lock_wait_timeout' => ['recommended' => 600, 'type' => 'Greater', 'container' => 'db', 'testCli' => false],
		'wait_timeout' => ['recommended' => 600, 'type' => 'Greater', 'container' => 'db', 'testCli' => false],
		'interactive_timeout' => ['recommended' => 600, 'type' => 'Greater', 'container' => 'db', 'testCli' => false],
		'sql_mode' => ['recommended' => '', 'type' => 'NotIn', 'container' => 'db', 'testCli' => false, 'values' => ['STRICT_ALL_TABLES', 'STRICT_TRANS_TABLE']],
		'max_allowed_packet' => ['recommended' => '10 MB', 'type' => 'GreaterMb', 'container' => 'db', 'testCli' => false],
		'log_error' => ['container' => 'db', 'testCli' => false],
		'max_connections' => ['container' => 'db', 'testCli' => false],
		'bulk_insert_buffer_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => false],
		'key_buffer_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => false],
		'thread_cache_size' => ['container' => 'db', 'testCli' => false],
		'query_cache_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => false],
		'myisam_sort_buffer_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => false],
		'tmp_table_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => false],
		'max_heap_table_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => false],
		'innodb_file_per_table' => ['recommended' => 'On', 'container' => 'db', 'testCli' => false],
		'innodb_stats_on_metadata' => ['recommended' => 'Off', 'container' => 'db', 'testCli' => false],
		'innodb_buffer_pool_instances' => ['container' => 'db', 'testCli' => false],
		'innodb_buffer_pool_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => false],
		'innodb_log_file_size' => ['container' => 'db', 'type' => 'ShowBytes', 'testCli' => false],
		'innodb_io_capacity_max' => ['container' => 'db', 'testCli' => false],
		'tx_isolation' => ['container' => 'db', 'testCli' => false],
		'transaction_isolation' => ['container' => 'db', 'testCli' => false],
		'character_set_server' => ['recommended' => 'utf8', 'type' => 'Equal', 'container' => 'db', 'testCli' => false],
		'character_set_database' => ['recommended' => 'utf8', 'type' => 'Equal', 'container' => 'db', 'testCli' => false],
		'character_set_client' => ['recommended' => 'utf8', 'type' => 'Equal', 'container' => 'db', 'testCli' => false],
		'character_set_connection' => ['recommended' => 'utf8', 'type' => 'Equal', 'container' => 'db', 'testCli' => false],
		'character_set_results' => ['recommended' => 'utf8', 'type' => 'Equal', 'container' => 'db', 'testCli' => false],
		'character_set_system' => ['container' => 'db', 'testCli' => false],
		'character_set_filesystem' => ['container' => 'db', 'testCli' => false],
		'datadir' => ['container' => 'db', 'testCli' => false],
		'connect_timeout' => ['container' => 'db', 'testCli' => false],
		'lock_wait_timeout' => ['container' => 'db', 'testCli' => false],
		'lock_wait_timeout' => ['container' => 'db', 'testCli' => false],
		'net_read_timeout' => ['container' => 'db', 'testCli' => false],
		'net_write_timeout' => ['container' => 'db', 'testCli' => false],
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
		'opcache.revalidate_freq' => ['recommended' => 30, 'type' => 'Equal', 'container' => 'php', 'testCli' => true],
		'opcache.save_comments' => ['recommended' => 0, 'type' => 'Equal', 'container' => 'php', 'testCli' => true],
		'opcache.file_update_protection' => ['recommended' => 0, 'type' => 'Equal', 'container' => 'php', 'testCli' => true],
		'opcache.memory_consumption' => ['container' => 'php', 'testCli' => true],
		'realpath_cache_size' => ['recommended' => '256k', 'type' => 'RealpathCacheSize', 'container' => 'php', 'testCli' => true],
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
		'operatingSystem' => ['container' => 'env', 'testCli' => false, 'label' => 'OPERATING_SYSTEM'],
		'serverSoftware' => ['container' => 'env', 'testCli' => false, 'label' => 'SERVER_SOFTWARE'],
		'tempDir' => ['container' => 'env', 'testCli' => false, 'label' => 'TMP_DIR'],
		'crmDir' => ['container' => 'env', 'testCli' => false, 'label' => 'CRM_DIR'],
		'sapi' => ['container' => 'env', 'testCli' => true, 'label' => 'PHP_SAPI'],
		'locale' => ['container' => 'env', 'testCli' => true, 'label' => 'LOCALE'],
		'error_log' => ['container' => 'php', 'testCli' => true, 'label' => 'LOG_FILE'],
		'phpIni' => ['container' => 'env', 'testCli' => true, 'label' => 'PHPINI'],
		'phpIniAll' => ['container' => 'env', 'testCli' => true, 'label' => 'PHPINIS'],
		'spaceRoot' => ['container' => 'env', 'type' => 'Space', 'testCli' => false, 'label' => 'SPACE_ROOT'],
		'spaceStorage' => ['container' => 'env', 'type' => 'Space', 'testCli' => false, 'label' => 'SPACE_STORAGE'],
		'spaceTemp' => ['container' => 'env', 'type' => 'Space', 'testCli' => false, 'label' => 'SPACE_TEMP'],
		'lastCronStart' => ['container' => 'env', 'testCli' => false, 'label' => 'LAST_CRON_START'],
		'open_basedir' => ['container' => 'php', 'testCli' => true],
		'variables_order' => ['container' => 'php', 'testCli' => true],
	];
	/**
	 * Directory permissions map.
	 *
	 * @var array
	 */
	public static $publicDirectoryAccess = [
		'config' => ['type' => 'ExistsUrl', 'container' => 'request', 'testCli' => false],
		'cache' => ['type' => 'ExistsUrl', 'container' => 'request', 'testCli' => false],
		'storage' => ['type' => 'ExistsUrl', 'container' => 'request', 'testCli' => false],
		'user_privileges' => ['type' => 'ExistsUrl', 'container' => 'request', 'testCli' => false],
	];
	/**
	 * Writable files and folders permissions map.
	 *
	 * @var array
	 */
	public static $writableFilesAndFolders = [
		'config/' => ['type' => 'IsWritable', 'testCli' => true],
		'user_privileges/' => ['type' => 'IsWritable', 'testCli' => true],
		'user_privileges/tabdata.php' => ['type' => 'IsWritable', 'testCli' => true],
		'user_privileges/menu_0.php' => ['type' => 'IsWritable', 'testCli' => true],
		'user_privileges/user_privileges_1.php' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/addressBook/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/images/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/import/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/logs/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/session/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/templates_c/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/upload/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/vtlib/' => ['type' => 'IsWritable', 'testCli' => true],
		'cache/vtlib/HTML' => ['type' => 'IsWritable', 'testCli' => true],
		'cron/modules/' => ['type' => 'IsWritable', 'testCli' => true],
		'modules/' => ['type' => 'IsWritable', 'testCli' => true],
		'storage/' => ['type' => 'IsWritable', 'testCli' => true],
		'storage/Products/' => ['type' => 'IsWritable', 'testCli' => true],
		'storage/Users/' => ['type' => 'IsWritable', 'testCli' => true],
		'storage/Contacts/' => ['type' => 'IsWritable', 'testCli' => true],
		'storage/OSSMailView/' => ['type' => 'IsWritable', 'testCli' => true],
		'public_html/modules/OSSMail/' => ['type' => 'IsWritable', 'testCli' => true],
		'public_html/libraries/' => ['type' => 'IsWritable', 'testCli' => true],
		'public_html/layouts/resources/Logo/' => ['type' => 'IsWritable', 'testCli' => true],
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
	 * Get all configuration values.
	 *
	 * @return mixed
	 */
	public static function getAll(): array
	{
		static::init('all');
		$all = [];
		foreach (static::$types as $type) {
			$all[$type] = static::validate($type);
		}
		return $all;
	}

	/**
	 * Initializing variables.
	 *
	 * @param string $type
	 */
	private static function init(string $type)
	{
		$types = static::$container;
		if (isset(static::$$type)) {
			$types = \array_unique(\array_column(static::$$type, 'container'));
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
					static::$db = static::getConfigDb();
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
	private static function getConfig()
	{
		$php = [];
		foreach (ini_get_all() as $key => $value) {
			$php[$key] = $value['local_value'];
		}
		$locale = '';
		if (function_exists('locale_get_default')) {
			$locale = print_r(locale_get_default(), true);
		}
		$cron = static::getCronVariables('last_start');
		$lastCronStart = '-';
		$lastCronStartText = '-';
		if ($cron) {
			$lastCronStart = date('Y-m-d H:i:s', $cron);
			$lastCronStartText = \App\Fields\DateTime::formatToViewDate($lastCronStart);
		}
		return [
			'php' => $php,
			'env' => [
				'phpVersion' => PHP_VERSION,
				'sapi' => PHP_SAPI,
				'phpIni' => php_ini_loaded_file() ?: '-',
				'phpIniAll' => php_ini_scanned_files() ?: '-',
				'locale' => $locale,
				'https' => \App\RequestUtil::getBrowserInfo()->https,
				'cacertbundle' => \is_file(\Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()) ? 'On' : 'Off',
				'public_html' => IS_PUBLIC_DIR ? 'On' : 'Off',
				'crmVersion' => \App\Version::get(),
				'crmDate' => \App\Version::get('patchVersion'),
				'crmDir' => ROOT_DIRECTORY,
				'operatingSystem' => \App\Config::main('systemMode') === 'demo' ? php_uname('s') : php_uname(),
				'serverSoftware' => $_SERVER['SERVER_SOFTWARE'] ?? '-',
				'tempDir' => \App\Fields\File::getTmpPath(),
				'spaceRoot' => '',
				'spaceStorage' => '',
				'spaceTemp' => '',
				'lastCronStart' => $lastCronStartText,
				'lastCronStartDateTime' => $lastCronStart,
				'protocolVersion' => isset($_SERVER['SERVER_PROTOCOL']) ? substr($_SERVER['SERVER_PROTOCOL'], strpos($_SERVER['SERVER_PROTOCOL'], '/') + 1) : '-'
			]
		];
	}

	/**
	 * Get variable for cron.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public static function getCronVariables(string $type)
	{
		if (file_exists('user_privileges/cron.php')) {
			$cron = include \ROOT_DIRECTORY . '/user_privileges/cron.php';
			return $cron[$type] ?? null;
		}
		return [];
	}

	/**
	 * Get request request.
	 *
	 * @return array
	 */
	private static function getRequest()
	{
		$requestUrl = \App\Config::main('site_URL') ?: \App\RequestUtil::getBrowserInfo()->url;
		$request = [];
		try {
			$res = (new \GuzzleHttp\Client())->request('GET', $requestUrl, ['timeout' => 1, 'verify' => false]);
			foreach ($res->getHeaders() as $key => $value) {
				$request[strtolower($key)] = is_array($value) ? implode(',', $value) : $value;
			}
		} catch (\Throwable $e) {
		}
		return $request;
	}

	/**
	 * Get database variables.
	 *
	 * @return mixed[]
	 */
	private static function getConfigDb()
	{
		$pdo = false;
		if (\class_exists('\App\Db')) {
			$db = \App\Db::getInstance();
			$pdo = $db->getSlavePdo();
			$driver = $db->getDriverName();
		} elseif (!empty(static::$dbConfig['user'])) {
			$pdo = new PDO(static::$dbConfig['dsn'], static::$dbConfig['user'], static::$dbConfig['password'], static::$dbConfig['options']);
			$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
		}
		if (!$pdo) {
			return [];
		}
		$conf = [
			'driver' => $driver,
			'serverVersion' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
			'clientVersion' => $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION),
			'connectionStatus' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
			'serverInfo' => $pdo->getAttribute(PDO::ATTR_SERVER_INFO),
		];
		$statement = $pdo->prepare('SHOW VARIABLES');
		$statement->execute();
		return \array_merge($conf, $statement->fetchAll(PDO::FETCH_KEY_PAIR));
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
		foreach (static::$$type as $key => &$item) {
			$item['status'] = true;
			if (isset($main[$key])) {
				$item[static::$sapi] = $main[$key];
			}
			if ($item['testCli'] && static::$sapi === 'www') {
				if (isset($cron[$key]['cron'])) {
					$item['cron'] = $cron[$key]['cron'];
				}
			}
			if (isset($item['type'])) {
				$methodName = 'validate' . $item['type'];
				if (\method_exists(__CLASS__, $methodName)) {
					if (static::$sapi === 'www') {
						$item = static::$methodName($key, $item, 'www');
					}
					if ($item['testCli'] && !empty($cron)) {
						$item = static::$methodName($key, $item, 'cron');
					}
				}
				if (isset($item['skip'])) {
					unset(static::$$type[$key]);
				}
			}
		}
		return static::$$type;
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
		foreach (static::$$type as $key => $item) {
			if (static::$sapi === 'cron' && !$item['testCli']) {
				continue;
			}
			if (isset($item['type']) && ($methodName = 'parser' . $item['type']) && \method_exists(__CLASS__, $methodName)) {
				$values[$key] = call_user_func_array([__CLASS__, $methodName], [$key, $item]);
			} elseif (isset($item['container'])) {
				$container = $item['container'];
				if (isset(static::$$container[\strtolower($key)]) || isset(static::$$container[$key])) {
					$values[$key] = static::$$container[\strtolower($key)] ?? static::$$container[$key];
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
		$errorReporting = stripos($current, '_') === false ? \App\ErrorHandler::error2string($current) : $current;
		if ($row['recommended'] === 'E_ALL & ~E_NOTICE' && ((E_ALL & ~E_NOTICE) === (int) $current || 'E_ALL & ~E_NOTICE' === $errorReporting)) {
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
		if ($sapi !== 'cron' && strtolower($row[$sapi]) !== 'on') {
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
		if (isset($row[$sapi]) && (int) $row[$sapi] > 0 && (int) $row[$sapi] < (int) $row['recommended']) {
			$row['status'] = false;
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
		if (isset($row[$sapi]) && $row[$sapi] !== '-1' && \vtlib\Functions::parseBytes($row[$sapi]) < \vtlib\Functions::parseBytes($row['recommended'])) {
			$row['status'] = false;
		}
		$row[$sapi] = \vtlib\Functions::showBytes($row[$sapi]);
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
		$row[$sapi] = \vtlib\Functions::showBytes($row[$sapi]);
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
		if (isset($row[$sapi]) && strtolower((string) $row[$sapi]) !== strtolower((string) $row['recommended'])) {
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
		if (isset($row[$sapi]) && $row[$sapi] !== $row['recommended'] && !(isset($row['demoMode']) && \App\Config::main('systemMode') !== 'prod')) {
			$row['status'] = false;
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
		$row['status'] = function_exists($row['fnName']);
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
	 * Validate htaccess .
	 *
	 * @param string $name
	 * @param array  $row
	 * @param string $sapi
	 *
	 * @return array
	 */
	private static function validateHtaccess(string $name, array $row, string $sapi)
	{
		unset($name);
		if (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') === false) {
			if (!isset($_SERVER['HTACCESS_TEST'])) {
				$row['status'] = false;
				$row[$sapi] = 'Off';
			} else {
				$row[$sapi] = 'On';
			}
		} else {
			$row['skip'] = true;
		}
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
		$current = static::$$container[\strtolower($name)] ?? '';
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
		if (\App\Config::main('site_URL')) {
			$row[$sapi] = \App\Config::main('session_regenerate_id') ? 'On' : 'Off';
			$row['status'] = \App\Config::main('session_regenerate_id');
		} else {
			$row['skip'] = true;
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
		$header = strtolower(\str_replace('Header: ', '', $name));
		if (isset(static::$request[$header])) {
			$row['status'] = strtolower(static::$request[$header]) === strtolower($row['recommended']);
			$row[$sapi] = static::$request[$header];
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
		$value = $row[$sapi];
		if (!\is_array($row[$sapi])) {
			$value = \explode(',', $row[$sapi]);
		}
		$recommended = (array) $row['values'];
		foreach ($recommended as $item) {
			if (\in_array($item, $value)) {
				$row['status'] = false;
				break;
			}
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
		$value = $row[$sapi];
		if (!\is_array($row[$sapi])) {
			$value = \explode(',', $row[$sapi]);
		}
		$value = \array_map('trim', $value);
		$recommended = \array_map('trim', \explode(',', $row['recommended']));
		foreach ($recommended as &$item) {
			if (!\in_array($item, $value)) {
				$row['status'] = false;
				$item = "<b class=\"text-danger\">$item</b>";
			}
		}
		$row['recommended'] = \implode(', ', $recommended);
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
		$row['status'] = !\App\Fields\File::isExistsUrl(\App\Config::main('site_URL') . $name);
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
		return \implode(', ', static::$ext);
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
		$dir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		switch ($name) {
			case 'spaceRoot':
				$dir .= 'storage';
				break;
			case 'spaceTemp':
				$dir = static::$env['tempDir'];
				break;
			default:
				break;
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
		$requestUrl = \App\Config::main('site_URL') . 'shorturl.php';
		foreach (\explode(', ', $row['recommended']) as $type) {
			try {
				$response = (new \GuzzleHttp\Client())->request($type, $requestUrl, ['timeout' => 1, 'verify' => false]);
				if ($response->getStatusCode() === 200 && 'No uid' === (string) $response->getBody()) {
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
		foreach (array_diff(\explode(',', $row['recommended']), \explode(',', $row[$sapi])) as $type) {
			$row['recommended'] = \str_replace($type, "<b class=\"text-danger\">$type</b>", $row['recommended']);
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
	private static function validateRealpathCacheSize(string $name, array $row, string $sapi)
	{
		unset($name);
		$current = realpath_cache_size();
		$max = \vtlib\Functions::parseBytes($row[$sapi]);
		$converter = $current / $max;
		if ($converter > 1) {
			$row['recommended'] = \vtlib\Functions::showBytes(ceil($converter) * $max);
			$row['status'] = false;
		}
		$row[$sapi] = \vtlib\Functions::showBytes($row[$sapi]);
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
		$row['status'] = \App\Fields\File::isWriteable($name);
		$row[$sapi] = $row['status'] ? 'LBL_YES' : 'LBL_NO';
		return $row;
	}

	/**
	 * Get all configuration error values.
	 *
	 * @return array
	 */
	public static function getAllErrors()
	{
		$result = [];
		foreach (static::getAll() as $category => $params) {
			foreach ($params as $param => $data) {
				if (!$data['status']) {
					if (!isset($data['www']) && !isset($data['cron'])) {
						if (!empty($data['type']) && $data['type'] === 'ExistsUrl') {
							$val = !$data['status'];
						} else {
							$val = $data['status'];
						}
					} else {
						$val = $data['www'] ?? $data['cron'];
					}
					$result[$category][$param] = $val;
				}
			}
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
			if (!$data['status']) {
				if (!isset($data['www']) && !isset($data['cron'])) {
					if (!empty($data['type']) && $data['type'] === 'ExistsUrl') {
						$val = !$data['status'];
					} else {
						$val = $data['status'];
					}
				} else {
					$tmp = [];
					if (isset($data['www'])) {
						$tmp[] = 'www: ' . $data['www'];
					}
					if (isset($data['cron'])) {
						$tmp[] = 'cron: ' . $data['cron'];
					}
					$val = \implode('|', $tmp) ?? '';
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
}
