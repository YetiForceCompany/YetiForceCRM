{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!DOCTYPE html>
	<html>
		<head>
			<title>Yetiforce: {\App\Language::translate('LBL_BAD_REQUEST')}</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<link rel="SHORTCUT ICON" href="{\App\Layout::getImagePath('favicon.ico')}">
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<link rel="stylesheet" href="{\App\Layout::getPublicUrl('libraries/bootstrap/dist/css/bootstrap.css')}" type="text/css" media="screen">
		</head>
		<body style="background: #ddecf0;">
			<div class="container">
				<div style="margin-top: 70px;" class="alert alert-warning shadow">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h2 class="alert-heading">{\App\Language::translate('LBL_BAD_REQUEST')}</h2>
					<p>{\App\Purifier::encodeHtml($MESSAGE)}</p>
					<p class="Buttons">
						<a class="btn btn-info" role="button" href="index.php">{\App\Language::translate('LBL_MAIN_PAGE')}</a>
					</p>
				</div>
			</div>
		</body>
	</html>
{/strip}
