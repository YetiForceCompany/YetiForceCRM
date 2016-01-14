<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/GitHubReposCollaborators.php');
require_once(__DIR__ . '/GitHubReposComments.php');
require_once(__DIR__ . '/GitHubReposCommits.php');
require_once(__DIR__ . '/GitHubReposContents.php');
require_once(__DIR__ . '/GitHubReposDownloads.php');
require_once(__DIR__ . '/GitHubReposForks.php');
require_once(__DIR__ . '/GitHubReposHooks.php');
require_once(__DIR__ . '/GitHubReposKeys.php');
require_once(__DIR__ . '/GitHubReposMerging.php');
require_once(__DIR__ . '/GitHubReposReleases.php');
require_once(__DIR__ . '/GitHubReposStatistics.php');
require_once(__DIR__ . '/GitHubReposStatuses.php');
require_once(__DIR__ . '/../objects/GitHubSimpleRepo.php');
require_once(__DIR__ . '/../objects/GitHubFullRepo.php');
require_once(__DIR__ . '/../objects/GitHubContributor.php');
require_once(__DIR__ . '/../objects/GitHubTeam.php');
require_once(__DIR__ . '/../objects/GitHubTag.php');
require_once(__DIR__ . '/../objects/GitHubBranches.php');
require_once(__DIR__ . '/../objects/GitHubBranch.php');
	

class GitHubRepos extends GitHubService
{

	/**
	 * @var GitHubReposCollaborators
	 */
	public $collaborators;
	
	/**
	 * @var GitHubReposComments
	 */
	public $comments;
	
	/**
	 * @var GitHubReposCommits
	 */
	public $commits;
	
	/**
	 * @var GitHubReposContents
	 */
	public $contents;
	
	/**
	 * @var GitHubReposDownloads
	 */
	public $downloads;
	
	/**
	 * @var GitHubReposForks
	 */
	public $forks;
	
	/**
	 * @var GitHubReposHooks
	 */
	public $hooks;
	
	/**
	 * @var GitHubReposKeys
	 */
	public $keys;
	
	/**
	 * @var GitHubReposMerging
	 */
	public $merging;
	
	/**
	 * @var GitHubReposReleases
	 */
	public $releases;
	
	/**
	 * @var GitHubReposStatistics
	 */
	public $statistics;
	
	/**
	 * @var GitHubReposStatuses
	 */
	public $statuses;
	
	
	/**
	 * Initialize sub services
	 */
	public function __construct(GitHubClient $client)
	{
		parent::__construct($client);
		
		$this->collaborators = new GitHubReposCollaborators($client);
		$this->comments = new GitHubReposComments($client);
		$this->commits = new GitHubReposCommits($client);
		$this->contents = new GitHubReposContents($client);
		$this->downloads = new GitHubReposDownloads($client);
		$this->forks = new GitHubReposForks($client);
		$this->hooks = new GitHubReposHooks($client);
		$this->keys = new GitHubReposKeys($client);
		$this->merging = new GitHubReposMerging($client);
		$this->releases = new GitHubReposReleases($client);
		$this->statistics = new GitHubReposStatistics($client);
		$this->statuses = new GitHubReposStatuses($client);
	}
	
	/**
	 * List your repositories
	 * 
	 * @param $type string (Optional) - Can be one of all, owner, public, private, member. Default: all 
	 * @param $sort string (Optional) - Can be one of created, updated, pushed, full_name. Default: full_name 
	 * @param $direction string (Optional) - Can be one of asc or desc. Default: when using full_name: asc; otherwise desc
	 * 
	 * @return array<GitHubSimpleRepo>
	 */
	public function listYourRepositories($type = null, $sort = null, $direction = null)
	{
		$data = array();
		if(!is_null($type))
			$data['type'] = $type;
		if(!is_null($sort))
			$data['sort'] = $sort;
		if(!is_null($direction))
			$data['direction'] = $direction;
		
		return $this->client->request("/user/repos", 'GET', $data, 200, 'GitHubSimpleRepo', true);
	}
	
	/**
	 * List user repositories
	 * 
	 * @param $user string - User login name
	 * @param $type string (Optional) - Can be one of all, owner, public, private, member. Default: all 
	 * @param $sort string (Optional) - Can be one of created, updated, pushed, full_name. Default: full_name 
	 * @param $direction string (Optional) - Can be one of asc or desc. Default: when using full_name: asc; otherwise desc
	 * 
	 * @return array<GitHubSimpleRepo>
	 */
	public function listUserRepositories($user, $type = null, $sort = null, $direction = null)
	{
		$data = array();
		if(!is_null($type))
			$data['type'] = $type;
		if(!is_null($sort))
			$data['sort'] = $sort;
		if(!is_null($direction))
			$data['direction'] = $direction;
		
		return $this->client->request("/users/$user/repos", 'GET', $data, 200, 'GitHubSimpleRepo', true);
	}
	
