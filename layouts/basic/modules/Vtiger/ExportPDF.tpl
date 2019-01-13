{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<form id="pdfExportModal" class="tpl-Vtiger-ExportPDF" action="index.php?module={$MODULE_NAME}&action=PDF&mode=generate" target="_blank" method="POST">
		<div class="modal-header">
			<h5 class="modal-title"><span class="fas fa-file-pdf mr-1"></span>{\App\Language::translate('LBL_GENERATE_PDF_FILE', $MODULE_NAME)}</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<input type="hidden" name="all_records" id="all_records" value="{\App\Purifier::encodeHtml(\App\Json::encode($ALL_RECORDS))}" />
			<input type="hidden" name="selectedRecords" value="[]" />
			<input type="hidden" name="validRecords" value="[]" />
			<input type="hidden" name="template" value="[]" />
			<input type="hidden" name="single_pdf" value="0" />
			<input type="hidden" name="email_pdf" value="0" />
			{foreach from=$EXPORT_VARS key=INDEX item=VALUE}
				<input type="hidden" name="{$INDEX}" value="{$VALUE}" />
			{/foreach}
			<div class="card">
				<div class="card-header"><strong>{\App\Language::translate('LBL_AVAILABLE_TEMPLATES', $MODULE_NAME)}</strong></div>
				<div class="card-body">
					{foreach from=$TEMPLATES item=TEMPLATE}
						<div class="form-group row">
							<label class="col-sm-11 col-form-label text-left pt-0" for="pdfTpl{$TEMPLATE->getId()}">
								{\App\Language::translate($TEMPLATE->get('primary_name'), $MODULE_NAME)} &nbsp;
								[<span class="secondaryName">{\App\Language::translate($TEMPLATE->get('secondary_name'), $MODULE_NAME)}</span>]
							</label>
							<div class="col-sm-1">
								<input type="checkbox" id="pdfTpl{$TEMPLATE->getId()}" name="pdf_template[]" class="checkbox" value="{$TEMPLATE->getId()}" {if $TEMPLATE->get('default') eq 1}checked="checked"{/if} />
							</div>
						</div>
					{/foreach}
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<div class="btn-group mr-0">
				<button id="generate_pdf" type="submit" class="btn btn-success"><span class="fas fa-file-pdf mr-1"></span>{\App\Language::translate('LBL_GENERATE', $MODULE_NAME)}</button>
				<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span class="caret"></span>
					<span class="sr-only">Toggle Dropdown</span>
				</button>
				<ul class="dropdown-menu">
					<li>
						<a class="dropdown-item" href="#" id="single_pdf">
							{\App\Language::translate('LBL_GENERATE_SINGLE', $MODULE_NAME)}
						</a>
					</li>
					{if \App\Privilege::isPermitted('OSSMail')}
						<li>
							<a class="dropdown-item" href="#" id="email_pdf">
								{\App\Language::translate('LBL_SEND_EMAIL', $MODULE_NAME)}
							</a>
						</li>
					{/if}
				</ul>
			</div>
			<button class="btn btn-danger" type="reset" data-dismiss="modal"><strong><span class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong></button>
		</div>
	</form>
{/strip}
