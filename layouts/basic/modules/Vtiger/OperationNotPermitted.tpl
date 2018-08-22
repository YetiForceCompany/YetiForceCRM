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
<!DOCTYPE html>
{strip}
	<html>
	<head>
		<title>Yetiforce: {\App\Language::translate('LBL_ERROR')}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="{\App\Layout::getPublicUrl('layouts/basic/styles/Main.css')}">
	</head>
	<body class="h-auto bg-color-amber-50">
	<div class="container">
		<div class="card mx-auto mt-5 u-w-fit shadow" role="alert">
			<div class="card-header d-flex color-red-a200 bg-color-red-50 justify-content-center">
				<i class="fas fa-exclamation-triangle fa-10x display-1 mr-3"></i>
				<h3 class="align-items-center card-title d-flex justify-content-center">{\App\Language::translate('LBL_ERROR')}</h3>
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
