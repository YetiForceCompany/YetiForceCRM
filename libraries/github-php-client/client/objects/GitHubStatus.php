<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubUser.php');
	

class GitHubStatus extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'created_at' => 'string',
			'updated_at' => 'string',
			'state' => 'string',
			'target_url' => 'string',
			'description' => 'string',
			'id' => 'int',
			'url' => 'string',
			'creator' => 'GitHubUser',
		));
	}
	
	/**
	 * @var string
	 */
	protected $created_at;

	/**
	 * @var string
	 */
	protected $updated_at;

	/**
	 * @var string
	 */
	protected $state;

	/**
	 * @var string
	 */
	protected $target_url;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var GitHubUser
	 */
	protected $creator;

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
	public function getUpdatedAt()
	{
		return $this->updated_at;
	}

	/**
	 * @return string
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @return string
	 */
	public function getTargetUrl()
	{
		return $this->target_url;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
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
	 * @return GitHubUser
	 */
	public function getCreator()
	{
		return $this->creator;
	}

}

