{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Base-Index -->
	<div class="o-breadcrumb widget_header row">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
		</div>
	</div>
	{if $WARNINGS}
		{include file=\App\Layout::getTemplatePath('DashBoard/SystemWarningAletrs.tpl', $QUALIFIED_MODULE)}
	{/if}
	<div class="o-settings-dashboard js-dashboard-container pt-2 h-100" data-js="container">
		<div class="container-fluid h-100 px-0">
			{if $SYSTEM_MONITORING}
				<div class="c-panel c-panel--collapsible c-panel--white">
					<div class="c-panel__header collapsed" id="system-monitoring" data-toggle="collapse"
						data-target="#system-monitoring-collapse" aria-expanded="false" aria-controls="system-monitoring-collapse">
						<span class="mdi mdi-chevron-up mx-2 u-fs-26px"
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
			{/if}
			<div class="c-panel c-panel--collapsible c-panel--white">
				<div class="c-panel__header collapsed" id="my-shortcuts" data-toggle="collapse"
					data-target="#my-shortcuts-collapse" aria-expanded="false" aria-controls="my-shortcuts-collapse">
					<span class="mdi mdi-chevron-up mx-2 u-fs-26px"
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
