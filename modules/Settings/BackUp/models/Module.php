<?php

/**
 * @package YetiForce.models
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_BackUp_Module_Model extends Vtiger_Base_Model
{

	var $tempDir = 'cache/backup';
	var $destDir = 'backup';
	var $backupInfo = false;
	var $ajaxFilesLimit = 100;
	var $ajaxDBLimit = 100;
	var $cron = false;

	public static function getCleanInstance()
	{
		$instance = new self();
		return $instance;
	}

	public function getProgress($id)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT value FROM vtiger_backup_settings WHERE type = ? && param = ?', ['ftp', 'active']);
		$ftpActive = $db->query_result_raw($result, 0, 'value');
		if ($ftpActive) {
			
		}

		$result = $db->pquery('SELECT * FROM vtiger_backup_tmp WHERE id = ?', [$id]);
		if ($db->num_rows($result) > 0) {
			$progress = $db->raw_query_result_rowdata($result, 0);
			$mainBar = $progress['b1'] * 0.025 + $progress['b2'] * 0.05 + $progress['b3'] * 0.35 +
				$progress['b4'] * 0.05 + $progress['b6'] * 0.025 +
				$progress['b7'] * 0.025 + $progress['b9'] * 0.025;
			if ($ftpActive) {
				$mainBar += $progress['b5'] * 0.35;
				$mainBar += $progress['b8'] * 0.10;
			} else {
				$mainBar += $progress['b5'] * 0.45;
			}
			return [
				'b1' => $progress['b1'] != null ? $progress['b1'] : 0,
				'b2' => $progress['b2'] != null ? $progress['b2'] : 0,
				'b3' => $progress['b3'] != null ? $progress['b3'] : 0,
				'b4' => $progress['b4'] != null ? $progress['b4'] : 0,
				'b5' => $progress['b5'] != null ? $progress['b5'] : 0,
				'b6' => $progress['b6'] != null ? $progress['b6'] : 0,
				'b7' => $progress['b7'] != null ? $progress['b7'] : 0,
				'b8' => $progress['b8'] != null ? $progress['b8'] : 0,
				'b9' => $progress['b9'] != null ? $progress['b9'] : 0,
				'mainBar' => round($mainBar, 2)
			];
		} else {
			return false;
		}
	}

	public function updateProgress($stage, $value, $time = 0)
	{
		$adb = PearDatabase::getInstance();
		$query = sprintf('UPDATE vtiger_backup_tmp SET b%s = ?,t%s = t%s + %s  WHERE id = ?;', $stage, $stage, $stage, $time);
		$adb->pquery($query, [ $value, $this->get('id')]);
	}

	public function runBackup()
	{
		$this->getBackupInfo();
		$this->backupInit();

		$this->performDBBackup();
		$this->performBackupFiles();

		if ($this->get('allfiles')) {
			$this->sendBackupToFTP();
			$this->sendNotification();
			$this->postBackup();
			$this->clearStructure();
		}
	}

	public function backupInit()
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);
		$db = PearDatabase::getInstance();
		if ($this->get('id') == NULL) {
			$name = date('Ymd_Hi');
			$db->pquery('INSERT INTO vtiger_backup (filename, starttime) VALUES (?,?)', [ $name, date('Y-m-d H:i:s')]);
			$this->set('id', $db->getLastInsertID());
			$db->pquery('INSERT INTO vtiger_backup_tmp (id) VALUES (?)', [$this->get('id')]);
			$this->set('filename', $name);
		} else {
			$db->pquery('UPDATE vtiger_backup SET backupcount = backupcount + 1 WHERE id = ?;', [ $this->get('id')]);
		}
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
	}

	public function performDBBackup()
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);
		$db = PearDatabase::getInstance();
		if ($this->get('b1') == 0) {
			$this->createEmptySQLFile();
		}
		if ($this->get('b2') == 0) {
			$this->createTablesStructure();
		}
		$tablesName = $this->getTablesStructure();
		if ($db->getRowCount($tablesName) > 0) {
			$this->backupTable($tablesName);
			$this->createEndSQLFile();
		}
		$this->postDBBackup();
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
	}

	public function createTablesStructure()
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);
		$db = PearDatabase::getInstance();
		$result = $this->getTablesName();
		$count = $db->getRowCount($result);

		if ($count > 0) {
			for ($i = 0; $i < $count; $i++) {
				$start = self::getTime();
				$tableName = $db->query_result($result, $i, 'Tables_in_' . $db->getDatabaseName());
				$this->addTableToBackup($tableName);
				$this->updateProgress('2', (($i + 1) / $count) * 100, self::getTime() - $start);
			}
		}
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
	}

	public function getTablesName()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SHOW TABLES WHERE ? NOT IN ( SELECT \'vtiger_backup_files\' UNION SELECT \'vtiger_backup_db\' UNION SELECT tablename FROM vtiger_backup_db)', ['Tables_in_' . $db->getDatabaseName()]);
		return $result;
	}

	public function addTableToBackup($tableName)
	{
		$db = PearDatabase::getInstance();
		$query = sprintf('SELECT COUNT(*) AS count FROM %s', $tableName);
		$countResult = $db->query($query);
		$count = $db->query_result_raw($countResult, 0, 'count');
		$db->pquery('INSERT INTO vtiger_backup_db (tablename,count) VALUES (?,?);', [$tableName, $count]);
	}

	public function getTablesStructure($status = false)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT tablename, offset, count FROM vtiger_backup_db WHERE status = ?', [$status]);
		return $result;
	}

	public function backupTable($result)
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);
		$db = PearDatabase::getInstance();
		$rowLimit = 1000;

		for ($a = 0; $a < $db->num_rows($result); $a++) {
			$limit = '';
			$tableName = $db->query_result_raw($result, $a, 'tablename');
			$dbOffset = $db->query_result_raw($result, $a, 'offset');
			$count = $db->query_result_raw($result, $a, 'count');
			$parcels = (int) ($count / $rowLimit);
			$numRows = 0;

			for ($k = 0; $k <= $parcels; $k++) {
				$start = self::getTime();
				$offset = $dbOffset + ($k * $rowLimit);
				$sqlLimit = " LIMIT $offset, $rowLimit";
				$numRows = $offset;
				$query = sprintf('SELECT * FROM %s %s', $tableName, $sqlLimit);
				$contentResult = $db->query($query);
				$numFields = $db->getFieldsCount($contentResult);
				$fields = $db->getFieldsArray($contentResult);
				$fieldsList = '';

				foreach ($fields as $key => $field) {
					if ($key == 0) {
						$fieldsList .= '(';
					}
					$fieldsList .= "`$field`";
					if ($key < ($numFields - 1)) {
						$fieldsList.= ',';
					}
					if (($numFields - 1) == $key) {
						$fieldsList .= ')';
					}
				}

				if ($offset == 0) {
					$tableSchema = $db->query('SHOW CREATE TABLE ' . $tableName);
					$tableSchemaRowData = $db->raw_query_result_rowdata($tableSchema);
					$createSchema = $this->strInsert($tableSchemaRowData[1], 'CREATE TABLE', ' IF NOT EXISTS ');
					$content = $createSchema . ';\n';
					$this->addToSQLFiles($content);
				}
				for ($i = 0; $i < $db->num_rows($contentResult); $i++) {
					$content = 'INSERT INTO `' . $tableName . '` ' . $fieldsList . ' VALUES(';
					for ($j = 0; $j < $numFields; $j++) {
						$row = $db->raw_query_result_rowdata($contentResult, $i);
						if (isset($row[$j])) {
							$row[$j] = $this->cleanSqlVal($row[$j]);
							$content.= '"' . $row[$j] . '"';
						} else {
							$content.= 'NULL';
						}
						if ($j < ($numFields - 1)) {
							$content.= ',';
						}
					}
					$content.= ');\n';
					$this->addToSQLFiles($content);
					$numRows++;
					$db->pquery('Update vtiger_backup_db SET offset = ? WHERE tablename = ?', [$numRows, $tableName]);
				}
				if ($numRows == $count) {
					$db->pquery('Update vtiger_backup_db SET status = ? WHERE tablename = ?', [true, $tableName]);
				}
				$this->updateDBPrecent(self::getTime() - $start);
				$info = $this->getBackupInfo();
				if ($info === false) {
					return;
				}
			}
		}
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
	}

	public function strInsert($str, $search, $insert)
	{
		$index = strpos($str, $search);
		if ($index === false) {
			return $str;
		}
		return substr_replace($str, $search . $insert, $index, strlen($search));
	}

	public function cleanSqlVal($str)
	{
		if (@isset($str)) {
			$sqlstr = addslashes($str);
			$sqlstr = preg_replace("/\n/", "\\n", $sqlstr);
			$sqlstr = preg_replace("/\r\n/", "\\r\\n", $sqlstr);
			return $sqlstr;
		} else {
			return 'NULL';
		}
	}

	public function updateDBPrecent($time)
	{
		$db = PearDatabase::getInstance();
		if ($this->get('allCountTables') == null) {
			$result = $db->pquery('SELECT SUM(`count`) AS `count` FROM vtiger_backup_db');
			$allCount = $db->query_result_raw($result, 0, 'count');
			$this->set('allCountTables', $allCount);
		}
		$allCount = $this->get('allCountTables');
		$result = $db->pquery('SELECT SUM(`count`) AS `count` FROM vtiger_backup_db WHERE status = ?', [1]);
		$count = $db->query_result_raw($result, 0, 'count');
		$count = ($count / $allCount) * 100;
		$this->updateProgress('3', $count, $time);
	}

	public function postDBBackup()
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);
		if (extension_loaded('zip')) {
			$zip = new ZipArchive();
			$zip->open($this->tempDir . '/' . $this->get('filename') . '.db.zip', ZipArchive::CREATE);
			$zip->addFile($this->tempDir . '/' . $this->get('filename') . '.sql', $this->get('filename') . '.sql');
			if (vglobal('encryptBackup') && version_compare(PHP_VERSION, '5.6.0') >= 0) {
				$code = $zip->setPassword(AppConfig::securityKeys('backupPassword'));
				if ($code === true)
					$log->debug('Backup files password protection is enabled');
				else
					$log->error('Has not been possible password protect your backup files');
			}
			$zip->close();

			if (file_exists($this->tempDir . '/' . $this->get('filename') . '.sql')) {
				unlink($this->tempDir . '/' . $this->get('filename') . '.sql');
			}
		}
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
	}

	public function performBackupFiles()
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);
		if (!extension_loaded('zip')) {
			$log->warn('ZIP library was not found');
			return false;
		}
		$db = PearDatabase::getInstance();
		$this->generateFilesStructure();
		$this->zipData();
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
	}

	public function clearBackupFilesTable()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT COUNT(*) FROM vtiger_backup_files WHERE backup = ?;', [0]);
		$count = $adb->query_result_raw($result, 0, 0);
		if (!$count) {
			$result = $adb->pquery("DELETE FROM `vtiger_backup_files`");
			return 1;
		}
		return 0;
	}

	public function generateFilesStructure()
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);

		$configFolder = $this->getConfig('folder');
		$newBackup = $this->clearBackupFilesTable();
		$dirs = array_filter(array_merge(glob('*'), glob('.htaccess')));
		$dirs = array_diff($dirs, array('cache'));
		if ('true' != $configFolder['storage_folder'])
			$dirs = array_diff($dirs, ['storage']);
		if ('true' != $configFolder['backup_folder'])
			$dirs = array_diff($dirs, [$this->destDir]);

		if ($newBackup) {
			$log->debug('Cron BackUp - New files backup');
			$allDir = count($dirs);
			$count = 1;
			$allFiles = 0;
			foreach ($dirs as $dir) {
				$start = self::getTime();
				if (is_dir($dir)) {
					$this->addFileToBackup($dir);
					$allFiles++;
					$flags = RecursiveIteratorIterator::SELF_FIRST || FilesystemIterator::KEY_AS_PATHNAME;
					$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS), $flags);
					foreach ($iterator as $path => $file) {
						$this->addFileToBackup($path);
						$allFiles++;
					}
				} else {
					$this->addFileToBackup($dir);
					$allFiles++;
				}
				$this->updateProgress('4', ($count / $allDir) * 100, self::getTime() - $start);
				$count++;
				$info = $this->getBackupInfo();
				if ($info === false) {
					return;
				}
			}
			$this->set('allfiles', $allFiles);
		} else {
			$this->updateProgress('4', 100);
		}
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
	}

	public function getFilesStructure()
	{
		$adb = PearDatabase::getInstance();
		$files = [];
		$result = $adb->pquery('SELECT id, backup, name FROM `vtiger_backup_files` WHERE backup=?', [0]);
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$files[$adb->query_result($result, $i, 'id')] = $adb->query_result($result, $i, 'name');
		}
		return $files;
	}

	public function zipData()
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);
		$dbFiles = $this->getFilesStructure();

		$zip = new ZipArchive();
		$destination = $this->tempDir . '/' . $this->get('filename') . '.files.zip';
		$count = 1;
		$allFiles = $this->get('allfiles');

		$mainConfig = $this->getConfig('main');
		$maxTime = ini_get('max_execution_time') * 0.5;
		$startTime = self::getTime();
		$singleMode = $mainConfig['type'] == 'true'; // Overall mode or Single mode

		if ($zip->open($destination, ZIPARCHIVE::CREATE) && $allFiles != 0) {
			foreach ($dbFiles as $id => $path) {
				$start = self::getTime();
				if (is_dir($path)) {
					$zip->addEmptyDir($path . '/');
				} elseif (is_file($path)) {
					$zip->addFile($path, $path);
				}
				$this->markFile($id);
				$this->updateProgress('5', ($count / $allFiles) * 100, self::getTime() - $start);
				$count++;
				if ($singleMode && (self::getTime() - $startTime) >= $maxTime) {
					continue;
				}
			}
			if (vglobal('encryptBackup') && version_compare(PHP_VERSION, '5.6.0') >= 0) {
				$code = $zip->setPassword(AppConfig::securityKeys('backupPassword'));
				if ($code === true)
					$log->debug('Backup files password protection is enabled');
				else
					$log->error('Has not been possible password protect your backup files');
			}
			$zip->close();
		}
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
	}

	public function postBackup()
	{
		$start = self::getTime();
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);

		$dbZip = $this->get('filename') . '.db.zip';
		if (!rename($this->tempDir . '/' . $dbZip, $this->destDir . '/' . $dbZip)) {
			$log->debug('Error while moving a file: ' . $this->tempDir . '/' . $dbZip);
		}

		$filesZip = $this->get('filename') . '.files.zip';
		if (!rename($this->tempDir . '/' . $filesZip, $this->destDir . '/' . $filesZip)) {
			$log->debug('Error while moving a file: ' . $this->tempDir . '/' . $filesZip);
		}
		$this->updateProgress('6', 100, self::getTime() - $start);

		$start = self::getTime();
		$adb = PearDatabase::getInstance();
		$adb->pquery('UPDATE vtiger_backup_tmp SET status = ? WHERE id = ?;', [1, $this->get('id')]);

		$this->getBackupInfo(true);
		for ($i = 1; $i <= 9; $i++) {
			$time += $this->get('t' . $i);
		}
		$adb->pquery('UPDATE vtiger_backup SET endtime = ?, status = ?, backuptime = ? WHERE id = ?;', [date('Y-m-d H:i:s'), 1, $time, $this->get('id')]);
		$this->updateProgress('9', 100, self::getTime() - $start);
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
	}

	public function addToSQLFiles($content = '')
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);
		$fileName = $this->get('filename');
		@file_put_contents($this->tempDir . "/$fileName.sql", $content, FILE_APPEND);
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
	}

	public function createEmptySQLFile()
	{
		$start = self::getTime();
		$content = "/*!40101 SET NAMES utf8 */;
