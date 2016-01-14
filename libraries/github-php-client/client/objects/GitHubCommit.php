<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubUser.php');
require_once(__DIR__ . '/GitHubCommitCommit.php');
require_once(__DIR__ . '/GitHubCommitParents.php');
	

class GitHubCommit extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'url' => 'string',
			'html_url' => 'string',
			'sha' => 'string',
			'author' => 'GitHubUser',
			'committer' => 'GitHubUser',
			'commit' => 'GitHubCommitCommit',
			'parents' => 'array<GitHubCommitParents>',
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
	 * @var string
	 */
	protected $sha;

	/**
	 * @var GitHubUser
	 */
	protected $author;

	/**
	 * @var GitHubUser
	 */
	protected $committer;

	/**
	 * @var GitHubCommitCommit
	 */
	protected $commit;

	/**
	 * @var array<GitHubCommitParents>
	 */
	protected $parents;

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
	public function getSha()
	{
		return $this->sha;
	}

	/**
	 * @return GitHubUser
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @return GitHubUser
	 */
	public function getCommitter()
	{
		return $this->committer;
	}

	/**
	 * @return GitHubCommitCommit
	 */
	public function getCommit()
	{
		return $this->commit;
	}

	/**
	 * @return array<GitHubCommitParents>
	 */
	public function getParents()
	{
		return $this->parents;
	}

}

