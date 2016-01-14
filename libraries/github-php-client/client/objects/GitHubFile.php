<?php

require_once(__DIR__ . '/../GitHubObject.php');

	

class GitHubFile extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'sha' => 'string',
			'filename' => 'string',
			'status' => 'string',
			'additions' => 'int',
			'deletions' => 'int',
			'changes' => 'int',
			'blob_url' => 'string',
			'raw_url' => 'string',
			'patch' => 'string',
		));
	}
	
	/**
	 * @var string
	 */
	protected $sha;

	/**
	 * @var string
	 */
	protected $filename;

	/**
	 * @var string
	 */
	protected $status;

	/**
	 * @var int
	 */
	protected $additions;

	/**
	 * @var int
	 */
	protected $deletions;

	/**
	 * @var int
	 */
	protected $changes;

	/**
	 * @var string
	 */
	protected $blob_url;

	/**
	 * @var string
	 */
	protected $raw_url;

	/**
	 * @var string
	 */
	protected $patch;

	/**
	 * @return string
	 */
	public function getSha()
	{
		return $this->sha;
	}

	/**
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * @return string
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @return int
	 */
	public function getAdditions()
	{
		return $this->additions;
	}

	/**
	 * @return int
	 */
	public function getDeletions()
	{
		return $this->deletions;
	}

	/**
	 * @return int
	 */
	public function getChanges()
	{
		return $this->changes;
	}

	/**
	 * @return string
	 */
	public function getBlobUrl()
	{
		return $this->blob_url;
	}

	/**
	 * @return string
	 */
	public function getRawUrl()
	{
		return $this->raw_url;
	}

	/**
	 * @return string
	 */
	public function getPatch()
	{
		return $this->patch;
	}

}

