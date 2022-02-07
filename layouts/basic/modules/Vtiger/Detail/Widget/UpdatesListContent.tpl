{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-UpdatesListContent -->
	<table class="table table-bordered mt-2">
		<thead>
			<tr>
				<th class="{$WIDTHTYPE}">{\App\Language::translate($FIELD_LABEL, $MODULE_NAME)}</th>
				<th class="{$WIDTHTYPE}">{\App\Language::translate('Modified Time', $MODULE_NAME)}</th>
				<th class="{$WIDTHTYPE}">{\App\Language::translate('LBL_DURATION_SUMMARY', $MODULE_NAME)}</th>
			</tr>
		</thead>
		<tbody>
			{foreach item=ROW from=$FIELD_HISTORY}
				<tr>
					<th class="{$WIDTHTYPE}" scope="row">{$ROW['value']}</th>
					<td class="{$WIDTHTYPE}"><span title="{\Vtiger_Util_Helper::formatDateDiffInStrings($ROW['date'])}">{\App\Fields\DateTime::formatToDisplay($ROW['date'])}</span></td>
					<td class="{$WIDTHTYPE}">{App\Fields\RangeTime::displayElapseTime($ROW['time'],'i')}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	<!-- /tpl-Base-Detail-Widget-UpdatesListContent -->
{/strip}
