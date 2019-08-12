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
{function SHOW_HOSTING TYPE=''}
	<h3 class="w-100 text-center">
	{\App\Language::translate('LBL_HOSTING_'|cat:$TYPE, 'Install')}
	</h3>
{/function}
<!-- tpl-install-tpl-StepChooseHost -->
	<div class="w-100">
		<main class="main-container">
			<div class="inner-container">
				<form name="step-stepChooseHost" method="post" action="Install.php">
					<input type="hidden" name="mode" value="step3">
					<input type="hidden" name="lang" value="{$LANG}">
					<div class="row">
						<div class="col-12 text-center">
						<h3>{App\Language::translate('LBL_CHOOSE_HOSTING', 'Install')}</h3>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col col-lg-4 text-white bg-red">
							{SHOW_HOSTING TYPE='OWN'}
						</div>
						<div class="col col-lg-4 text-white bg-blue">
							{SHOW_HOSTING TYPE='CLOUD'}
						</div>
						<div class="col col-lg-4 text-white bg-grey">
							{SHOW_HOSTING TYPE='SHARED'}
						</div>
					</div>
					<div class="form-button-nav fixed-bottom button-container p-1 bg-light">
						<div class="text-center w-100">
							<a class="btn btn-lg c-btn-block-xs-down btn-danger mr-sm-1 mb-1 mb-sm-0" href="Install.php?mode=step2"
							   role="button">
								<span class="fas fa-lg fa-arrow-circle-left mr-2"></span>
								{App\Language::translate('LBL_BACK', 'Install')}
							</a>
							<button type="submit" class="btn btn-lg c-btn-block-xs-down btn-primary js-submit" data-js="container">
								{App\Language::translate('LBL_NEXT', 'Install')}
								<span class="fas fa-lg fa-arrow-circle-right ml-2"></span>
							</button>
						</div>
					</div>
				</form>
			</div>
		</main>
	</div>
<!-- /tpl-install-tpl-StepChooseHost -->
{/strip}
