<?php

require_once(__DIR__ . '/../GitHubObject.php');

	

class GitHubFullCommitStats extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'additions' => 'int',
			'deletions' => 'int',
			'total' => 'int',
		));
	}
	
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
	protected $total;

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
	public function getTotal()
	{
		return $this->total;
	}

}

