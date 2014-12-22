{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{strip}
{foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET']}
	{if ($DETAIL_VIEW_WIDGET->getLabel() eq 'Documents') }
		{assign var=DOCUMENT_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
	{elseif ($DETAIL_VIEW_WIDGET->getLabel() eq 'LBL_MILESTONES')}
		{assign var=MILESTONE_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
	{elseif ($DETAIL_VIEW_WIDGET->getLabel() eq 'HelpDesk')}
		{assign var=HELPDESK_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
	{elseif ($DETAIL_VIEW_WIDGET->getLabel() eq 'LBL_TASKS')}
		{assign var=TASKS_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
	{elseif ($DETAIL_VIEW_WIDGET->getLabel() eq 'ModComments')}
		{assign var=COMMENTS_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
	{elseif ($DETAIL_VIEW_WIDGET->getLabel() eq 'LBL_UPDATES')}
		{assign var=UPDATES_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
	{elseif ($DETAIL_VIEW_WIDGET->getLabel() eq 'Email_list')}
		{assign var=MAIL_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
	{elseif ($DETAIL_VIEW_WIDGET->getLabel() eq 'Summary of costs' )}
		{assign var=COSTS_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
	{/if}
{/foreach}


<div class="row-fluid">
	<div class="span7">
		{* Module Summary View *}
			<div class="summaryView row-fluid">
				{$MODULE_SUMMARY}
			</div>
		{* Module Summary View Ends Here *}

		{* Summary View comments Widget*}
		{if $COMMENTS_WIDGET_MODEL}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_comments" data-url="{$COMMENTS_WIDGET_MODEL->getUrl()}" data-name="{$COMMENTS_WIDGET_MODEL->getLabel()}">
					<div class="widget_header row-fluid">
						<span class="span9"><h4>{vtranslate($COMMENTS_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4></span>

						<span class="span3">
							{if $COMMENTS_WIDGET_MODEL->get('action')}
								<button class="btn pull-right addButton createRecord" type="button" data-url="{$COMMENTS_WIDGET_MODEL->get('actionURL')}">
									<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
								</button>
							{/if}
						</span>
						<input type="hidden" name="relatedModule" value="{$COMMENTS_WIDGET_MODEL->get('linkName')}" />
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View Comments Widget Ends Here *}

		{* Summary View HelpDesk Widget *}
		{if $HELPDESK_WIDGET_MODEL}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_troubleTickets" data-url="{$HELPDESK_WIDGET_MODEL->getUrl()}" data-name="{$HELPDESK_WIDGET_MODEL->getLabel()}">
					<div class="widget_header row-fluid">
						<span class="span9">
							<div class="row-fluid">
								<span class="span4 margin0px"><h4>{vtranslate($HELPDESK_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4></span>
								<span class="span7">
									{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance('HelpDesk')}
									{assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField('ticketstatus')}
									{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
									{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
									{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
									<select class="chzn-select" name="{$FIELD_MODEL->get('name')}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
										<option>{vtranslate('LBL_SELECT_STATUS',$MODULE_NAME)}</option>
										{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
											<option value="{$PICKLIST_NAME}" {if $FIELD_MODEL->get('fieldvalue') eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
										{/foreach}
									</select>
								</span>
							</div>
						</span>
						<span class="span3">
							{if $HELPDESK_WIDGET_MODEL->get('action')}
								<button class="btn pull-right addButton createRecord" type="button" data-url="{$HELPDESK_WIDGET_MODEL->get('actionURL')}">
									<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
								</button>
							{/if}
						</span>
						<input type="hidden" name="relatedModule" value="{$HELPDESK_WIDGET_MODEL->get('linkName')}" />
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View HelpDesk Widget Ends here *}
	</div>
	<div class='span5' style="overflow: hidden">
		{if $MAIL_WIDGET_MODEL}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_contacts" data-url="{$MAIL_WIDGET_MODEL->getUrl()}" data-name="{$MAIL_WIDGET_MODEL->getLabel()}">
					<div class="widget_header row-fluid">
						<input type="hidden" name="relatedModule" value="{$MAIL_WIDGET_MODEL->get('linkName')}" />
						<span class="span9 margin0px"><h4>{vtranslate($MAIL_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4></span>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{if $COSTS_WIDGET_MODEL}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_contacts" data-url="{$COSTS_WIDGET_MODEL->getUrl()}" data-name="{$COSTS_WIDGET_MODEL->getLabel()}">
					<div class="widget_header row-fluid">
						<input type="hidden" name="relatedModule" value="{$COSTS_WIDGET_MODEL->get('linkName')}" />
						<span class="span9 margin0px"><h4>{vtranslate($COSTS_WIDGET_MODEL->getLabel(),'OSSCosts')}</h4></span>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View MileStone Widget*}
		{if $MILESTONE_WIDGET_MODEL}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_mileStone" data-url="{$MILESTONE_WIDGET_MODEL->getUrl()}" data-name="{$MILESTONE_WIDGET_MODEL->getLabel()}">
					<div class="widget_header row-fluid">
						<span class="span9"><h4>{vtranslate($MILESTONE_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4></span>
						<span class="span3">
							<span class=" pull-right">
								{if $MILESTONE_WIDGET_MODEL->get('action')}
									<button class="btn addButton" id="createProjectMileStone" type="button" data-url="{$MILESTONE_WIDGET_MODEL->get('actionURL')}" data-parent-related-field="projectid">
										<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
									</button>
								{/if}
							</span>
						</span>
						<input type="hidden" name="relatedModule" value="{$MILESTONE_WIDGET_MODEL->get('linkName')}" />
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View MileStone Widget Ends Here*}

		{* Summary View Tasks Widgte*}
		{if $TASKS_WIDGET_MODEL}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_tasks" data-url="{$TASKS_WIDGET_MODEL->getUrl()}" data-name="{$TASKS_WIDGET_MODEL->getLabel()}">
					<div class="widget_header row-fluid">
						<span class="span9">
							<div class="row-fluid">
								<span class="span4 margin0px"><h4>{vtranslate($TASKS_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4></span>
								<span class="span7">
									{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance('ProjectTask')}
									{assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField('projecttaskstatus')}
									{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
									{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
									{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
									<select style="width: 160px;" class="chzn-select" name="{$FIELD_MODEL->get('name')}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
										<option>{vtranslate('LBL_SELECT_STATUS',$MODULE_NAME)}</option>
										{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
											<option value="{$PICKLIST_NAME}" {if $FIELD_MODEL->get('fieldvalue') eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
										{/foreach}
									</select>
								</span>
							</div>
						</span>
						<span class="span3">
							{if $TASKS_WIDGET_MODEL->get('action')}
								<button class="btn pull-right addButton" id="createProjectTask" type="button" data-url="{$TASKS_WIDGET_MODEL->get('actionURL')}" data-parent-related-field="projectid">
									<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
								</button>
							{/if}
						</span>
						<input type="hidden" name="relatedModule" value="{$TASKS_WIDGET_MODEL->get('linkName')}" />
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View Tasks Widget Ends Here *}

		{* Summary View Document Widget*}
		{if $DOCUMENT_WIDGET_MODEL}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_documents" data-url="{$DOCUMENT_WIDGET_MODEL->getUrl()}" data-name="{$DOCUMENT_WIDGET_MODEL->getLabel()}">
					<div class="widget_header row-fluid">
						<span class="span9"><h4>{vtranslate($DOCUMENT_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4></span>
						<span class="span3">
							{if $DOCUMENT_WIDGET_MODEL->get('action')}
								<button class="btn pull-right addButton createRecord" type="button" data-url="{$DOCUMENT_WIDGET_MODEL->get('actionURL')}">
									<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
								</button>
							{/if}
						</span>
						<input type="hidden" name="relatedModule" value="{$DOCUMENT_WIDGET_MODEL->get('linkName')}" />
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View Document Widget Ends Here*}

		{* Summary View Updates Widget *}
		{if $UPDATES_WIDGET_MODEL}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_updates" data-url="{$UPDATES_WIDGET_MODEL->getUrl()}" data-name="{$UPDATES_WIDGET_MODEL->getLabel()}">
					<div class="widget_header row-fluid">
						<span class="span9"><h4>{vtranslate($UPDATES_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4></span>

						<span class="span3">
							{if $UPDATES_WIDGET_MODEL->get('action')}
								<button class="btn pull-right addButton createRecord" type="button" data-url="{$UPDATES_WIDGET_MODEL->get('actionURL')}">
									<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
								</button>
							{/if}
						</span>
						<input type="hidden" name="relatedModule" value="{$UPDATES_WIDGET_MODEL->get('linkName')}" />
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View Updates Widget Ends Here*}
	</div>
</div>
{/strip}