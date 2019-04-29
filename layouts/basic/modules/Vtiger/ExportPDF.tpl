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
			{function TEMPLATE_LIST STANDARD_TEMPLATES=[]}
				{foreach from=$STANDARD_TEMPLATES item=TEMPLATE}
					<div class="form-group row">
						<label class="col-sm-11 col-form-label text-left pt-0" for="pdfTpl{$TEMPLATE->getId()}">
							{\App\Language::translate($TEMPLATE->get('primary_name'), $MODULE_NAME)}
							<span class="secondaryName ml-2">[ {\App\Language::translate($TEMPLATE->get('secondary_name'), $MODULE_NAME)} ]</span>
						</label>
						<div class="col-sm-1">
							<input type="checkbox" id="pdfTpl{$TEMPLATE->getId()}" name="pdf_template[]" class="checkbox" value="{$TEMPLATE->getId()}" {if $TEMPLATE->get('default') eq 1}checked="checked"{/if} />
						</div>
					</div>
				{/foreach}
			{/function}
			{if $DYNAMIC_TEMPLATES}
				<ul class="nav nav-tabs" id="generate-pdf-tab" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="home-tab" data-toggle="tab" href="#standard" role="tab" aria-controls="standard" aria-selected="true"><span class="mr-2 js-popover-tooltip" data-js="popover" data-content="{\App\Language::translate('LBL_STANDARD_TEMPLATES_DESC',$MODULE_NAME)}"><span class="fas fa-info-circle"></span></span>{\App\Language::translate('LBL_STANDARD_TEMPLATES', $MODULE_NAME)}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="profile-tab" data-toggle="tab" href="#dynamic" role="tab" aria-controls="dynamic" aria-selected="false"><span class="mr-2 js-popover-tooltip" data-js="popover" data-content="{\App\Language::translate('LBL_DYNAMIC_TEMPLATES_DESC', $MODULE_NAME)}"><span class="fas fa-info-circle"></span></span>{\App\Language::translate('LBL_DYNAMIC_TEMPLATES', $MODULE_NAME)}</a>
					</li>
				</ul>
				<div class="tab-content p-3 border-left border-right border-bottom mb-3" id="generate-pdf-tab-content">
					<div class="tab-pane fade show active" id="standard" role="tabpanel" aria-labelledby="standard-tab">
						{TEMPLATE_LIST STANDARD_TEMPLATES=$STANDARD_TEMPLATES}
					</div>
					<div class="tab-pane fade" id="dynamic" role="tabpanel" aria-labelledby="dynamic-tab">
						{foreach from=$DYNAMIC_TEMPLATES item=TEMPLATE}
							<div class="dynamic-template-container" data-js="container">
								<div class="form-group row">
									<label class="col-sm-11 col-form-label text-left pt-0" for="pdfTpl{$TEMPLATE->getId()}">
										{\App\Language::translate($TEMPLATE->get('primary_name'), $MODULE_NAME)}
										<span class="secondaryName ml-2">[ {\App\Language::translate($TEMPLATE->get('secondary_name'), $MODULE_NAME)} ]</span>
									</label>
									<div class="col-sm-1">
										<input type="checkbox" id="pdfTpl{$TEMPLATE->getId()}" name="pdf_template[]" class="checkbox dynamic-template" data-dynamic="1" value="{$TEMPLATE->getId()}" {if $TEMPLATE->get('default') eq 1}checked="checked"{/if} data-js="change" />
									</div>
								</div>
								<h6 class="pt-4 border-top"><label><input type="checkbox" name="isCustomMode" class="mr-2 checkbox" value="1"{if !$CAN_CHANGE_SCHEME} disabled="disabled"{/if}>{\App\Language::translate('LBL_SELECT_COLUMNS',$MODULE_NAME)}</label></h6>
								<div class="form-group row">
									<div class="col">
										<select class="select2" name="inventoryColumns[]" multiple="multiple" data-select-cb="registerSelectSortable" disabled="disabled" data-js="select2 | sortable">
											{foreach from=$SELECTED_INVENTORY_COLUMNS item=$NAME}
												<option value="{$NAME}" selected="selected">{\App\Language::translate($ALL_INVENTORY_COLUMNS[$NAME], $MODULE_NAME)}</option>
											{/foreach}
										{foreach from=$ALL_INVENTORY_COLUMNS item=$LABEL key=$NAME}
											{if !in_array($NAME, $SELECTED_INVENTORY_COLUMNS)}
												<option value="{$NAME}">{\App\Language::translate($LABEL, $MODULE_NAME)}</option>
											{/if}
										{/foreach}
										</select>
									</div>
								</div>
								{if $CAN_CHANGE_SCHEME}
								<div class="row">
									<div class="col">
										<button class="btn btn-success js-save-scheme w-100" disabled="disabled" data-js="click"><span class="fas fa-save"></span> {\App\Language::translate('LBL_SAVE_SCHEME',$MODULE_NAME)}</button>
									</div>
								</div>
								{/if}
							</div>
						{/foreach}
					</div>
				</div>
			{else}
				<div class="card">
					<div class="card-header">{\App\Language::translate('LBL_AVAILABLE_TEMPLATES', $MODULE_NAME)}</div>
						<div class="card-body">
							{TEMPLATE_LIST STANDARD_TEMPLATES=$STANDARD_TEMPLATES}
						</div>
					</div>
				</div>
			{/if}
		<div class="modal-footer">
			<div class="btn-group mr-0">
				<button id="generate_pdf" type="submit" class="btn btn-success">
					<span class="fas fa-file-pdf mr-1"></span>{\App\Language::translate('LBL_GENERATE', $MODULE_NAME)}
				</button>
				{if count($ALL_RECORDS) > 1}
					<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown"
							aria-haspopup="true" aria-expanded="false">
					</button>
					<ul class="dropdown-menu">
						<li>
							<a class="dropdown-item" href="#" id="single_pdf">
								{\App\Language::translate('LBL_GENERATE_SINGLE', $MODULE_NAME)}
							</a>
						</li>
					</ul>
				{/if}
			</div>
			{if \App\Privilege::isPermitted('OSSMail')}
				<button id="email_pdf" type="submit" class="btn btn-info mr-0">
					<span class="fas fa-envelope mr-1"></span>{\App\Language::translate('LBL_SEND_EMAIL', $MODULE_NAME)}
				</button>
			{/if}
			<button class="btn btn-danger" type="reset" data-dismiss="modal"><span class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</button>
		</div>
	</form>
{/strip}
