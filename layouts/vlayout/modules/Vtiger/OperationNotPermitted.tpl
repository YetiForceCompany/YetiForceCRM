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
<html>
	<head>
			<title>Yetiforce: {vtranslate('LBL_ERROR')}</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<link rel="SHORTCUT ICON" href="layouts/vlayout/skins/images/favicon.ico">
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<link rel="stylesheet" href="libraries/bootstrap/css/bootstrap.css" type="text/css" media="screen">
			<script type="text/javascript" src="libraries/jquery/jquery.min.js"></script>
			<script type="text/javascript" src="libraries/jquery/jquery-migrate-1.2.1.js"></script>
			<script type="text/javascript" src="libraries/bootstrap/js/bootstrap-alert.js"></script>
			<style>
				.shadow{
					-webkit-box-shadow: 3px 3px 14px 0px rgba(50, 50, 50, 0.75);
					-moz-box-shadow:    3px 3px 14px 0px rgba(50, 50, 50, 0.75);
					box-shadow:         3px 3px 14px 0px rgba(50, 50, 50, 0.75);
				}
			</style>
	</head>
	<body class="row-fluid">
		<div style="margin-top: 10px;" class="span6 alert alert-error shadow">
			<div style="position: relative;" >
				<div>
					<h2 class="alert-heading">{vtranslate('LBL_ERROR')}</h2>
					<p>{vtranslate($MESSAGE)}</p>
					<p class="Buttons">
						<a class="btn btn-warning" href="javascript:window.history.back();">{vtranslate('LBL_GO_BACK')}</a>
						<a class="btn btn-info" href="index.php">{vtranslate('LBL_MAIN_PAGE')}</a>
					</p>
				</div>
			</div>
		</div>
	</body>
</html>