{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<form id="pdfExportModal" action="index.php?module={$MODULE_NAME}&action=PDF&mode=generate" target="_blank" method="POST">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_GENERATE_PDF_FILE', $MODULE_NAME)}</h3>
	</div>
	<div class="modal-body">
		<input type="hidden" name="all_records" id="all_records" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($ALL_RECORDS))}" />
		<input type="hidden" name="selectedRecords" value="[]" />
		<input type="hidden" name="validRecords" value="[]" />
		<input type="hidden" name="template" value="[]" />
		<input type="hidden" name="single_pdf" value="0" />
		<input type="hidden" name="email_pdf" value="0" />
		{foreach from=$EXPORT_VARS key=INDEX item=VALUE}
			<input type="hidden" name="{$INDEX}" value="{$VALUE}" />
		{/foreach}
		<div class="panel panel-default">
			<div class="panel-heading"><strong>{vtranslate('LBL_AVAILABLE_TEMPLATES', $MODULE_NAME)}</strong></div>
			<div class="panel-body">
				{foreach from=$TEMPLATES item=TEMPLATE}
					<div class="form-group row form-horizontal">
						<label class="col-sm-6 control-label" for="pdfTpl{$TEMPLATE->getId()}">
							{$TEMPLATE->get('primary_name')}<br />
							<span class="secondaryName">{$TEMPLATE->get('secondary_name')}</span>
						</label>
						<div class="col-sm-6 control-group">
							<input type="checkbox" id="pdfTpl{$TEMPLATE->getId()}" name="pdf_template[]" class="checkbox" value="{$TEMPLATE->getId()}" {if $TEMPLATE->get('default') eq 1}checked="checked"{/if} />
						</div>
					</div>
				{/foreach}
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<div class="btn-group">
			<button id="generate_pdf" type="submit" class="btn btn-success">{vtranslate('LBL_GENERATE', $MODULE_NAME)}</button>
			<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<span class="caret"></span>
				<span class="sr-only">Toggle Dropdown</span>
			</button>
			<ul class="dropdown-menu">
				<li>
					<a href="#" id="single_pdf">
						{vtranslate('LBL_GENERATE_SINGLE', $MODULE_NAME)}
					</a>
				</li>
				<li>
					<a href="#" id="email_pdf">
						{vtranslate('LBL_SEND_EMAIL', $MODULE_NAME)}
					</a>
				</li>
			</ul>
		</div>&nbsp;
		<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{vtranslate('LBL_CANCEL', $MODULE_NAME)}</strong></button>
	</div>
	</form>
{/strip}
