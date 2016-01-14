<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubUser.php');
require_once(__DIR__ . '/GitHubPullLinks.php');
require_once(__DIR__ . '/GitHubPullBranch.php');


class GitHubPull extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'id' => 'int',
			'url' => 'string',
			'html_url' => 'string',
			'diff_url' => 'string',
			'patch_url' => 'string',
			'issue_url' => 'string',
			'commits_url' => 'string',
			'review_comments_url' => 'string',
			'review_comment_url' => 'string',
			'comments_url' => 'string',
			'statuses_url' => 'string',
			'number' => 'int',
			'state' => 'string',
			'title' => 'string',
			'body' => 'string',
			'created_at' => 'string',
			'updated_at' => 'string',
			'closed_at' => 'string',
			'merged_at' => 'string',
			'user' => 'GitHubUser',
			'_links' => 'GitHubPullLinks',
			'head' => 'GitHubPullBranch',
			'base' => 'GitHubPullBranch',
			'mergeable' => 'bool',
		));
	}

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $html_url;

	/**
	 * @var string
	 */
	protected $diff_url;

	/**
	 * @var string
	 */
	protected $patch_url;

	/**
	 * @var string
	 */
	protected $issue_url;

	/**
	 * @var string
	 */
	protected $commits_url;

	/**
	 * @var string
	 */
	protected $review_comments_url;

	/**
	 * @var string
	 */
	protected $review_comment_url;

	/**
	 * @var string
	 */
	protected $comments_url;

	/**
	 * @var string
	 */
	protected $statuses_url;

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
	protected $closed_at;

	/**
	 * @var string
	 */
	protected $merged_at;

	/**
	 * @var GitHubUser
	 */
	protected $user;

	/**
	 * @var GitHubPullLinks
	 */
	protected $_links;

	/**
	 * @var GitHubPullBranch
	 */
	protected $head;

	/**
	 * @var GitHubPullBranch
	 */
	protected $base;

	/**
	 * @var bool
	 */
	protected $mergeable;

	/**
	 * @return string
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
	public function getHtmlUrl()
	{
		return $this->html_url;
	}

	/**
	 * @return string
	 */
	public function getDiffUrl()
	{
		return $this->diff_url;
	}

	/**
	 * @return string
	 */
	public function getPatchUrl()
	{
		return $this->patch_url;
	}

	/**
	 * @return string
	 */
	public function getIssueUrl()
	{
		return $this->issue_url;
	}

	/**
	 * @return string
	 */
	public function getCommitsUrl()
	{
		return $this->commits_url;
	}

	/**
	 * @return string
	 */
	public function getReviewCommentsUrl()
	{
		return $this->review_comments_url;
	}

	/**
	 * @return string
	 */
	public function getReviewCommentUrl()
	{
		return $this->review_comment_url;
	}

	/**
	 * @return string
	 */
	public function getCommentsUrl()
	{
		return $this->comments_url;
	}

	/**
	 * @return string
	 */
	public function getStatusesUrl()
	{
		return $this->statuses_url;
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
	public function getClosedAt()
	{
		return $this->closed_at;
	}

	/**
	 * @return string
	 */
	public function getMergedAt()
	{
		return $this->merged_at;
	}

	/**
	 * @return GitHubUser
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return GitHubPullLinks
	 */
	public function getLinks()
	{
		return $this->_links;
	}

	/**
	 * @return GitHubPullBranch
	 */
	public function getHead()
	{
		return $this->head;
	}

	/**
	 * @return GitHubPullBranch
	 */
	public function getBase()
	{
		return $this->base;
	}

	/**
	 * @return bool
	 */
	public function isMergeable()
	{
		return $this->mergeable;
	}
}

