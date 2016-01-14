<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubReposRelease.php');
require_once(__DIR__ . '/GitHubReposReleasesAssets.php');
	

class GitHubReposReleases extends GitHubService
{
	/**
	 * @var GitHubReposReleasesAssets
	 */
	public $assets;

	/**
	 * Initialize sub services
	 */
	public function __construct(GitHubClient $client)
	{
		parent::__construct($client);
		
		$this->assets = new GitHubReposReleasesAssets($client);
	}
	
	/**
	 * List
	 * 
	 * @return array<GitHubReposRelease>
	 */
	public function listReposReleases($owner, $repo)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/releases", 'GET', $data, 200, 'GitHubReposRelease', true);
	}
	
	/**
	 * Get
	 * 
	 * @return GitHubReposRelease
	 */
	public function get($owner, $repo, $id)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/releases/$id", 'GET', $data, 200, 'GitHubReposRelease');
	}
	
	/**
	 * Create
	 * @param $tag_name string (Required) The name of the tag.
	 * @param $target_commitish string Specifies the commitish value that determines where the Git tag is created from. Can be any branch or commit SHA. Unused if the Git tag already exists. Default: the repository’s default branch (usually master).
	 * @param $name string The name of the release.
	 * @param $body string Text describing the contents of the tag.
	 * @param $draft boolean true to create a draft (unpublished) release, false to create a published one. Default: false
	 * @param $prerelease boolean true to identify the release as a prerelease. false to identify the release as a full release. Default: false
	 * 
	 * @return GitHubReposRelease
	 */
	public function create($owner, $repo, $tag_name, $target_commitish = null, $name = null, $body = null, $draft = null, $prerelease = null)
	{
		$data = array();
		$data['tag_name'] = $tag_name;
		if(!is_null($target_commitish))
			$data['target_commitish'] = $target_commitish;
		if(!is_null($name))
			$data['name'] = $name;
		if(!is_null($body))
			$data['body'] = $body;
		if(!is_null($draft))
			$data['draft'] = $draft;
		if(!is_null($prerelease))
			$data['prerelease'] = $prerelease;
			
		$data = json_encode($data);
		
		return $this->client->request("/repos/$owner/$repo/releases", 'POST', $data, 201, 'GitHubReposRelease');
	}
	
	/**
	 * Create
	 * @param $id int (Required) Release id.
	 * @param $tag_name string (Required) The name of the tag.
	 * @param $target_commitish string Specifies the commitish value that determines where the Git tag is created from. Can be any branch or commit SHA. Unused if the Git tag already exists. Default: the repository’s default branch (usually master).
	 * @param $name string The name of the release.
	 * @param $body string Text describing the contents of the tag.
	 * @param $draft boolean true to create a draft (unpublished) release, false to create a published one. Default: false
	 * @param $prerelease boolean true to identify the release as a prerelease. false to identify the release as a full release. Default: false
	 * 
	 * @return GitHubReposRelease
	 */
	public function update($owner, $repo, $id, $tag_name = null, $target_commitish = null, $name = null, $body = null, $draft = null, $prerelease = null)
	{
		$data = array();
		if(!is_null($tag_name))
			$data['tag_name'] = $tag_name;
		if(!is_null($target_commitish))
			$data['target_commitish'] = $target_commitish;
		if(!is_null($name))
			$data['name'] = $name;
		if(!is_null($body))
			$data['body'] = $body;
		if(!is_null($draft))
			$data['draft'] = $draft;
		if(!is_null($prerelease))
			$data['prerelease'] = $prerelease;
			
//		$data = json_encode($data);
		
		return $this->client->request("/repos/$owner/$repo/releases/$id", 'PATCH', $data, 200, 'GitHubReposRelease');
	}
	
}

