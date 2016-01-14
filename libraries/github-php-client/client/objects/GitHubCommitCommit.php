<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubCommitCommitAuthor.php');
require_once(__DIR__ . '/GitHubCommitCommitCommitter.php');
require_once(__DIR__ . '/GitHubCommitCommitTree.php');
	

class GitHubCommitCommit extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'url' => 'string',
			'message' => 'string',
			'author' => 'GitHubCommitCommitAuthor',
			'committer' => 'GitHubCommitCommitCommitter',
			'tree' => 'GitHubCommitCommitTree',
		));
	}
	
	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $message;

	/**
	 * @var GitHubCommitCommitAuthor
	 */
	protected $author;

	/**
	 * @var GitHubCommitCommitCommitter
	 */
	protected $committer;

	/**
	 * @var GitHubCommitCommitTree
	 */
	protected $tree;

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
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @return GitHubCommitCommitAuthor
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @return GitHubCommitCommitCommitter
	 */
	public function getCommitter()
	{
		return $this->committer;
	}

	/**
	 * @return GitHubCommitCommitTree
	 */
	public function getTree()
	{
		return $this->tree;
	}

}

