{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-Shop">
		<div class="o-breadcrumb widget_header mb-2 d-flex flex-nowrap flex-md-wrap justify-content-between px-2 row">
			<div class="o-breadcrumb__container">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="container mt-3 js-products-container">
			{if $STATUS}
				<div class="col-md-12">
					{if 'success'===$STATUS}
						<div class="alert alert-success">
							{\App\Language::translate('LBL_SUCCESSFUL_TRANSACTION', $QUALIFIED_MODULE)}
						</div>
					{else}
						<div class="alert alert-danger">
							{\App\Language::translate('LBL_FAILED_TRANSACTION', $QUALIFIED_MODULE)}
						</div>
					{/if}
				</div>
			{/if}
			<nav>
				<div class="o-shop__nav nav nav-under nav-fill mb-3" role="tablist">
					<a class="o-shop__nav__item nav-item nav-link{if $TAB === 'Premium'} active{/if}" id="nav-premium-tab" data-toggle="tab" href="#nav-premium" role="tab" aria-controls="nav-premium" aria-selected="{$TAB === 'Premium'}">
						<span class="yfi yfi-for-admin"></span>
						{\App\Language::translate('LBL_PREMIUM_ZONE', $QUALIFIED_MODULE)}
					</a>
					<a class="o-shop__nav__item nav-item nav-link{if $TAB === 'Partner'} active{/if}" id="nav-partner-tab" data-toggle="tab" href="#nav-partner" role="tab" aria-controls="nav-partner" aria-selected="{$TAB === 'Partner'}" data-js="data">
						<span class="yfi yfi-for-partners"></span>
						{\App\Language::translate('LBL_PARTNER_ZONE', $QUALIFIED_MODULE)}
					</a>
				</div>
			</nav>
			<div class="tab-content d-flex justify-content-center">
				<div class="tab-pane fade{if $TAB === 'Premium'} show active{/if}" id="nav-premium" role="tabpanel" aria-labelledby="nav-premium-tab">
					{foreach $PRODUCTS_PREMIUM as $PRODUCT}
						{include file=\App\Layout::getTemplatePath('Shop/ProductPremium.tpl', $QUALIFIED_MODULE)}
					{/foreach}
				</div>
				<div class="tab-pane fade js-department{if $TAB === 'Partner'} show active{/if}" data-department="Partner" id="nav-partner" role="tabpanel" aria-labelledby="nav-partner-tab">
					{foreach $PRODUCTS_PARTNER as $PRODUCT}
						{include file=\App\Layout::getTemplatePath('Shop/ProductPartner.tpl', $QUALIFIED_MODULE)}
					{/foreach}
				</div>
			</div>
		</div>
	</div>
{/strip}
