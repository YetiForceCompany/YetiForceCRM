{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{if 'test' === \App\Config::main('systemMode')}
	{$HEADER_MESSAGE}
	{if $MESSAGE_EXPANDED}
		{if !empty($MESSAGE['message'])}{$MESSAGE['message']}{/if}
		{if !empty($MESSAGE['query'])}{$MESSAGE['query']}{/if}
		{if !empty($MESSAGE['params'])}
			{implode(',', $MESSAGE['params'])}
		{/if}
		{if !empty($MESSAGE['trace'])}
			{\App\Language::translate($MESSAGE['trace'])}
		{/if}
	{else}
		{$MESSAGE}
	{/if}
{else}
	<!DOCTYPE html>
	{strip}
	<html>
	<head>
		<title>Yetiforce: {\App\Purifier::encodeHtml($HEADER_MESSAGE)}</title>
		{if !empty($IS_IE)}
			<meta http-equiv="x-ua-compatible" content="IE=11,edge" >
		{/if}
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="{\App\Layout::getPublicUrl('layouts/basic/styles/Main.css')}">
		<link rel="stylesheet" href="{\App\Layout::getPublicUrl('libraries/@fortawesome/fontawesome-free/css/all.css')}">
	</head>
	<body class="h-auto bg-color-amber-50 overflow-auto">
	<div class="o-exception-fixed-block container u-white-space-n u-word-break">
		<div class="card mx-auto mt-5 u-w-fit shadow" role="alert">
			<div class="card-header d-flex color-red-a200 bg-color-red-50 justify-content-center flex-wrap">
				<span class="display-1">
					<i class="fas fa-exclamation-triangle mr-3"></i>
				</span>
				<h3 class="align-items-center card-title d-flex justify-content-center">{\App\Purifier::encodeHtml($HEADER_MESSAGE)}</h3>
			</div>
			<div class="card-body text-black rd-body bg-color-grey-50 js-exception-error">
				<p class="card-text u-font-size-19px">{if $MESSAGE_EXPANDED}{\App\Purifier::encodeHtml($MESSAGE['message'])}.{else}{\App\Purifier::encodeHtml($MESSAGE)}{/if}</p>
			</div>
			<div class="card-footer text-black d-flex flex-wrap flex-sm-nowrap">
				<a class="btn btn-lg btn-default mr-sm-2 mb-1 mb-sm-0 w-100" role="button"
				   href="javascript:window.history.back();"><i
							class="fas fa-chevron-left mr-2"></i>{\App\Language::translate('LBL_GO_BACK')}</a>
				<a class="btn btn-lg btn-default w-100" role="button"
				   href="index.php"><i class="fas fa-home mr-2"></i>{\App\Language::translate('LBL_MAIN_PAGE')}</a>
			</div>
		</div>
		{if $MESSAGE_EXPANDED}
			{if !empty($MESSAGE['query'])}
				<div class="my-5 mx-auto card u-w-fit shadow">
					<div class="card-header">
						<h5>{\App\Language::translate('LBL_SQL_QUERY')}</h5>
					</div>
					<div class="card-body text-black">
						<pre class="u-white-space-n u-word-break text-black">{$MESSAGE['query']}</pre>
					</div>
				</div>
			{/if}
			{if !empty($MESSAGE['params'])}
				<div class="my-5 mx-auto card u-w-fit shadow">
					<div class="card-header">
						<h5>{\App\Language::translate('LBL_SQL_PARAMS')}</h5>
					</div>
					<div class="card-body text-black">
						<pre class="u-white-space-n u-word-break">{implode(',', $MESSAGE['params'])}</pre>
					</div>
				</div>
			{/if}
			{if !empty($MESSAGE['trace'])}
				<div class="my-5 mx-auto card u-w-fit shadow">
					<div class="card-header">
						<h5>{\App\Language::translate('LBL_BACKTRACE')}</h5>
					</div>
					<div class="card-body text-black">
						<pre class="u-white-space-n u-word-break">{\App\Language::translate($MESSAGE['trace'])}</pre>
					</div>
				</div>
			{/if}
		{/if}
	</div>
	<div class="my-5 mx-auto card p-3 u-w-fit shadow">
		<pre class="js-backtrace-content" data-js="html"></pre>
	</div>
	<script type="text/javascript" {if \App\Session::get('CSP_TOKEN')}nonce="{\App\Session::get('CSP_TOKEN')}"{/if}>
		function errorLog() {
			console.error(document.querySelector('.js-exception-error').textContent);
			var html = '';
			var backtrace = document.querySelector('.js-exception-backtrace');
			var logs = document.querySelector('.js-exception-logs');
			if(backtrace){
				html += backtrace.textContent;
				backtrace.remove()
			}
			if(logs){
				html += logs.textContent;
				logs.remove()
			}
			document.querySelector('.js-backtrace-content').textContent = html;
		}
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', errorLog);
		} else {
			errorLog();
		}
	</script>
	</body>
	</html>
	{/strip}
{/if}
