<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubGittagTagger.php');
require_once(__DIR__ . '/GitHubGittagObject.php');
	

class GitHubGittag extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'tag' => 'string',
			'sha' => 'string',
			'url' => 'string',
			'message' => 'string',
			'tagger' => 'GitHubGittagTagger',
			'object' => 'GitHubGittagObject',
		));
	}
	
	/**
	 * @var string
	 */
	protected $tag;

	/**
	 * @var string
	 */
	protected $sha;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $message;

	/**
	 * @var GitHubGittagTagger
	 */
	protected $tagger;

	/**
	 * @var GitHubGittagObject
	 */
	protected $object;

	/**
	 * @return string
	 */
	public function getTag()
	{
		return $this->tag;
	}

	/**
	 * @return string
	 */
	public function getSha()
	{
		return $this->sha;
	}

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
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @return GitHubGittagTagger
	 */
	public function getTagger()
	{
		return $this->tagger;
	}

	/**
	 * @return GitHubGittagObject
	 */
	public function getObject()
	{
		return $this->object;
	}

}

