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
	<div class="tpl-install-tpl-Step7">
		{if $INSTALLATION_SUCCESS}
			<form name="step{$STEP_NUMBER}" method="post" action="../index.php?module=Users&action=Login">
				<input type="hidden" name="mode" value="install">
				<input type="hidden" name="username" value="{$USER_NAME}">
				<input type="hidden" name="password" value="{$PASSWORD}">
			</form>
			<script type="text/javascript">
				window.localStorage.removeItem('yetiforce_install');
				jQuery(function () { /* Delay to let page load complete */
					setTimeout(function () {
						jQuery('form[name="step7"]').submit();
					}, 150);
				});
			</script>
		{else}
			<div class="container u-white-space-n u-word-break">
				<div class="card mx-auto mt-5 u-w-fit shadow" role="alert">
					<div class="card-header d-flex color-red-a200 bg-color-red-50 justify-content-center flex-wrap">
						<h3 class="align-items-center card-title d-flex justify-content-center">{\App\Language::translate('LBL_ERROR_INSTALL', 'Install')}</h3>
					</div>
				</div>
			</div>
			<div class="form-button-nav fixed-bottom button-container p-1 bg-light">
				<div class="text-center w-100">
					<a class="btn btn-lg c-btn-block-xs-down btn-danger mr-sm-1 mb-1 mb-sm-0" href="Install.php" role="button">
						<span class="fas fa-lg fa-arrow-circle-left mr-2"></span>
						{App\Language::translate('LBL_BACK', 'Install')}
					</a>
				</div>
			</div>
			<script type="text/javascript">
				$('#progressIndicator').remove();
			</script>
		{/if}
	</div>
{/strip}
