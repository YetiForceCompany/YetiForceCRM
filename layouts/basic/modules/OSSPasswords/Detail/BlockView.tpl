{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
{if $BLOCK eq null or $FIELD_MODEL_LIST|@count lte 0}{continue}{/if}
{assign var=BLOCKS_HIDE value=$BLOCK->isHideBlock($RECORD,$VIEW)}
{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
{assign var=IS_DYNAMIC value=$BLOCK->isDynamic()}
{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
{if $BLOCKS_HIDE}
<div class="tpl-OSSPasswrds-DetailViewBlock detailViewTable">
	<div class="js-toggle-panel c-panel" data-js="click|data-dynamic" {if $IS_DYNAMIC} data-dynamic="true"{/if} data-label="{$BLOCK_LABEL}">
		<div class="blockHeader card-header px-0">
			<span class="u-cursor-pointer js-block-toggle fas fa-angle-right m-2 {if !($IS_HIDDEN)}d-none{/if}"
				  data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"
				  data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}></span>
			<span class="u-cursor-pointer js-block-toggle fas fa-angle-down m-2 {if $IS_HIDDEN}d-none{/if}"
				  data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show"
				  data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}></span>
			<h4>{\App\Language::translate({$BLOCK_LABEL_KEY},{$MODULE_NAME})}</h4>
		</div>
		<div class="col-md-12 card-body blockContent px-1 py-0 {if $IS_HIDDEN} d-none{/if}">
			{assign var=COUNTER value=0}
			<div class="form-row">
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
			<div class="px-0 form-row px-0 py-0">
				{assign var=COUNTER value=1}
				{else}
				{assign var=COUNTER value=$COUNTER+1}
				{/if}
				<div class="col-sm-6">
					<div class="form-row">
						<div class="fieldLabel border-top border-left col-sm-6  {$WIDTHTYPE}"
							 id="{$MODULE}_detailView_fieldLabel_{$FIELD_MODEL->getName()}">
							<label class="muted small font-weight-bold float-sm-left float-md-right float-lg-right">
								{\App\Language::translate({$FIELD_MODEL->getFieldLabel()},{$MODULE_NAME})}
							</label>
						</div>
						<div class="fieldValue  border-top border-left col-sm-6  {$WIDTHTYPE}"
							 id="{$MODULE}_detailView_fieldValue_{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->getUIType() eq '19' or $FIELD_MODEL->getUIType() eq '20'} {assign var=COUNTER value=$COUNTER+1} {/if} {if $FIELD_MODEL->getName() eq 'password'}onclick="PasswordHelper.showPasswordQuickEdit('{$smarty.get.record}');" {/if}>
											<span class="value" data-field-type="{$FIELD_MODEL->getFieldDataType()}"
												  {if $FIELD_MODEL->getName() eq 'password'}id="detailPassword" {/if}>
												{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
											</span>
							{if $FIELD_MODEL->isEditable() eq 'true' && ($FIELD_MODEL->getFieldDataType()!=Vtiger_Field_Model::REFERENCE_TYPE) && $IS_AJAX_ENABLED && $FIELD_MODEL->isAjaxEditable() eq 'true'}
								<span class="d-none edit">
													{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
									{if $FIELD_MODEL->getFieldDataType() eq 'boolean' || $FIELD_MODEL->getFieldDataType() eq 'picklist'}
										<input type="hidden" class="fieldname"
											   data-type="{$FIELD_MODEL->getFieldDataType()}"
											   value='{$FIELD_MODEL->getName()}'
											   data-prev-value='{$FIELD_MODEL->get('fieldvalue')}'/>


{else}
														{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}
														{if $FIELD_VALUE|is_array}
										{assign var=FIELD_VALUE value=\App\Json::encode($FIELD_VALUE)}
									{/if}


										<input type="hidden" class="fieldname" value='{$FIELD_MODEL->getName()}'
											   data-type="{$FIELD_MODEL->getFieldDataType()}"
											   data-prev-value='{\App\Purifier::encodeHtml($FIELD_VALUE)}'/>
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
	{/if}
	{/foreach}
	<div class="contentHeader form-row m-0">
		<div class="col-12 px-0">
			<div class="float-right">
				<button class="btn btn-success d-none" data-copy-target="detailPassword" id="copy-button" type="button"
						title="{\App\Language::translate('LBL_CopyToClipboardTitle', $MODULE_NAME)}"><span
							class="fas fa-copy"></span> {\App\Language::translate('LBL_CopyToClipboard', $MODULE_NAME)}
				</button>&nbsp;&nbsp;
				<button class="btn btn-warning" onclick="PasswordHelper.showDetailsPassword('{$smarty.get.record}');return false;"
						id="show-btn">
					<span class="fas fa-eye u-mr-5px"></span>{\App\Language::translate('LBL_ShowPassword', $MODULE_NAME)}
				</button>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	{/strip}
