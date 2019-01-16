{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-DownloadLanguageModal modal-body">
		<table class="table">
			{foreach key=FIELD_NAME item=FIELD_DETAILS from=$LANGUAGES}
				<tr>
					<td>{$FIELD_DETAILS['name']} [{$FIELD_NAME}]</td>
					<td>
						<button class="js-download btn btn-sm btn-outline-dark" data-prefix="{$FIELD_NAME}" data-js="click | data">
							{if $FIELD_DETAILS['exist']}
								<span class="fas fa-sync fa-xs mr-1"></span>
							{\App\Language::translate('LBL_UPDATE', 'Settings::YetiForce')}
							{else}
								<span class="fas fa-download fa-xs mr-1"></span>
								{\App\Language::translate('LBL_DOWNLOAD', 'Settings::YetiForce')}
							{/if}
						</button>
					</td>
				</tr>
			{/foreach}
		</table>
	</div>
{/strip}
