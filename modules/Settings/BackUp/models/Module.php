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

class Settings_BackUp_Module_Model extends Vtiger_Module_Model {
	static $tempDir = 'cache/backup';
	static $destDir = 'backup';
	
    public static function getBackUps($offset = null, $limit = null) {
        global $adb;
        $query = ('SELECT * FROM vtiger_backup  ORDER BY created_at DESC');

        if ($offset !== null) {
            $query .= " LIMIT $offset, $limit";
        }
        $result = $adb->query($query, true);

        foreach ($result as $value) {
            $output[] = $value;
        }
        return $output;
    }

    public function deleteTmpBackUpContent() {
        global $adb;
        $adb->query("DELETE  FROM `vtiger_backup_db_tmp`");
        $adb->query("DELETE  FROM `vtiger_backup_db_tmp_info`");
    }

    public function getTablesName() {
        global $adb;
        $result = $adb->query('SHOW TABLES WHERE Tables_in_' . $adb->dbName . ' NOT IN (
                SELECT table_name
                FROM vtiger_backup_db_tmp)');
        return $result;
    }

    public function getAmountAllTablesToBackup() {
        global $adb;
        $result = $adb->query('SELECT COUNT(*) FROM vtiger_backup_db_tmp');
        return $result;
    }

    public function getDBTablesAmount() {
        global $adb;
        $query = $adb->query("SELECT count( * ) AS `AllTables` FROM information_schema.TABLES WHERE table_schema = '$adb->dbName'");
        $output = $adb->query_result_rowdata($query, 0);
        return $output;
    }

    public function saveTableName($tableName) {
        global $adb;
        $adb->pquery("INSERT INTO vtiger_backup_db_tmp (table_name) VALUES (?)", array($tableName));
    }

    public function clearBackupFilesTable() {
        global $adb;
        $result = $adb->pquery("SELECT COUNT(*) FROM `vtiger_backup_dir` WHERE backup=0;");
        $count = $adb->query_result_raw($result, 0, 0);
        if (!$count) {
            $result = $adb->pquery("DELETE FROM `vtiger_backup_dir`");
            return 1;
        }
        return 0;
    }

    public function addBackupDirs($dirs) {
        global $adb;
        $adb->startTransaction();
        foreach ($dirs as $dir) {
            $dir = str_replace('\\', '/', $dir);
            $result = $adb->pquery("INSERT IGNORE INTO `vtiger_backup_dir` (`name`, `backup`) VALUES ('$dir', 0);");
        }
        $adb->completeTransaction();
    }

    public function getPercentage() {
        global $adb;
        $result = $adb->pquery("SELECT TRUNCATE(((SELECT COUNT(*) FROM `vtiger_backup_dir` WHERE BACKUP='1') / COUNT(*)) * 100, 2) AS 'percentage' FROM `vtiger_backup_dir`");
        $percentage = $adb->query_result_raw($result, 0, 0);
        return $percentage;
    }

    public function getDirs() {
        global $adb;
        $result = $adb->pquery("SELECT backup, name FROM `vtiger_backup_dir`");
        $numOfRows = $adb->num_rows($result);
        for ($i = 0; $i < $numOfRows; $i++) {
            $dirs[$adb->query_result($result, $i, 'name')] = $adb->query_result($result, $i, 'backup');
        }
        return $dirs;
    }

    public function setDirBackuped($source) {
        global $adb;
        $dir = str_replace('\\', '/', $source);
        $result = $adb->pquery("UPDATE `vtiger_backup_dir` vd SET vd.backup='1' WHERE vd.name = '$dir'");
    }

    public function saveTmpBackUpTableName($tableName) {
        global $adb;
        $adb->pquery("INSERT INTO vtiger_backup_db_tmp (table_name) VALUES (?)", array($tableName));
    }

    public function getNotBackUpTables($limit) {
        global $adb;
        $query = ('SELECT table_name FROM vtiger_backup_db_tmp WHERE status = ?');
        if ($limit == TRUE) {
            $query .= ' LIMIT 45';
        }
        $result = $adb->pquery($query, array(FALSE));
        return $result;
    }

    public function getBackUpInfo() {
        global $adb;
        $query = $adb->query("SELECT * FROM  vtiger_backup_db_tmp_info ");
        $result = $adb->fetch_array($query);
        return $result;
    }

    public function setStartTmpBackupInfo($fileName) {
        global $adb;
        $adb->pquery("INSERT INTO vtiger_backup_db_tmp_info (status, file_name) VALUES (?, ?)", array('pending', $fileName));
    }

    public function setTablesPrepare($tablesPrepare, $backUpId) {
        global $adb;
        $adb->pquery("Update vtiger_backup_db_tmp_info SET tables_prepare = ?  WHERE id = ? ", array($tablesPrepare, $backUpId));
    }

