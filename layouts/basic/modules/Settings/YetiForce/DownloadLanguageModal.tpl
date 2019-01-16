{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-DownloadLanguageModal modal-body">
		<table class="table mb-0">
			{assign var="INDEX" value=0}

			{foreach key=FIELD_NAME item=FIELD_DETAILS from=$LANGUAGES}
				{if !$FIELD_DETAILS['exist']}
					<tr>
						<td {if $INDEX == 0} class="border-top-0"{/if}>
							<strong>{$FIELD_DETAILS['name']}</strong> [{$FIELD_NAME}]
						</td>
						<td {if $INDEX == 0} class="border-top-0"{/if}>
							<button class="js-download btn btn-sm btn-outline-success" data-prefix="{$FIELD_NAME}" data-js="click | data">
								<span class="fas fa-download fa-xs mr-1"></span>
								{\App\Language::translate('LBL_DOWNLOAD', 'Settings::YetiForce')}
							</button>
						</td>
					</tr>
					{assign var="INDEX" value=$INDEX + 1}
				{/if}
			{/foreach}
		</table>
	</div>
{/strip}
