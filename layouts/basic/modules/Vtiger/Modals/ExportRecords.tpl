{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-ExportRecords -->
	<div class="modal-body mb-0">
		<form class="form-horizontal js-modal-form js-validate-form" data-js="container|validate">
			<input type="hidden" name="module" value="{$MODULE_NAME}" />
			<input type="hidden" name="action" value="ExportData" />
			<input type="hidden" name="quickExport" value="true" />
			<label class="col-form-label">
				{\App\Language::translate('LBL_EXPORT_TYPE', $MODULE_NAME)}:
			</label>
			<div class="col-sm-12 p-0">
				<select name="export_type" class="select2 form-control">
					{foreach from=$EXPORT_TYPE item=EXPORT_TYPE key=NAME }
						<option value="{$EXPORT_TYPE}">{\App\Language::translate($NAME,$MODULE_NAME)}</option>
					{/foreach}
				</select>
			</div>
			<div class="form-group">
				<label class=" col-form-label">{\App\Language::translate('LBL_CHOOSE_COLUMNS',$MODULE_NAME)}
					<span class="js-popover-tooltip ml-1" data-toggle="popover" data-placement="top"
						data-content="{\App\Language::translate('LBL_QUICK_EXPORT_INFO', $MODULE_NAME)}" data-js="popover">
						<span class="fas fa-info-circle"></span>
				</label>
				<div class="columnsSelectDiv col-md-12 p-0">
					<div>
						<select name="exportColumns[]"
							data-placeholder="{\App\Language::translate('LBL_ADD_MORE_COLUMNS',$MODULE_NAME)}"
							multiple="multiple" class="select2 form-control js-view-columns-select"
							data-select-cb="registerSelectSortable" id="viewColumnsSelect"
							data-js="appendTo | select2 | sortable">
							{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
								<optgroup label="{\App\Language::translate($BLOCK_LABEL, $MODULE_NAME)}">
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
										<option value="{$FIELD_MODEL->getCustomViewSelectColumnName()}"
											data-field-name="{$FIELD_NAME}" data-js="data-sort-index|data-field-name">
											{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
										</option>
									{/foreach}
								</optgroup>
							{/foreach}
							{foreach key=MODULE_KEY item=RECORD_STRUCTURE_FIELD from=$RECORD_STRUCTURE_RELATED_MODULES}
								{foreach key=RELATED_FIELD_NAME item=RECORD_STRUCTURE from=$RECORD_STRUCTURE_FIELD}
									{assign var=RELATED_FIELD_LABEL value=Vtiger_Field_Model::getInstance($RELATED_FIELD_NAME, Vtiger_Module_Model::getInstance($MODULE_NAME))->getFieldLabel()}
									{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
										<optgroup
											label="{\App\Language::translate($RELATED_FIELD_LABEL, $MODULE_NAME)}&nbsp;-&nbsp;{\App\Language::translate($MODULE_KEY, $MODULE_KEY)}&nbsp;-&nbsp;{\App\Language::translate($BLOCK_LABEL, $MODULE_KEY)}">
											{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}

												<option value="{$FIELD_MODEL->getCustomViewSelectColumnName($RELATED_FIELD_NAME)}"
													data-field-name="{$FIELD_NAME}" data-js="data-sort-index|data-field-name">
													{\App\Language::translate($RELATED_FIELD_LABEL, $MODULE_NAME)}
													&nbsp;-&nbsp;{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_KEY)}
												</option>
											{/foreach}
										</optgroup>
									{/foreach}
								{/foreach}
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Base-Modals-ExportRecords -->
{/strip}
