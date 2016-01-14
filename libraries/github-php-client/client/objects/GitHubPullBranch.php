<?php

require_once(__DIR__ . '/../GitHubObject.php');



class GitHubPullBranch extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'label' => 'string',
			'ref' => 'string',
			'sha' => 'string',
			'user' => 'GitHubUser',
			'repo' => 'GitHubRepo',
		));
	}


	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $ref;

	/**
	 * @var string
	 */
	protected $sha;

	/**
	 * @var GitHubUser
	 */
	protected $user;

	/**
	 * @var GitHubRepo
	 */
	protected $repo;


	/**
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @return string
	 */
	public function getRef()
	{
		return $this->ref;
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
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return GitHubRepo
	 */
	public function getRepo()
	{
		return $this->repo;
	}
}