    public function updateTmpBackUpInfo($status, $time, $howMany = null) {
        global $adb;
        $backUpInfo = self::getBackUpInfo();
        if ($howMany != null) {
            $howMany = (int) $backUpInfo['howmany'];
            $howMany++;
        } else {
            $howMany = (int) $backUpInfo['howmany'];
        }
        $time = $backUpInfo['time'] + $time;
        $adb->pquery("Update vtiger_backup_db_tmp_info SET status = ?, time = ?, howmany = ? WHERE id = ?", array($status, $time, $howMany, $backUpInfo['id']));
    }

    public function getPercentageDBBackUp() {
        global $adb;
        $result = $adb->pquery("SELECT TRUNCATE(((SELECT COUNT(*) FROM `vtiger_backup_db_tmp` WHERE status = 1) / COUNT(*)) * 100, 2) AS 'percentage' FROM `vtiger_backup_db_tmp`");
        $percentage = $adb->query_result_raw($result, 0, 0);
        return $percentage;
    }

    public function setBackUp() {
        $now = date('Y-m-d H:i:s');
        global $adb;
        $backUpInfo = self::getBackUpInfo();
        $adb->pquery("INSERT INTO vtiger_backup (file_name, created_at, create_time, how_many) VALUES (?,?,?,?)", array(
            $backUpInfo['file_name'].".zip",
            $now,
            $backUpInfo['time'],
            $backUpInfo['howmany'])
        );
    }

    static public function getFTPSettings() {
        global $adb;
        $result = $adb->query("SELECT * FROM vtiger_backup_ftp", true);
        $numOfRows = $adb->num_rows($result);

        if ($numOfRows == 0)
            return false;

        $output = $adb->query_result_rowdata($result, 0);
        return $output;
    }

	public function saveFTPSettings($host, $login, $password, $status, $port, $active, $path) {
		global $adb;
		$ftpExist = self::getFTPSettings();
		$password = self::encrypt_decrypt('encrypt', $password);
		if($ftpExist[0])
			$adb->pquery("UPDATE vtiger_backup_ftp SET host=?, login=?, password=?, status=?, port=?, active=?, path=? WHERE id=?", array($host, $login, $password, $status, $port, $active, $path, $ftpExist[0]));
		else
			$adb->pquery("INSERT INTO vtiger_backup_ftp (host, login, password, status, port, active, path) VALUES(?,?,?,?,?,?,?)", array($host, $login, $password, $status, $port, $active, $path));

	}
	
    public function createBackUpSQLStatement($tablesName, $fileName) {
        global $adb;
        foreach ($tablesName as $key => $tableName) {
            $return = '';
            $result = $adb->query('SELECT * FROM ' . $tableName[0]);
            $numFields = $adb->num_fields($result);
            $fields = $adb->getFieldsArray($result);
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
            $tableSchema = $adb->query('SHOW CREATE TABLE ' . $tableName[0]);
            $tableSchemaRowData = $adb->raw_query_result_rowdata($tableSchema);
            $createSchema = self::strInsert($tableSchemaRowData[1], 'CREATE TABLE', ' IF NOT EXISTS ');
            $return.= "\n" . $createSchema . ";\n";

            for ($i = 0; $i < $adb->num_rows($result); $i++) {
                    $return.= 'INSERT INTO `' . $tableName[0] . '` ' . $fieldsList . ' VALUES(';
                    for ($j = 0; $j < $numFields; $j++) {
						$row = $adb->raw_query_result_rowdata($result, $i);
                        if (isset($row[$j])) {
							$row[$j] = self::clean_sql_val($row[$j]);
                            $return.= '"' . $row[$j] . '"';
                        } else {
                            $return.= "NULL";
                        }
                        if ($j < ($numFields - 1)) {
                            $return.= ',';
                        }
                    }
                    $return.= ");\n";
            }
            $return.="\n";
			self::saveSQLStatementIntoFile($fileName, $return);
            $adb->pquery("Update vtiger_backup_db_tmp SET status = ? WHERE table_name = ?", array(TRUE, $tableName[0]));
        }
    }
	
	public function clean_sql_val($str) {
		if(@isset($str)){
			$sqlstr = addslashes($str);
			$sqlstr = ereg_replace("\n","\\n",$sqlstr);
			$sqlstr = preg_replace("/\r\n/","\\r\\n",$sqlstr);
			return $sqlstr;
		}else{
			return 'NULL';	
		}
	}
    public function strInsert($str, $search, $insert) {
        $index = strpos($str, $search);
        if ($index === false) {
            return $str;
        }
        return substr_replace($str, $search . $insert, $index, strlen($search));
    }
	
