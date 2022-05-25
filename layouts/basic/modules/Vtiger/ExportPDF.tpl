{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-ExportPDF -->
	<form id="pdfExportModal" action="index.php" target="_blank" method="POST">
		<div class="modal-header">
			<h5 class="modal-title"><span class="fas fa-file-pdf mr-1"></span>{\App\Language::translate('LBL_GENERATE_PDF_FILE', $MODULE_NAME)}</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<input type="hidden" name="module" value="{$MODULE_NAME}" />
			<input type="hidden" name="action" value="PDF" />
			<input type="hidden" name="mode" value="generate" />
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			<input type="hidden" name="fromview" value="{$FROM_VIEW}" />
			<input type="hidden" name="viewname" value="{$VIEW_NAME}" />
			<input type="hidden" name="entityState" value="{\App\Purifier::encodeHtml($ENTITY_STATE)}" />
			{if isset($RELATED_MODULE)}
				<input type="hidden" name="relatedModule" value="{$RELATED_MODULE}" />
				<input type="hidden" name="relationId" value="{$RELATION_ID}" />
				<input type="hidden" name="cvId" value="{$CV_ID}" />
			{/if}
			<input type="hidden" name="search_key" value="{$SEARCH_KEY}" />
			<input type="hidden" name="operator" value="{$OPERATOR}" />
			<input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
			<input type="hidden" name="search_params" value="{\App\Purifier::encodeHtml(\App\Json::encode($SEARCH_PARAMS))}" />
			<input type="hidden" name="selected_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($SELECTED_IDS))}">
			<input type="hidden" name="excluded_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($EXCLUDED_IDS))}">
			<input type="hidden" name="orderby" value="{\App\Purifier::encodeHtml(\App\Json::encode($ORDER_BY))}" />
			<input type="hidden" name="advancedConditions" value="{\App\Purifier::encodeHtml(\App\Json::encode($ADVANCED_CONDITIONS))}" />
			<input type="hidden" name="single_pdf" value="0" />
			<input type="hidden" name="email_pdf" value="0" />
			<input type="hidden" name="isSortActive" value="1" />
			{function TEMPLATE_USER_VARIABLE}
				<div class="js-pdf-user-variable row col-12{if !$TEMPLATE->get('default')} d-none{/if}">
					{assign var=TEMPLATE_CONTENT value="{$TEMPLATE->getBody()}{$TEMPLATE->getHeader()}{$TEMPLATE->getFooter()}"}
					{assign var=TEMPLATE_USER_VARIABLES value=$TEMPLATE->getParser()->getUserVariables($TEMPLATE_CONTENT)}
					{if $TEMPLATE_USER_VARIABLES}
						{foreach from=$TEMPLATE_USER_VARIABLES item=USER_VARIABLE key=FIELD_NAME}
							<div class="col-md-6 mb-1">
								<input type="text" name="userVariables[{$TEMPLATE->getId()}][{\App\Purifier::encodeHtml($FIELD_NAME)}]"
									class="form-control form-control-sm"
									title="{\App\Language::translate($USER_VARIABLE['label'], $MODULE_NAME)}"
									placeholder="{\App\Language::translate($USER_VARIABLE['label'], $MODULE_NAME)}"
									value="{\App\Language::translate($USER_VARIABLE['default'], $MODULE_NAME)}" />
							</div>
						{/foreach}
					{/if}
				</div>
			{/function}
			{function TEMPLATE_LIST STANDARD_TEMPLATES=[]}
				{foreach from=$STANDARD_TEMPLATES item=TEMPLATE}
					<div class="js-pdf-template-content form-group row" data-js="container">
						<label class="col-sm-11 col-form-label text-left pt-0" for="pdfTpl{$TEMPLATE->getId()}">
							{\App\Language::translate($TEMPLATE->get('primary_name'), $MODULE_NAME)}
							<span class="secondaryName ml-2">[ {\App\Language::translate($TEMPLATE->get('secondary_name'), $MODULE_NAME)} ]</span>
						</label>
						<div class="col-sm-1">
							<input type="{$SELECT_MODE}" id="pdfTpl{$TEMPLATE->getId()}" name="pdf_template[]" class="checkbox" value="{$TEMPLATE->getId()}"
								{if $TEMPLATE->get('default') eq 1}checked="checked" {/if} />
						</div>
						{TEMPLATE_USER_VARIABLE}
					</div>
				{/foreach}
			{/function}
			{function TEMPLATE_LIST_DYNAMIC DYNAMIC_TEMPLATES=[]}
				{foreach from=$DYNAMIC_TEMPLATES item=TEMPLATE name=dynamicTemplates}
					<div class="dynamic-template-container" data-js="container">
						<div class="js-pdf-template-content form-group row" data-js="container">
							<label class="col-sm-11 col-form-label text-left pt-0" for="pdfTpl{$TEMPLATE->getId()}">
								{\App\Language::translate($TEMPLATE->get('primary_name'), $MODULE_NAME)}
								<span class="secondaryName ml-2">[ {\App\Language::translate($TEMPLATE->get('secondary_name'), $MODULE_NAME)} ]</span>
							</label>
							<div class="col-sm-1">
								<input type="{$SELECT_MODE}" id="pdfTpl{$TEMPLATE->getId()}" name="pdf_template[]" class="checkbox dynamic-template" data-dynamic="1" value="{$TEMPLATE->getId()}" {if $TEMPLATE->get('default') eq 1}checked="checked" {/if} data-js="change" />
							</div>
							{TEMPLATE_USER_VARIABLE}
						</div>
						{if $smarty.foreach.dynamicTemplates.last}
							<h6 class="pt-4 border-top"><label><input type="checkbox" name="isCustomMode" class="mr-2 checkbox" value="1" {if !$CAN_CHANGE_SCHEME} disabled="disabled" {/if}>{\App\Language::translate('LBL_SELECT_COLUMNS',$MODULE_NAME)}</label></h6>
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
						{/if}
					</div>
				{/foreach}
			{/function}
			{if $DYNAMIC_TEMPLATES && $STANDARD_TEMPLATES}
				<ul class="nav nav-tabs" id="generate-pdf-tab" role="tablist">
					<li class="nav-item">
						<a class="nav-link {if !$ACTIVE_DYNAMIC} active {/if}" id="home-tab" data-toggle="tab" href="#standard" role="tab" aria-controls="standard" aria-selected="true"><span class="mr-2 js-popover-tooltip" data-js="popover" data-content="{\App\Language::translate('LBL_STANDARD_TEMPLATES_DESC',$MODULE_NAME)}"><span class="fas fa-info-circle"></span></span>{\App\Language::translate('LBL_STANDARD_TEMPLATES', $MODULE_NAME)}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link {if $ACTIVE_DYNAMIC} active {/if}" id="profile-tab" data-toggle="tab" href="#dynamic" role="tab" aria-controls="dynamic" aria-selected="false"><span class="mr-2 js-popover-tooltip" data-js="popover" data-content="{\App\Language::translate('LBL_DYNAMIC_TEMPLATES_DESC', $MODULE_NAME)}"><span class="fas fa-info-circle"></span></span>{\App\Language::translate('LBL_DYNAMIC_TEMPLATES', $MODULE_NAME)}</a>
					</li>
				</ul>
				<div class="tab-content p-3 border-left border-right border-bottom mb-3" id="generate-pdf-tab-content">
					<div class="tab-pane fade {if !$ACTIVE_DYNAMIC} active show {/if} js-content-templates-standard" id="standard" role="tabpanel" aria-labelledby="standard-tab">
						{TEMPLATE_LIST STANDARD_TEMPLATES=$STANDARD_TEMPLATES}
					</div>
					<div class="tab-pane fade {if $ACTIVE_DYNAMIC} active show {/if} js-content-templates-dynamic" id="dynamic" role="tabpanel" aria-labelledby="dynamic-tab">
						{TEMPLATE_LIST_DYNAMIC DYNAMIC_TEMPLATES=$DYNAMIC_TEMPLATES}
					</div>
				</div>
			{else}
				<div class="card">
					<div class="card-header">{\App\Language::translate('LBL_AVAILABLE_TEMPLATES', $MODULE_NAME)}</div>
					<div class="card-body">
						{if $STANDARD_TEMPLATES}
							{TEMPLATE_LIST STANDARD_TEMPLATES=$STANDARD_TEMPLATES}
						{else}
							{TEMPLATE_LIST_DYNAMIC DYNAMIC_TEMPLATES=$DYNAMIC_TEMPLATES}
						{/if}
					</div>
				</div>
			</div>
		{/if}
		<span class="js-records-info pl-3 text-info d-none" data-js="text"></span>
		<div class="modal-footer">
			<div class="btn-group mr-0">
				<button id="generate_pdf" type="submit" class="btn btn-success js-submit-button" {if !$ACTIVE} disabled="disabled" {/if} data-js="click">
					<span class="fas fa-file-pdf mr-1"></span>{\App\Language::translate('LBL_GENERATE', $MODULE_NAME)}
				</button>
				<button type="button" class="btn btn-success dropdown-toggle js-submit-button" data-toggle="dropdown"
					aria-haspopup="true" aria-expanded="false" {if !$ACTIVE} disabled="disabled" {/if}>
				</button>
				<ul class="dropdown-menu">
					<li>
						<a class="dropdown-item" href="#" id="single_pdf">
							{\App\Language::translate('LBL_GENERATE_SINGLE', $MODULE_NAME)}
						</a>
					</li>
				</ul>
			</div>
			{if \App\Mail::checkInternalMailClient()}
				<button id="email_pdf" type="submit" class="btn btn-info mr-0 js-submit-button" {if !$ACTIVE} disabled="disabled" {/if}>
					<span class="fas fa-envelope mr-1"></span>{\App\Language::translate('LBL_SEND_EMAIL', $MODULE_NAME)}
				</button>
			{/if}
			<button class="btn btn-danger" type="reset" data-dismiss="modal"><span class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</button>
		</div>
	</form>
	<!-- /tpl-Base-ExportPDF -->
{/strip}
