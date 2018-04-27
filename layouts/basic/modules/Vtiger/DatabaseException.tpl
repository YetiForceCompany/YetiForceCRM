{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!DOCTYPE html>
	<html>
		<head>
			<title>Yetiforce: {\App\Language::translate('LBL_ERROR')}</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<link rel="SHORTCUT ICON" href="{\App\Layout::getImagePath('favicon.ico')}">
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<link rel="stylesheet" href="{\App\Layout::getPublicUrl('libraries/bootstrap/dist/css/bootstrap.css')}" type="text/css" media="screen">
			<script type="text/javascript" src="{\App\Layout::getPublicUrl('libraries/jquery/dist/jquery.min.js')}"></script>
		</head>
		<body>
			<div class="alert alert-danger shadow" style="margin-top: 10px;position: relative;">
				<h2 class="alert-heading">{\App\Language::translate('LBL_SQL_ERROR')}</h2>
				<div>
					<strong>{\App\Language::translate('LBL_ERROR_MASAGE')}</strong>:
					<pre>{$MESSAGE['message']}</pre>
				</div>
				<div>
					<strong>{\App\Language::translate('LBL_SQL_QUERY')}</strong>:
					<pre>{$MESSAGE['query']}</pre>
				</div>
				{if $MESSAGE['params']}
					<div>
						<strong>{\App\Language::translate('LBL_SQL_PARAMS')}</strong>:
						<pre>{implode(',', $MESSAGE['params'])}</pre>
					</div>
				{/if}
				{if $MESSAGE['trace']}
					<div>
						<strong>{\App\Language::translate('LBL_BACKTRACE')}</strong>:
						<pre>{\App\Language::translate($MESSAGE['trace'])}</pre>
					</div>
				{/if}
			</div>
		</body>
	</html>
{/strip}
