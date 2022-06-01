{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-YetiForce-DownloadLanguageModal -->
	<div class="modal-body p-0">
		<table class="table mb-0 mx-auto">
			{assign var="INDEX" value=0}
			{if \App\RequestUtil::isNetConnection()}
				<thead>
					<tr>
						<th class="text-center border-top-0">{App\Language::translate('LBL_LANG', $QUALIFIED_MODULE)}</th>
						<th class="text-center border-top-0">{App\Language::translate('LBL_Lang_prefix', 'Settings:LangManagement')}</th>
						<th class="text-center border-top-0">{App\Language::translate('LBL_TRANSLATED_WORDS', $QUALIFIED_MODULE)}</th>
						<th class="text-center border-top-0">{App\Language::translate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				{foreach key=PREFIX item=DETAILS from=$LANGUAGES}
					{if !\App\Installer\Languages::exists($PREFIX)}
						{assign var="NAME" value=\App\Language::getDisplayName($PREFIX)}
						<tr>
							<td class="align-middle{if $INDEX == 0} border-top-0{/if} u-white-space-normal u-white-space-lg-nowrap text-truncate" title="{\App\Purifier::encodeHtml($NAME)}">
								<strong>{\App\Purifier::encodeHtml(\App\TextUtils::textTruncate($NAME, 20))}</strong>
							</td>
							<td class="align-middle{if $INDEX == 0} border-top-0{/if} u-white-space-normal u-white-space-lg-nowrap">
								<strong>{\App\Purifier::encodeHtml($PREFIX)}</strong>
							</td>
							<td class="align-middle u-table-column__vw-20 u-table-column__before-block{if $INDEX == 0} border-top-0{/if} w-100">
								<div class="progress position-relative u-h-line-normal">
									<div class="progress-bar bg-color-blue-100" role="progressbar"
										style="width: {$DETAILS['progress']}%;"
										aria-valuenow="{$DETAILS['progress']}" aria-valuemin="0" aria-valuemax="100">
									</div>
									<div class="position-absolute w-100 text-center">{$DETAILS['progress']}%</div>
								</div>
							</td>
							<td class="align-middle {if $INDEX == 0} border-top-0{/if}">
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
	<!-- /tpl-Settings-YetiForce-DownloadLanguageModal -->
{/strip}
