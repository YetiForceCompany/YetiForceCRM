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
	<body class="h-auto">
	<div class="container">
		<div class="card mx-auto mt-5 u-w-fit" role="alert">
			<div class="d-flex color-red-a200 bg-color-red-50 p-3 border-bottom">
				<i class="fas fa-exclamation-triangle fa-10x display-1 mx-auto"></i>
			</div>
			<div class="card-body bg-color-grey-50">
				<h3 class="align-items-center card-title d-flex justify-content-center">{\App\Language::translate('LBL_ERROR')}</h3>
				<p class="card-text u-font-size-19px">{\App\Purifier::encodeHtml($MESSAGE)}</p>
				<div class="d-flex flex-nowrap">
					<a class="btn btn-lg btn-outline-dark w-100 mr-2" role="button"
					   href="javascript:window.history.back();"><i
								class="fas fa-chevron-left mr-2"></i>{\App\Language::translate('LBL_GO_BACK')}</a>
					<a class="btn btn-lg btn-outline-dark w-100" role="button"
					   href="index.php"><i class="fas fa-home mr-2"></i>{\App\Language::translate('LBL_MAIN_PAGE')}</a>
				</div>
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
