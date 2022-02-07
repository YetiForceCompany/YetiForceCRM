{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="">
		<table class="table table-striped">
			<thead>
				<tr>
					<th>{\App\Language::translate('LBL_USER',$MODULE_NAME)}</th>
					<th class="text-center">{\App\Language::translate('LBL_ACCEPT_ANNOUNCEMENT',$MODULE_NAME)}</th>
					<th class="text-center">{\App\Language::translate('LBL_DATE',$MODULE_NAME)}</th>
				</tr>
			</thead>
			<tbody>
				{foreach item=USER key=USERID from=$USERS}
					{assign var=STATUS value=isset($USER['status']) && $USER['status'] == 1}
					<tr data-id="{$USERID}" class="text-{if $STATUS}success{else}danger{/if}">
						<td>{$USER['name']}</td>
						<td class="text-center">
							{if $STATUS}
								<span class="fas fa-check"></span>
							{else}
								<span class="fas fa-times"></span>
							{/if}
						</td>
						<td class="text-center">
							{if isset($USER['date'])}
								{\App\Fields\DateTime::formatToViewDate($USER['date'])}
							{/if}
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{/strip}
