{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Base-DashBoard-PremiumZone -->
	<div class="d-flex flex-wrap mb-3 mr-n3">
		{assign var="QUALIFIED_MODULE" value='Settings:YetiForce'}
		{foreach $PRODUCTS_PREMIUM as $PRODUCT}
			{include file=\App\Layout::getTemplatePath('Shop/ProductPremium.tpl', 'Settings:YetiForce')}
		{/foreach}
	</div>
	<!-- tpl-Settings-Base-DashBoard-PremiumZone -->
{/strip}
