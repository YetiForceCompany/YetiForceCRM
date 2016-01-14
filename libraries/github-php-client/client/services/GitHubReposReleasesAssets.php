<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubReposReleaseAsset.php');

	

class GitHubReposReleasesAssets extends GitHubService
{

	/**
	 * List
	 * 
	 * @return array<GitHubReposReleaseAsset>
	 */
	public function listReposReleases($owner, $repo, $releaseId)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/releases/$releaseId/assets", 'GET', $data, 200, 'GitHubReposRelease', true);
	}
	
	/**
	 * Get
	 * 
	 * @return GitHubReposReleaseAsset
	 */
	public function get($owner, $repo, $releaseId, $id)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/releases/$releaseId/assets/$id", 'GET', $data, 200, 'GitHubReposReleaseAsset');
	}
	
	/**
	 * Create
	 * @param $contentType string (Required) The content type of the asset. This should be set in the Header. Example: “application/zip”. For a list of acceptable types, refer this list of common media types.
	 * @param $name string (Required) The file name of the asset. This should be set in the URI query parameter.
	 * @param $filePath string (Required)
	 * 
	 * @return GitHubReposReleaseAsset
	 */
	public function upload($owner, $repo, $releaseId, $name, $contentType, $filePath)
	{
		$data = array();
		$data['name'] = $name;
		
		return $this->client->upload("/repos/$owner/$repo/releases/$releaseId/assets", $data, 201, 'GitHubReposReleaseAsset', $contentType, $filePath);
	}
	
}

