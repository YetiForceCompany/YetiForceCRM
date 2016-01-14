<?php

require_once(__DIR__ . '/GitHubFullUser.php');

	

class GitHubPrivateUser extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'total_private_repos' => 'int',
			'owned_private_repos' => 'int',
			'private_gists' => 'int',
			'disk_usage' => 'int',
			'collaborators' => 'int',
		));
	}
	
	/**
	 * @var int
	 */
	protected $total_private_repos;

	/**
	 * @var int
	 */
	protected $owned_private_repos;

	/**
	 * @var int
	 */
	protected $private_gists;

	/**
	 * @var int
	 */
	protected $disk_usage;

	/**
	 * @var int
	 */
	protected $collaborators;

	/**
	 * @return int
	 */
	public function getTotalPrivateRepos()
	{
		return $this->total_private_repos;
	}

	/**
	 * @return int
	 */
	public function getOwnedPrivateRepos()
	{
		return $this->owned_private_repos;
	}

	/**
	 * @return int
	 */
	public function getPrivateGists()
	{
		return $this->private_gists;
	}

	/**
	 * @return int
	 */
	public function getDiskUsage()
	{
		return $this->disk_usage;
	}

	/**
	 * @return int
	 */
	public function getCollaborators()
	{
		return $this->collaborators;
	}

}

