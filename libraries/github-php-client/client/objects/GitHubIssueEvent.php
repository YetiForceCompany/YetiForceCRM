<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubUser.php');
	

class GitHubIssueEvent extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'url' => 'string',
			'actor' => 'GitHubUser',
			'event' => 'string',
			'commit_id' => 'string',
			'created_at' => 'string',
		));
	}
	
	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var GitHubUser
	 */
	protected $actor;

	/**
	 * @var string
	 */
	protected $event;

	/**
	 * @var string
	 */
	protected $commit_id;

	/**
	 * @var string
	 */
	protected $created_at;

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
	public function getActor()
	{
		return $this->actor;
	}

	/**
	 * @return string
	 */
	public function getEvent()
	{
		return $this->event;
	}

	/**
	 * @return string
	 */
	public function getCommitId()
	{
		return $this->commit_id;
	}

	/**
	 * @return string
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
	}

}

