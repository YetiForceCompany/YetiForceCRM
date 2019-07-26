{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Base-Index -->
	{if $WARNINGS}
		{include file=\App\Layout::getTemplatePath('DashBoard/SystemWarningAletrs.tpl', $QUALIFIED_MODULE)}
	{/if}
	<div class="settingsIndexPage pt-2 h-100">
		<div class="container-fluid h-100 px-0">
			<div class="c-panel c-panel--collapsible">
				<div class="c-panel__header" id="marketplace" data-toggle="collapse" data-target="#marketplace-collapse" aria-expanded="true" aria-controls="marketplace-collapse">
					<span class="fas fa-angle-up m-2" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					<span class="fas fa-angle-down m-2" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}"></span>
						<h5>
							<a class="text-decoration-none" href="index.php?module=YetiForce&parent=Settings&view=Shop">
								{\App\Language::translate('LBL_SHOP_MARKETPLACE', $QUALIFIED_MODULE)}
							</a>
						</h5>
				</div>
				<div id="marketplace-collapse" class="collapse multi-collapse show" aria-labelledby="marketplace">
					<div class="c-panel__body js-products-container">
								{include file=\App\Layout::getTemplatePath('DashBoard/PremiumZone.tpl', $QUALIFIED_MODULE)}
								{include file=\App\Layout::getTemplatePath('DashBoard/PartnerZone.tpl', $QUALIFIED_MODULE)}
					</div>
				</div>
			</div>
			<div class="c-panel c-panel--collapsible">
				<div class="c-panel__header" id="system-monitoring" data-toggle="collapse" data-target="#system-monitoring-collapse" aria-expanded="true" aria-controls="system-monitoring-collapse">
					<span class="fas fa-angle-up m-2" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					<span class="fas fa-angle-down m-2" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}"></span>
					<h5>
						{\App\Language::translate('LBL_SYSTEM_MONITORING', $QUALIFIED_MODULE)}
					</h5>
				</div>
				<div id="system-monitoring-collapse" class="collapse multi-collapse show" aria-labelledby="system-monitoring">
					<div class="c-panel__body">
								{include file=\App\Layout::getTemplatePath('DashBoard/SystemMonitoring.tpl', $QUALIFIED_MODULE)}
					</div>
				</div>
			</div>
			<div class="c-panel c-panel--collapsible">
				<div class="c-panel__header" id="my-shortcuts" data-toggle="collapse" data-target="#my-shortcuts-collapse" aria-expanded="true" aria-controls="my-shortcuts-collapse">
					<span class="fas fa-angle-up m-2" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					<span class="fas fa-angle-down m-2" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}"></span>
					<h5>
						{\App\Language::translate('LBL_SETTINGS_SHORT_CUT', $QUALIFIED_MODULE)}
					</h5>
				</div>
				<div id="my-shortcuts-collapse" class="collapse multi-collapse show" aria-labelledby="my-shortcuts">
					<div class="c-panel__body">
						{include file=\App\Layout::getTemplatePath('DashBoard/SettingsShortCutsContainer.tpl', $QUALIFIED_MODULE)}
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-Base-Index -->
{/strip}
