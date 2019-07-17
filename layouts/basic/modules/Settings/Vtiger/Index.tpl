{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Base-Index -->
	{*<div class="widget_header row ">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="row m-0 mt-2">
		<ul class="nav nav-tabs massEditTabs js-tabs mx-0" data-js="click">

			<li class="nav-item" data-mode="index">
				<a class="active nav-link" href="#" data-toggle="tab">
					<span class="fas fa-home fa-fw mr-1"></span>
					{\App\Language::translate('LBL_START', $QUALIFIED_MODULE)}
				</a>
			</li>

		</ul>
	</div>
	<div class="indexContainer"></div>*}

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
