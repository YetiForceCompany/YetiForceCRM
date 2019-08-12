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
<!-- tpl-install-tpl-StepChooseHost -->
	<div class="container px-2 px-sm-3">
		<main class="main-container">
			<div class="inner-container">
				<form name="step-stepChooseHost" method="post" action="Install.php">
					<input type="hidden" name="mode" value="step3">
					<input type="hidden" name="lang" value="{$LANG}">
					<div class="row">
						<div class="col-12 text-center">
						<h3>{App\Language::translate('LBL_CHOOSE_HOST', 'Install')}</h3>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col col-lg-4">
						</div>
						<div class="col col-lg-4">
						</div>
						<div class="col col-lg-4">
						</div>
					</div>
					<div class="form-button-nav fixed-bottom button-container p-1 bg-light">
						<div class="text-center w-100">
							<button type="button" class="btn btn-lg c-btn-block-sm-down btn-outline-info mb-1 mb-md-0 mr-md-1" data-toggle="modal" data-target="#license-modal">
								<span class="fas fa-lg fas fa-bars mr-2"></span>
								{App\Language::translate('LBL_EXTERNAL_LIBRARIES_LICENSES', 'Install')}
							</button>
							<a class="btn btn-lg c-btn-block-sm-down btn-danger mb-1 mb-md-0 mr-md-1" href="Install.php" role="button">
								<span class="fas fa-lg fa-times-circle mr-2"></span>
								{App\Language::translate('LBL_DISAGREE', 'Install')}
							</a>
							<button type="submit" class="btn btn-lg c-btn-block-sm-down btn-primary">
								<span class="fas fa-lg fa-check mr-2"></span>
								{App\Language::translate('LBL_I_AGREE', 'Install')}
							</button>
						</div>
					</div>
				</form>
			</div>
		</main>
	</div>
<!-- /tpl-install-tpl-StepChooseHost -->
{/strip}
