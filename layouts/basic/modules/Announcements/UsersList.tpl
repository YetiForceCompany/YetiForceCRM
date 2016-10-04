{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="">
		<table class="table table-striped">
			<thead>
				<tr> 
					<th>{vtranslate('LBL_USER',$MODULE_NAME)}</th>
					<th class="text-center">{vtranslate('LBL_ACCEPT_ANNOUNCEMENT',$MODULE_NAME)}</th>
					<th class="text-center">{vtranslate('LBL_DATE',$MODULE_NAME)}</th>
				</tr> 
			</thead>
			<tbody>
				{foreach item=USER key=USERID from=$USERS}
					{assign var=STATUS value=isset($USER['status']) && $USER['status'] == 1}
					<tr data-id="{$USERID}" class="{if $STATUS}success{else}danger{/if}">
						<td>{$USER['name']}</td>
						<td class="text-center">
							{if $STATUS}
								<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
							{else}
								<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
							{/if}
						</td>
						<td class="text-center">
							{if isset($USER['date'])}
								{Vtiger_Util_Helper::formatDateTimeIntoDayString($USER['date'])}&nbsp;
								- {Vtiger_Util_Helper::formatDateDiffInStrings($USER['date'])}	
							{/if}
						</td>
					</tr> 
				{/foreach}
			</tbody> 
		</table>
	</div>
{/strip}
