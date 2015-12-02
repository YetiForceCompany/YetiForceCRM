<style>
	.redd{
		color: red;
	}
</style>
<form action="index.php" method="get">
	<?php
	/* +***********************************************************************************************************************************
	 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
	 * in compliance with the License.
	 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
	 * See the License for the specific language governing rights and limitations under the License.
	 * The Original Code is YetiForce.
	 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
	 * All Rights Reserved.
	 * *********************************************************************************************************************************** */
	$required_php_exts = array(
		'PCRE' => 'pcre',
		'DOM' => 'dom',
		'Session' => 'session',
		'XML' => 'xml',
		'JSON' => 'json',
		'PDO' => 'PDO',
	);

	$optional_php_exts = array(
		'FileInfo' => 'fileinfo',
		'Libiconv' => 'iconv',
		'Multibyte' => 'mbstring',
		'OpenSSL' => 'openssl',
		'Mcrypt' => 'mcrypt',
		'Intl' => 'intl',
		'Exif' => 'exif',
	);

	$required_libs = array(
		'PEAR' => 'PEAR.php',
		'Net_SMTP' => 'Net/SMTP.php',
		'Net_IDNA2' => 'Net/IDNA2.php',
		'Mail_mime' => 'Mail/mime.php',
	);

	$ini_checks = array(
		'mbstring.func_overload' => 0,
		'suhosin.session.encrypt' => 0,
	);

	$optional_checks = array(
		// required for utils/modcss.inc, should we require this?
		'allow_url_fopen' => 1,
		'date.timezone' => '-VALID-',
	);

	$source_urls = array(
		'Sockets' => 'http://www.php.net/manual/en/book.sockets.php',
		'Session' => 'http://www.php.net/manual/en/book.session.php',
		'PCRE' => 'http://www.php.net/manual/en/book.pcre.php',
		'FileInfo' => 'http://www.php.net/manual/en/book.fileinfo.php',
		'Libiconv' => 'http://www.php.net/manual/en/book.iconv.php',
		'Multibyte' => 'http://www.php.net/manual/en/book.mbstring.php',
		'Mcrypt' => 'http://www.php.net/manual/en/book.mcrypt.php',
		'OpenSSL' => 'http://www.php.net/manual/en/book.openssl.php',
		'JSON' => 'http://www.php.net/manual/en/book.json.php',
		'DOM' => 'http://www.php.net/manual/en/book.dom.php',
		'Intl' => 'http://www.php.net/manual/en/book.intl.php',
		'Exif' => 'http://www.php.net/manual/en/book.exif.php',
		'PDO' => 'http://www.php.net/manual/en/book.pdo.php',
		'pdo_mysql' => 'http://www.php.net/manual/en/ref.pdo-mysql.php',
		'pdo_pgsql' => 'http://www.php.net/manual/en/ref.pdo-pgsql.php',
		'pdo_sqlite' => 'http://www.php.net/manual/en/ref.pdo-sqlite.php',
		'pdo_sqlite2' => 'http://www.php.net/manual/en/ref.pdo-sqlite.php',
		'pdo_sqlsrv' => 'http://www.php.net/manual/en/ref.pdo-sqlsrv.php',
		'pdo_dblib' => 'http://www.php.net/manual/en/ref.pdo-dblib.php',
		'PEAR' => 'http://pear.php.net',
		'Net_SMTP' => 'http://pear.php.net/package/Net_SMTP',
		'Mail_mime' => 'http://pear.php.net/package/Mail_mime',
		'Net_IDNA2' => 'http://pear.php.net/package/Net_IDNA2',
	);
	$supported_dbs = array(
		'MySQL' => 'pdo_mysql',
		'PostgreSQL' => 'pdo_pgsql',
		'SQLite' => 'pdo_sqlite',
		'SQLite (v2)' => 'pdo_sqlite2',
		'SQL Server (SQLSRV)' => 'pdo_sqlsrv',
		'SQL Server (DBLIB)' => 'pdo_dblib',
	);

	?>
	<h3>Checking PHP extensions</h3>
	<p class="hint">The following modules/extensions are <em>required</em> to run Roundcube:</p>
	<?php
