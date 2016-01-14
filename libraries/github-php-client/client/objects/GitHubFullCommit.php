<?php

require_once(__DIR__ . '/GitHubCommit.php');
require_once(__DIR__ . '/GitHubFullCommitStats.php');
require_once(__DIR__ . '/GitHubFullCommitFiles.php');
	

class GitHubFullCommit extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'stats' => 'GitHubFullCommitStats',
			'files' => 'array<GitHubFullCommitFiles>',
		));
	}
	
	/**
	 * @var GitHubFullCommitStats
	 */
	protected $stats;

	/**
	 * @var array<GitHubFullCommitFiles>
	 */
	protected $files;

	/**
	 * @return GitHubFullCommitStats
	 */
	public function getStats()
	{
		return $this->stats;
	}

	/**
	 * @return array<GitHubFullCommitFiles>
	 */
	public function getFiles()
	{
		return $this->files;
	}

}

