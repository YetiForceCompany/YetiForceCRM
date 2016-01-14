<?php

require_once(__DIR__ . '/GitHubOrg.php');

	

class GitHubFullOrg extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'name' => 'string',
			'company' => 'string',
			'blog' => 'string',
			'location' => 'string',
			'email' => 'string',
			'public_repos' => 'int',
			'public_gists' => 'int',
			'followers' => 'int',
			'following' => 'int',
			'html_url' => 'string',
			'created_at' => 'string',
			'type' => 'string',
		));
	}
	
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $company;

	/**
	 * @var string
	 */
	protected $blog;

	/**
	 * @var string
	 */
	protected $location;

	/**
	 * @var string
	 */
	protected $email;

	/**
	 * @var int
	 */
	protected $public_repos;

	/**
	 * @var int
	 */
	protected $public_gists;

	/**
	 * @var int
	 */
	protected $followers;

	/**
	 * @var int
	 */
	protected $following;

	/**
	 * @var string
	 */
	protected $html_url;

	/**
	 * @var string
	 */
	protected $created_at;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getCompany()
	{
		return $this->company;
	}

	/**
	 * @return string
	 */
	public function getBlog()
	{
		return $this->blog;
	}

	/**
	 * @return string
	 */
	public function getLocation()
	{
		return $this->location;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @return int
	 */
	public function getPublicRepos()
	{
		return $this->public_repos;
	}

	/**
	 * @return int
	 */
	public function getPublicGists()
	{
		return $this->public_gists;
	}

	/**
	 * @return int
	 */
	public function getFollowers()
	{
		return $this->followers;
	}

	/**
	 * @return int
	 */
	public function getFollowing()
	{
		return $this->following;
	}

	/**
	 * @return string
	 */
	public function getHtmlUrl()
	{
		return $this->html_url;
	}

	/**
	 * @return string
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

}

