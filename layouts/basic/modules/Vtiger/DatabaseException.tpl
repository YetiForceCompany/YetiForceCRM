{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<!DOCTYPE html>
	<html>
		<head>
			<title>Yetiforce: {vtranslate('LBL_ERROR')}</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<link rel="SHORTCUT ICON" href="{vimage_path('favicon.ico')}">
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<link rel="stylesheet" href="libraries/bootstrap/css/bootstrap.css" type="text/css" media="screen">
			<script type="text/javascript" src="libraries/jquery/jquery.min.js"></script>
			<script type="text/javascript" src="libraries/jquery/jquery-migrate.js"></script>
		</head>
		<body>
			<div class="alert alert-danger shadow" style="margin-top: 10px;position: relative;">
				<h2 class="alert-heading">{vtranslate('LBL_SQL_ERROR')}</h2>
				<div>
					<strong>{vtranslate('LBL_ERROR_MASAGE')}</strong>:
					<pre>{$MESSAGE['message']}</pre>
				</div>
				<div>
					<strong>{vtranslate('LBL_SQL_QUERY')}</strong>:
					<pre>{$MESSAGE['query']}</pre>
				</div>
				{if $MESSAGE['params']}
					<div>
						<strong>{vtranslate('LBL_SQL_PARAMS')}</strong>:
						<pre>{implode(',', $MESSAGE['params'])}</pre>
					</div>
				{/if}
				{if $MESSAGE['trace']}
					<div>
						<strong>{vtranslate('LBL_BACKTRACE')}</strong>:
						<pre>{vtranslate($MESSAGE['trace'])}</pre>
					</div>
				{/if}
			</div>
		</body>
	</html>
{/strip}
