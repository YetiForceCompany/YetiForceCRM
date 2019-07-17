{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Base-Index -->
	{if $WARNINGS}
		{include file=\App\Layout::getTemplatePath('IndexView/SystemWarningAletrs.tpl', $QUALIFIED_MODULE)}
	{/if}
	<div class="settingsIndexPage">
		<div class="container-fluid">
			<div class="row">
				<div class="col-sm bg-primary text-white m-1">
					{\App\Language::translate('LBL_PREMIUM_ZONE', $QUALIFIED_MODULE)}
				</div>
				<div class="col-sm bg-primary text-white m-1">
					{\App\Language::translate('LBL_SYSTEM_MONITORING', $QUALIFIED_MODULE)}
				</div>
				<div class="col-sm bg-primary text-white m-1">
					{\App\Language::translate('LBL_SETTINGS_SHORT_CUT', $QUALIFIED_MODULE)}
				</div>
			</div>
			<div class="row">
				<div class="col-sm bg-light m-1">
					{include file=\App\Layout::getTemplatePath('IndexView/PremiumZone.tpl', $QUALIFIED_MODULE)}
				</div>
				<div class="col-sm bg-light m-1">
					{include file=\App\Layout::getTemplatePath('IndexView/SystemMonitoring.tpl', $QUALIFIED_MODULE)}
				</div>
				<div class="col-sm bg-light m-1">
					{include file=\App\Layout::getTemplatePath('IndexView/SettingsShortCutsContainer.tpl', $QUALIFIED_MODULE)}
				</div>
			</div>
		</div>
	<!-- /tpl-Settings-Base-Index -->
{/strip}
