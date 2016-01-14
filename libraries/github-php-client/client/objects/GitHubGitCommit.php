<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubGitCommitAuthor.php');
require_once(__DIR__ . '/GitHubGitCommitCommitter.php');
require_once(__DIR__ . '/GitHubGitCommitTree.php');
	

class GitHubGitCommit extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'sha' => 'string',
			'url' => 'string',
			'message' => 'string',
			'author' => 'GitHubGitCommitAuthor',
			'committer' => 'GitHubGitCommitCommitter',
			'tree' => 'GitHubGitCommitTree',
		));
	}
	
	/**
	 * @var string
	 */
	protected $sha;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $message;

	/**
	 * @var GitHubGitCommitAuthor
	 */
	protected $author;

	/**
	 * @var GitHubGitCommitCommitter
	 */
	protected $committer;

	/**
	 * @var GitHubGitCommitTree
	 */
	protected $tree;

	/**
	 * @return string
	 */
	public function getSha()
	{
		return $this->sha;
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
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @return GitHubGitCommitAuthor
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @return GitHubGitCommitCommitter
	 */
	public function getCommitter()
	{
		return $this->committer;
	}

	/**
	 * @return GitHubGitCommitTree
	 */
	public function getTree()
	{
		return $this->tree;
	}

}

