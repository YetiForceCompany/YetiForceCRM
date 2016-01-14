<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubUser.php');
require_once(__DIR__ . '/GitHubPullCommentLinks.php');
	

class GitHubPullComment extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'url' => 'string',
			'id' => 'int',
			'body' => 'string',
			'path' => 'string',
			'position' => 'int',
			'commit_id' => 'string',
			'user' => 'GitHubUser',
			'created_at' => 'string',
			'updated_at' => 'string',
			'links' => 'GitHubPullCommentLinks',
		));
	}
	
	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $body;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var int
	 */
	protected $position;

	/**
	 * @var string
	 */
	protected $commit_id;

	/**
	 * @var GitHubUser
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $created_at;

	/**
	 * @var string
	 */
	protected $updated_at;

	/**
	 * @var GitHubPullCommentLinks
	 */
	protected $links;

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
	public function getId()
	{
		return $this->id;
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
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * @return int
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 * @return string
	 */
	public function getCommitId()
	{
		return $this->commit_id;
	}

	/**
	 * @return GitHubUser
	 */
	public function getUser()
	{
		return $this->user;
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
	 * @return GitHubPullCommentLinks
	 */
	public function getLinks()
	{
		return $this->links;
	}

}

