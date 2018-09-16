{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Extended-EventForm -->
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	<form class="form-horizontal recordEditView" id="quickCreate" name="QuickCreate" method="post" action="index.php">
		{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
			<input name="picklistDependency" value='{\App\Purifier::encodeHtml($PICKIST_DEPENDENCY_DATASOURCE)}' type="hidden"/>
		{/if}
		{if !empty($RECORD_ID)}
			<input name="record" value="{$RECORD_ID}" type="hidden"/>
		{/if}
		{if !empty($MAPPING_RELATED_FIELD)}
			<input name="mappingRelatedField" value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}' type="hidden"/>
		{/if}
		<input name="module" value="{$MODULE}" type="hidden"/>
		<input name="action" value="SaveAjax" type="hidden"/>
		<input name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" type="hidden"/>
		<input name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" type="hidden"/>
		<input name="userChangedEndDateTime" value="0" type="hidden"/>
		<div class="row no-margin tabbable paddingLRZero">
			<div class="pull-left marginRight10">
				<div class="pull-left">
					{if !empty($RECORD_ID)}
						<h4 class="boxEventTitle">{\App\Language::translate('LBL_EDIT_EVENT',$MODULE)}</h4>
					{else}
						<h4 class="boxEventTitle">{\App\Language::translate('LBL_ADD',$MODULE)}</h4>
					{/if}
				</div>
				{if !empty($RECORD_ID)}
					<div class="pull-right">
						<a href="#" class="btn btn-default summaryCloseEdit">
							<span title="{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}"
								  class="glyphicon glyphicon-remove"></span>
						</a>
					</div>
				{/if}
				<div class="clearfix"></div>
			</div>
			<div class="eventFormContent">
				<div class="massEditTable row no-margin">
					<div class="col-xs-12 paddingLRZero fieldRow">
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
							{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
							{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
							{assign var="refrenceListCount" value=count($refrenceList)}
							<div class="row fieldsLabelValue paddingLRZero">
								<div class="col-12">
									{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
									{assign var=HELPINFO_LABEL value=$MODULE|cat:'|'|cat:$FIELD_MODEL->getFieldLabel()}
									<label class="muted pull-left-xs pull-left-sm pull-left-lg">
										{if $FIELD_MODEL->isMandatory() eq true}
											<span class="redColor">*</span>
										{/if}
										{if in_array($VIEW,$HELPINFO) && \App\Language::translate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
											<a href="#" class="HelpInfoPopover pull-right"
											   title="" data-placement="auto top"
											   data-content="{htmlspecialchars(\App\Language::translate($MODULE|cat:'|'|cat:$FIELD_MODEL->getFieldLabel(), 'HelpInfo'))}"
											   data-original-title='{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}'>
												<span class="glyphicon glyphicon-info-sign"></span>
											</a>
										{/if}
										{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}
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
		</div>
		{if !empty($SOURCE_RELATED_FIELD)}
			{foreach key=RELATED_FIELD_NAME item=RELATED_FIELD_MODEL from=$SOURCE_RELATED_FIELD}
				<input type="hidden" name="{$RELATED_FIELD_NAME}"
					   value="{\App\Purifier::encodeHtml($RELATED_FIELD_MODEL->get('fieldvalue'))}"
					   data-fieldtype="{$RELATED_FIELD_MODEL->getFieldDataType()}"/>
			{/foreach}
		{/if}
		<div class="formActionsPanel hidden-xs hidden-sm">
			<button type="button" class="btn btn-success save"
					title="{\App\Language::translate('LBL_SAVE', $MODULE)}" data-js="click">
				<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong>
			</button>
		</div>
	</form>
	<!-- /tpl-Calendar-Extended-EventForm -->
{/strip}
