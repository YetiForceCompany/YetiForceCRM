<?php

class GitHubClientException extends Exception
{
	const CLASS_NOT_FOUND = 1;
	const PAGE_INVALID = 2;
	const PAGE_SIZE_INVALID = 3;
	const INVALID_HTTP_CODE = 4;
	const INVALID_RESULT = 5;
}