	/**
	 * List organization repositories
	 * 
	 * @param $organization string - Organization name
	 * @param $type string (Optional) - Can be one of all, owner, public, private, member. Default: all 
	 * 
	 * @return array<GitHubSimpleRepo>
	 */
	public function listOrganizationRepositories($organization, $type = null)
	{
		$data = array();
		if(!is_null($type))
			$data['type'] = $type;
		
		return $this->client->request("/orgs/$organization/repos", 'GET', $data, 200, 'GitHubSimpleRepo', true);
	}
	
	/**
	 * List repositories
	 * 
	 * @param $since string (Optional) - The integer ID of the last Repository that you�ve seen.
	 * 
	 * @return array<GitHubSimpleRepo>
	 */
	public function listRepositories($since = null)
	{
		$data = array();
		if(!is_null($since))
			$data['since'] = $since;
		
		return $this->client->request("/repositories", 'GET', $data, 200, 'GitHubSimpleRepo', true);
	}
	
	/**
	 * Create
	 * 
	 * @param $owner String (Optional) - The name of the organization, if organization not specified the repo will be created for the authenticated user.
	 * @param $repo String (Required) - The name of the repository.
	 * @param $private boolean (Optional) - `true` makes the repository private, and
	 * 	`false` makes it public.
	 * @param $has_issues boolean (Optional) - `true` to enable issues for this repository,
	 * 	`false` to disable them. Default is `true`.
	 * @param $has_wiki boolean (Optional) - `true` to enable the wiki for this
	 * 	repository, `false` to disable it. Default is `true`.
	 * @param $has_downloads boolean (Optional) - `true` to enable downloads for this
	 * 	repository, `false` to disable them. Default is `true`.
	 * @param $default_branch String (Optional) - Update the default branch for this repository.
	 * @param $description String (Optional) - A short description of the repository.
	 * @param $homepage String (Optional) - A URL with more information about the repository.
	 * @param $team_id int (Optional) - The id of the team that will be granted access to this repository. This is only valid when creating a repository in an organization.
	 * @param $auto_init Boolean (Optional) - Pass true to create an initial commit with empty README. Default: false.
	 * @param $gitignore_template String (Optional) - Desired language or platform .gitignore template to apply. Use the name of the template without the extension. For example, �Haskell�.
	 * @param $license_template String (Optional) - Desired LICENSE template to apply. Use the name of the template without the extension. For example, �mit� or �mozilla�.
	 * @return GitHubFullRepo
	 */
	public function create($owner = null, $repo, $private = null, $has_issues = null, $has_wiki = null, $has_downloads = null, $default_branch = null, $description = null, $homepage = null, $team_id = null, $auto_init = null, $gitignore_template = null, $license_template = null)
	{
		$data = array('name' => $repo);
		
		if(!is_null($private))
			$data['private'] = $private;
		if(!is_null($has_issues))
			$data['has_issues'] = $has_issues;
		if(!is_null($has_wiki))
			$data['has_wiki'] = $has_wiki;
		if(!is_null($has_downloads))
			$data['has_downloads'] = $has_downloads;
		if(!is_null($default_branch))
			$data['default_branch'] = $default_branch;
		if(!is_null($team_id))
			$data['team_id'] = $team_id;
		if(!is_null($auto_init))
			$data['auto_init'] = $auto_init;
		if(!is_null($gitignore_template))
			$data['gitignore_template'] = $gitignore_template;
		if(!is_null($license_template))
			$data['license_template'] = $license_template;

		$data = json_encode($data);

		if($owner)
			return $this->client->request("/orgs/$owner/repos", 'POST', $data, 201, 'GitHubFullRepo');
		else
			return $this->client->request("/user/repos", 'POST', $data, 201, 'GitHubFullRepo');
	}
	
	/**
	 * Get Repo
	 * 
	 * @return GitHubFullRepo
	 */
	public function get($owner, $repo)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo", 'GET', $data, 200, 'GitHubFullRepo');
	}
	
	/**
	 * List contributors
	 * 
	 * @return array<GitHubContributor>
	 */
	public function listContributors($owner, $repo)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/contributors", 'GET', $data, 200, 'GitHubContributor', true);
	}
	
	/**
	 * List languages
	 * 
	 * @return array<GitHubTeam>
	 */
	public function listLanguages($owner, $repo)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/teams", 'GET', $data, 200, 'GitHubTeam', true);
	}
	
	/**
	 * List Tags
	 * 
	 * @return array<GitHubTag>
	 */
	public function listTags($owner, $repo)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/tags", 'GET', $data, 200, 'GitHubTag', true);
	}
	
	/**
	 * List Branches
	 * 
	 * @return array<GitHubBranches>
	 */
	public function listBranches($owner, $repo)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/branches", 'GET', $data, 200, 'GitHubBranch', true);
	}
	
	/**
	 * Get Branch
	 * 
	 * @return array<GitHubBranch>
	 */
	public function getBranch($owner, $repo, $branch)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/branches/$branch", 'GET', $data, 200, 'GitHubBranch', true);
	}
	
	/**
	 * Delete a Repository
	 * 
	 */
	public function deleteRepository($owner, $repo)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo", 'DELETE', $data, 204, '');
	}
	
}

