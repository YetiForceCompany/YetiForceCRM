<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubUser.php');
	

class GitHubSimpleRepo extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'id' => 'int',
			'owner' => 'GitHubUser',
			'name' => 'string',
			'full_name' => 'string',
			'description' => 'string',
			'private' => 'boolean',
			'fork' => 'boolean',
			'url' => 'string',
			'html_url' => 'string',
		));
	}
	
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var GitHubUser
	 */
	protected $owner;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $full_name;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var boolean
	 */
	protected $private;

	/**
	 * @var boolean
	 */
	protected $fork;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $html_url;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return GitHubUser
	 */
	public function getOwner()
	{
		return $this->owner;
	}

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
	public function getFullName()
	{
		return $this->full_name;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @return boolean
	 */
	public function getPrivate()
	{
		return $this->private;
	}

	/**
	 * @return boolean
	 */
	public function getFork()
	{
		return $this->fork;
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
	public function getHtmlUrl()
	{
		return $this->html_url;
	}

}

