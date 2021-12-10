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
	<div class="mainContainer container">
		<div class="jumbotron mt-5">
			<div class="row">
				<div class="col-md-12 text-center">
					<h1><span class="far fa-times-circle u-fs-10x"></span></h1>
					<h2>{\App\Language::translate('LBL_NO_FOUND_VIEW')}</h2>
					<p class="my-5">
						<a class="btn btn-success mr-2" role="button" href="javascript:window.history.back();">
							<span class="fas fa-chevron-left mr-2"></span>{\App\Language::translate('LBL_GO_BACK')}
						</a>
						<a class="btn btn-warning mr-2 js-post-action" role="button" href="index.php?module=Users&action=Logout">
							<span class="fas fa-power-off fa-fw mr-2"></span>{\App\Language::translate('LBL_SIGN_OUT')}
						</a>
						<a class="btn btn-primary" role="button" href="index.php">
							<i class="fas fa-home mr-2"></i>{\App\Language::translate('LBL_MAIN_PAGE')}
						</a>
					</p>
				</div>
			</div>
		</div>
	</div>
{/strip}
