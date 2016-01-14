<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubUser.php');
	

class GitHubGist extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'url' => 'string',
			'id' => 'string',
			'description' => 'string',
			'public' => 'boolean',
			'user' => 'GitHubUser',
			'comments' => 'int',
			'comments_url' => 'string',
			'html_url' => 'string',
			'git_pull_url' => 'string',
			'git_push_url' => 'string',
			'created_at' => 'string',
		));
	}
	
	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var boolean
	 */
	protected $public;

	/**
	 * @var GitHubUser
	 */
	protected $user;

	/**
	 * @var int
	 */
	protected $comments;

	/**
	 * @var string
	 */
	protected $comments_url;

	/**
	 * @var string
	 */
	protected $html_url;

	/**
	 * @var string
	 */
	protected $git_pull_url;

	/**
	 * @var string
	 */
	protected $git_push_url;

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
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
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
	public function getPublic()
	{
		return $this->public;
	}

	/**
	 * @return GitHubUser
	 */
	public function getUser()
	{
		return $this->user;
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
	public function getCommentsUrl()
	{
		return $this->comments_url;
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
	public function getGitPullUrl()
	{
		return $this->git_pull_url;
	}

	/**
	 * @return string
	 */
	public function getGitPushUrl()
	{
		return $this->git_push_url;
	}

	/**
	 * @return string
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
	}

}

