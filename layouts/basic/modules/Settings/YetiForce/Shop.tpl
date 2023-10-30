{*<!-- {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
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
						<button class="btn btn-primary js-popover-tooltip mr-n1 js-refresh-status" type="button">
							<i class="fas fa-refresh mr-1"></i>
							{App\Language::translate('LBL_REFRESH', $QUALIFIED_MODULE)}
						</button>
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
					<div class="tab-pane fade js-nav-premium show active" id="nav-premium" role="tabpanel" aria-labelledby="nav-premium-tab" data-js="container">
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
				</div>
			{/if}
		</div>
	</div>
	<!-- /tpl-Settings-YetiForce-Shop -->
{/strip}
