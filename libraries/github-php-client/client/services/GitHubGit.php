<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/GitHubGitBlobs.php');
require_once(__DIR__ . '/GitHubGitCommits.php');
require_once(__DIR__ . '/GitHubGitImport.php');
require_once(__DIR__ . '/GitHubGitRefs.php');
require_once(__DIR__ . '/GitHubGitTags.php');
require_once(__DIR__ . '/GitHubGitTrees.php');
	

class GitHubGit extends GitHubService
{

	/**
	 * @var GitHubGitBlobs
	 */
	public $blobs;
	
	/**
	 * @var GitHubGitCommits
	 */
	public $commits;
	
	/**
	 * @var GitHubGitImport
	 */
	public $import;
	
	/**
	 * @var GitHubGitRefs
	 */
	public $refs;
	
	/**
	 * @var GitHubGitTags
	 */
	public $tags;
	
	/**
	 * @var GitHubGitTrees
	 */
	public $trees;
	
	
	/**
	 * Initialize sub services
	 */
	public function __construct(GitHubClient $client)
	{
		parent::__construct($client);
		
		$this->blobs = new GitHubGitBlobs($client);
		$this->commits = new GitHubGitCommits($client);
		$this->import = new GitHubGitImport($client);
		$this->refs = new GitHubGitRefs($client);
		$this->tags = new GitHubGitTags($client);
		$this->trees = new GitHubGitTrees($client);
	}
	
}

