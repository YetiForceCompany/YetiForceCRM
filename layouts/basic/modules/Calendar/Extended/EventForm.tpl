{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Extended-EventForm -->
	<input value="{\App\Purifier::encodeHtml(\AppConfig::module('Calendar', 'AUTOFILL_TIME'))}"
		   type="hidden" id="autofillTime"/>
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	<form class="form-horizontal recordEditView" id="quickCreate" name="QuickCreate" method="post" action="index.php">
		{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
			<input name="picklistDependency" value='{\App\Purifier::encodeHtml($PICKIST_DEPENDENCY_DATASOURCE)}'
				   type="hidden"/>
		{/if}
		{if !empty($RECORD_ID)}
			<input name="record" value="{$RECORD_ID}" type="hidden"/>
		{/if}
		{if !empty($MAPPING_RELATED_FIELD)}
			<input name="mappingRelatedField" value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}'
				   type="hidden"/>
		{/if}
		{if !empty($IS_POSTPONED)}
			<input name="postponed" value="1" type="hidden"/>
		{/if}
		<input name="module" value="{$MODULE_NAME}" type="hidden"/>
		<input name="action" value="SaveAjax" type="hidden"/>
		<input name="defaultOtherEventDuration"
			   value="{\App\Purifier::encodeHtml($USER_MODEL->get('othereventduration'))}" type="hidden"/>
		<input name="userChangedEndDateTime" value="0" type="hidden"/>
		<div class="o-calendar__form w-100 d-flex flex-column">
			<div class="o-calendar__form__wrapper js-calendar__form__wrapper massEditTable no-margin"
				 data-js="perfectscrollbar">
				<h6 class="boxEventTitle text-muted text-center mt-1">
					{if !empty($RECORD_ID)}
						<span class="fas fa-edit mr-1"></span>
					{\App\Language::translate('LBL_EDIT_EVENT',$MODULE_NAME)}
					{else}
						<span class="fas fa-plus mr-1"></span>
						{\App\Language::translate('LBL_ADD',$MODULE_NAME)}
					{/if}
				</h6>
				{if !empty(AppConfig::module('Calendar', 'SHOW_ACTIVITY_BUTTONS_IN_EDIT_FORM')) && empty($IS_POSTPONED) && !empty($RECORD_ID)}
					{include file=\App\Layout::getTemplatePath('Extended/ActivityButtons.tpl', $MODULE_NAME)}
				{/if}
				<div class="fieldRow">
					{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
						{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
						{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
						{assign var="refrenceListCount" value=count($refrenceList)}
						<div class="row fieldsLabelValue pl-0 pr-0 mb-2">
							<div class="col-12">
								{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
								{assign var=HELPINFO_LABEL value=$MODULE_NAME|cat:'|'|cat:$FIELD_MODEL->getFieldLabel()}
								<label class="muted mt-0">
									{if in_array($VIEW,$HELPINFO) && \App\Language::translate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
										<a href="#" class="js-popover-tooltip mr-1" data-toggle="popover"
										   data-content="{htmlspecialchars(\App\Language::translate($MODULE_NAME|cat:'|'|cat:$FIELD_MODEL->getFieldLabel(), 'HelpInfo'))}"
										   data-original-title='{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}'
										   role="tooltip">
											<span class="fas fa-info-circle small align-baseline"></span>
										</a>
									{/if}
									{if $FIELD_MODEL->isMandatory() eq true}
										<span class="redColor">*</span>
									{/if}
									{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
								</label>
							</div>
							<div class="fieldValue col-12">
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
							</div>
						</div>
					{/foreach}
				</div>
			</div>
			{if !empty($SOURCE_RELATED_FIELD)}
				{foreach key=RELATED_FIELD_NAME item=RELATED_FIELD_MODEL from=$SOURCE_RELATED_FIELD}
					<input type="hidden" name="{$RELATED_FIELD_NAME}"
						   value="{\App\Purifier::encodeHtml($RELATED_FIELD_MODEL->get('fieldvalue'))}"
						   data-fieldtype="{$RELATED_FIELD_MODEL->getFieldDataType()}"/>
				{/foreach}
			{/if}
			<div class="o-calendar__form__actions">
				<div class="d-flex flex-wrap{if empty($RECORD_ID)} justify-content-center{/if}">
					{if !empty($QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER'])}
						{foreach item=LINK from=$QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER']}
							{if $LINK->get('linkhint') neq 'LBL_GO_TO_FULL_FORM'}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='quickcreateViewHeader'}
							{/if}
						{/foreach}
					{/if}
					<button type="submit" class="js-save-event btn btn-success"
							title="{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}" data-js="click">
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
	<!-- /tpl-Calendar-Extended-EventForm -->
{/strip}
