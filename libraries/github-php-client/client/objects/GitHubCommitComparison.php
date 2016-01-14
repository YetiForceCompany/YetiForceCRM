<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubCommit.php');
	

class GitHubCommitComparison extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'url' => 'string',
			'html_url' => 'string',
			'permalink_url' => 'string',
			'diff_url' => 'string',
			'patch_url' => 'string',
			'base_commit' => 'GitHubCommit',
			'status' => 'string',
			'ahead_by' => 'int',
			'behind_by' => 'int',
			'total_commits' => 'int',
			'files' => 'array<GitHubFile>'
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
	protected $permalink_url;

	/**
	 * @var string
	 */
	protected $diff_url;

	/**
	 * @var string
	 */
	protected $patch_url;

	/**
	 * @var GitHubCommit
	 */
	protected $base_commit;

	/**
	 * @var string
	 */
	protected $status;

	/**
	 * @var int
	 */
	protected $ahead_by;

	/**
	 * @var int
	 */
	protected $behind_by;

	/**
	 * @var int
	 */
	protected $total_commits;

	/**
	 *
	 * @var GitHubFile[]
	 */
	protected $files;
	
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
	public function getPermalinkUrl()
	{
		return $this->permalink_url;
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
	 * @return GitHubCommit
	 */
	public function getBaseCommit()
	{
		return $this->base_commit;
	}

	/**
	 * @return string
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @return int
	 */
	public function getAheadBy()
	{
		return $this->ahead_by;
	}

	/**
	 * @return int
	 */
	public function getBehindBy()
	{
		return $this->behind_by;
	}

	/**
	 * @return int
	 */
	public function getTotalCommits()
	{
		return $this->total_commits;
	}

	/**
	 *
	 * @return GitHubFile[]
	 */
	public function getFiles()
	{
		return $this->files;
	}
}

