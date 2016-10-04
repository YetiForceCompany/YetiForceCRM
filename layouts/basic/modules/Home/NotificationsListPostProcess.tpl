{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	</div>
	<div class="{if $LEFT_PANEL_HIDE}leftPanelOpen {/if}siteBarRight col-xs-12">
		<div class="btn btn-block toggleSiteBarRightButton hidden-xs hidden-sm" title="{vtranslate('LBL_RIGHT_PANEL_SHOW_HIDE', $MODULE)}">
			<span class="glyphicon glyphicon-chevron-right"></span>
		</div>
		<div class="siteBarContent paddingTop10">
			<h4>{vtranslate('LBL_TYPE_NOTIFICATIONS', $MODULE)}</h4>
			<hr>
			<input type="hidden" name="notificationTypes" value="{Vtiger_Util_Helper::toSafeHTML($NOTIFICATION_TYPES)}">
			<div id="jstreeContainer"></div>
		</div>
	</div>
{/strip}
