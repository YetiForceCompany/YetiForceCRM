{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-Base-Index -->
{if $WARNINGS}
	{include file=\App\Layout::getTemplatePath('DashBoard/SystemWarningAletrs.tpl', $QUALIFIED_MODULE)}
{/if}
<div class="o-settings-dashboard js-dashboard-container pt-2 h-100" data-js="container">
	<div class="container-fluid h-100 px-0">
		<div class="c-panel c-panel--collapsible c-panel--white">
			<div class="c-panel__header collapsed" id="marketplace" data-toggle="collapse" data-target="#marketplace-collapse"
				aria-expanded="false" aria-controls="marketplace-collapse">
				<span class="mdi mdi-chevron-up mx-2 u-font-size-26"
					alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
				<div class="c-mds-input input-group">
					<input type="text" class="js-shop-search form-control form-control-sm u-max-w-250px ml-auto u-outline-none"
						aria-label="{\App\Language::translate('LBL_SEARCH_PLACEHOLDER', $QUALIFIED_MODULE)}"
						placeholder="{\App\Language::translate('LBL_SEARCH_PLACEHOLDER', $QUALIFIED_MODULE)}"
						aria-describedby="{\App\Language::translate('LBL_SEARCH_PLACEHOLDER', $QUALIFIED_MODULE)}">
					<div class="input-group-append pl-1 d-none d-xsm-flex align-items-center">
						<span class="fas fa-search fa-sm  "
							id="{\App\Language::translate('LBL_SEARCH_PLACEHOLDER', $QUALIFIED_MODULE)}"></span>
					</div>
				</div>
				<div class="c-panel__title">
					<span class="yfi yfi-marketplace"></span>
					<h5>
						{\App\Language::translate('LBL_SHOP_MARKETPLACE', $QUALIFIED_MODULE)}
					</h5>
				</div>
			</div>
			<div id="marketplace-collapse" class="js-collapse collapse multi-collapse" aria-labelledby="marketplace">
				<div class="c-panel__body px-3 js-products-container">
					<div class="c-text-divider mb-3">
						<hr class="c-text-divider__line u-text-gray" />
					</div>
					<nav>
						<div class="o-shop__nav nav nav-under mt-3" role="tablist">
							<a class="o-shop__nav__item nav-item nav-link active" id="nav-premium-tab" data-toggle="tab"
								href="#nav-premium" role="tab" aria-controls="nav-premium" aria-selected="true">
								<span class="yfi yfi-for-admin"></span>
								{\App\Language::translate('LBL_PREMIUM_ZONE', $QUALIFIED_MODULE)}
							</a>
							<a class="o-shop__nav__item nav-item nav-link" id="nav-partner-tab" data-toggle="tab" href="#nav-partner"
								role="tab" aria-controls="nav-partner" aria-selected="false" data-js="data">
								<span class="yfi yfi-for-partners"></span>
								{\App\Language::translate('LBL_PARTNER_ZONE', $QUALIFIED_MODULE)}
							</a>
						</div>
					</nav>
					<div class="tab-content">
						<div class="tab-pane fade show active" id="nav-premium" role="tabpanel" aria-labelledby="nav-premium-tab">
							<div class="alert alert-info alert-dismissible fade show mt-3 mb-0" role="alert">
								{\App\Language::translate('LBL_SHOP_INFORMATION','Settings::YetiForce')}
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							{include file=\App\Layout::getTemplatePath('DashBoard/PremiumZone.tpl', $QUALIFIED_MODULE)}
						</div>
						<div class="tab-pane fade js-department" data-department="Partner" id="nav-partner" role="tabpanel"
							aria-labelledby="nav-partner-tab">
							{include file=\App\Layout::getTemplatePath('DashBoard/PartnerZone.tpl', $QUALIFIED_MODULE)}
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="c-panel c-panel--collapsible c-panel--white">
			<div class="c-panel__header collapsed" id="system-monitoring" data-toggle="collapse"
				data-target="#system-monitoring-collapse" aria-expanded="false" aria-controls="system-monitoring-collapse">
				<span class="mdi mdi-chevron-up mx-2 u-font-size-26"
					alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
				<div class="c-panel__title">
					<span class="yfi yfi-system-monitoring"></span>
					<h5>
						{\App\Language::translate('LBL_SYSTEM_MONITORING', $QUALIFIED_MODULE)}
					</h5>
				</div>
			</div>
			<div id="system-monitoring-collapse" class="js-collapse collapse multi-collapse"
				aria-labelledby="system-monitoring">
				<div class="c-panel__body px-3">
					<div class="c-text-divider mb-3">
						<hr class="c-text-divider__line u-text-gray" />
					</div>
					{include file=\App\Layout::getTemplatePath('DashBoard/SystemMonitoring.tpl', $QUALIFIED_MODULE)}
				</div>
			</div>
		</div>
		<div class="c-panel c-panel--collapsible c-panel--white">
			<div class="c-panel__header collapsed" id="my-shortcuts" data-toggle="collapse"
				data-target="#my-shortcuts-collapse" aria-expanded="false" aria-controls="my-shortcuts-collapse">
				<span class="mdi mdi-chevron-up mx-2 u-font-size-26"
					alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
				<div class="c-panel__title">
					<span class="yfi yfi-my-shortcuts"></span>
					<h5>
						{\App\Language::translate('LBL_SETTINGS_SHORT_CUT', $QUALIFIED_MODULE)}
					</h5>
				</div>
			</div>
			<div id="my-shortcuts-collapse" class="js-collapse collapse multi-collapse" aria-labelledby="my-shortcuts">
				<div class="c-panel__body px-3">
					<div class="c-text-divider mb-3">
						<hr class="c-text-divider__line u-text-gray" />
					</div>
					{include file=\App\Layout::getTemplatePath('DashBoard/SettingsShortCutsContainer.tpl', $QUALIFIED_MODULE)}
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /tpl-Settings-Base-Index -->
{/strip}
