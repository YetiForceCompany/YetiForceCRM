{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if $ACCOUNTSLIST}
		{assign var="MAILS" value=OSSMail_Record_Model::getMailsFromIMAP($OWNER)}
		<div>
			{foreach from=$MAILS item=item key=key}
				<div class="row mailRow" data-mailId="{$key}">
					<div class="col-md-12 small">
						<div class="float-right muted">
							<span>{\App\Fields\DateTime::formatToViewDate($item->get('date'))}</span>
						</div>
						<h5>{\App\Purifier::encodeHtml($item->get('subject'))} {if count($item->get('attachments')) > 0}<img alt="{\App\Language::translate('LBL_ATTACHMENT')}" class="float-right" src="{\App\Layout::getLayoutFile('modules/OSSMailView/attachment.png')}" />{/if}</h5>
								</div>
								<div class="col-md-12">
									<div class="float-right" >
										<button class="badge badge-pill badge-light mb-1 showMailBody" >
											<span class="body-icon fas fa-chevron-down"></span>
										</button>
									</div>
									<span class="float-left">{\App\Language::translate('From', 'OSSMailView')}: {\App\Purifier::encodeHtml($item->get('fromaddress'))}</span>
								</div>
								<div class="col-md-12 mailBody" style="display: none;border: 1px solid #ddd;">
									{\App\Purifier::purifyHtml($item->get('body'))}
								</div>
								</div>
								<hr/>
							{/foreach}
							</div>
						{else}
							<span class="noDataMsg" style="position: relative; top: 115px; left: 133px;">
								{\App\Language::translate('LBL_NOMAILSLIST', 'OSSMail')}
							</span>
						{/if}
						</div>
					{/strip}
