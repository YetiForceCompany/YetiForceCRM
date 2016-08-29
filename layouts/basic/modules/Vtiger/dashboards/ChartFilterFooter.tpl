{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	<div class="widgetFooterContent">
		<div class="row no-margin">
			<div class="col-md-4">
				<button class="btn btn-xs btn-default recordCount" data-url="{Vtiger_Util_Helper::toSafeHTML($CHART_MODEL->getGetTotalCountURL($OWNER))}">
					<span class="glyphicon glyphicon-equalizer" title="{vtranslate('LBL_WIDGET_FILTER_TOTAL_COUNT_INFO')}"></span>
					<a class="pull-left hide" href="{Vtiger_Util_Helper::toSafeHTML($CHART_MODEL->getListViewURL())}">
						<span class="count badge pull-left"></span>
					</a>
				</button>
			</div>
		</div>
	</div>
{/strip}
