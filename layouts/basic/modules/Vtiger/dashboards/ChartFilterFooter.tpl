{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<div class="widgetFooterContent">
		<div class="row no-margin">
			<div class="col-md-4">
				<button class="btn btn-xs btn-default recordCount" data-url="{Vtiger_Util_Helper::toSafeHTML($CHART_MODEL->getTotalCountURL($OWNER))}">
					<span class="glyphicon glyphicon-equalizer" title="{\App\Language::translate('LBL_WIDGET_FILTER_TOTAL_COUNT_INFO')}"></span>
					<a class="pull-left hide" href="{Vtiger_Util_Helper::toSafeHTML($CHART_MODEL->getListViewURL())}">
						<span class="count badge pull-left"></span>
					</a>
				</button>
			</div>
		</div>
	</div>
{/strip}
