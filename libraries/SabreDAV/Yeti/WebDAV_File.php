<?php
namespace Yeti;
use Sabre\DAV;

/**
 * File class
 *
 * @copyright Copyright (C) 2007-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class WebDAV_File extends WebDAV_Node implements DAV\IFile {

    /**
     * Updates the data
     *
     * @param resource $data
     * @return void
     */
    public function put($data) {
		
    }

    /**
     * Returns the data
     *
     * @return string
     */
    public function get() {
		$stmt = $this->exData->pdo->prepare('UPDATE vtiger_files SET downloadcount=downloadcount+1 WHERE filesid=?;');
		$stmt->execute([$this->filesid]);
	
		$path = $this->exData->localStorageDir . $this->localPath;
        return fopen($path,'r');
    }

    /**
     * Delete the current file
     *
     * @return void
     */
    public function delete() {
		$path = $this->exData->localStorageDir . $this->localPath;
		$stmt = $this->exData->pdo->prepare('UPDATE vtiger_crmentity SET deleted = ? WHERE crmid = ?;');
		$stmt->execute([1,$this->filesid]);
		//unlink($path);
    }

    /**
     * Returns the size of the node, in bytes
     *
     * @return int
     */
    public function getSize() {
		if (isset($this->size)) {
			return $this->size;
		}
		$path = $this->exData->localStorageDir . $this->localPath;
        return filesize($path);
    }

    /**
     * Returns the ETag for a file
     *
     * An ETag is a unique identifier representing the current version of the file. If the file changes, the ETag MUST change.
     * The ETag is an arbitrary string, but MUST be surrounded by double-quotes.
     *
     * Return null if the ETag can not effectively be determined
     *
     * @return mixed
     */
    public function getETag() {
        return null;
    }

    /**
     * Returns the mime-type for a file
     *
     * If null is returned, we'll assume application/octet-stream
     *
     * @return mixed
     */
    public function getContentType() {
        return null;
    }

	/**
	 * Renames the node
	 *
	 * @param string $name The new name
	 * @return void
	 */
	public function setName($name) {
		list($parentLocalPath, ) = URLUtil::splitPath($this->localPath);
		list($parentPath, ) = URLUtil::splitPath($this->path);
		list(, $newName) = URLUtil::splitPath($name);
		$newPath = $parentLocalPath . '/' . $newName.'/';
		$path = trim($parentPath, 'files') . '/' . $newName.'/';
		$hash = sha1($path);
		
		$log = print_r([$this->exData->pdo, $name, $newPath, $hash, date('Y-m-d H:i:s'), $this->dirid], true);
		file_put_contents('cache/logs/xxebug.log', ' --- '.date('Y-m-d H:i:s').' --- RequestInterface --- '.PHP_EOL.$log, FILE_APPEND);

		$stmt = $this->exData->pdo->prepare('UPDATE vtiger_files_dir SET name=?, path = ?, hash=?, mtime=? WHERE id=?;');
		$stmt->execute([$name, $newPath, $hash, date('Y-m-d H:i:s'), $this->dirid]);
		rename($this->exData->localStorageDir .$this->localPath, $this->exData->localStorageDir .$newPath);
		$this->path = $newPath;
	}
}

