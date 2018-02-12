{strip}
	{*<!--
	/*********************************************************************************
	** The contents of this file are subject to the vtiger CRM Public License Version 1.0
	* ("License"); You may not use this file except in compliance with the License
	* The Original Code is:  vtiger CRM Open Source
	* The Initial Developer of the Original Code is vtiger.
	* Portions created by vtiger are Copyright (C) vtiger.
	* All Rights Reserved.
	* Contributor(s): YetiForce.com
	********************************************************************************/
	-->*}
	{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
		{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
		{if $BLOCK eq null or $FIELD_MODEL_LIST|@count lte 0}
			{continue}
		{/if}
		{assign var=BLOCKS_HIDE value=$BLOCK->isHideBlock($RECORD,$VIEW)}
		{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
		{if $BLOCKS_HIDE}
			<div class="detailViewTable">
				<div class="panel panel-default row no-margin border rounded" data-label="{$BLOCK_LABEL_KEY}">
					<div class="row blockHeader panel-heading no-margin bg-light">
						<div class="iconCollapse">
							<span class="cursorPointer blockToggle fas fa-angle-right {if !($IS_HIDDEN)}hide{/if}" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide" data-id="{$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}"></span>
							<span class="cursorPointer blockToggle fas fa-angle-down {if $IS_HIDDEN}hide{/if}" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show" data-id="{$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}"></span>
							<h4>{\App\Language::translate($BLOCK_LABEL_KEY,$MODULE_NAME)}</h4>
						</div>
					</div>
					<div class="col-md-12 panel-body blockContent {if $IS_HIDDEN}hide{/if}">
						{assign var=COUNTER value=0}
						<div class="row fieldRow">
							{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
								{if !$FIELD_MODEL->isViewableInDetailView()}
									{continue}
								{/if}
								{if $FIELD_MODEL->getUIType() eq "69" || $FIELD_MODEL->getUIType() eq "105"}
									{if $COUNTER neq 0}
										{if $COUNTER eq 2}
										</div>
										<div class="row fieldRow">
											{assign var=COUNTER value=0}
										{/if}
									{/if}
									<div class="col-md-6 fieldsLabelValue">
										<div class="fieldLabel col-sm-5 {$WIDTHTYPE}">
											<label class="muted pull-left-xs pull-right-sm pull-right-md pull-right-lg">{\App\Language::translate({$FIELD_MODEL->getFieldLabel()},{$MODULE_NAME})}</label>
										</div>
										<div class="fieldValue col-sm-7 {$WIDTHTYPE}">
											<div id="imageContainer">
												{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
													{if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
														<img src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE_INFO.path))}" width="300" height="200">
													{/if}
												{/foreach}
											</div>
										</div>
									</div>
									{assign var=COUNTER value=$COUNTER+1}
								{else}
									{if $FIELD_MODEL->getUIType() eq "20" or $FIELD_MODEL->getUIType() eq "19" or $FIELD_MODEL->getUIType() eq '300'}
										{if $COUNTER eq '1'}
											{assign var=COUNTER value=0}
										{/if}
									{/if}
									{if $COUNTER eq 2}
									</div>
									<div class="row fieldRow">
										{assign var=COUNTER value=1}
									{else}
										{assign var=COUNTER value=$COUNTER+1}
									{/if}
									<div class="col-md-6 fieldsLabelValue paddingLRZero">
										<div class="row">
										<div class="fieldLabel col-sm-5 {$WIDTHTYPE}" id="{$MODULE_NAME}_detailView_fieldLabel_{$FIELD_MODEL->getName()}">
											{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
											{assign var=HELPINFO_LABEL value=$MODULE_NAME|cat:'|'|cat:$FIELD_MODEL->getFieldLabel()}
											<label class="muted pull-left-xs pull-right-sm pull-right-md pull-right-lg">
												{\App\Language::translate({$FIELD_MODEL->getFieldLabel()},{$MODULE_NAME})}
												{if in_array($VIEW,$HELPINFO) && \App\Language::translate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
													<a href="#" class="HelpInfoPopover float-right cursorPointer" title="" data-placement="auto top" data-content="{htmlspecialchars(\App\Language::translate($MODULE_NAME|cat:'|'|cat:$FIELD_MODEL->getFieldLabel(), 'HelpInfo'))}" data-original-title='{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}'><span class="fas fa-info-circle"></span></a>
													{/if}
											</label>
										</div>
										<div class="fieldValue col-sm-7 {$WIDTHTYPE}" id="{$MODULE_NAME}_detailView_fieldValue_{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->getUIType() eq '19' or $FIELD_MODEL->getUIType() eq '20' or $FIELD_MODEL->getUIType() eq '300'} {assign var=COUNTER value=$COUNTER+1} {/if}>
											<span class="value" data-field-type="{$FIELD_MODEL->getFieldDataType()}" {if $FIELD_MODEL->getUIType() eq '19' or $FIELD_MODEL->getUIType() eq '20' or $FIELD_MODEL->getUIType() eq '21' or $FIELD_MODEL->getUIType() eq '300'} style="white-space:normal;" {/if}>
												{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
											</span>
											{assign var=EDIT value=false}
											{if in_array($FIELD_MODEL->getName(),['date_start','due_date']) && ($MODULE_NAME eq 'Calendar' || $MODULE_NAME eq 'Events')}
												{assign var=EDIT value=true}
											{/if}
											{if $IS_AJAX_ENABLED && $FIELD_MODEL->isEditable() eq 'true' && $FIELD_MODEL->isAjaxEditable() eq 'true' && !$EDIT}
												<span class="summaryViewEdit cursorPointer float-right ">
													&nbsp;<i class="fas fa-edit" title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}"></i>
												</span>
												<span class="hide edit">
													{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
													{if $FIELD_MODEL->getFieldDataType() eq 'boolean' || $FIELD_MODEL->getFieldDataType() eq 'picklist'}
														<input type="hidden" class="fieldname" data-type="{$FIELD_MODEL->getFieldDataType()}" value='{$FIELD_MODEL->getName()}' data-prev-value='{\App\Purifier::encodeHtml($FIELD_MODEL->get('fieldvalue'))}' />		
													{else}
														{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}
														{if $FIELD_VALUE|is_array}
															{assign var=FIELD_VALUE value=\App\Json::encode($FIELD_VALUE)}
														{/if}
														<input type="hidden" class="fieldname" value='{$FIELD_MODEL->getName()}' data-type="{$FIELD_MODEL->getFieldDataType()}" data-prev-value='{\App\Purifier::encodeHtml($FIELD_VALUE)}' />
													{/if}
												</span>
											{/if}
										</div>
										</div>
									</div>
								{/if}
							{/foreach}
							{if $COUNTER eq 1}
								<div class="col-md-6 fieldsLabelValue paddingLRZero"></div>
							{/if}
						</div>
					</div>
				</div>
			</div>
		{/if}
	{/foreach}
{/strip}
