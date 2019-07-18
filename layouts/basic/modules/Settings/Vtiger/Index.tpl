{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Base-Index -->
	{if $WARNINGS}
		{include file=\App\Layout::getTemplatePath('DashBoard/SystemWarningAletrs.tpl', $QUALIFIED_MODULE)}
	{/if}
	<div class="settingsIndexPage mx-n2 h-100">
		<div class="container-fluid h-100 px-0">
			<div class="row no-gutters pr-0 h-100 mb-0">
				<div class="col-md d-flex flex-column">
					<h5 class="bg-primary text-white text-center font-weight-normal text-uppercase p-3 mb-0">
						{\App\Language::translate('LBL_PREMIUM_ZONE', $QUALIFIED_MODULE)}
					</h5>
					<div class="bg-light h-100">
						{include file=\App\Layout::getTemplatePath('DashBoard/PremiumZone.tpl', $QUALIFIED_MODULE)}
					</div>
				</div>
				<div class="col-md d-flex flex-column mx-md-2">
					<h5 class="bg-primary text-white text-center font-weight-normal text-uppercase p-3 mb-0">
						{\App\Language::translate('LBL_SYSTEM_MONITORING', $QUALIFIED_MODULE)}
					</h5>
					<div class="bg-light h-100">
						{include file=\App\Layout::getTemplatePath('DashBoard/SystemMonitoring.tpl', $QUALIFIED_MODULE)}
					</div>
				</div>
				<div class="col-md d-flex flex-column">
					<h5 class="bg-primary text-white text-center font-weight-normal text-uppercase p-3 mb-0">
						{\App\Language::translate('LBL_SETTINGS_SHORT_CUT', $QUALIFIED_MODULE)}
					</h5>
					<div class="bg-light h-100">
					{include file=\App\Layout::getTemplatePath('DashBoard/SettingsShortCutsContainer.tpl', $QUALIFIED_MODULE)}
					</div>
				</div>
			</div>
		</div>
	<!-- /tpl-Settings-Base-Index -->
{/strip}
