{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-QuickEdit -->
	<input type="hidden" name="module" value="{$MODULE_NAME}" />
	<input type="hidden" name="record" value="{$RECORD_ID}" />
	<input type="hidden" name="action" value="SaveAjax" />
	<input type="hidden" name="fromView" value="QuickEdit" />
	{if $RECORD_ID && !empty($RECORD_ACTIVITY_NOTIFIER)}
		<input type="hidden" id="recordActivityNotifier" data-interval="{App\Config::performance('recordActivityNotifierInterval', 10)}" data-record="{$RECORD_ID}" data-module="{$MODULE_NAME}" />
	{/if}
	<input type="hidden" id="preSaveValidation" value="{!empty(\App\EventHandler::getByType(\App\EventHandler::EDIT_VIEW_PRE_SAVE, $MODULE_NAME))}" />
	<input type="hidden" class="js-change-value-event" value="{\App\EventHandler::getVarsByType(\App\EventHandler::EDIT_VIEW_CHANGE_VALUE, $MODULE_NAME, [$RECORD, 'QuickEdit'])}" />
	{if !empty($MAPPING_RELATED_FIELD)}
		<input type="hidden" name="mappingRelatedField" value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}' />
	{/if}
	{if !empty($LIST_FILTER_FIELDS)}
		<input type="hidden" name="listFilterFields" value='{\App\Purifier::encodeHtml($LIST_FILTER_FIELDS)}' />
	{/if}
	{if !empty($CHANGED_FIELDS)}
		{foreach key=FIELD_NAME item=FIELD_MODEL from=$CHANGED_FIELDS}
			<input type="hidden" name="{$FIELD_NAME}" value="{\App\Purifier::encodeHtml($FIELD_MODEL->get('fieldvalue'))}" data-fieldtype="{$FIELD_MODEL->getFieldDataType()}" />
		{/foreach}
	{/if}
	{if $SHOW_ALERT_NO_POWERS}
		<div class="alert alert-danger mx-4 mt-4" role="alert">
			{\App\Language::translate('LBL_PERMISSION_DENIED_CERTAIN_FIELDS', $MODULE_NAME)}
		</div>
	{/if}
	<div class="quickCreateContent">
		<div class="modal-body m-0">
			{if $LAYOUT === 'blocks'}
				{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
					{if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
					{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL]}
					{assign var=IS_DYNAMIC value=$BLOCK->isDynamic()}
					<div class="js-toggle-panel c-panel c-panel--edit mb-3"
						data-js="click|data-dynamic" {if $IS_DYNAMIC} data-dynamic="true" {/if}
						data-label="{$BLOCK_LABEL}">
						<div class="blockHeader c-panel__header align-items-center">
							{if in_array($BLOCK_LABEL, $ADDRESS_BLOCK_LABELS)}
								{assign var=SEARCH_ADDRESS value=TRUE}
							{else}
								{assign var=SEARCH_ADDRESS value=FALSE}
							{/if}
							<h5 class="ml-2">{\App\Language::translate($BLOCK_LABEL, $MODULE_NAME)}</h5>
						</div>
						<div class="c-panel__body c-panel__body--edit blockContent js-block-content"
							data-js="display">
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
									{if isset($RECORD_STRUCTURE_RIGHT)}
										<div class="col-sm-12  row form-group align-items-center my-1">
										{else}
											<div class="{if $FIELD_MODEL->get('label') eq "FL_REAPEAT"} col-sm-3
							{elseif $FIELD_MODEL->get('label') eq "FL_RECURRENCE"} col-sm-9
							{elseif $FIELD_MODEL->getUIType() neq "300"}col-sm-6
							{else} col-md-12 m-auto{/if}  row form-group align-items-center my-1 js-field-block-column{if $FIELD_MODEL->get('hideField')} d-none{/if}" data-field="{$FIELD_MODEL->getFieldName()}" data-js="container">
											{/if}
											{assign var=HELPINFO_LABEL value=\App\Language::getTranslateHelpInfo($FIELD_MODEL, $VIEW)}
											<label class="my-0 col-lg-12 col-xl-3 fieldLabel text-lg-left {if $FIELD_MODEL->getUIType() neq "300"} text-xl-right {/if} u-text-small-bold">
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
				{elseif $LAYOUT === 'vertical'}
					<div class="massEditTable border-0 px-1 mx-auto m-0">
						<div class="col-12 form-row d-flex justify-content-center px-0 m-0 {$WIDTHTYPE}">
							{if !empty($NO_FIELD_ACCESS)}
								<div class="alert alert-warning w-100 mt-2">
									<div class="font-weight-bold d-flex align-items-center">
										<span class="mdi mdi-alert-outline mr-2 u-fs-2em float-left"></span>
										{\App\Language::translate('LBL_NO_FIELD_ACCESS')}
									</div>
									{foreach key=FIELD_LABEL item=FIELD_VALUE from=$NO_FIELD_ACCESS}
										<div class="w-100 row">
											<span class="col-4">
												{\App\Language::translate($FIELD_LABEL, $MODULE_NAME)}:
											</span>
											<span class="col-8">
												{$FIELD_VALUE}
											</span>
										</div>
									{/foreach}
								</div>
							{/if}
							{if !empty($CHANGED_FIELDS)}
								{foreach key=FIELD_NAME item=FIELD_MODEL from=$CHANGED_FIELDS}
									<div class="fieldLabel col-lg-12 col-xl-3 text-lg-left text-xl-right u-text-ellipsis mt-1 text-wrap">
										<span class="text-right muted small font-weight-bold">
											{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
										</span>
									</div>
									<div class="fieldValue col-lg-12 col-xl-9 px-0 px-sm-1 mt-1">
										{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD_ID,$RECORD)}
									</div>
								{/foreach}
							{/if}
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
								<div class="fieldLabel col-lg-12 col-xl-3 mt-1 text-lg-left text-xl-right u-text-ellipsis text-wrap">
									{assign var=HELPINFO_LABEL value=\App\Language::getTranslateHelpInfo($FIELD_MODEL, $VIEW)}
									<label class="text-right muted small font-weight-bold">
										{if $FIELD_MODEL->isMandatory() eq true}
											<span class="redColor">*</span>
										{/if}
										{if $HELPINFO_LABEL}
											<a href="#" class="js-help-info float-right u-cursor-pointer" title="" data-placement="top" data-content="{$HELPINFO_LABEL}" data-original-title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}">
												<span class="fas fa-info-circle"></span>
											</a>
										{/if}
										{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
									</label>
								</div>
								<div class="fieldValue col-lg-12 col-xl-9 mt-1">
									{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
								</div>
							{/foreach}
						</div>
					</div>
				{else}
					<div class="massEditTable border-0 px-1 mx-auto m-0">
						<div class="px-0 m-0 form-row d-flex justify-content-center">
							{assign var=COUNTER value=0}
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
								{if ($FIELD_NAME === 'time_start' || $FIELD_NAME === 'time_end') && ($MODULE_NAME === 'OSSTimeControl' || $MODULE_NAME === 'Reservations')}{continue}{/if}
								{if $COUNTER eq 2}
								</div>
								<div class="col-12 form-row d-flex justify-content-center px-0 m-0">
									{assign var=COUNTER value=1}
								{else}
									{assign var=COUNTER value=$COUNTER+1}
								{/if}
								<div class="col-md-6 py-2 form-row d-flex justify-content-center px-0 m-0 {$WIDTHTYPE} ">
									<div class="fieldLabel col-lg-12 col-xl-3 pl-0 text-lg-left text-xl-right u-text-ellipsis">
										{assign var=HELPINFO_LABEL value=\App\Language::getTranslateHelpInfo($FIELD_MODEL, $VIEW)}
										<label class="text-right muted small font-weight-bold">
											{if $FIELD_MODEL->isMandatory() eq true}
												<span class="redColor">*</span>
											{/if}
											{if $HELPINFO_LABEL}
												<a href="#" class="js-help-info float-right u-cursor-pointer"
													title=""
													data-placement="top"
													data-content="{$HELPINFO_LABEL}"
													data-original-title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}">
													<span class="fas fa-info-circle"></span>
												</a>
											{/if}
											{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
										</label>
									</div>
									<div class="fieldValue col-lg-12 col-xl-9 px-0 px-sm-1">
										{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
									</div>
								</div>
							{/foreach}
							{if $COUNTER eq 1}
								<div class="col-md-6 form-row align-items-center p-1 {$WIDTHTYPE} px-0"></div>
							{/if}
						</div>
					</div>
				{/if}
			</div>
		</div>
		</form>
		<!-- /tpl-Base-Modals-QuickEdit -->
{/strip}
