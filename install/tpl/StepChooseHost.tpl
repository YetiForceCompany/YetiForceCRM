{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-install-tpl-StepChooseHost -->
{assign var=COL_CLASS value='col-12 col-lg-4 text-white p-2 p-lg-3 p-xl-5 pt-xl-2 d-flex flex-column'}
{assign var=BTN_CLASS value='btn btn-lg c-btn-block-xs-down btn-outline-light mt-auto'}
{function SHOW_HOSTING_TITLE TYPE=''}
	<h4 class="w-100 text-center u-font-weight-350">
	{\App\Language::translate('LBL_HOSTING_'|cat:$TYPE, 'Install')}
	</h4>
{/function}
{function SHOW_HOSTING_BODY PRODUCT=''}
	<div class="py-4 w-100 text-center">
		<div class="pb-3">
			<img class="o-buy-modal__img u-img-invert" src="../{$PRODUCT->getImage()}" alt="{\App\Purifier::encodeHtml($PRODUCT->getLabel())}" title="{\App\Purifier::encodeHtml($PRODUCT->getLabel())}"/>
		</div>
		<h5 class="u-font-weight-300">
			{$PRODUCT->getLabel()}
		</h5>
		<hr class="w-50 mx-auto">
		<p>{$PRODUCT->getDescription()}</p>
<!--
		<button type="button" class="btn btn-outline-light js-product-modal my-2" data-product={$PRODUCT->getName()}>
			<span class="fas fa-info-circle mr-2"></span>
			{App\Language::translate('LBL_SHOW_MORE', 'Install')}
		</button>
-->
	</div>
	<button type="button" class="{$BTN_CLASS} js-buy-modal" data-product={$PRODUCT->getName()}>
		<span class="yfi-shop mr-2"></span>
		{App\Language::translate('LBL_BUY', 'Install')}
	</button>
{/function}
	<div class="w-100 js-products-container">
		<main class="main-container mx-lg-3">
			<div class="inner-container">
					<div class="row">
						<div class="col-12 text-center">
						<h3>{App\Language::translate('LBL_CHOOSE_INSTALLATION_TYPE', 'Install')}</h3>
						</div>
						<hr class="w-100">
					</div>
					<div class="row">
						<form name="step-stepChooseHost" method="post" action="Install.php" class="{$COL_CLASS} o-product o-product--own">
							<input type="hidden" name="mode" value="step3">
							<input type="hidden" name="lang" value="{$LANG}">
							{SHOW_HOSTING_TITLE TYPE='SELF'}
							<div class="py-4 w-100 text-center">
								<div class="pb-3">
									<img class="o-buy-modal__img u-img-invert" src="../{\App\Layout::getPublicUrl('install/tpl/resources/images/own_hosting.png')}" alt="{App\Language::translate('LBL_MY_SERVER_TITLE', 'Install')}" title="{App\Language::translate('LBL_MY_SERVER_TITLE', 'Install')}"/>
								</div>
								<h5 class="u-font-weight-300">
									{App\Language::translate('LBL_MY_SERVER_TITLE', 'Install')}
								</h5>
								<hr class="w-50 mx-auto">
								<p>{App\Language::translate('LBL_MY_SERVER_DESC', 'Install')}</p>
							</div>
							<button type="submit" class="{$BTN_CLASS} js-submit">
								<span class="fas fa-lg fa-arrow-circle-right mr-2"></span>
								{App\Language::translate('LBL_INSTALL_YOURSELF', 'Install')}
							</button>
						</form>
						<div class="{$COL_CLASS} o-product o-product--shared">
							{SHOW_HOSTING_TITLE TYPE='SHARED'}
							{SHOW_HOSTING_BODY PRODUCT=$PRODUCT_SHARED}
						</div>
						<div class="{$COL_CLASS} o-product o-product--cloud">
							{SHOW_HOSTING_TITLE TYPE='CLOUD'}
							{SHOW_HOSTING_BODY PRODUCT=$PRODUCT_ClOUD}
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
