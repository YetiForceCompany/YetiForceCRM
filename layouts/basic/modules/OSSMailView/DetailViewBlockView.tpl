{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE name=block}
		{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
		{if $BLOCK eq null or $FIELD_MODEL_LIST|@count lte 0}{continue}{/if}
		{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
		{assign var=IS_DYNAMIC value=$BLOCK->isDynamic()}
		{assign var=BLOCK_ICON value=$BLOCK->get('icon')}
		<div class="detailViewTable">
			<div class="c-panel js-toggle-panel" data-js="click|data-dynamic" {if $IS_DYNAMIC} data-dynamic="true" {/if} data-label="{$BLOCK_LABEL_KEY}">
				<div class="blockHeader c-panel__header">
					<div class="d-flex">
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-right m-2 {if !($IS_HIDDEN)}d-none{/if}"
							data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"
							data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}></span>
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-down m-2 {if $IS_HIDDEN}d-none{/if}"
							data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}"
							data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}></span>
						<h5>{if !empty($BLOCK_ICON)}<span class="{$BLOCK_ICON} mr-2"></span>{/if}{\App\Language::translate({$BLOCK_LABEL_KEY},{$MODULE_NAME})}</h5>
					</div>
				</div>
				<div class="c-panel__body blockContent {if $IS_HIDDEN} d-none{/if}">
					{assign var=COUNTER value=0}
					<div class="form-row px-0 fieldRow">
						{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
							{if !$FIELD_MODEL->isViewableInDetailView()}
								{continue}
							{/if}
							{if $FIELD_MODEL->getUIType() eq "20" or $FIELD_MODEL->getUIType() eq "19"}
								{if $COUNTER eq '1'}
									{assign var=COUNTER value=0}
								{/if}
							{/if}
							{if $COUNTER eq 2}
							</div>
							<div class="form-row px-0 fieldRow">
								{assign var=COUNTER value=1}
							{else}
								{assign var=COUNTER value=$COUNTER+1}
							{/if}
							<div class="col-md-6 col-12 fieldsLabelValue px-0">
								<div class="form-row">
									<div class="fieldLabel col-sm-5 col-12 {$WIDTHTYPE}"
										id="{$MODULE}_detailView_fieldLabel_{$FIELD_MODEL->getName()}">
										<label class="muted float-left float-sm-right float-md-right float-lg-right">
											{\App\Language::translate({$FIELD_MODEL->getFieldLabel()},{$MODULE_NAME})}
										</label>
									</div>
									<div class="fieldValue col-sm-7 col-12 {$WIDTHTYPE}"
										id="{$MODULE}_detailView_fieldValue_{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->getUIType() eq '19' or $FIELD_MODEL->getUIType() eq '20'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
										<span class="value" data-field-type="{$FIELD_MODEL->getFieldDataType()}">
											{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD SOURCE_TPL='BlockView'}
										</span>
										{if $IS_AJAX_ENABLED && $FIELD_MODEL->isEditable() eq 'true' && ($FIELD_MODEL->getFieldDataType()!=Vtiger_Field_Model::REFERENCE_TYPE) && $FIELD_MODEL->isAjaxEditable() eq 'true'}
											<span class="d-none edit">
												{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
												{if $FIELD_MODEL->getFieldDataType() eq 'multipicklist'}
													<input type="hidden" class="fieldname"
														value='{$FIELD_MODEL->getName()}[]'
														data-prev-value='{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}' />
												{else}
													<input type="hidden" class="fieldname" value='{$FIELD_MODEL->getName()}'
														data-prev-value='{\App\Purifier::encodeHtml($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}' />
												{/if}
											</span>
										{/if}
									</div>
								</div>
							</div>
						{/foreach}
					</div>
				</div>
			</div>
		</div>
	{/foreach}
{/strip}
