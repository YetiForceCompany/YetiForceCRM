{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Base-DashBoard-SettingsShortCutsContainer -->
	<div class="js-shortcuts pt-0 px-0 pb-3 d-flex flex-wrap" data-js="container">
		{foreach item=SETTINGS_SHORTCUT from=$SETTINGS_SHORTCUTS}
			{include file=\App\Layout::getTemplatePath('DashBoard/SettingsShortCut.tpl', $QUALIFIED_MODULE)}
		{/foreach}
	</div>
	<!-- /tpl-Settings-Base-DashBoard-SettingsShortCutsContainer -->
{/strip}
