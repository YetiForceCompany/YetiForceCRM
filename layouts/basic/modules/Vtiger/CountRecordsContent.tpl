{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<table class="table table-bordered table-condensed">
		<thead>
			<tr>
				<th>{vtranslate('LBL_MODULE_NAME', $MODULE_NAME)}</th>
				<th>{vtranslate('LBL_QTY', $MODULE_NAME)}</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$RELATED_MODULES item=RELATED_MODULE}
				<tr>
					<td>{vtranslate($RELATED_MODULE, $RELATED_MODULE)}</td>
					<td><span class="badge">{$COUNT_RECORDS[$RELATED_MODULE]}</span></td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/strip}

