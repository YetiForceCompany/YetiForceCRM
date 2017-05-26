{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<div class="span12 welcome-div alignCenter">
		<h3>{vtranslate('LBL_INSTALLATION_IN_PROGRESS','Install')}...</h3><br>
		<img src="../{\App\Layout::getPublicUrl('layouts/basic/skins/images/install_loading.gif')}" alt="Install loading"/>
		<h6>{vtranslate('LBL_PLEASE_WAIT','Install')}.... </h6>
	</div>
	<form class="form-horizontal" name="step7" method="post" action="../index.php?module=Users&action=Login">
		<input type="hidden" name="mode" value="install" >
		<input type="hidden" name="username" value="admin" >
		<input type="hidden" name="password" value="{$PASSWORD}" >
	</form>
	<script type="text/javascript">
		window.localStorage.removeItem('yetiforce_install');
		jQuery(function () { /* Delay to let page load complete */
			setTimeout(function () {
				jQuery('form[name="step7"]').submit();
			}, 150);
		});
	</script>
{/strip}
