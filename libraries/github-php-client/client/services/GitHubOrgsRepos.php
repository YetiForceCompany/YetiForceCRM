<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubFullOrg.php');
  

class GithubOrgsRepos extends GitHubService
{

   /**
   * Create team
   * 
   * @return array<GitHubFullTeam>
   */
  public function createTeam($id)
  {
    $data = array();
    
    return $this->client->request("/teams/$id", 'PATCH', $data, 200, 'GitHubFullTeam', true);
  }

    /**
   * Create
   * 
   * @param $private boolean (Optional) - `true` makes the repository private, and
   *  `false` makes it public.
   * @param $has_issues boolean (Optional) - `true` to enable issues for this repository,
   *  `false` to disable them. Default is `true`.
   * @param $has_wiki boolean (Optional) - `true` to enable the wiki for this
   *  repository, `false` to disable it. Default is `true`.
   * @param $has_downloads boolean (Optional) - `true` to enable downloads for this
   *  repository, `false` to disable them. Default is `true`.
   * @param $default_branch String (Optional) - Update the default branch for this repository.
   * @return GitHubFullRepo
   */
  public function createRepo($org, $repo, $private = null, $has_issues = null, $has_wiki = null, $has_downloads = null, $default_branch = null, $auto_init = null, $gitignore_template = null)
  {
    $data = array("name" => $repo);
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
    if(!is_null($auto_init))
      $data['auto_init'] = $auto_init;
    if(!is_null($gitignore_template))
      $data['gitignore_template'] = $gitignore_template;
    
    return $this->client->request("/orgs/$org/repos", 'POST', $data, 201, 'GitHubFullRepo');
  }

}