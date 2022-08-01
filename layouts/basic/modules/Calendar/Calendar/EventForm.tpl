{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Calendar-EventForm -->
	<div class="js-edit-form">
		<input type="hidden" id="autofillTime" value="{\App\Purifier::encodeHtml(\App\Config::module('Calendar', 'AUTOFILL_TIME'))}" />
		{foreach key=index item=jsModel from=$SCRIPTS}
			<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
		{/foreach}
		<form class="form-horizontal recordEditView js-form" id="quickCreate" name="QuickCreate" method="post" action="index.php" enctype="multipart/form-data" data-js="container">
			<input type="hidden" name="module" value="{$MODULE_NAME}" />
			<input type="hidden" name="action" value="SaveAjax" />
			{if !empty($RECORD_ID)}
				<input type="hidden" name="record" value="{$RECORD_ID}" />
				<input type="hidden" name="fromView" value="QuickEdit" />
				{assign var="FROM_VIEW" value='QuickEdit'}
			{else}
				<input type="hidden" name="fromView" value="QuickCreate" />
				{assign var="FROM_VIEW" value='QuickCreate'}
			{/if}
			<input type="hidden" id="preSaveValidation" value="{!empty(\App\EventHandler::getByType(\App\EventHandler::EDIT_VIEW_PRE_SAVE, $MODULE_NAME))}" />
			<input type="hidden" class="js-change-value-event" value="{\App\EventHandler::getVarsByType(\App\EventHandler::EDIT_VIEW_CHANGE_VALUE, $MODULE_NAME, [$RECORD, $FROM_VIEW])}" />
			{if !empty($MAPPING_RELATED_FIELD)}
				<input type="hidden" name="mappingRelatedField" value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}' />
			{/if}
			{if !empty($LIST_FILTER_FIELDS)}
				<input type="hidden" name="listFilterFields" value='{\App\Purifier::encodeHtml($LIST_FILTER_FIELDS)}' />
			{/if}
			{if !empty($IS_POSTPONED)}
				<input type="hidden" name="postponed" value="1" />
			{/if}
			<input type="hidden" name="defaultOtherEventDuration" value="{\App\Purifier::encodeHtml($USER_MODEL->get('othereventduration'))}" />
			<input type="hidden" name="userChangedEndDateTime" value="0" />
			{if !empty($SOURCE_RELATED_FIELD)}
				{foreach key=FIELD_NAME item=FIELD_MODEL from=$SOURCE_RELATED_FIELD}
					<div class="d-none fieldValue source-related-fields" data-field="{$FIELD_NAME}">
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
					</div>
				{/foreach}
			{/if}
			<div class="o-calendar__form w-100 d-flex flex-column">
				<div class="o-calendar__form__wrapper js-calendar__form__wrapper massEditTable no-margin" data-js="perfectscrollbar">
					<h6 class="boxEventTitle text-muted text-center mt-1">
						{if !empty($RECORD_ID)}
							<div class="js-sidebar-title" data-title="edit">
								<span class="yfi yfi-full-editing-view mr-1"></span>
								{\App\Language::translate('LBL_EDIT_EVENT',$MODULE_NAME)}
							</div>
						{else}
							<div class="js-sidebar-title" data-title="add">
								<span class="fas fa-plus mr-1"></span>
								{\App\Language::translate('LBL_ADD',$MODULE_NAME)}
							</div>
						{/if}
					</h6>
					{if !empty(App\Config::module('Calendar', 'SHOW_ACTIVITY_BUTTONS_IN_EDIT_FORM')) && empty($IS_POSTPONED) && !empty($RECORD_ID)}
						{include file=\App\Layout::getTemplatePath('Calendar/ActivityButtons.tpl', $MODULE_NAME)}
					{/if}
					<div class="fieldRow">
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
							{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
							{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
							{assign var="refrenceListCount" value=count($refrenceList)}
							{assign var="PARAMS" value=$FIELD_MODEL->getFieldParams()}
							<div class="row-form fieldsLabelValue px-1 mb-2 {$WIDTHTYPE} {$WIDTHTYPE_GROUP}">
								{if !(isset($PARAMS['hideLabel']) && in_array($VIEW, $PARAMS['hideLabel']))}
									<div class="col-12 u-fs-sm px-0">
										{assign var=HELPINFO_LABEL value=\App\Language::getTranslateHelpInfo($FIELD_MODEL,$VIEW)}
										<label class="muted mt-0 mb-0">
											{if $HELPINFO_LABEL}
												<a href="#" class="js-help-info float-right u-cursor-pointer"
													title=""
													data-placement="top"
													data-content="{$HELPINFO_LABEL}"
													data-original-title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}">
													<span class="fas fa-info-circle"></span>
												</a>
											{/if}
											{if $FIELD_MODEL->isMandatory() eq true}
												<span class="redColor">*</span>
											{/if}
											{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
										</label>
									</div>
								{/if}
								<div class="fieldValue col-12 px-0">
									{if $FIELD_MODEL->name === 'activitytype' && App\Config::module('Calendar','SHOW_ACTIVITYTYPES_AS_BUTTONS')}
										{include file=\App\Layout::getTemplatePath('Edit/Field/ActivityType.tpl', $MODULE_NAME)}
									{else}
										{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
									{/if}
								</div>
							</div>
						{/foreach}
						<div class="row-form fieldsLabelValue px-1 mb-2">
							<div class="col-12 px-0 u-fs-sm">
								<label class="muted mt-0 mb-0">
									{\App\Language::translate('LBL_INVITE_RECORDS', $MODULE_NAME)}
								</label>
							</div>
							<div class="fieldValue col-12 px-0">
								<div class="input-group js-popover-tooltip" data-js="popover" data-content="{\App\Language::translate('LBL_SELECT_INVITE', $MODULE_NAME)}">
									<input type="text" class="form-control js-participants-search" title="{\App\Language::translate('LBL_SELECT_INVITE', $MODULE_NAME)}"
										placeholder="{\App\Language::translate('LBL_SELECT_INVITE', $MODULE_NAME)}" data-js="click" />
									<div class="input-group-append">
										<button type="button" class="js-btn-add-invitation btn btn-light" title="{\App\Language::translate('LBL_ADD_PARTICIPANT', $MODULE_NAME)}">
											<span class="fa fa-plus" title="{\App\Language::translate('LBL_ADD_PARTICIPANT', $MODULE_NAME)}"></span>
										</button>
									</div>
								</div>
							</div>
							<div class="col-12 px-2 mt-1 js-participants-content d-flex flex-wrap flex-row justify-content-start align-items-left" data-js="container">
								<div class="d-none">
									{include file=\App\Layout::getTemplatePath('InviteRow.tpl', $MODULE_NAME)}
								</div>
								{if !empty($RECORD_ID)}
									{foreach key=KEY item=INVITIE from=$RECORD->getInvities()}
										{include file=\App\Layout::getTemplatePath('InviteRow.tpl', $MODULE_NAME)}
									{/foreach}
								{/if}
							</div>
						</div>
					</div>
				</div>
				<div class="o-calendar__form__actions">
					<div class="d-flex flex-wrap{if empty($RECORD_ID)} justify-content-center{/if}">
						{if !empty($QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER'])}
							{foreach item=LINK from=$QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER']}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='quickcreateViewHeader'}
							{/foreach}
						{/if}
						<button type="submit" class="js-save-event btn btn-success"
							title="{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}" data-js="click">
							<span title="{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}" class="fas fa-check mr-1"></span>
							{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}
						</button>
						{if !empty($RECORD_ID) && $VIEW === 'EventForm'}
							<a href="#" role="button" class="btn btn-danger js-summary-close-edit ml-auto u-h-fit">
								<span title="{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}"
									class="fas fa-times"></span>
							</a>
						{/if}
					</div>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Calendar-Calendar-EventForm -->
{/strip}
