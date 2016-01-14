<?php

require_once(__DIR__ . '/GitHubTeam.php');

	

class GitHubFullTeam extends GitHubTeam
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'permission' => 'string',
			'members_count' => 'int',
			'repos_count' => 'int',
		));
	}
	
	/**
	 * @var string
	 */
	protected $permission;

	/**
	 * @var int
	 */
	protected $members_count;

	/**
	 * @var int
	 */
	protected $repos_count;

	/**
	 * @return string
	 */
	public function getPermission()
	{
		return $this->permission;
	}

	/**
	 * @return int
	 */
	public function getMembersCount()
	{
		return $this->members_count;
	}

	/**
	 * @return int
	 */
	public function getReposCount()
	{
		return $this->repos_count;
	}

}

