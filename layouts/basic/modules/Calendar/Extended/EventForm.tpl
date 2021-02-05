{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Extended-EventForm -->
	<div class="js-edit-form">
		<input value="{\App\Purifier::encodeHtml(\App\Config::module('Calendar', 'AUTOFILL_TIME'))}" type="hidden" id="autofillTime"/>
		{foreach key=index item=jsModel from=$SCRIPTS}
			<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
		{/foreach}
		<form class="form-horizontal recordEditView js-form" id="quickCreate" name="QuickCreate" method="post" action="index.php" enctype="multipart/form-data" data-js="container">
			<input name="module" value="{$MODULE_NAME}" type="hidden"/>
			<input name="action" value="SaveAjax" type="hidden"/>
			{if !empty($RECORD_ID)}
				<input name="record" value="{$RECORD_ID}" type="hidden"/>
				<input type="hidden" name="fromView" value="QuickEdit"/>
				{assign var="FROM_VIEW" value='QuickEdit'}
			{else}
				<input type="hidden" name="fromView" value="QuickCreate"/>
				{assign var="FROM_VIEW" value='QuickCreate'}
			{/if}
			<input type="hidden" id="preSaveValidation" value="{!empty(\App\EventHandler::getByType(\App\EventHandler::EDIT_VIEW_PRE_SAVE, $MODULE_NAME))}"/>
			<input type="hidden" class="js-change-value-event" value="{\App\EventHandler::getVarsByType(\App\EventHandler::EDIT_VIEW_CHANGE_VALUE, $MODULE_NAME, [$RECORD, $FROM_VIEW])}"/>
			<input name="defaultOtherEventDuration" value="{\App\Purifier::encodeHtml($USER_MODEL->get('othereventduration'))}" type="hidden"/>
			<input name="userChangedEndDateTime" value="0" type="hidden"/>
			{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
				<input name="picklistDependency" type="hidden" value='{\App\Purifier::encodeHtml($PICKIST_DEPENDENCY_DATASOURCE)}' />
			{/if}
			{if !empty($MAPPING_RELATED_FIELD)}
				<input name="mappingRelatedField" type="hidden" value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}'/>
			{/if}
			{if !empty($LIST_FILTER_FIELDS)}
				<input name="listFilterFields" type="hidden" value='{\App\Purifier::encodeHtml($LIST_FILTER_FIELDS)}'/>
			{/if}
			{if !empty($IS_POSTPONED)}
				<input name="postponed" value="1" type="hidden"/>
			{/if}
			<div class="o-calendar__form w-100 d-flex flex-column">
				<div class="o-calendar__form__wrapper js-calendar__form__wrapper massEditTable no-margin"
					data-js="perfectscrollbar">
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
						{include file=\App\Layout::getTemplatePath('Extended/ActivityButtons.tpl', $MODULE_NAME)}
					{/if}
					<div class="fieldRow">
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
							{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
							{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
							{assign var="refrenceListCount" value=count($refrenceList)}
							{assign var="PARAMS" value=$FIELD_MODEL->getFieldParams()}
							<div class="fieldsLabelValue pl-0 pr-0 mb-2 {$WIDTHTYPE} {$WIDTHTYPE_GROUP}">
								{if !(isset($PARAMS['hideLabel']) && in_array($VIEW, $PARAMS['hideLabel']))}
									<div class="col-12 px-2 u-fs-sm">
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
								<div class="fieldValue col-12 px-2">
									{if $FIELD_MODEL->name === 'activitytype' && App\Config::module('Calendar','SHOW_ACTIVITYTYPES_AS_BUTTONS')}
										{include file=\App\Layout::getTemplatePath('Edit/Field/ActivityType.tpl', $MODULE_NAME)}
									{else}
										{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
									{/if}
								</div>
							</div>
						{/foreach}
						<div class="fieldsLabelValue pl-0 pr-0 mb-2">
							<div class="col-12 px-2 u-fs-sm">
								<label class="muted mt-0 mb-0">
									{\App\Language::translate('LBL_INVITE_RECORDS', $MODULE_NAME)}
								</label>
							</div>
							<div class="fieldValue col-12 px-2">
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
				{if !empty($SOURCE_RELATED_FIELD)}
					{foreach key=FIELD_NAME item=FIELD_MODEL from=$SOURCE_RELATED_FIELD}
						<div class="d-none">
							{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
						</div>
					{/foreach}
				{/if}
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
	<!-- /tpl-Calendar-Extended-EventForm -->
{/strip}
