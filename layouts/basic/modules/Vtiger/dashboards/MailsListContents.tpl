{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=MAILS value=OSSMail_Record_Model::getMailsFromIMAP($USER)}
	{if $MAILS}
		<div>
			{foreach from=$MAILS item=item key=key}
				<div class="form-row mailRow px-2" data-mailId="{$key}">
					<div class="d-flex col-12 mb-1 justify-content-center">
						<div class="firstLetter d-lg-block d-md-none d-sm-block d-none mr-2 u-box-shadow-light u-h-line-normal">
							<span>{$item->get('firstLetterBg')} </span>
						</div>
						<div class="col-11 px-0">
							<p class="mb-0 u-fs-15px u-lh-12 u-text-ellipsis">
								{\App\Language::translate('LBL_FROM', 'Settings:Mail')}: {\App\Purifier::encodeHtml($item->get('from_email'))}
							</p>
							<p class="mb-0 u-fs-15px u-lh-12 u-text-ellipsis">
								{\App\Language::translate('LBL_TO', 'Settings:Mail')}: {\App\Purifier::encodeHtml($item->get('to_email'))}
							</p>
							<p class="font-small mb-0 text-truncate mb-0 u-fs-15px u-lh-12 u-text-ellipsis">
							<div class="float-right muted">
								<span>{\App\Fields\DateTime::formatToViewDate($item->get('date'))}</span>
								<button class="btn btn-xs btn-outline-dark ml-2 showMailBody">
									<span class="body-icon fas fa-chevron-down"></span>
								</button>
							</div>
							{\App\Purifier::encodeHtml($item->get('subject'))}

							</p>

						</div>
					</div>
					<div class="col-md-12 mailBody my-2" style="display: none;border: 1px solid #ddd;">
						{\App\Purifier::purifyHtml($item->get('body'))}
					</div>
				</div>
				<hr />
			{/foreach}
		</div>
	{else}
		<span class="noDataMsg" style="position: relative; top: 115px; left: 133px;">
			{\App\Language::translate('LBL_NOMAILSLIST', 'OSSMail')}
		</span>
	{/if}
	</div>
{/strip}