/*!40101 SET SQL_MODE=''*/;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;";
		$content.= "\n";
		$this->addToSQLFiles($content);
		$this->updateProgress('1', 100, self::getTime() - $start);
	}

	public function createEndSQLFile()
	{
		$content = "/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;";
		$content.= "\n";
		$this->addToSQLFiles($content);
	}

	public function clearStructure()
	{
		$adb = PearDatabase::getInstance();
		$start = self::getTime();

		$adb->query("TRUNCATE TABLE `vtiger_backup_db`");
		$adb->query("TRUNCATE TABLE `vtiger_backup_files`");

		$this->updateProgress('7', 100, self::getTime() - $start);
	}

	public function getBackupList($offset = null, $limit = null)
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM vtiger_backup ORDER BY endtime DESC';

		if ($offset !== null) {
			$query .= " LIMIT $offset, $limit";
		}
		$result = $db->query($query);
		$return = [];
		$moduleName = 'Settings::BackUp';
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$return[] = [
				'id' => $db->query_result_raw($result, $i, 'id'),
				'starttime' => $db->query_result_raw($result, $i, 'starttime'),
				'endtime' => $db->query_result_raw($result, $i, 'endtime') != null ? $db->query_result_raw($result, $i, 'endtime') : '',
				'filename' => $db->query_result_raw($result, $i, 'filename') . '.zip',
				'status' => vtranslate(self::getStatusName($db->query_result_raw($result, $i, 'status')), $moduleName),
				'backuptime' => Settings_BackUp_Module_Model::formatBackupTime($db->query_result_raw($result, $i, 'backuptime')),
			];
		}
		return $return;
	}

	public function getBackupCount()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->query('SELECT COUNT(*) AS num FROM vtiger_backup;');
		return $adb->query_result($result, 0, 'num');
	}

	public function addFileToBackup($file)
	{
		$db = PearDatabase::getInstance();
		$db->pquery('INSERT INTO `vtiger_backup_files` (`name`) VALUES (?);', [$file]);
		$db->pquery('UPDATE vtiger_backup_tmp SET allfiles = allfiles + 1 WHERE id = ?;', [$this->get('id')]);
	}

	public function markFile($id)
	{
		$adb = PearDatabase::getInstance();
		$adb->pquery("UPDATE vtiger_backup_files SET backup=? WHERE id = ?", [1, $id]);
	}

	public function getBackupInfo($type = false)
	{
		$adb = PearDatabase::getInstance();
		if ($type) {
			$where = 'vtiger_backup_tmp.id = ' . $this->get('id');
		} else {
			$where = 'vtiger_backup_tmp.status = 0';
		}

		$query = sprintf('SELECT vtiger_backup_tmp.*, vtiger_backup.filename FROM vtiger_backup_tmp LEFT JOIN vtiger_backup ON vtiger_backup.id = vtiger_backup_tmp.id WHERE %s', $where);
		$query = $adb->query($query);
		$result = $adb->fetch_array($query);
		if (!$result) {
			return false;
		}
		$data = [];
		foreach ($result as $index => $value) {
			$this->set($index, $value);
			$data[$index] = $value;
		}
		return $data;
	}

	public static function getConfig($type)
	{
		$db = PearDatabase::getInstance();

		$result = $db->pquery('SELECT * FROM vtiger_backup_settings WHERE type = ?;', [$type]);
		if ($db->num_rows($result) == 0) {
			return [];
		}
		$config = [];
		for ($i = 0; $i < $db->num_rows($result); ++$i) {
			$param = $db->query_result_raw($result, $i, 'param');
			$value = $db->query_result_raw($result, $i, 'value');
			if ($param == 'users') {
				$config[$param] = $value == '' ? [] : explode(',', $value);
			} else {
				$config[$param] = $value;
			}
		}
		return $config;
	}

	public function sendNotification()
	{
		$log = vglobal('log');
		$usersId = $this->getUsersForNotifications();
		if ($usersId) {
			foreach ($usersId as $id) {
				$recordModel = Vtiger_Record_Model::getInstanceById($id, 'Users');
				$userEmail = $recordModel->get('email1');
				$emails[] = $userEmail;
			}
			$emailsList = implode(',', $emails);
			$data = [
				'sysname' => 'BackupHasBeenMade',
				'to_email' => $emailsList,
				'module' => 'Contacts',
			];
			$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
			$mail_status = $recordModel->sendMailFromTemplate($data);

			if ($mail_status != 1) {
				$log->error('Settings_BackUp_Module_Model Error occurred while sending mail');
			}
		} else {
			$log->debug('Settings_BackUp_Module_Model Users notificastions list - empty');
		}
	}

	public static function getUsersForNotifications()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT * FROM vtiger_backup_settings WHERE param = ?', ['users']);
		$value = $adb->query_result($result, 0, 'value');
		return explode(',', $value);
	}

	public function sendBackupToFTP()
	{
		$start = self::getTime();
		$log = vglobal('log');
		$ftp = $this->getFTPSettings();

		$backupFile = $this->get('filename') . '.zip';
		if ($ftp['active'] == 1) {
			$log->debug('Start sending backup to ftp');
			$password = $this->encrypt_decrypt('decrypt', $ftp['password']);

			if ($ftp['port'])
				$connection = ftp_connect($ftp['host'], $ftp['port']);
			else
				$connection = ftp_connect($ftp['host']);

			ftp_login($connection, $ftp['login'], $password);
			ftp_pasv($connection, true);

			if ($ftp['path']) {
				@ftp_mkdir($connection, $ftp['path']);
				$fileTo = $ftp['path'] . '/' . $backupFile;
			} else {
				$fileTo = $backupFile;
			}
			$log->debug('Sending backup to ftp');
			$upload = ftp_put($connection, $fileTo, $this->destDir . '/' . $backupFile, FTP_BINARY);
			ftp_close($connection);
			$this->updateProgress('8', 100, self::getTime() - $start);
			$log->debug('Closing connection after send backup to ftp');
		}
	}

	static public function getFTPSettings()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_backup_settings WHERE type = ?', ['ftp']);
		$numRows = $db->num_rows($result);
		$output = [];
		for ($i = 0; $i < $numRows; $i++) {
			$output[$db->query_result_raw($result, $i, 'param')] = $db->query_result_raw($result, $i, 'value');
		}
		return $output;
	}

	public function encrypt_decrypt($action, $string)
	{
		$output = false;

		$encrypt_method = "AES-256-CBC";
		$secret_key = 'This is my secret key';
		$secret_iv = 'This is my secret iv';
		$key = hash('sha256', $secret_key);
		$iv = substr(hash('sha256', $secret_iv), 0, 16);

		if ($action == 'encrypt') {
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
		} else if ($action == 'decrypt') {
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}
		return $output;
	}

	public function saveFTPSettings($host, $login, $password, $status, $port, $active, $path)
	{
		$password = self::encrypt_decrypt('encrypt', $password);

		self::updateSettings(['param' => 'host', 'val' => $host]);
		self::updateSettings(['param' => 'login', 'val' => $login]);
		self::updateSettings(['param' => 'password', 'val' => $password]);
		self::updateSettings(['param' => 'active', 'val' => $status]);
		self::updateSettings(['param' => 'port', 'val' => $port]);
		self::updateSettings(['param' => 'active', 'val' => $active]);
		self::updateSettings(['param' => 'path', 'val' => $path]);
	}

	public function updateSettings($params)
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);
		$db = PearDatabase::getInstance();
		$val = $params['val'];
		if (is_array($val)) {
			$val = implode(",", $val);
		}
		$db->pquery('UPDATE `vtiger_backup_settings` SET `value` = ? WHERE `param` = ?;', [$val, $params['param']]);
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
		return true;
	}

	public function tarData($tarFile, $cron)
	{
		$log = vglobal('log');
		$log->info('BackUp - Start ZipArchive');
		$dbFiles = $this->getFilesStructure();

		$destination = $this->tempDir . '/' . $tarFile . '.tar';
		$tar = new PharData($destination);

		$count = 0;
		foreach ($dbFiles as $id => $path) {
			if (is_dir($path)) {
				$tar->addEmptyDir($path . '/');
			} elseif (is_file($path)) {
				$tar->addFile($path, $path);
			}
			$this->markFile($id);
			if ($count == $this->ajaxFilesLimit && $cron == FALSE) {
				$percentage = $this->getPercentage();
				exit(json_encode(['percentage' => $percentage]));
			}
			$count++;
		}
	}

	public function getStatusName($id)
	{
		switch ($id) {
			case 0:
				$return = 'In progress';
				break;
			case 1:
				$return = 'Completed';
				break;
			case 2:
				$return = 'Aborted by user';
				break;
		}
		return $return;
	}

	public function formatBackupTime($time)
	{
		$days = floor($time / 86400);
		$hours = floor($time / 60 / 60);
		$mins = floor($time / 60);
		$return = '';

		if ($days) {
			$return .= $days . ' ';
			$return .= vtranslate('LBL_DAYS') . ' ';
		}
		if ($hours) {
			$return .= $hours - ($days * 24) . ' ';
			$return .= vtranslate('LBL_HOURS') . ' ';
		}
		if ($mins) {
			$return .= $mins - ($hours * 60) . ' ';
			$return .= vtranslate('LBL_MINUTES') . ' ';
		}
		$return .= (int) $time - ($days * 24) - ($hours * 60) - ($mins * 60) . ' ';
		$return .= vtranslate('LBL_SECONDS');
		return $return;
	}

	public function checkCron()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT status FROM vtiger_cron_task WHERE module = ?;', ['BackUp']);
		return $db->query_result_raw($result, 0, 'status');
	}

	public function checkMail()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT 1 FROM vtiger_systems WHERE server_type = ?', ['email']);
		if ($db->num_rows($result)) {
			return true;
		} else {
			return false;
		}
	}

	public static function scheduleBackup()
	{
		$db = PearDatabase::getInstance();
		$db->pquery('UPDATE `vtiger_cron_task` SET `laststart` = ? WHERE `module` = ?;', [0, 'BackUp']);
	}

	public function getTime()
	{
		$a = explode(' ', microtime());
		return (double) $a[0] + $a[1];
	}

	public function stopBackup()
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);

		$db = PearDatabase::getInstance();
		$db->delete('vtiger_backup_db');
		$db->delete('vtiger_backup_files');
		$db->delete('vtiger_backup_tmp');
		$db->update('vtiger_backup', ['status' => 2], 'status = ? ', [0]);

		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
	}
}
