{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Base-DashBoard-PremiumZone">
		{assign var="QUALIFIED_MODULE" value='Settings:YetiForce'}
		{if isset($PRODUCTS_PARTNER)}
			<div class="d-flex flex-wrap mb-3">
				{foreach $PRODUCTS_PARTNER as $PRODUCT}
					<div class="dashboardWidget mt-3 mr-3 flex-grow-1 u-w-max-320px">
						{include file=\App\Layout::getTemplatePath('Shop/SmallProduct.tpl', 'Settings:YetiForce')}
					</div>
				{/foreach}
			</div>
		{/if}
		{if isset($PARTNERS)}
			<div class="d-flex flex-wrap">
				{foreach $PARTNERS as $PARTNER}
					<img src={$PARTNER['src']} alt="{$PARTNER['name']}" />
				{/foreach}
			</div>
		{/if}
	</div>
{/strip}
