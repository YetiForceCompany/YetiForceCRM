{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-Shop">
		<div class="o-breadcrumb widget_header mb-2 d-flex flex-nowrap flex-md-wrap justify-content-between px-2 row pt-md-0 pt-1">
			<div class="o-breadcrumb__container">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="mt-3 mx-n2 js-products-container">
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
				<div class="o-shop__nav nav nav-under mx-3" role="tablist">
					<a class="o-shop__nav__item nav-item nav-link {if $TAB === 'Premium'} active{/if}" id="nav-premium-tab" data-toggle="tab" href="#nav-premium" role="tab" aria-controls="nav-premium" aria-selected="{$TAB === 'Premium'}">
						<span class="yfi yfi-for-admin"></span>
						{\App\Language::translate('LBL_PREMIUM_ZONE', $QUALIFIED_MODULE)}
					</a>
					<a class="o-shop__nav__item nav-item nav-link {if $TAB === 'Partner'} active{/if}" id="nav-partner-tab" data-toggle="tab" href="#nav-partner" role="tab" aria-controls="nav-partner" aria-selected="{$TAB === 'Partner'}" data-js="data">
						<span class="yfi yfi-for-partners"></span>
						{\App\Language::translate('LBL_PARTNER_ZONE', $QUALIFIED_MODULE)}
					</a>
					<div class="c-mds-input input-group ml-sm-auto h-100 u-max-w-250px">
						<input type="text" class="js-shop-search form-control form-control-sm u-max-w-250px ml-2 u-outline-none" aria-label="{\App\Language::translate('LBL_SEARCH_PLACEHOLDER', $QUALIFIED_MODULE)}" placeholder="{\App\Language::translate('LBL_SEARCH_PLACEHOLDER', $QUALIFIED_MODULE)}" aria-describedby="{\App\Language::translate('LBL_SEARCH_PLACEHOLDER', $QUALIFIED_MODULE)}">
						<div class="input-group-append pl-1 d-none d-xsm-flex align-items-center">
							<span class="fas fa-search fa-sm  " id="{\App\Language::translate('LBL_SEARCH_PLACEHOLDER', $QUALIFIED_MODULE)}"></span>
  						</div>
					</div>
				</div>
			</nav>
			<div class="tab-content d-flex justify-content-center">
				<div class="tab-pane fade{if $TAB === 'Premium'} show active{/if}" id="nav-premium" role="tabpanel" aria-labelledby="nav-premium-tab">
					<div class="d-flex flex-wrap mb-3 ml-3">
						{foreach $PRODUCTS_PREMIUM as $PRODUCT}
							{include file=\App\Layout::getTemplatePath('Shop/ProductPremium.tpl', $QUALIFIED_MODULE)}
						{/foreach}
					</div>
				</div>
				<div class="tab-pane fade js-department{if $TAB === 'Partner'} show active{/if}" data-department="Partner" id="nav-partner" role="tabpanel" aria-labelledby="nav-partner-tab">
					<div class="d-flex flex-wrap mb-3 ml-3 justify-content-center">
						{foreach $PRODUCTS_PARTNER as $PRODUCT}
							{include file=\App\Layout::getTemplatePath('Shop/ProductPartner.tpl', $QUALIFIED_MODULE)}
						{/foreach}
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
