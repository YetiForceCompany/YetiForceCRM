<?php
namespace Yeti;
use Sabre\DAV;

/**
 * Directory class
 *
 * @copyright Copyright (C) 2007-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class WebDAV_Directory extends WebDAV_Node implements DAV\ICollection, DAV\IQuota {
	/**
	 * Creates a new file in the directory
	 *
	 * Data will either be supplied as a stream resource, or in certain cases
	 * as a string. Keep in mind that you may have to support either.
	 *
	 * After successful creation of the file, you may choose to return the ETag
	 * of the new file here.
	 *
	 * The returned ETag must be surrounded by double-quotes (The quotes should
	 * be part of the actual string).
	 *
	 * If you cannot accurately determine the ETag, you should not return it.
	 * If you don't store the file exactly as-is (you're transforming it
	 * somehow) you should also not return an ETag.
	 *
	 * This means that if a subsequent GET to this new file does not exactly
	 * return the same contents of what was submitted here, you are strongly
	 * recommended to omit the ETag.
	 *
	 * @param string $name Name of the file
	 * @param resource|string $data Initial payload
	 * @return null|string
	 */
	function createFile($name, $data = null) {
		include_once 'include/main/WebUI.php';
		GLOBAL $log,$adb,$current_user;
		$adb = \PearDatabase::getInstance();
		$log = \LoggerManager::getLogger('DavToCRM');
		$user = new \Users();
		$current_user = $user->retrieveCurrentUserInfoFromFile( 1 );
		
		$path = trim($this->path, 'files') .'/'.$name;
		$lacalPath = $this->lacalPath . $name;
		$hash = sha1($path);
		$pathParts = pathinfo($path);
		file_put_contents($this->exData->lacalStorageDir . $lacalPath, $data);
		
		$rekord = \Vtiger_Record_Model::getCleanInstance( 'Files' );
		$rekord->set( 'assigned_user_id', 1 );
		$rekord->set( 'title', $pathParts['filename'] );
		$rekord->set( 'name', $pathParts['filename'] );
		$rekord->set( 'path', $lacalPath );
		$rekord->save();
		$id = $rekord->getId();
		
		$stmt = $this->exData->pdo->prepare('UPDATE vtiger_files SET dirid=?,extension=?,size=?,hash=?,ctime=? WHERE filesid=?;');
		$stmt->execute([$this->dirid, $pathParts['extension'], filesize ( $this->exData->lacalStorageDir . $lacalPath ), $hash, date('Y-m-d H:i:s'), $id]);
	}

	/**
	 * Creates a new subdirectory
	 *
	 * @param string $name
	 * @return void
	 */
	function createDirectory($name) {
		$path = trim($this->path, 'files') . '/' . $name . '/';
		$dirHash = sha1($path);
		$newPath = $this->lacalPath . $name. '/';
		$parent_dirid = $this->dirid;
		mkdir($this->exData->lacalStorageDir . $newPath);
		
		$stmt = $this->exData->pdo->prepare('INSERT INTO vtiger_files_dir (name,path,parent_dirid,hash,mtime) VALUES (?,?,?,?,NOW());');
		$stmt->execute([$name, $newPath, $parent_dirid, $dirHash]);
	}

	function getRootChild() {
		$path = '/';
		$dirHash = sha1($path);
		$stmt = $this->exData->pdo->prepare('SELECT id, path, size, UNIX_TIMESTAMP(`mtime`) AS mtime FROM vtiger_files_dir WHERE hash = ?;');
		$stmt->execute([$dirHash]);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		
		$this->mtime = $row['mtime'];
		$this->size = $row['size'];
		$this->lacalPath = $row['path'];
		$this->dirid = $row['id'];
	}
	/**
	 * Returns a specific child node, referenced by its name
	 *
	 * This method must throw DAV\Exception\NotFound if the node does not
	 * exist.
	 *
	 * @param string $name
	 * @throws DAV\Exception\NotFound
	 * @return DAV\INode
	 */
	function getChild($file) {
		$path = trim($this->path, 'files') . '/' . $file . '/';
		$hash = sha1($path);
		$stmt = $this->exData->pdo->prepare('SELECT id, path, size, UNIX_TIMESTAMP(`mtime`) AS mtime FROM vtiger_files_dir WHERE hash = ?;');
		$stmt->execute([$hash]);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		if ($row){
			$directory = new WebDAV_Directory($this->path . '/' . $file, $this->exData);
			$directory->mtime = $row['mtime'];
			$directory->size = $row['size'];
			$directory->lacalPath = $row['path'];
			$directory->dirid = $row['id'];
			return $directory;
		}
		$path = trim($this->path, 'files') . '/' . $file;
		$hash = sha1($path);
		$stmt = $this->exData->pdo->prepare('SELECT filesid, path, size, UNIX_TIMESTAMP(`mtime`) AS mtime FROM vtiger_files WHERE hash = ?;');
		$stmt->execute([$hash]);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		if ($row){
			$directory = new WebDAV_File($this->path . '/' . $file, $this->exData);
			$directory->size = $row['size'];
			$directory->lacalPath = $row['path'];
			$directory->filesid = $row['filesid'];
			return $directory;
		}
		throw new DAV\Exception\NotFound('File with name ' . $file . ' could not be located');
	}

	/**
	 * Returns an array with all the child nodes
	 *
	 * @return DAV\INode[]
	 */
	function getChildren() {
		$path = trim($this->path, 'files') . '/';
		$dirHash = sha1($path);
		$nodes = [];

		$stmt = $this->exData->pdo->prepare('SELECT * FROM vtiger_files_dir WHERE parent_dirid = ?;');
		$stmt->execute([$this->dirid]);
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$path = $this->path . '/' . $row['name'];
			$directory = new WebDAV_Directory($path, $this->exData);
			$directory->mtime = $row['mtime'];
			$directory->size = $row['size'];
			$directory->lacalPath = $row['path'];
			$nodes[] = $directory;
		}
		$stmt = $this->exData->pdo->prepare('SELECT * FROM vtiger_files WHERE dirid = ?;');
		$stmt->execute([$this->dirid]);
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$path = $this->path . '/' . $row['name'] . '.' . $row['extension'];
			$file = new WebDAV_File($path, $this->exData);
			$file->mtime = $row['mtime'];
			$file->size = $row['size'];
			$file->lacalPath = $row['path'];
			$nodes[] = $file;
		}
		//var_dump($path);exit;
		// foreach(scandir($this->path) as $node) if($node!='.' && $node!='..') $nodes[] = $this->getChild($node);
		return $nodes;
	}

	/**
	 * Checks if a child exists.
	 *
	 * @param string $name
	 * @return bool
	 */
	function childExists($name) {
		$path = $this->path . '/' . $name;
		return file_exists($path);
	}

	/**
	 * Deletes all files in this directory, and then itself
	 *
	 * @return void
	 */
	function delete() {
		foreach ($this->getChildren() as $child)
			$child->delete();
		rmdir($this->path);
	}

	/**
	 * Returns available diskspace information
	 *
	 * @return array
	 */
	function getQuotaInfo() {
		$path = $this->exData->lacalStorageDir . $this->lacalPath;
		return [
			disk_total_space($path) - disk_free_space($path),
			disk_free_space($path)
		];
	}

}
