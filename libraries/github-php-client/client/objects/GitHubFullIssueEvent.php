<?php

require_once(__DIR__ . '/GitHubIssueEvent.php');
require_once(__DIR__ . '/GitHubUser.php');
require_once(__DIR__ . '/GitHubMilestone.php');
require_once(__DIR__ . '/GitHubIssuePullRequest.php');
	

class GitHubFullIssueEvent extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'url' => 'string',
			'html_url' => 'string',
			'number' => 'int',
			'state' => 'string',
			'title' => 'string',
			'body' => 'string',
			'user' => 'GitHubUser',
			'assignee' => 'GitHubUser',
			'milestone' => 'GitHubMilestone',
			'comments' => 'int',
			'closed_at' => 'string',
			'created_at' => 'string',
			'updated_at' => 'string',
			'pull_request' => 'GitHubIssuePullRequest',
		));
	}
	
	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $html_url;

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
	protected $body;

	/**
	 * @var GitHubUser
	 */
	protected $user;

	/**
	 * @var GitHubUser
	 */
	protected $assignee;

	/**
	 * @var GitHubMilestone
	 */
	protected $milestone;

	/**
	 * @var int
	 */
	protected $comments;

	/**
	 * @var string
	 */
	protected $closed_at;

	/**
	 * @var string
	 */
	protected $created_at;

	/**
	 * @var string
	 */
	protected $updated_at;

	/**
	 * @var GitHubIssuePullRequest
	 */
	protected $pull_request;

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
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * @return GitHubUser
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return GitHubUser
	 */
	public function getAssignee()
	{
		return $this->assignee;
	}

	/**
	 * @return GitHubMilestone
	 */
	public function getMilestone()
	{
		return $this->milestone;
	}

	/**
	 * @return int
	 */
	public function getComments()
	{
		return $this->comments;
	}

	/**
	 * @return string
	 */
	public function getClosedAt()
	{
		return $this->closed_at;
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
	public function getUpdatedAt()
	{
		return $this->updated_at;
	}

	/**
	 * @return GitHubIssuePullRequest
	 */
	public function getPullRequest()
	{
		return $this->pull_request;
	}

}

