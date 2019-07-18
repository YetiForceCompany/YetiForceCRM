{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Base-DashBoard-SettingsShortCutsContainer px-3 pt-3 u-columns-width-200px-rem u-columns-count-5"  id="settingsShortCutsContainer">
		{assign var=SPAN_COUNT value=1}
			{foreach item=SETTINGS_SHORTCUT from=$SETTINGS_SHORTCUTS name=shortcuts}
				{include file=\App\Layout::getTemplatePath('DashBoard/SettingsShortCut.tpl', $QUALIFIED_MODULE)}
				{if $SPAN_COUNT==3}
					{$SPAN_COUNT=1} {continue}
				{/if}
					{$SPAN_COUNT=$SPAN_COUNT+1}
			{/foreach}
	</div>
{/strip}
