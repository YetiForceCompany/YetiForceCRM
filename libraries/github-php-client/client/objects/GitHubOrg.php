<?php

require_once(__DIR__ . '/../GitHubObject.php');

	

class GitHubOrg extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'login' => 'string',
			'id' => 'int',
			'url' => 'string',
			'avatar_url' => 'string',
		));
	}
	
	/**
	 * @var string
	 */
	protected $login;

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $avatar_url;

	/**
	 * @return string
	 */
	public function getLogin()
	{
		return $this->login;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @return string
	 */
	public function getAvatarUrl()
	{
		return $this->avatar_url;
	}

}

