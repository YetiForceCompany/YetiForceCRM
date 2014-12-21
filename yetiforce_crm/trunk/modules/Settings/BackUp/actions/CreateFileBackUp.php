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

class Settings_BackUp_CreateFileBackUp_Action extends Settings_Vtiger_Basic_Action {

    private $filename = '';

    public function process(Vtiger_Request $request) {
        global $log;
        $newBackup = Settings_BackUp_Module_Model::clearBackupFilesTable();
        $log->info('Settings_BackUp_CreateFileBackUp_Action::process - Start files backup');
        $dirs = array_filter(array_merge(glob('*'), glob('.htaccess')));
        $dirs = array_diff($dirs, array( Settings_BackUp_Module_Model::$destDir, 'cache' ));
        $dbDirs = Settings_BackUp_Module_Model::getDirs();
        $newDirs = array();
        $count = 0;
		
        $backUpInfo = Settings_BackUp_Module_Model::getBackUpInfo();
        $sqlFileName = $backUpInfo['file_name'];
		
        $this->fileName = $sqlFileName.'.files';
        if ($request->get('backUpAction') == 'cron')
            $cron = TRUE;
        else
            $cron = FALSE;

        if ($newBackup) {
            $log->info('New files backup');

            foreach ($dirs as $dir) {
                $dir = str_replace('\\', '/', $dir);
                if (!isset($dbDirs[$dir])) {
                    $newDirs[] = $dir;
                }
                if (!isset($dbDirs[$dir]) || $dbDirs[$dir] == 0) {
                    Settings_BackUp_CreateFileBackUp_Action::zipData($dir, Settings_BackUp_Module_Model::$tempDir.'/'.$this->fileName.'.zip', 0, $cron,array() , $this->fileName);
                }
            }
            Settings_BackUp_Module_Model::addBackupDirs($newDirs);
        }

        $dbAccuallyDirs = Settings_BackUp_Module_Model::getDirs();
        foreach ($dirs as $dir) {
            Settings_BackUp_CreateFileBackUp_Action::zipData($dir, Settings_BackUp_Module_Model::$tempDir.'/'.$this->fileName.'.zip', 1, $cron, $dbAccuallyDirs, $this->fileName);
        }
        $zip = new ZipArchive();
        $zip->open( Settings_BackUp_Module_Model::$destDir.'/'.$sqlFileName.'.zip', ZipArchive::CREATE );
        $zip->addFile( Settings_BackUp_Module_Model::$tempDir.'/'.$sqlFileName.'.db.zip', "db.zip" );
        $zip->addFile( Settings_BackUp_Module_Model::$tempDir.'/'.$this->fileName.'.zip', "files.zip" );
        $zip->close();

        Settings_BackUp_Module_Model::setBackUp();
        Settings_BackUp_Module_Model::deleteTmpBackUpContent();
        Settings_BackUp_Module_Model::deleteFile($sqlFileName . '.db.zip');
        Settings_BackUp_Module_Model::deleteFile($this->fileName . '.zip');
        echo json_encode(array('percentage' => 100));
    }

    public function zipData($source, $destination, $backup, $cron, $backupedDirs = array(), $fileName) {
        global $log;
        $log->info('Create zip');
        $newSubDirs[] = (string) $source;
        if (extension_loaded('zip')) {
            if (file_exists($source)) {
                $zip = new ZipArchive();
                if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
                    if (is_dir($source)) {
                        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

                        foreach ($files as $file) {
                            if ($count == 80) {
                                if ($cron == FALSE) {
                                    $percentage = Settings_BackUp_Module_Model::getPercentage();
                                    exit(json_encode(array('percentage' => $percentage)));
                                }
                            }
                            if (is_dir($file)) {
                                $newSubDirs[] = $file;
                                if ($backup) {
                                    $fileName = str_replace('\\', '/', (string) $file);

                                    if (isset($backupedDirs[$fileName]) && $backupedDirs[$fileName] == 0) {
                                        Settings_BackUp_Module_Model::setDirBackuped($fileName);
                                        $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                                        $count++;
                                    }
                                }
                            } else if (is_file($file)) {
                                if ($backup && strpos((string) $file, $fileName) === FALSE)
                                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                            }
                        }
                        if (!$backup)
                            Settings_BackUp_Module_Model::addBackupDirs($newSubDirs);
                    } else if (is_file($source)) {
                        if ($backup)
                            $zip->addFromString(basename($source), file_get_contents($source));
                    }
                }
                Settings_BackUp_Module_Model::setDirBackuped($source);
                return $zip->close();
            }
        }
        return false;
    }
}