{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<table class="table table-bordered table-condensed">
		<thead>
			<tr>
				<th>{\App\Language::translate('LBL_MODULE_NAME', $MODULE_NAME)}</th>
				<th>{\App\Language::translate('LBL_QTY', $MODULE_NAME)}</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$RELATED_MODULES item=RELATED_MODULE}
				<tr>
					<td>{\App\Language::translate($RELATED_MODULE, $RELATED_MODULE)}</td>
					<td><span class="badge">{$COUNT_RECORDS[$RELATED_MODULE]}</span></td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/strip}

