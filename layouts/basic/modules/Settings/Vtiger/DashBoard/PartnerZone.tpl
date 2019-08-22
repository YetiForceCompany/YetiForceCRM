{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Base-DashBoard-PremiumZone">
		{assign var="QUALIFIED_MODULE" value='Settings:YetiForce'}
		{if isset($PRODUCTS_PARTNER)}
			<div class="d-flex flex-wrap mb-3 justify-content-center">
				{foreach $PRODUCTS_PARTNER as $PRODUCT}
					{include file=\App\Layout::getTemplatePath('Shop/SmallProduct.tpl', 'Settings:YetiForce')}
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
