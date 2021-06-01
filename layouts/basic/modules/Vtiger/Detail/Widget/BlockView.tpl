{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-BlockView -->
	{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
		{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
		{if $BLOCK eq null or $FIELD_MODEL_LIST|@count lte 0}
			{continue}
		{/if}
		{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
		{assign var=IS_DYNAMIC value=$BLOCK->isDynamic()}
		{assign var=BLOCK_ICON value=$BLOCK->get('icon')}
		<div class="table-responsive-sm">
			<div class="js-toggle-panel c-panel"
				 data-js="click|data-dynamic" {if $IS_DYNAMIC} data-dynamic="true"{/if}
					data-label="{$BLOCK_LABEL_KEY}">
				<div class="blockHeader c-panel__header py-0">
					<div class="mx-2 my-1">
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-right {if !($IS_HIDDEN)}d-none{/if}"
							  data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"
							  data-id="{$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}"></span>
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-down {if $IS_HIDDEN}d-none{/if}"
							  data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}"
						 	  data-mode="show" data-id="{$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}"></span>
					</div>
					<h5 class="mb-sm-1 mb-0">{if !empty($BLOCK_ICON)}<span class="{$BLOCK_ICON} mr-2"></span>{/if}{\App\Language::translate($BLOCK_LABEL_KEY,$MODULE_NAME)}</h5>
				</div>
				<div class="c-detail-widget__content js-detail-widget-collapse blockContent table-responsive-sm {if $IS_HIDDEN}d-none{/if} js-detail-widget-content py-sm-2 py-0 overflow-hidden" data-js="container|value">
					<div class="c-detail-widget__table">
						{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
							{if !$FIELD_MODEL->isViewableInDetailView()}
								{continue}
							{/if}
							<div class="form-row c-table__row--hover border-bottom py-0 py-sm-1 u-fs-13px c-detail-widget__mobile-line ">
								<div class="col-5 medium d-flex align-items-center"
									 id="{$MODULE_NAME}_detailView_fieldLabel_{$FIELD_MODEL->getName()}">
									{assign var=HELPINFO_LABEL value=\App\Language::getTranslateHelpInfo($FIELD_MODEL,$VIEW)}
									<div class="font-weight-bold text-truncate mb-1 mt-1"
										 title="{\App\Language::translate({$FIELD_MODEL->getFieldLabel()},{$MODULE_NAME})}">
										{\App\Language::translate({$FIELD_MODEL->getFieldLabel()},{$MODULE_NAME})}
										{if $HELPINFO_LABEL}
											<a href="#" class="js-help-info float-right u-cursor-pointer"
											   title=""
											   data-placement="top"
											   data-content="{$HELPINFO_LABEL}"
											   data-original-title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}">
												<span class="fas fa-info-circle"></span>
											</a>
										{/if}
									</div>
								</div>
								<div class="col-7 fieldValue medium"
									 id="{$MODULE_NAME}_detailView_fieldValue_{$FIELD_MODEL->getName()}">
									<div class="row">
										<div class="value col-9 mt-1 mb-1">
											<span class=""
												  data-field-type="{$FIELD_MODEL->getFieldDataType()}" {if $FIELD_MODEL->getUIType() eq '19' or $FIELD_MODEL->getUIType() eq '20' or $FIELD_MODEL->getUIType() eq '21' or $FIELD_MODEL->getUIType() eq '300'} style="white-space:normal;" {/if}>
												{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD SOURCE_TPL='BlockViewWidget'}
											</span>
											{assign var=EDIT value=false}
											{if in_array($FIELD_MODEL->getName(),['date_start','due_date']) && $MODULE_NAME eq 'Calendar'}
												{assign var=EDIT value=true}
											{/if}
										</div>
										{if $IS_AJAX_ENABLED && $FIELD_MODEL->isEditable() eq 'true' && $FIELD_MODEL->isAjaxEditable() eq 'true' && !$EDIT}
											<div class="c-table__action--hover js-detail-quick-edit col-3 u-cursor-pointer align-items-center justify-content-end pl-4">
												<button type="button" class="btn btn-sm btn-light float-right">
													<span class="yfi yfi-full-editing-view mt-1" title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}"></span>
												</button>
											</div>
											<div class="d-none edit col-12">
												{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
												{if $FIELD_MODEL->getFieldDataType() eq 'boolean' || $FIELD_MODEL->getFieldDataType() eq 'picklist'}
													<input type="hidden" class="fieldname"
														   data-type="{$FIELD_MODEL->getFieldDataType()}"
														   value='{$FIELD_MODEL->getName()}'
														   data-prev-value='{\App\Purifier::encodeHtml($FIELD_MODEL->get('fieldvalue'))}'/>
												{else}
													{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}
													{if $FIELD_VALUE|is_array}
														{assign var=FIELD_VALUE value=\App\Json::encode($FIELD_VALUE)}
													{/if}
													<input type="hidden" class="fieldname"
														   value='{$FIELD_MODEL->getName()}'
														   data-type="{$FIELD_MODEL->getFieldDataType()}"
														   data-prev-value='{\App\Purifier::encodeHtml($FIELD_VALUE)}'/>
												{/if}
											</div>
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
	<!-- /tpl-Base-Detail-Widget-BlockView -->
{/strip}
