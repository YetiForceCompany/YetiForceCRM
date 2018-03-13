{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
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
						<div class="col-5 px-0">
							<div class="float-left">
								<h3 class="modal-title quickCreateTitle">{\App\Language::translate('LBL_QUICK_CREATE', $MODULE)}:&nbsp; <p class="textTransform"><b>{\App\Language::translate($SINGLE_MODULE, $MODULE)}<b></p></h3>
							</div>
						</div>
						<div class="col-7 px-0">
							<div class="float-right quickCreateActions pullRight">
								{foreach item=LINK from=$QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER']}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='quickcreateViewHeader'}
								&nbsp;&nbsp;
								{/foreach}
								{assign var="EDIT_VIEW_URL" value=$MODULE_MODEL->getCreateRecordUrl()}
								<button class="btn btn-light goToFullFormOne" id="goToFullForm" data-edit-view-url="{$EDIT_VIEW_URL}" type="button"><strong>{\App\Language::translate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button>&nbsp;
								<button class="btn btn-success" type="submit" title="{\App\Language::translate('LBL_SAVE', $MODULE)}"><strong><span class="fas fa-check"></span></strong></button>
								<button class="cancelLink btn btn-warning" type="reset" aria-hidden="true" data-dismiss="modal"	type="button" title="{\App\Language::translate('LBL_CLOSE')}"><span class="fas fa-times"></span></button>
							</div>
						</div>
						<div class="clearfix"></div>
					</div>
					{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
					<input type="hidden" name="picklistDependency" value='{\App\Purifier::encodeHtml($PICKIST_DEPENDENCY_DATASOURCE)}' />
					{/if}
					{if !empty($MAPPING_RELATED_FIELD)}
					<input type="hidden" name="mappingRelatedField" value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}' />
					{/if}
					<input type="hidden" id="disallowLongerThan24Hours" value="{AppConfig::module('OSSTimeControl', 'DISALLOW_LONGER_THAN_24_HOURS')}" />
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="action" value="SaveAjax" />
					<div class="quickCreateContent">
						<div class="modal-body m-0">
							<div class="massEditTable px-1 mx-auto m-0">
								<div class="col-12 px-0 form-row">
									{assign var=COUNTER value=0}
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
									{if in_array($FIELD_NAME, ['time_start','time_end'])}{continue}{/if}
									{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
									{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
									{assign var="refrenceListCount" value=count($refrenceList)}
									{if $COUNTER eq 2}
								</div>
								<div class="col-12 px-0 form-row">
									{assign var=COUNTER value=1}
									{else}
									{assign var=COUNTER value=$COUNTER+1}
									{/if}
									<div class="col-12 col-md-6 py-2 form-row px-0 {$WIDTHTYPE}">
										<div class="fieldLabel col-12 col-sm-5">
											{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
											{assign var=HELPINFO_LABEL value=$MODULE|cat:'|'|cat:$FIELD_MODEL->getFieldLabel()}
											<label class="muted small font-weight-bold float-sm-left float-md-right float-lg-right">
												{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span>{/if}
												{if in_array($VIEW,$HELPINFO) && \App\Language::translate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
													<a href="#" class="js-help-info float-right" title="" data-placement="auto top" data-content="{htmlspecialchars(\App\Language::translate($MODULE|cat:'|'|cat:$FIELD_MODEL->getFieldLabel(), 'HelpInfo'))}" data-original-title='{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}'><span class="fas fa-info-circle"></span></a>
												{/if}
												{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}
											</label>
										</div>
										<div class="fieldValue col-12 col-sm-7" >
											{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE)}
										</div>
									</div>
									{/foreach}
									{if $COUNTER eq 1}
										<div class="col-12 col-md-6 py-2 form-row px-0 {$WIDTHTYPE}"></div>
									{/if}
								</div>
							</div>
						</div>
						</div>
						{if !empty($SOURCE_RELATED_FIELD)}
							{foreach key=RELATED_FIELD_NAME item=RELATED_FIELD_MODEL from=$SOURCE_RELATED_FIELD}
								<input type="hidden" name="{$RELATED_FIELD_NAME}" value="{\App\Purifier::encodeHtml($RELATED_FIELD_MODEL->get('fieldvalue'))}" data-fieldtype="{$RELATED_FIELD_MODEL->getFieldDataType()}" />
							{/foreach}
						{/if}
					</form>
			</div>
		</div>
	</div>
</div>
{/strip}
