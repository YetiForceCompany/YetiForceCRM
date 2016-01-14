<?php

require_once(__DIR__ . '/../GitHubObject.php');

	

class GitHubIssuePullRequest extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'html_url' => 'string',
			'diff_url' => 'string',
			'patch_url' => 'string',
		));
	}
	
	/**
	 * @var string
	 */
	protected $html_url;

	/**
	 * @var string
	 */
	protected $diff_url;

	/**
	 * @var string
	 */
	protected $patch_url;

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

}

