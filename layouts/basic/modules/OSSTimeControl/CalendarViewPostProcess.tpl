{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-CalendarViewPostProcess {if $USER_MODEL->get('leftpanelhide')} c-menu--open {/if} siteBarRight calendarRightPanel hideSiteBar col-12"
		 data-showPanel="{if !AppConfig::module($MODULE, 'SHOW_RIGHT_PANEL')}0{else}1{/if}" id="rightPanel">
		<div class="btn btn-block toggleSiteBarRightButton hideToggleSiteBarRightButton d-none d-lg-block"
			 title="{\App\Language::translate('LBL_RIGHT_PANEL_SHOW_HIDE', $MODULE)}">
			<span class="fas fa-chevron-left"></span>
		</div>
		<div class="siteBarContent pt-4">
			<div class="alert alert-danger refreshHeader d-none" role="alert">
				<div class="quickWidgetHeader">
					<div class="form-row align-items-center">
						<div class="o-label-container col-xl-8">
							<h5 class="m-0 p-0 text-center">{\App\Language::translate('LBL_INFO_REFRESH', $MODULE)}</h5>
						</div>
						<div class="o-btn-container col-xl-4">
							<button name="drefresh" class="btn btn-danger btn-sm refreshCalendar u-cursor-pointer">
								<span class="fas fa-sync-alt icon-white" hspace="0" border="0"
									  title="{\App\Language::translate('LBL_REFRESH')}"
									  alt="{\App\Language::translate('LBL_REFRESH')}"></span>
								&nbsp;{\App\Language::translate('LBL_REFRESH')}
							</button>
						</div>
					</div>
				</div>
			</div>
			{foreach item=SIDEBARWIDGET key=index from=$QUICK_LINKS['SIDEBARWIDGET']}
				<div class="js-toggle-panel c-panel border-info quickWidget" data-js="click">
					<div class="c-panel__header quickWidgetHeader bg-info">
						<div class="form-row align-items-center px-4">
							<h5 class="card-title h6 text-white o-label-container col-xl-5"
								title="{\App\Language::translate($SIDEBARWIDGET->getLabel(), $MODULE)}">
								{\App\Language::translate($SIDEBARWIDGET->getLabel(), $MODULE)}
							</h5>
							<div class="o-btn-container col-xl-7">
								<button class="selectAllBtn btn btn-light btn-sm">
									<div class="selectAll d-none">{\App\Language::translate('LBL_SELECT_ALL', $MODULE)}</div>
									<div class="deselectAll">{\App\Language::translate('LBL_DESELECT_ALL', $MODULE)}</div>
								</button>
							</div>
						</div>
					</div>
					<div class="widgetContainer panel-collapse {$SIDEBARWIDGET->get('linkclass')}"
						 id="{$MODULE}_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}"
						 data-url="{$SIDEBARWIDGET->getUrl()}">
						<div class="card-body">

						</div>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
{/strip}
