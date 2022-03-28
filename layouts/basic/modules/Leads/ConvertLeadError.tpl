{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
<!DOCTYPE html>
<html>

<head>
	<title>Yetiforce: {\App\Language::translate('LBL_ERROR')}</title>
	{if !empty($IS_IE)}
		<meta http-equiv="x-ua-compatible" content="IE=11,edge">
	{/if}
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="SHORTCUT ICON" href="{\App\Layout::getImagePath('favicon.ico')}">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="{\App\Layout::getPublicUrl('layouts/basic/styles/Main.css')}">
	<link rel="stylesheet" href="{\App\Layout::getPublicUrl('libraries/@fortawesome/fontawesome-free/css/all.css')}">
</head>

<body class="container">
	<div class="col-md-12 alert alert-warning mt-4 u-box-shadow">
		<div class="position-relative">
			<div>
				<h2 class="alert-heading">{\App\Language::translate('CANNOT_CONVERT', $MODULE)}</h2>
				<p>
				<ul> {\App\Language::translate('LBL_FOLLOWING_ARE_POSSIBLE_REASONS', $MODULE)}
					<li>{\App\Language::translate('LBL_LEADS_FIELD_MAPPING_INCOMPLETE', $MODULE)}</li>
					<li>{\App\Language::translate('LBL_MANDATORY_FIELDS_ARE_EMPTY', $MODULE)}</li>
					{if !empty($EXCEPTION)}
						<li><strong>{$EXCEPTION}</strong></li>
					{/if}
				</ul>
				</p>
				<p class="clearfix"></p>
				<div>
					{if $CURRENT_USER->isAdmin()}
						<a class="btn btn-info" role="button"
							href='index.php?parent=Settings&module=Leads&view=MappingDetail'>{\App\Language::translate('LBL_LEADS_FIELD_MAPPING', $MODULE)}</a>
					{/if}
					<a class="btn btn-warning" role="button"
						href="javascript:window.history.back();">{\App\Language::translate('LBL_GO_BACK')}</a>
					<a class="btn btn-primary" role="button"
						href="index.php">{\App\Language::translate('LBL_MAIN_PAGE')}</a>
				</div>
			</div>
		</div>
	</div>
</body>

</html>
