{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Base-DashBoard-PremiumZone">
		{assign var="QUALIFIED_MODULE" value='Settings:YetiForce'}
		{if isset($PARTNER_PRODUCTS)}
			<div class="u-columns-width-200px-rem u-columns-count-5 px-3 pb-4">
				{foreach $PARTNER_PRODUCTS as $PRODUCT}
					<div class="dashboardWidget u-columns__item mb-n1 mt-3 d-inline-block">
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
