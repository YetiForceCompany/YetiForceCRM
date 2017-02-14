{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}

{strip}
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<div class="modelContainer modal fade" tabindex="-1">
		<div class="modal-dialog modal-full">
			<div class="modal-content">
				<form class="form-horizontal recordEditView" name="QuickCreate" method="post" action="index.php">
					<div class="modal-header">
						<div class="pull-left">
							<h3 class="modal-title">{vtranslate('LBL_QUICK_CREATE', $MODULE)} {vtranslate($SINGLE_MODULE, $MODULE)}</h3>
						</div>
						<div class="pull-right quickCreateActions">
							{foreach item=LINK from=$QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER']}
								{include file='ButtonLink.tpl'|@vtemplate_path:$MODULE BUTTON_VIEW='quickcreateViewHeader'}
								&nbsp;&nbsp;
							{/foreach}
							{assign var="EDIT_VIEW_URL" value=$MODULE_MODEL->getCreateRecordUrl()}
							<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
							<button class="btn btn-default" id="goToFullForm" data-edit-view-url="{$EDIT_VIEW_URL}" type="button"><strong>{vtranslate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button>&nbsp;

							<button class="cancelLink  btn btn-warning" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
						</div>
						<div class="clearfix"></div>
					</div>
					{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
						<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
					{/if}
					{if !empty($MAPPING_RELATED_FIELD)}
						<input type="hidden" name="mappingRelatedField" value='{Vtiger_Util_Helper::toSafeHTML($MAPPING_RELATED_FIELD)}' />
					{/if}
					<input type="hidden" name="module" value="{$MODULE}">
					<input type="hidden" name="action" value="SaveAjax">
					<div class="quickCreateContent">
						<div class="modal-body row no-margin">
							<div class="massEditTable row no-margin">
								<div class="col-xs-12 paddingLRZero fieldRow">
									{assign var=COUNTER value=0}
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
									{if in_array($FIELD_NAME, ['time_start','time_end'])}{continue}{/if}
									{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
									{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
									{assign var="refrenceListCount" value=count($refrenceList)}
									{if $COUNTER eq 2}
									</div>
									<div class="col-xs-12 paddingLRZero fieldRow">
										{assign var=COUNTER value=1}
									{else}
										{assign var=COUNTER value=$COUNTER+1}
									{/if}
									<div class="col-xs-12 col-md-6 fieldsLabelValue {$WIDTHTYPE} paddingLRZero">
										<div class="fieldLabel col-xs-12 col-sm-5">
											{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
											{assign var=HELPINFO_LABEL value=$MODULE|cat:'|'|cat:$FIELD_MODEL->get('label')}
											<label class="muted pull-left-xs pull-right-sm pull-right-lg">
												{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span>{/if}
												{if in_array($VIEW,$HELPINFO) && vtranslate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
													<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="auto top" data-content="{htmlspecialchars(vtranslate($MODULE|cat:'|'|cat:$FIELD_MODEL->get('label'), 'HelpInfo'))}" data-original-title='{vtranslate($FIELD_MODEL->get("label"), $MODULE)}'><span class="glyphicon glyphicon-info-sign"></span></a>
													{/if}
													{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
											</label>
										</div>
										<div class="fieldValue col-xs-12 col-sm-7" >
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
										</div>
									</div>
								{/foreach}
								{if $COUNTER eq 1}
									<div class="col-xs-12 col-md-6 fieldsLabelValue {$WIDTHTYPE} paddingLRZero"></div>
								{/if}
							</div>
						</div>
					</div>
				</div>

				{if !empty($SOURCE_RELATED_FIELD)}
					{foreach key=RELATED_FIELD_NAME item=RELATED_FIELD_MODEL from=$SOURCE_RELATED_FIELD}
						<input type="hidden" name="{$RELATED_FIELD_NAME}" value="{$RELATED_FIELD_MODEL->get('fieldvalue')}" data-fieldtype="{$RELATED_FIELD_MODEL->getFieldDataType()}" />
					{/foreach}
				{/if}
			</form>
		</div>
	</div>
</div>
{/strip}
