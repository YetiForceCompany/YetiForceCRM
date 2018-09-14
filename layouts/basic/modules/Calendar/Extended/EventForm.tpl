{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Extended-EventForm -->
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	<form class="form-horizontal recordEditView" id="quickCreate" name="QuickCreate" method="post"
		  action="index.php">
		{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
			<input value='{\App\Purifier::encodeHtml($PICKIST_DEPENDENCY_DATASOURCE)}' type="hidden"
				   name="picklistDependency"
			/>
		{/if}
		{if !empty($RECORD->getId())}
			<input name="record" value='{$RECORD->getId()}' type="hidden"/>
		{/if}
		{if !empty($MAPPING_RELATED_FIELD)}
			<input name="mappingRelatedField" value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}'
				   type="hidden"/>
		{/if}
		<input name="module" value="{$MODULE}" type="hidden"/>
		<input name="action" value="SaveAjax" type="hidden"/>
		<input name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" type="hidden"/>
		<input name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" type="hidden"/>
		<input name="userChangedEndDateTime" value="0" type="hidden"/>
		<!-- Random number is used to make specific tab is opened -->
		{assign var="RAND_NUMBER" value=rand()}
		{if count($QUICK_CREATE_CONTENTS) > 1}
			{assign var="VISIBLE_TABS" value=1}
		{else}
			{assign var="VISIBLE_TABS" value=0}
		{/if}
		<div class="row no-margin tabbable paddingLRZero">
			<div class="pull-left marginRight10">
				<div class="pull-left">
					{if !empty($RECORD->getId())}
						<h4 class="boxEventTitle">{\App\Language::translate('LBL_EDIT_EVENT',$MODULE)}</h4>
					{else}
						<h4 class="boxEventTitle">{\App\Language::translate('LBL_ADD',$MODULE)}</h4>
					{/if}
				</div>
				{if !empty($RECORD->getId())}
					<div class="pull-right">
						<a href="#" class="btn btn-default summaryCloseEdit">
							<span title="{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}"
								  class="glyphicon glyphicon-remove"></span>
						</a>
					</div>
				{/if}
				<div class="clearfix"></div>
			</div>
			{if $VISIBLE_TABS}
				<ul class="nav nav-pills">
					<li class="active">
						<a href="javascript:void(0);"
						   data-target=".EventsQuikcCreateContents_{$RAND_NUMBER}" data-toggle="tab"
						   data-tab-name="Event">
							{\App\Language::translate('LBL_EVENT',$MODULE)}
						</a>
					</li>
					<li class="">
						<a href="javascript:void(0);"
						   data-target=".CalendarQuikcCreateContents_{$RAND_NUMBER} " data-toggle="tab"
						   data-tab-name="Task">
							{\App\Language::translate('LBL_TASK',$MODULE)}
						</a>
					</li>
				</ul>
			{/if}
			{if $VISIBLE_TABS}
			<div class="tab-content overflowVisible">
				{/if}
				{foreach item=MODULE_DETAILS key=MODULE_NAME from=$QUICK_CREATE_CONTENTS}
				{if $VISIBLE_TABS}
				<div class="{$MODULE_NAME}QuikcCreateContents_{$RAND_NUMBER} tab-pane {if $MODULE_NAME eq 'Events'} active in {/if}fade">
					{else}
					<div class="{$MODULE_NAME}QuikcCreateContents_{$RAND_NUMBER}">
						{/if}
						<input name="mode" value="{if $MODULE_NAME eq 'Calendar'}calendar{else}events{/if}"
							   type="hidden"/>
						{assign var="RECORD_STRUCTURE_MODEL" value=$QUICK_CREATE_CONTENTS[$MODULE_NAME]['recordStructureModel']}
						{assign var="RECORD_STRUCTURE" value=$QUICK_CREATE_CONTENTS[$MODULE_NAME]['recordStructure']}
						{assign var="MODULE_MODEL" value=$QUICK_CREATE_CONTENTS[$MODULE_NAME]['moduleModel']}
						<div class="eventFormContent">
							<div class="massEditTable row no-margin">
								<div class="col-xs-12 paddingLRZero fieldRow">
									{assign var=COUNTER value=0}
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
										{if $FIELD_NAME eq 'allday'}
											{continue}
										{/if}
										{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
										{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
										{assign var="refrenceListCount" value=count($refrenceList)}
										{if $COUNTER eq 2}
											{assign var=COUNTER value=1}
										{else}
											{assign var=COUNTER value=$COUNTER+1}
										{/if}
										<div class="col-xs-12 col-md-12 fieldsLabelValue {$WIDTHTYPE} paddingLRZero"
											 style="margin-top:10px;">
											<div class="col-xs-12 col-sm-12">
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
											<div class="fieldValue col-xs-12 col-sm-12">
												{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
											</div>
										</div>
									{/foreach}
								</div>
							</div>
						</div>
					</div>
					{/foreach}
					{if $COUNTER eq 1}
						<div class="col-xs-12 col-md-6 fieldsLabelValue {$WIDTHTYPE} paddingLRZero"></div>
					{/if}
					{if $VISIBLE_TABS}
				</div>
				{/if}
			</div>
			{if !empty($SOURCE_RELATED_FIELD)}
				{foreach key=RELATED_FIELD_NAME item=RELATED_FIELD_MODEL from=$SOURCE_RELATED_FIELD}
					<input type="hidden" name="{$RELATED_FIELD_NAME}"
						   value="{\App\Purifier::encodeHtml($RELATED_FIELD_MODEL->get('fieldvalue'))}"
						   data-fieldtype="{$RELATED_FIELD_MODEL->getFieldDataType()}"/>
				{/foreach}
			{/if}
			<div class="formActionsPanel hidden-xs hidden-sm">
				<button type="button" class="btn btn-primary saveAndComplete marginRight10" data-js="click">
					{\App\Language::translate('LBL_SAVE_AND_CLOSE', $MODULE)}
				</button>
				<button type="button" class="btn btn-success save"
						title="{\App\Language::translate('LBL_SAVE', $MODULE)}" data-js="click">
					<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong>
				</button>
			</div>
	</form>
	<script type="text/javascript">
		jQuery(document).ready(function () {
			var instance = Calendar_CalendarExtendedView_Js.getInstanceByView();
			instance.registerAddForm();
		});
	</script>
	<!-- /tpl-Calendar-Extended-EventForm -->
{/strip}
