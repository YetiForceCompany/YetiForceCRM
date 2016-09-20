<?php

namespace Yeti;

use Sabre\DAV,
	Sabre\HTTP\URLUtil;

/**
 * Base node-class
 *
 * The node class implements the method used by both the File and the Directory classes
 *
 * @copyright Copyright (C) 2007-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
abstract class WebDAV_Node implements DAV\INode {

	/**
	 * The path to the current node
	 *
	 * @var string
	 */
	public $path;
	public $localPath = false;
	public $dirid = 0;
	public $size = 0;
	public $mtime = 0;

	/**
	 * Sets up the node, expects a full path name
	 *
	 * @param string $path
	 */
	public function __construct($path, $exData) {
		$this->path = $path;
		$this->exData = $exData;
	}

	/**
	 * Returns the name of the node
	 *
	 * @return string
	 */
	public function getName() {
		list(, $name) = URLUtil::splitPath($this->path);
		return $name;
	}

	/**
	 * Returns the last modification time, as a unix timestamp
	 *
	 * @return int
	 */
	public function getLastModified() {
		if (isset($this->mtime)) {
			return $this->mtime;
		}
		$path = $this->exData->localStorageDir . $this->localPath;
		return filemtime($path);
	}

	
	public function getCurrentUser() {
		$digest = $_SERVER['PHP_AUTH_DIGEST'];
		$needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
		$data = array();

		preg_match_all('@(\w+)=(?:(?:")([^"]+)"|([^\s,$]+))@', $digest, $matches, PREG_SET_ORDER);

		foreach ($matches as $m) {
			$data[$m[1]] = $m[2] ? $m[2] : $m[3];
			unset($needed_parts[$m[1]]);
		}
		$user = $needed_parts ? false : $data;
		$stmt = $this->exData->pdo->prepare('SELECT * FROM dav_users WHERE username = ?;');
		$stmt->execute([$user['username']]);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		if($row){
			$this->exData->username = $user['username'];
			$this->exData->userId = $row['id'];
			$this->exData->crmUserId = $row['userid'];
			return $row;
		}
	}
}
