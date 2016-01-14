<?php

require_once(__DIR__ . '/../GitHubObject.php');

	

class GitHubFullCommitFiles extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'filename' => 'string',
			'additions' => 'int',
			'deletions' => 'int',
			'changes' => 'int',
			'status' => 'string',
			'raw_url' => 'string',
			'blob_url' => 'string',
			'patch' => 'string',
		));
	}
	
	/**
	 * @var string
	 */
	protected $filename;

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
	protected $status;

	/**
	 * @var string
	 */
	protected $raw_url;

	/**
	 * @var string
	 */
	protected $blob_url;

	/**
	 * @var string
	 */
	protected $patch;

	/**
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
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
	public function getStatus()
	{
		return $this->status;
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
	public function getBlobUrl()
	{
		return $this->blob_url;
	}

	/**
	 * @return string
	 */
	public function getPatch()
	{
		return $this->patch;
	}

}

