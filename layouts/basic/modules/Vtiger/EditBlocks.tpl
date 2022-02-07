{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-EditBlocks -->
	{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
		{if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
		{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL]}
		{assign var=BLOCK_ICON value=$BLOCK->get('icon')}
		<div class="js-toggle-panel c-panel c-panel--edit mb-3" data-label="{$BLOCK_LABEL}">
			<div class="blockHeader c-panel__header align-items-center py-1">
				<h5>{if !empty($BLOCK_ICON)}<span class="{$BLOCK_ICON} mr-2"></span>{/if}{\App\Language::translate($BLOCK_LABEL, $MODULE_NAME)}</h5>
			</div>
			<div class="c-panel__body c-panel__body--edit blockContent js-block-content">
				<div class="row">
					{assign var=COUNTER value=0}
					{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
						{if ($FIELD_NAME === 'time_start' || $FIELD_NAME === 'time_end') && ($MODULE_NAME === 'OSSTimeControl' || $MODULE_NAME === 'Reservations')}{continue}{/if}
						{if $FIELD_MODEL->getUIType() eq '20' || $FIELD_MODEL->getUIType() eq '300'}
							{if $COUNTER eq '1'}
							</div>
							<div class="row">
								{assign var=COUNTER value=0}
							{/if}
						{/if}
						{if $COUNTER eq 2}
						</div>
						<div class="row">
							{assign var=COUNTER value=1}
						{else}
							{assign var=COUNTER value=$COUNTER+1}
						{/if}
						<div class="{if $FIELD_MODEL->getUIType() neq "300"}col-sm-6 {else} col-md-12 m-auto{/if} row form-group align-items-center my-1 js-field-block-column{if $FIELD_MODEL->get('hideField')} d-none{/if}" data-field="{$FIELD_MODEL->getFieldName()}" data-js="container">
							{assign var=HELPINFO_LABEL value=\App\Language::getTranslateHelpInfo($FIELD_MODEL, $VIEW)}
							<label class="flCT_{$MODULE_NAME}_{$FIELD_MODEL->getFieldName()} my-0 col-lg-12 col-xl-3 fieldLabel text-lg-left {if $FIELD_MODEL->getUIType() neq "300"} text-xl-right {/if} u-text-small-bold">
								{if $FIELD_MODEL->isMandatory() eq true}
									<span class="redColor">*</span>
								{/if}
								{if $HELPINFO_LABEL}
									<a href="#" class="js-help-info float-right u-cursor-pointer"
										title=""
										data-placement="top"
										data-content="{$HELPINFO_LABEL}"
										data-original-title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}">
										<span class="fas fa-info-circle"></span>
									</a>
								{/if}
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
							</label>
							<div class="{$WIDTHTYPE} w-100 {if $FIELD_MODEL->getUIType() neq "300"} col-lg-12 col-xl-9 {/if} fieldValue" {if $FIELD_MODEL->getUIType() eq '20'} colspan="3" {assign var=COUNTER value=$COUNTER+1}{elseif $FIELD_MODEL->getUIType() eq '300'} colspan="4" {assign var=COUNTER value=$COUNTER+1} {/if}>
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME) BLOCK_FIELDS=$BLOCK_FIELDS}
							</div>
						</div>
					{/foreach}
				</div>
			</div>
		</div>
	{/foreach}
	<!-- /tpl-Base-EditBlocks -->
{/strip}
