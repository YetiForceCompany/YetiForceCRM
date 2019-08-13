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
{assign var=COL_CLASS value='col col-lg-4 text-white p-2 d-flex flex-column justify-content-between'}
{function SHOW_HOSTING_TITLE TYPE=''}
	<h4 class="w-100 text-center u-font-weight-350">
	{\App\Language::translate('LBL_HOSTING_'|cat:$TYPE, 'Install')}
	</h4>
{/function}
{function SHOW_HOSTING_BODY PRODUCT=''}
	<div class="py-5 w-100 text-center">
		<h5 class="u-font-weight-300">
			{$PRODUCT->getLabel()}
		</h5>
	</div>
	<button type="button" class="btn btn-lg c-btn-block-xs-down btn-outline-light js-buy-modal" data-product={$PRODUCT->getName()}>
		<span class="yfi-shop mr-1"></span>
		{App\Language::translate('LBL_BUY', 'Install')}
	</button>
{/function}
<!-- tpl-install-tpl-StepChooseHost -->
	<div class="w-100 js-products-container">
		<main class="main-container">
			<div class="inner-container">
					<div class="row">
						<div class="col-12 text-center">
						<h3>{App\Language::translate('LBL_CHOOSE_HOSTING', 'Install')}</h3>
						</div>
					</div>
					<hr>
					<div class="row">
						<form name="step-stepChooseHost" method="post" action="Install.php" class="{$COL_CLASS} bg-danger">
								<input type="hidden" name="mode" value="step3">
								<input type="hidden" name="lang" value="{$LANG}">
								{SHOW_HOSTING_TITLE TYPE='OWN'}
								<button type="submit" class="btn btn-lg c-btn-block-xs-down btn-outline-light js-submit">
									{App\Language::translate('LBL_INSTALL_YOURSELF', 'Install')}
									<span class="fas fa-lg fa-arrow-circle-right ml-2"></span>
								</button>
						</form>
						<div class="{$COL_CLASS} bg-color-cyan-500">
							{SHOW_HOSTING_TITLE TYPE='CLOUD'}
							{SHOW_HOSTING_BODY PRODUCT=$PRODUCT_ClOUD}
						</div>
						<div class="{$COL_CLASS} bg-blue-grey-13">
							{SHOW_HOSTING_TITLE TYPE='SHARED'}
							{SHOW_HOSTING_BODY PRODUCT=$PRODUCT_SHARED}
						</div>
					</div>
					<div class="form-button-nav fixed-bottom button-container p-1 bg-light">
						<div class="text-center w-100">
							<a class="btn btn-lg c-btn-block-xs-down btn-danger mr-sm-1 mb-1 mb-sm-0" href="Install.php?mode=step2"
							   role="button">
								<span class="fas fa-lg fa-arrow-circle-left mr-2"></span>
								{App\Language::translate('LBL_BACK', 'Install')}
							</a>
						</div>
					</div>
			</div>
		</main>
	</div>
<!-- /tpl-install-tpl-StepChooseHost -->
{/strip}
