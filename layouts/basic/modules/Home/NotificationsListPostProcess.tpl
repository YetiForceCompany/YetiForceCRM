{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	</div>
	<div class="siteBarRight col-xs-12 hideSiteBar">
		<div class="btn btn-block toggleSiteBarRightButton hideToggleSiteBarRightButton hidden-xs hidden-sm" title="{vtranslate('LBL_RIGHT_PANEL_SHOW_HIDE', $MODULE)}">
			<span class="glyphicon glyphicon-chevron-left"></span>
		</div>
		<div class="siteBarContent paddingTop10">
			<h4>{vtranslate('LBL_TYPE_NOTIFICATIONS', $MODULE)}</h4>
			<hr>
			<input type="hidden" name="notificationTypes" value="{Vtiger_Util_Helper::toSafeHTML($NOTIFICATION_TYPES)}">
			<div id="jstreeContainer"></div>
		</div>
	</div>
{/strip}
