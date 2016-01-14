<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');

	

class GitHubMarkdown extends GitHubService
{
	public function getTextAsMarkdown($text, $mode = 'markdown', $context = null)
	{
		$data = array(
			'text' => $text,
			'mode' => $mode
		);
		if(!is_null($context))
			$data['context'] = $context;
		
		return $this->client->request("/markdown", 'POST', json_encode($data), 200, 'string', false);
	}
}

