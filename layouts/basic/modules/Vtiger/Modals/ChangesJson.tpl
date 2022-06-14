{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-ChangesJson -->
	<div class="tpl-Modals-ChangesJson modal-body">
		{if !empty($MAPPING_RELATED_FIELD)}
			<input type="hidden" name="mappingRelatedField" value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}' />
		{/if}
		{if !empty($LIST_FILTER_FIELDS)}
			<input type="hidden" name="listFilterFields" value='{\App\Purifier::encodeHtml($LIST_FILTER_FIELDS)}' />
		{/if}
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<input type="hidden" class="js-edit-field-list" data-value="{\App\Purifier::encodeHtml(\App\Json::encode($EDIT_FIELD_DETAILS))}" />
		<form id="{\App\Layout::getUniqueId('ChangesJson')}" name="ChangesJson" method="post">
			<div class="modal-body">
				<ul class="nav nav-tabs">
					{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
						{if $BLOCK_FIELDS|@count gt 0}
							<li class="nav-item col-6 col-sm-4 p-0 text-center">
								<a class="nav-link{if $smarty.foreach.blockIterator.iteration eq 1} active{/if}"
									href="#block_{$smarty.foreach.blockIterator.iteration}"
									data-toggle="tab"><strong>{\App\Language::translate($BLOCK_LABEL, $MODULE)}</strong></a>
							</li>
						{/if}
					{/foreach}
				</ul>
				<div class="tab-content">
					{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
						{if $BLOCK_FIELDS|@count gt 0}
							{assign var=BLOCK_INDEX value=$smarty.foreach.blockIterator.iteration}
							<div class="tab-pane fade{if $BLOCK_INDEX eq 1} show active{/if}"
								id="block_{$BLOCK_INDEX}" role="tabpanel">
								<div class="p-3">
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
										{if $FIELD_MODEL->getUIType() neq 104 && $FIELD_MODEL->isEditable()}
											<div class="form-row mb-2 js-form-row-container" data-js="container">
												<div class="col-sm-6 col-lg-4">
													<div class="btn-group-toggle mt-1 w-100" data-toggle="buttons">
														<label class="btn btn-sm btn-outline-secondary w-100 text-right {if isset($FIELD_MODEL->fieldvalue)} active{/if}" id="block-{$BLOCK_INDEX}-{$FIELD_MODEL->getName()}-label">
															<input aria-pressed="false"
																autocomplete="off" type="checkbox"
																id="selectRow{$FIELD_MODEL->getName()}"
																title="{\App\Language::translate('LBL_SELECT_SINGLE_ROW')}"
																data-field-name="{$FIELD_MODEL->getName()}"
																class="js-changesjson-select" {if $FIELD_MODEL->isEditableReadOnly()} disabled{/if}
																{if isset($FIELD_MODEL->fieldvalue)} checked="checked" {/if}>&nbsp;
															{if $FIELD_MODEL->isMandatory() eq true}
																<span class="redColor">*</span>
															{/if}
															{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}:
														</label>
													</div>
												</div>
												<div class="col-sm-6 col-lg-8">
													<div class="fieldValue"
														id="block-{$BLOCK_INDEX}-{$FIELD_MODEL->getName()}-input">
														{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE) VIEW='' RECORD=null}
													</div>
												</div>
											</div>
										{/if}
									{/foreach}
								</div>
							</div>
						{/if}
					{/foreach}
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Base-Modals-ChangesJson -->
{/strip}
