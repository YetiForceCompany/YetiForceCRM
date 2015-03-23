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
	public $lacalPath = false;
	public $dirid = 0;
	public $size = 0;
	public $mtime = 0;

	/**
	 * Sets up the node, expects a full path name
	 *
	 * @param string $path
	 */
	function __construct($path, $exData) {
		$this->path = $path;
		$this->exData = $exData;
	}

	/**
	 * Returns the name of the node
	 *
	 * @return string
	 */
	function getName() {
		list(, $name) = URLUtil::splitPath($this->path);
		return $name;
	}

	/**
	 * Renames the node
	 *
	 * @param string $name The new name
	 * @return void
	 */
	function setName($name) {
		list($parentPath, ) = URLUtil::splitPath($this->path);
		list(, $newName) = URLUtil::splitPath($name);

		$newPath = $parentPath . '/' . $newName;
		rename($this->path, $newPath);

		$this->path = $newPath;
	}

	/**
	 * Returns the last modification time, as a unix timestamp
	 *
	 * @return int
	 */
	function getLastModified() {
		if (isset($this->mtime)) {
			return $this->mtime;
		}
		$path = $this->exData->lacalStorageDir . $this->lacalPath;
		return filemtime($path);
	}

}
