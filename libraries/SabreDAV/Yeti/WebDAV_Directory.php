<?php namespace Yeti;

use Sabre\DAV;

/**
 * Directory class
 *
 * @copyright Copyright (C) 2007-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class WebDAV_Directory extends WebDAV_Node implements DAV\ICollection, DAV\IQuota, DAV\IMoveTarget
{

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
	public function createFile($name, $data = null)
	{
		if ($this->dirid == 0)
			throw new DAV\Exception\Forbidden('Permission denied to create file: ' . $name);
		include_once 'include/main/WebUI.php';
		$adb = \PearDatabase::getInstance();
		$user = new \Users();
		$current_user = $user->retrieveCurrentUserInfoFromFile($this->exData->crmUserId);

		$path = trim($this->path, 'files') . '/' . $name;
		$hash = sha1($path);
		$pathParts = pathinfo($path);
		$localPath = $this->localPath . $name;

		$stmt = $this->exData->pdo->prepare('SELECT crmid, smownerid, deleted FROM vtiger_files INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_files.filesid WHERE vtiger_files.hash = ?;');
		$stmt->execute([$hash]);
		$rows = $stmt->fetch(\PDO::FETCH_ASSOC);
		if ($rows != false && ($rows['smownerid'] != $this->exData->crmUserId || $rows['deleted'] == 1)) {
			throw new DAV\Exception\Conflict('File with name ' . $file . ' could not be located');
		}
		file_put_contents($this->exData->localStorageDir . $localPath, $data);

		if ($rows) {
			$record = \Vtiger_Record_Model::getInstanceById($rows['crmid'], 'Files');
			$record->set('mode', 'edit');
		} else {
			$record = \Vtiger_Record_Model::getCleanInstance('Files');
			$record->set('assigned_user_id', $this->exData->crmUserId);
		}
		$record->set('title', $pathParts['filename']);
		$record->set('name', $pathParts['filename']);
		$record->set('path', $localPath);
		$record->save();
		$id = $record->getId();

		$stmt = $this->exData->pdo->prepare('UPDATE vtiger_files SET dirid=?,extension=?,size=?,hash=?,ctime=? WHERE filesid=?;');
		$stmt->execute([$this->dirid, $pathParts['extension'], filesize($this->exData->localStorageDir . $localPath), $hash, date('Y-m-d H:i:s'), $id]);
	}

	/**
	 * Creates a new subdirectory
	 *
	 * @param string $name
	 * @return void
	 */
	public function createDirectory($name)
	{
		if ($this->dirid == 0)
			throw new DAV\Exception\Forbidden('Permission denied to create directory: ' . $name);
		$path = trim($this->path, 'files') . '/' . $name . '/';
		$dirHash = sha1($path);
		$newPath = $this->localPath . $name . '/';
		$parent_dirid = $this->dirid;
		mkdir($this->exData->localStorageDir . $newPath);

		$stmt = $this->exData->pdo->prepare('INSERT INTO vtiger_files_dir (name,path,parent_dirid,hash,mtime,userid) VALUES (?,?,?,?, NOW(),?);');
		$stmt->execute([$name, $newPath, $parent_dirid, $dirHash, $this->exData->crmUserId]);
	}

	public function getRootChild()
	{
		$path = '/';
		$dirHash = sha1($path);
		$stmt = $this->exData->pdo->prepare('SELECT id, path, size, UNIX_TIMESTAMP(`mtime`) AS mtime FROM vtiger_files_dir WHERE hash = ? && userid IN (?,?);');
		$stmt->execute([$dirHash, 0, $this->exData->crmUserId]);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);

		$this->mtime = $row['mtime'];
		$this->size = $row['size'];
		$this->localPath = $row['path'];
		$this->dirid = $row['id'];
		$currentUser = $this->getCurrentUser();
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
	public function getChild($file)
	{
		$path = trim($this->path, 'files') . '/' . $file . '/';
		$hash = sha1($path);
		$stmt = $this->exData->pdo->prepare('SELECT id, path, size, UNIX_TIMESTAMP(`mtime`) AS mtime FROM vtiger_files_dir WHERE hash = ? && userid IN (?,?);');
		$stmt->execute([$hash, 0, $this->exData->crmUserId]);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		if ($row) {
			$directory = new WebDAV_Directory($this->path . '/' . $file, $this->exData);
			$directory->mtime = $row['mtime'];
			$directory->size = $row['size'];
			$directory->localPath = $row['path'];
			$directory->dirid = $row['id'];
			return $directory;
		}
		$path = trim($this->path, 'files') . '/' . $file;
		$hash = sha1($path);
		$stmt = $this->exData->pdo->prepare('SELECT filesid, path, size, UNIX_TIMESTAMP(`mtime`) AS mtime FROM vtiger_files INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_files.filesid WHERE vtiger_files.hash = ? && vtiger_crmentity.deleted = ? && vtiger_crmentity.smownerid = ?;');
		$stmt->execute([$hash, 0, $this->exData->crmUserId]);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		if ($row) {
			$directory = new WebDAV_File($this->path . '/' . $file, $this->exData);
			$directory->size = $row['size'];
			$directory->localPath = $row['path'];
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
	public function getChildren()
	{
		$path = trim($this->path, 'files') . '/';
		$dirHash = sha1($path);
		$nodes = [];

		$stmt = $this->exData->pdo->prepare('SELECT * FROM vtiger_files_dir WHERE parent_dirid = ? && userid IN (?,?);');
		$stmt->execute([$this->dirid, 0, $this->exData->crmUserId]);
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$path = $this->path . '/' . $row['name'];
			$directory = new WebDAV_Directory($path, $this->exData);
			$directory->mtime = $row['mtime'];
			$directory->size = $row['size'];
			$directory->localPath = $row['path'];
			$nodes[] = $directory;
		}
		$stmt = $this->exData->pdo->prepare('SELECT vtiger_files.* FROM vtiger_files INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_files.filesid WHERE vtiger_files.dirid = ? && vtiger_crmentity.deleted = ? && vtiger_crmentity.smownerid = ?;');
		$stmt->execute([$this->dirid, 0, $this->exData->crmUserId]);
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$path = $this->path . '/' . $row['name'] . '.' . $row['extension'];
			$file = new WebDAV_File($path, $this->exData);
			$file->mtime = $row['mtime'];
			$file->size = $row['size'];
			$file->localPath = $row['path'];
			$nodes[] = $file;
		}
		return $nodes;
	}

	/**
	 * Checks if a child exists.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function childExists($name)
	{
		$path = $this->path . '/' . $name;
		return file_exists($path);
	}

	/**
	 * Deletes all files in this directory, and then itself
	 *
	 * @return void
	 */
	public function delete()
	{
		foreach ($this->getChildren() as $child)
			$child->delete();
	}

	/**
	 * Returns available diskspace information
	 *
	 * @return array
	 */
	public function getQuotaInfo()
	{
		$path = $this->exData->localStorageDir . $this->localPath;
		return [
			disk_total_space($path) - disk_free_space($path),
			disk_free_space($path)
		];
	}

	/**
	 * Renames the node
	 *
	 * @param string $name The new name
	 * @return void
	 */
	public function setName($name)
	{
		list($parentLocalPath, ) = URLUtil::splitPath($this->localPath);
		list($parentPath, ) = URLUtil::splitPath($this->path);
		list(, $newName) = URLUtil::splitPath($name);
		$newPath = $parentLocalPath . '/' . $newName . '/';
		$path = trim($parentPath, 'files') . '/' . $newName . '/';
		$hash = sha1($path);

		$log = print_r([$this->exData->pdo, $name, $newPath, $hash, date('Y-m-d H:i:s'), $this->dirid], true);
		file_put_contents('cache/logs/xxebug.log', ' --- ' . date('Y-m-d H:i:s') . ' --- RequestInterface --- ' . PHP_EOL . $log, FILE_APPEND);

		$stmt = $this->exData->pdo->prepare('UPDATE vtiger_files_dir SET name=?, path = ?, hash=?, mtime=? WHERE id=?;');
		$stmt->execute([$name, $newPath, $hash, date('Y-m-d H:i:s'), $this->dirid]);
		rename($this->exData->localStorageDir . $this->localPath, $this->exData->localStorageDir . $newPath);
		$this->path = $newPath;
	}

	/**
	 * Moves a node into this collection.
	 *
	 * It is up to the implementors to:
	 *   1. Create the new resource.
	 *   2. Remove the old resource.
	 *   3. Transfer any properties or other data.
	 *
	 * Generally you should make very sure that your collection can easily move
	 * the move.
	 *
	 * If you don't, just return false, which will trigger sabre/dav to handle
	 * the move itself. If you return true from this function, the assumption
	 * is that the move was successful.
	 *
	 * @param string $targetName New local file/collection name.
	 * @param string $sourcePath Full path to source node
	 * @param DAV\INode $sourceNode Source node itself
	 * @return bool
	 */
	public function moveInto($targetName, $sourcePath, DAV\INode $sourceNode)
	{
		$log = print_r([$targetName, $sourcePath, $sourceNode, $this], true);
		file_put_contents('cache/logs/xxebug.log', ' --- ' . date('Y-m-d H:i:s') . ' --- RequestInterface --- ' . PHP_EOL . $log, FILE_APPEND);

		if (!$sourceNode instanceof WebDAV_File) {
			return false;
		}
		$from = $sourceNode->exData->localStorageDir . $sourceNode->localPath;
		$to = $this->exData->localStorageDir . $this->localPath . '/' . $targetName;
		// PHP allows us to access protected properties from other objects, as
		// long as they are defined in a class that has a shared inheritence
		// with the current class.
		rename($from, $to);

		$path = trim($this->path, 'files') . '/' . $targetName;
		$hash = sha1($path);
		$stmt = $this->exData->pdo->prepare('UPDATE vtiger_files SET dirid=?, path = ?, hash=? WHERE filesid=?;');
		$stmt->execute([$this->dirid, $this->localPath . $targetName, $hash, $sourceNode->filesid]);
		$stmt = $this->exData->pdo->prepare('UPDATE vtiger_crmentity SET modifiedtime=?, modifiedby=? WHERE crmid=?;');
		$stmt->execute([date('Y-m-d H:i:s'), $this->exData->crmUserId, $sourceNode->filesid]);
		return true;
	}
}
