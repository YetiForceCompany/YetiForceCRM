<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/GitHubGistsComments.php');
require_once(__DIR__ . '/../objects/GitHubFullGist.php');
	

class GitHubGists extends GitHubService
{

	/**
	 * @var GitHubGistsComments
	 */
	public $comments;
	
	
	/**
	 * Initialize sub services
	 */
	public function __construct(GitHubClient $client)
	{
		parent::__construct($client);
		
		$this->comments = new GitHubGistsComments($client);
	}
	
	/**
	 * Authentication
	 * 
	 * @return GitHubFullGist
	 */
	public function authentication($id)
	{
		$data = array();
		
		return $this->client->request("/gists/$id", 'GET', $data, 200, 'GitHubFullGist');
	}
	
	/**
	 * Create a gist
	 * 
	 * @param $files hash (Optional) - Files that make up this gist. The key of which
	 * 	should be an _optional_ **string** filename and the value another
	 * 	_optional_ **hash** with parameters:
	 * @return GitHubFullGist
	 */
	public function createGist($id, $files = null)
	{
		$data = array();
		if(!is_null($files))
			$data['files'] = $files;
		
		return $this->client->request("/gists/$id", 'PATCH', $data, 200, 'GitHubFullGist');
	}
	
	/**
	 * Star a gist
	 * 
	 */
	public function starGist($id)
	{
		$data = array();
		
		return $this->client->request("/gists/$id/star", 'PUT', $data, 204, '');
	}
	
	/**
	 * Unstar a gist
	 * 
	 */
	public function unstarGist($id)
	{
		$data = array();
		
		return $this->client->request("/gists/$id/star", 'DELETE', $data, 204, '');
	}
	
	/**
	 * Check if a gist is starred
	 * 
	 */
	public function checkIfGistIsStarred($id)
	{
		$data = array();
		
		return $this->client->request("/gists/$id", 'DELETE', $data, 204, '');
	}
	
}