// get extensions location
	$ext_dir = ini_get('extension_dir');
	$prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
	foreach ($required_php_exts as $name => $ext) {
		if (extension_loaded($ext)) {
			echo 'OK - ' . $name;
		} else {
			$_ext = $ext_dir . '/' . $prefix . $ext . '.' . PHP_SHLIB_SUFFIX;
			$msg = @is_readable($_ext) ? 'Could be loaded. Please add in php.ini' : '';
			echo '<span class="redd">ERROR</span> - ' . $name . ' | ' . $msg . ' | ' . $source_urls[$name];
		}
		echo '<br />';
	}

	?>
	<p class="hint">The next couple of extensions are <em>optional</em> and recommended to get the best performance:</p>
	<?php
	foreach ($optional_php_exts as $name => $ext) {
		if (extension_loaded($ext)) {
			echo 'OK - ' . $name;
		} else {
			$_ext = $ext_dir . '/' . $prefix . $ext . '.' . PHP_SHLIB_SUFFIX;
			$msg = @is_readable($_ext) ? 'Could be loaded. Please add in php.ini' : '';
			echo '<span class="redd">ERROR</span> - ' . $name . ' | ' . $msg . ' | ' . $source_urls[$name];
		}
		echo '<br />';
	}

	?>
	<h3>Checking available databases</h3>
	<p class="hint">Check which of the supported extensions are installed. At least one of them is required.</p>
	<?php
	$prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
	foreach ($supported_dbs as $database => $ext) {
		if (extension_loaded($ext)) {
			echo 'OK - ' . $database . ' ';
		} else {
			$_ext = $ext_dir . '/' . $prefix . $ext . '.' . PHP_SHLIB_SUFFIX;
			$msg = @is_readable($_ext) ? 'Could be loaded. Please add in php.ini' : '';
			echo '<span class="redd">ERROR</span> - ' . $database . ' | ' . $msg . ' | ' . $source_urls[$ext];
		}
		echo '<br />';
	}

	?>
	<h3>Check for required 3rd party libs</h3>
	<p class="hint">This also checks if the include path is set correctly.</p>
	<?php
	foreach ($required_libs as $classname => $file) {
		@include_once $file;
		if (class_exists($classname)) {
			echo 'OK - ' . $classname;
		} else {
			echo '<span class="redd">ERROR</span> - ' . $classname . " | Failed to load $file" . ' | ' . $source_urls[$classname];
		}
		echo "<br />";
	}

	?>
	<h3>Checking php.ini/.htaccess settings</h3>
	<p class="hint">The following settings are <em>required</em> to run Roundcube:</p>
	<?php
	foreach ($ini_checks as $var => $val) {
		$status = ini_get($var);
		if ($val === '-NOTEMPTY-') {
			if (empty($status)) {
				echo '<span class="redd">ERROR</span> - ' . $var . " | empty value detected";
			} else {
				echo 'OK - ' . $var;
			}
		} else if (filter_var($status, FILTER_VALIDATE_BOOLEAN) == $val) {
			echo 'OK - ' . $var;
		} else {
			echo '<span class="redd">ERROR</span> - ' . $var . "| is '$status', should be '$val'";
		}
		echo '<br />';
	}

	?>
	<p class="hint">The following settings are <em>optional</em> and recommended:</p>
	<?php
	foreach ($optional_checks as $var => $val) {
		$status = ini_get($var);
		if ($val === '-NOTEMPTY-') {
			if (empty($status)) {
				echo '<span class="redd">ERROR</span> - ' . $var . " | Could be set";
			} else {
				echo 'OK - ' . $var;
			}
			echo '<br />';
			continue;
		}
		if ($val === '-VALID-') {
			if ($var == 'date.timezone') {
				try {
					$tz = new DateTimeZone($status);
					echo 'OK - ' . $var;
				} catch (Exception $e) {
					echo '<span class="redd">ERROR</span> - ' . $var . ' | ' . empty($status) ? "not set" : "invalid value detected: $status";
				}
			} else {
				echo 'OK - ' . $var;
			}
		} else if (filter_var($status, FILTER_VALIDATE_BOOLEAN) == $val) {
			echo 'OK - ' . $var;
		} else {
			echo '<span class="redd">ERROR</span> - ' . $var . " | is '$status', could be '$val'";
		}
		echo '<br />';
	}

	?>
</form>
