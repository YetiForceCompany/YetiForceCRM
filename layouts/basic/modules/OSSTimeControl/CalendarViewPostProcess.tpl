{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
<div class="{if $USER_MODEL->get('leftpanelhide')}leftPanelOpen {/if}siteBarRight calendarRightPanel hideSiteBar col-xs-12" data-showPanel="{if !AppConfig::module($MODULE, 'SHOW_RIGHT_PANEL')}0{else}1{/if}" id="rightPanel">
	<div class="btn btn-block toggleSiteBarRightButton hideToggleSiteBarRightButton hidden-xs hidden-sm" title="{vtranslate('LBL_RIGHT_PANEL_SHOW_HIDE', $MODULE)}">
		<span class="glyphicon glyphicon-chevron-left"></span>
	</div>
	<div class="siteBarContent paddingTop10">
		<div class="alert alert-danger refreshHeader hide" role="alert">
			<div class="quickWidgetHeader calendarRightPanel clearfix">
				<div class="col-xs-12 paddingLRZero">
					<div class="col-lg-6 col-md-12 paddingLRZero pull-left">
						<h5 class="noSpaces">{vtranslate('LBL_INFO_REFRESH', $MODULE)}</h5>
					</div>
					<div class="col-lg-6 col-md-12 col-xs-5 paddingTop10-md paddingLRZero pull-right">
						<button name="drefresh" class="btn btn-danger btn-xs pull-left-md pull-right-lg pull-right-xs pull-right-sm refreshCalendar cursorPointer">
							<span class="glyphicon glyphicon-refresh icon-white" hspace="0" border="0" title="{vtranslate('LBL_REFRESH')}" alt="{vtranslate('LBL_REFRESH')}"></span>
							&nbsp;{vtranslate('LBL_REFRESH')}
						</button>
					</div>
				</div> 
			</div>
		</div>
		{foreach item=SIDEBARWIDGET key=index from=$QUICK_LINKS['SIDEBARWIDGET']}
			<div class="panel panel-primary quickWidget">
				<div class="panel-heading quickWidgetHeader calendarRightPanel clearfix">
					<h4 class="panel-title col-lg-6 col-md-12 col-xs-5 paddingLRZero pull-left" title="{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}">
						{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}
					</h4>
					<div class="col-lg-6 col-md-12 col-xs-5 paddingTop10-md paddingLRZero pull-right">
						<button class="selectAllBtn btn btn-default btn-xs  pull-left-md pull-right-lg pull-right-sm">
							<div class="selectAll hide">{vtranslate('LBL_SELECT_ALL', $MODULE)}</div>
							<div class="deselectAll">{vtranslate('LBL_DESELECT_ALL', $MODULE)}</div>
						</button>
					</div>
				</div>
				<div class="widgetContainer panel-collapse" id="{$MODULE}_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}" data-url="{$SIDEBARWIDGET->getUrl()}">
					<div class="panel-body">
						
					</div>
				</div>
			</div>
		{/foreach}
	</div>
</div>
</div>
</div>
</div>
{/strip}
