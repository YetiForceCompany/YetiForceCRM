{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Extended-EventForm -->
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
		<input name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" type="hidden"/>
		<input name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" type="hidden"/>
		<input name="userChangedEndDateTime" value="0" type="hidden"/>
		<div class=" w-100 d-flex flex-column">
			<h6 class="boxEventTitle text-muted text-center mt-1">
				{if !empty($RECORD_ID)}
					<span class="fas fa-edit mr-1"></span>
				{\App\Language::translate('LBL_EDIT_EVENT',$MODULE_NAME)}
				{else}
					<span class="fas fa-plus mr-1"></span>
					{\App\Language::translate('LBL_ADD',$MODULE_NAME)}
				{/if}
			</h6>
			<div class="o-calendar__form__wrapper js-calendar__form__wrapper massEditTable no-margin">
				<div class="fieldRow mt-2">
					{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
						{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
						{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
						{assign var="refrenceListCount" value=count($refrenceList)}
						<div class="row fieldsLabelValue paddingLRZero">
							<div class="col-12">
								{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
								{assign var=HELPINFO_LABEL value=$MODULE_NAME|cat:'|'|cat:$FIELD_MODEL->getFieldLabel()}
								<label class="muted pull-left-xs pull-left-sm pull-left-lg">
									{if $FIELD_MODEL->isMandatory() eq true}
										<span class="redColor">*</span>
									{/if}
									{if in_array($VIEW,$HELPINFO) && \App\Language::translate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
										<a href="#" class="HelpInfoPopover pull-right"
										   title="" data-placement="auto top"
										   data-content="{htmlspecialchars(\App\Language::translate($MODULE_NAME|cat:'|'|cat:$FIELD_MODEL->getFieldLabel(), 'HelpInfo'))}"
										   data-original-title='{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}'>
											<span class="glyphicon glyphicon-info-sign"></span>
										</a>
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
		</div>
		{if !empty($SOURCE_RELATED_FIELD)}
			{foreach key=RELATED_FIELD_NAME item=RELATED_FIELD_MODEL from=$SOURCE_RELATED_FIELD}
				<input type="hidden" name="{$RELATED_FIELD_NAME}"
					   value="{\App\Purifier::encodeHtml($RELATED_FIELD_MODEL->get('fieldvalue'))}"
					   data-fieldtype="{$RELATED_FIELD_MODEL->getFieldDataType()}"/>
			{/foreach}
		{/if}
		<div class="formActionsPanel mb-1 d-flex justify-content-center flex-wrap">
			{if !empty($QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER'])}
				{foreach item=LINK from=$QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER']}
					{if $LINK->get('linkhint') neq 'LBL_GO_TO_FULL_FORM'}
						{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='quickcreateViewHeader' CLASS="mt-1"}
					{/if}
				{/foreach}
			{/if}
			<button type="submit" class="js-save-event btn btn-success mt-1"
					title="{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}" data-js="click">
				{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}
			</button>
			{if !empty($RECORD_ID)}
				<a href="#" role="button" class="btn btn-danger summaryCloseEdit mt-1 ml-auto">
							<span title="{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}"
								  class="fas fa-times"></span>
				</a>
			{/if}
		</div>
	</form>
	<!-- /tpl-Calendar-Extended-EventForm -->
{/strip}
