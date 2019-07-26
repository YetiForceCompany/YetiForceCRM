{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Base-DashBoard-PremiumZone d-flex flex-wrap mb-3">
		{assign var="QUALIFIED_MODULE" value='Settings:YetiForce'}
		{foreach $PRODUCTS_PREMIUM as $PRODUCT}
			<div class="dashboardWidget mt-3 mr-3 flex-grow-1 u-w-max-320px">
				{include file=\App\Layout::getTemplatePath('Shop/SmallProduct.tpl', 'Settings:YetiForce')}
			</div>
		{/foreach}
	</div>
{/strip}
