{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Base-Index -->
	{if $WARNINGS}
		{include file=\App\Layout::getTemplatePath('DashBoard/SystemWarningAletrs.tpl', $QUALIFIED_MODULE)}
	{/if}
	{function WIDGET_TITLE CLASS='' TITLE=''}
		<h6 class="my-0 ellipsis-2-lines u-font-weight-600 u-font-size-14px {$CLASS}" title="{$TITLE}">{$TITLE}</h6>
	{/function}
	{function WIDGET_DESCRIPTION CLASS='' DESCRIPTION=''}
		<p class="font-small u-font-weight-450 ellipsis-2-lines mb-0 {$CLASS}" title="{$DESCRIPTION}">
		{$DESCRIPTION}
		</p>
	{/function}
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