    public function saveSQLStatementIntoFile($fileName, $content) {
		if(empty($content)){
			$content = ' ';
		}
		@file_put_contents(self::$tempDir."/$fileName.sql", $content, FILE_APPEND);
	}
	
    public function newBackUp() {
        global $log;
        $log->info('New db backup start');
        $now = date('Ymd-His');
        self::setStartTmpBackupInfo($now);
        self::setStartingSQLFileContent($now);
        $backUpInfo = self::getBackUpInfo();
        return $backUpInfo;
    }

    public function setStartingSQLFileContent($fileName) {
        $result = "/*!40101 SET NAMES utf8 */;
/*!40101 SET SQL_MODE=''*/;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;";
        $result.= "\n";
        self::saveSQLStatementIntoFile($fileName, $result);
    }
	
    public function setTmpBackUpTables($backUpId) {
        global $adb;
        $tablesName = self::getTablesName();
        $tablesNameAmount = $adb->getRowCount($tablesName);

        if ($tablesNameAmount > 0) {
            $counter = 0;
            foreach ($tablesName as $tableName) {
                self::saveTmpBackUpTableName($tableName);
                if ($counter > 50) {
                    $allDBTablesAmount = self::getDBTablesAmount();
                    $allDBTablesAmountInt = (int) $allDBTablesAmount[0];
                    $tmpTablesAmount = self::getAmountAllTablesToBackup();
                    $tmpTablesAmountInt = (int) $tmpTablesAmount->fields[0];
                    $percentage = ($tmpTablesAmountInt * 100) / $allDBTablesAmountInt;

                    exit(json_encode(array('success' => true, 'status' => 'prepare', 'percentage' => $percentage)));
                }
                $counter++;
            }
        } else {
            self::setTablesPrepare(true, $backUpId);
            exit(json_encode(array('success' => true, 'status' => 'pending', 'percentage' => 0)));
        }
        self::setTablesPrepare(true, $backUpId);
        exit(json_encode(array('success' => true, 'status' => 'prepare', 'percentage' => 100)));
    }
	
    public function userBackUpCall($request) {
        global $adb;

        $startCreateBackUp = microtime(TRUE);
        if ($request->get('backUpAction') == 'new') {
            self::deleteOldBackUpFiles();
            self::deleteTmpBackUpContent();
        }

        $backUpInfo = self::getBackUpInfo();
        if ($backUpInfo['backup_db'] != TRUE) {
            if ($request->get('backUpAction') == 'new') {
                $backUpInfo = self::newBackUp();
            }
            if ($request->get('backUpAction') == 'resume') {
                self::updateTmpBackUpInfo('pending', 0, true);
            }
            if ($backUpInfo['tables_prepare'] != TRUE) {
                self::setTmpBackUpTables($backUpInfo['id']);
            }

            $tablesName = self::getNotBackUpTables(TRUE);
            $rowsNum = $adb->getRowCount($tablesName);

            if ($rowsNum == 0) {
                $result = self::endDBBackUp($backUpInfo['file_name']);
            } else {
                $result = self::pendingBackUp($backUpInfo['file_name'], $tablesName);
            }
            $endCreateBackUp = microtime(TRUE);
            $executeTime = $endCreateBackUp - $startCreateBackUp;
            self::updateTmpBackUpInfo('pending', $executeTime);
        } else {
            $result = array('success' => true, 'status' => 'end');
        }
        exit(json_encode($result));
    }
    public function deleteOldBackUpFiles() {
		/*
        $deleteFiles = array_filter(array_merge(glob(self::$tempDir.'/backup*.zip'), glob(self::$tempDir.'/*.sql')));
        foreach ($deleteFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
		*/
    }
    public function endDBBackUp($fileName) {
        global $adb;
        global $log;
        self::createZip($fileName);
        self::deleteFile($fileName . '.sql');
        $adb->pquery("Update vtiger_backup_db_tmp_info SET backup_db  = ? WHERE file_name  = ?", array(TRUE, $fileName));

        $log->info('Finished db backup');
        $result = array('success' => true, 'status' => 'end');
        return $result;
    }
    public function pendingBackUp($fileName, $tablesName) {
        global $log;
        $log->info('Create SQL Statement');
		self::createBackUpSQLStatement($tablesName, $fileName);
        $percentage = self::getPercentageDBBackUp();
        $result = array('success' => true, 'status' => 'pending', 'percentage' => $percentage);
        return $result;
    }
    public function createZip($fileName) {
        $zip = new ZipArchive();
        $zip->open( self::$tempDir.'/'.$fileName.'.db.zip', ZipArchive::CREATE );
        $zip->addFile( self::$tempDir.'/'.$fileName.'.sql', $fileName.'.sql' );
        $zip->close();
    }
    public function deleteFile($fileName) {
        if (file_exists( self::$tempDir.'/'.$fileName )) {
            unlink( self::$tempDir.'/'.$fileName );
        }
    }
	public function cronBackUp() {
		global $adb;
		$backUpInfo = self::getBackUpInfo();
		if ($backUpInfo != NULL) {
			self::updateTmpBackUpInfo('pending', 0, true);
		} else {
			$backUpInfo = self::newBackUp();
		}
		if ($backUpInfo['tables_prepare'] != TRUE) {
			self::setCronTmpBackUpTables($backUpInfo['id']);
		}
		$tablesName = self::getNotBackUpTables(FALSE);
		$rowsNum = $adb->getRowCount($tablesName);
		if ($rowsNum > 0) {
			self::pendingBackUp($backUpInfo['file_name'], $tablesName);
		}
		self::endDBBackUp($backUpInfo['file_name']);
	}

