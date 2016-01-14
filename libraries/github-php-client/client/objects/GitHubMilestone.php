<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubUser.php');
	

class GitHubMilestone extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'url' => 'string',
			'number' => 'int',
			'state' => 'string',
			'title' => 'string',
			'description' => 'string',
			'creator' => 'GitHubUser',
			'open_issues' => 'int',
			'closed_issues' => 'int',
			'created_at' => 'string',
			'due_on' => 'string',
		));
	}
	
	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var int
	 */
	protected $number;

	/**
	 * @var string
	 */
	protected $state;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var GitHubUser
	 */
	protected $creator;

	/**
	 * @var int
	 */
	protected $open_issues;

	/**
	 * @var int
	 */
	protected $closed_issues;

	/**
	 * @var string
	 */
	protected $created_at;

	/**
	 * @var string
	 */
	protected $due_on;

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @return int
	 */
	public function getNumber()
	{
		return $this->number;
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
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @return GitHubUser
	 */
	public function getCreator()
	{
		return $this->creator;
	}

	/**
	 * @return int
	 */
	public function getOpenIssues()
	{
		return $this->open_issues;
	}

	/**
	 * @return int
	 */
	public function getClosedIssues()
	{
		return $this->closed_issues;
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
	public function getDueOn()
	{
		return $this->due_on;
	}

}

