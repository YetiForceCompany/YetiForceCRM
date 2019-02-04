{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-DownloadLanguageModal modal-body">
		<table class="table mb-0 mx-auto u-w-fit">
			{assign var="INDEX" value=0}
			{if $LANGUAGES}
				{foreach key=PREFIX item=DETAILS from=$LANGUAGES}
					{if !$DETAILS['exist']}
						<tr>
							<td {if $INDEX == 0} class="border-top-0"{/if}>
								<strong>{\App\Purifier::encodeHtml($DETAILS['name'])} {\App\Purifier::encodeHtml($PREFIX)}</strong>
							</td>
							<td class="u-table-column-vw-2 u-table-column__before-block{if $INDEX == 0} border-top-0{/if}">
								<div class="progress">
									<div class="progress-bar" role="progressbar" style="width: {$DETAILS['progress']}%;"
										 aria-valuenow="{$DETAILS['progress']}" aria-valuemin="0" aria-valuemax="100">
										{$DETAILS['progress']}%
									</div>
								</div>
							</td>
							<td {if $INDEX == 0} class="border-top-0"{/if}>
								<button class="js-download btn btn-sm btn-outline-success"
										data-prefix="{\App\Purifier::encodeHtml($PREFIX)}" data-js="click | data">
									<span class="fas fa-download fa-xs mr-1"></span>
									{\App\Language::translate('LBL_DOWNLOAD', $QUALIFIED_MODULE)}
								</button>
							</td>
						</tr>
						{assign var="INDEX" value=$INDEX + 1}
					{/if}
				{/foreach}
				{if $INDEX == 0}
					<div class="alert alert-warning" role="alert">
						<div>
							<h5>
								<span class="fas fa-exclamation-circle mr-2"></span>{App\Language::translate('LBL_NO_LANGUAGES_TO_DOWNLOAD', $QUALIFIED_MODULE)}
							</h5>
						</div>
					</div>
				{/if}
			{else}
				<div class="alert alert-danger" role="alert">
					<div>
						<h5>
							<span class="fas fa-exclamation-circle mr-2"></span>{App\Language::translate('LBL_NO_INTERNET_CONNECTION', $QUALIFIED_MODULE)}
						</h5>
					</div>
				</div>
			{/if}
		</table>
	</div>
{/strip}
