{*<!-- {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-ServiceContracts-SlaPolicyTemplate -->
	<div class="col-12">
		<table class="table js-sla-policy-template-table">
			<thead>
				<tr>
					<th></th>
					<th>{\App\Language::translate('LBL_POLICY_NAME', $MODULE_NAME)}</th>
					<th>{\App\Language::translate('LBL_OPERATIONAL_HOURS', $MODULE_NAME)}</th>
					<th>{\App\Language::translate('LBL_REACTION_TIME', $MODULE_NAME)}</th>
					<th>{\App\Language::translate('LBL_IDLE_TIME', $MODULE_NAME)}</th>
					<th>{\App\Language::translate('LBL_RESOLVE_TIME', $MODULE_NAME)}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=\App\Utils\ServiceContracts::getSlaPolicyByModule($TARGET_MODULE_ID) item=ROW key=key name=slaTemplates}
					<tr>
						<td><input type="radio" name="policy_id" value="{$ROW['id']}" {if $SELECTED_TEMPLATE === $ROW['id'] || (!$SELECTED_TEMPLATE && $smarty.foreach.slaTemplates.first)} checked="checked" {/if}></td>
						<td>{\App\Purifier::encodeHtml($ROW.name)}</td>
						<td>{\App\Purifier::encodeHtml($ROW.operational_hours)}</td>
						<td>{\App\Purifier::encodeHtml($ROW.reaction_time)}</td>
						<td>{\App\Purifier::encodeHtml($ROW.idle_time)}</td>
						<td>{\App\Purifier::encodeHtml($ROW.resolve_time)}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<!-- /tpl-ServiceContracts-SlaPolicyTemplate -->
{/strip}