	public function setCronTmpBackUpTables($backUpId) {
		global $adb;
		$tablesName = self::getTablesName();
		$tablesNameAmount = $adb->getRowCount($tablesName);

		if ($tablesNameAmount > 0) {
			foreach ($tablesName as $tableName) {
				self::saveTmpBackUpTableName($tableName);
			}
		}
		self::setTablesPrepare(true, $backUpId);
	}

	public function sendBackupToFTP($backUpPath, $backupFile){
		global $log;
		$ftp = self::getFTPSettings();
		
		if(TRUE == $ftp['active'] && TRUE == $ftp['status']){
			$log->debug('Start sending backup to ftp');
			$password = self::encrypt_decrypt('decrypt', $ftp['password']);
			
			if($ftp['port'])
				$connection = ftp_connect($ftp['host'], $ftp['port']);
			else
				$connection = ftp_connect($ftp['host']); 

			ftp_login($connection, $ftp['login'], $password);
			ftp_pasv($connection, true);

			if($ftp['path']){
				@ftp_mkdir($connection, $ftp['path']);
				$fileTo = $ftp['path'] .'/'. $backupFile;
			}else{
				$fileTo = $backupFile;
			}			
			$log->debug('Sending backup to ftp');
			$upload = ftp_put($connection, $fileTo, $backUpPath.'/'.$backupFile, FTP_BINARY);
			ftp_close($connection);
			$log->debug('Closing connection after send backup to ftp');
		}
	}

	public function encrypt_decrypt($action, $string) {
		$output = false;

		$encrypt_method = "AES-256-CBC";
		$secret_key = 'This is my secret key';
		$secret_iv = 'This is my secret iv';
		$key = hash('sha256', $secret_key);
		$iv = substr(hash('sha256', $secret_iv), 0, 16);

		if( $action == 'encrypt' ) {
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
		}
		else if( $action == 'decrypt' ){
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}

		return $output;
	}

	public static function updateUsersForNotifications($selectedUsers){
		global $adb;
		$deleteQuery = "DELETE FROM `vtiger_backup_users`";
		$adb->query($deleteQuery);
		if('null' != $selectedUsers){
			$insertQuery = "INSERT INTO `vtiger_backup_users` (id) VALUES(?)";
			foreach ($selectedUsers as $userId) {
				$adb->pquery($insertQuery, array($userId));
			}
		}
		
		return TRUE;
	}

	public static function getUsersForNotifications(){
		global $adb;
		$result = $adb->query("SELECT * FROM vtiger_backup_users", true);
		$numRows = $adb->num_rows($result);
		for($i = 0; $i < $numRows; $i++){
			$id = $adb->query_result($result, $i, 'id');
			$output[$id] = $id;
		}

		return $output;
	}

	public static function sendNotificationEmail(){
		global $log;
		$usersId = self::getUsersForNotifications();
		if($usersId){
			foreach ($usersId as $id) {     
				$recordModel = Vtiger_Record_Model::getInstanceById($id, 'Users'); 
				$userEmail = $recordModel->get('email1'); 
				$emails[] = $userEmail;
			}
			$emailsList = implode(',', $emails);
			$data = array(
				'id' => 108,
				'to_email' => $emailsList,
				'module' => 'Contacts',
			);
			$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
			$mail_status = $recordModel->sendMailFromTemplate($data);
		 
			if($mail_status != 1) {
				$log->debug('Settings_BackUp_Module_Model Error occurred while sending mail');
				throw new Exception('Error occurred while sending mail');
			} 
		}else{
			$log->debug('Settings_BackUp_Module_Model Users notificastions list - empty');
		}
	}
}