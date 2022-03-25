{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-YetiForce-Shop -->
	<div class="tpl-Settings-YetiForce-Shop">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="mt-2 mx-n2 js-products-container">
			{if !\App\YetiForce\Register::isRegistered()}
				<div class="col-md-12">
					<div class="alert alert-danger">
						<span class="yfi yfi-yeti-register-alert color-red-600 u-fs-5x mr-4 float-left"></span>
						<h1 class="alert-heading">{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_TITLE',$QUALIFIED_MODULE)}</h1>
						{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_DESC',$QUALIFIED_MODULE)}
					</div>
				</div>
			{else}
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
						<div class="js-popover-tooltip ml-sm-auto mr-2 d-inline mt-2" data-js="popover" data-content="{\App\Language::translate('LBL_MARKETPLACE_YETIFORCE_DESCRIPTION', $QUALIFIED_MODULE)}">
							<span class="fas fa-info-circle"></span>
						</div>
						<div class="c-mds-input input-group h-100 u-max-w-250px">
							<input type="text" class="js-shop-search form-control form-control-sm u-max-w-250px ml-2 u-outline-none" aria-label="{\App\Language::translate('LBL_SEARCH_PLACEHOLDER', $QUALIFIED_MODULE)}" placeholder="{\App\Language::translate('LBL_SEARCH_PLACEHOLDER', $QUALIFIED_MODULE)}" aria-describedby="{\App\Language::translate('LBL_SEARCH_PLACEHOLDER', $QUALIFIED_MODULE)}">
							<div class="input-group-append pl-1 d-none d-xsm-flex align-items-center">
								<span class="fas fa-search fa-sm " id="{\App\Language::translate('LBL_SEARCH_PLACEHOLDER', $QUALIFIED_MODULE)}"></span>
							</div>
						</div>
					</div>
				</nav>
				<div class="tab-content justify-content-center">
					<div class="tab-pane fade js-nav-premium {if $TAB === 'Premium'} show active{/if}" id="nav-premium" role="tabpanel" aria-labelledby="nav-premium-tab" data-js="container">
						<div class="mt-2 mx-3">
							<ul class="nav nav-tabs" role="tablist">
								{foreach \App\YetiForce\Shop::PRODUCT_CATEGORIES as $KEY => $ITEM}
									<li class="nav-item flex-sm-fill text-sm-center">
										<a class="nav-link js-select-category {if $KEY === $CATEGORY}active{/if}" role="button" data-toggle="tab" data-tab="{$KEY}" data-js="click">
											<span class="{$ITEM['icon']} mr-2"></span>{\App\Language::translate($ITEM['label'], $QUALIFIED_MODULE)}
										</a>
									</li>
								{/foreach}
							</ul>
						</div>
						<div class="d-flex flex-wrap mb-3 mx-3">
							{foreach $PRODUCTS_PREMIUM as $PRODUCT}
								{include file=\App\Layout::getTemplatePath('Shop/ProductPremium.tpl', $QUALIFIED_MODULE)}
							{/foreach}
						</div>
					</div>
					<div class="tab-pane fade js-department{if $TAB === 'Partner'} show active{/if}" data-department="Partner" id="nav-partner" role="tabpanel" aria-labelledby="nav-partner-tab">
						<div class="d-flex flex-wrap mb-3 mx-3 justify-content-center">
							{foreach $PRODUCTS_PARTNER as $PRODUCT}
								{include file=\App\Layout::getTemplatePath('Shop/ProductPartner.tpl', $QUALIFIED_MODULE)}
							{/foreach}
						</div>
					</div>
				</div>
			{/if}
		</div>
	</div>
	<!-- /tpl-Settings-YetiForce-Shop -->
{/strip}
