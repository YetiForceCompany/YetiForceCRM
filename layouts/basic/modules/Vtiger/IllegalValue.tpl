{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!DOCTYPE html>
	<html>
	<head>
		<title>Yetiforce: {\App\Language::translate('LBL_BAD_REQUEST')}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="{\App\Layout::getPublicUrl('layouts/basic/styles/Main.css')}">
	</head>
	<body class="h-auto bg-color-amber-50">
	<div class="container">
		<div class="card mx-auto mt-5 u-w-fit shadow" role="alert">
			<div class="card-header d-flex color-red-a200 bg-color-red-50 justify-content-center">
				<i class="fas fa-exclamation-triangle fa-10x display-1 mr-3"></i>
				<h3 class="align-items-center card-title d-flex justify-content-center">{\App\Language::translate('LBL_BAD_REQUEST')}</h3>
			</div>
			<div class="card-body bg-color-grey-50">
				<p class="card-text u-font-size-19px">{\App\Purifier::encodeHtml($MESSAGE)}.</p>
			</div>
			<div class="card-footer d-flex flex-nowrap">
				<a class="btn btn-lg btn-default mr-2 w-100" role="button"
				   href="javascript:window.history.back();"><i
							class="fas fa-chevron-left mr-2"></i>{\App\Language::translate('LBL_GO_BACK')}</a>
				<a class="btn btn-lg btn-default w-100" role="button"
				   href="index.php"><i class="fas fa-home mr-2"></i>{\App\Language::translate('LBL_MAIN_PAGE')}</a>
			</div>
		</div>
	</div>
	<script type="text/javascript"
			src="{\App\Layout::getPublicUrl('libraries/@fortawesome/fontawesome/index.js')}"></script>
	<script type="text/javascript"
			src="{\App\Layout::getPublicUrl('libraries/@fortawesome/fontawesome-free-solid/index.js')}"></script>
	</body>
	</html>
{/strip}
