{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<input type="hidden" id="allowedLetters" value="{$allowChars}"/>
	<input type="hidden" id="maxChars" value="{$passLengthMax}"/>
	<input type="hidden" id="minChars" value="{$passLengthMin}"/>
	<div class="tpl-QuickCreate modelContainer modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-full" role="document">
			<div class="modal-content">
				<form class="form-horizontal recordEditView" name="QuickCreate" method="post" action="index.php">
					<div class="modal-header col-12 m-0 align-items-center form-row d-flex justify-content-between pb-1">
						<div class="col-xl-6 col-12">
							<h5 class="modal-title form-row text-center text-xl-left mb-2 mb-xl-0">
								<span class="col-12">
									<span class="fas fa-plus mr-1"></span>
									<strong class="mr-1">{\App\Language::translate('LBL_QUICK_CREATE', $MODULE)}:</strong>
									<strong class="text-uppercase u-text-ellipsis"><span class="userIcon-{$MODULE} mx-1"></span>{\App\Language::translate($SINGLE_MODULE, $MODULE)}</strong>
								</span>
							</h5>
						</div>
						<div class="col-xl-6 col-12 text-center text-xl-right">
							{foreach item=LINK from=$QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER']}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='quickcreateViewHeader'}
							{/foreach}
							{assign var="EDIT_VIEW_URL" value=$MODULE_MODEL->getCreateRecordUrl()}
							<button class="btn btn-outline-secondary mr-0 mr-md-1 mb-2 mb-md-0 col-12 col-md-4 u-text-ellipsis" id="goToFullForm"
									data-edit-view-url="{$EDIT_VIEW_URL}" type="button">
								<strong>{\App\Language::translate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong>
							</button>
							<button class="btn btn-success js-generatePass mr-0 mr-md-1 mb-2 mb-md-0 col-12 col-md-4 u-text-ellipsis" name="save" type="button">
								<strong>{\App\Language::translate('Generate Password', $RELATEDMODULE)}</strong>
							</button>
							<button class="btn btn-success col-12 col-md-1 mb-2 mb-md-0" type="submit"
									title="{\App\Language::translate('LBL_SAVE', $MODULE)}"><strong><span
											class="fas fa-check"></span></strong>
							</button>
							<button class="cancelLink btn btn-danger col-12 col-md-1 ml-0 ml-md-1" aria-hidden="true" data-dismiss="modal"
									type="button" title="{\App\Language::translate('LBL_CLOSE')}"><span
										class="fas fa-times"></span>
							</button>
						</div>
					</div>
					{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
						<input type="hidden" name="picklistDependency"
							   value='{\App\Purifier::encodeHtml($PICKIST_DEPENDENCY_DATASOURCE)}'/>
					{/if}
					{if !empty($MAPPING_RELATED_FIELD)}
						<input type="hidden" name="mappingRelatedField"
							   value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}'/>
					{/if}
					<input type="hidden" name="module" value="{$MODULE}"/>
					<input type="hidden" name="action" value="SaveAjax"/>
					<div class="quickCreateContent">
						<div class="modal-body m-0">
							<div class="massEditTable border-0 px-1 mx-auto m-0">
								<div class="px-0 m-0 form-row d-flex justify-content-center">
									{assign var=COUNTER value=0}
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
									{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
									{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
									{assign var="refrenceListCount" value=count($refrenceList)}
									{if $COUNTER eq 2}
								</div>
								<div class="col-12 form-row d-flex justify-content-center px-0 m-0">
									{assign var=COUNTER value=1}
									{else}
									{assign var=COUNTER value=$COUNTER+1}
									{/if}
									<div class="col-md-6 py-2 form-row align-items-center px-0 m-0 {$WIDTHTYPE}">
										<div class="fieldLabel col-lg-12 col-xl-3 pl-0 text-lg-left text-xl-right u-text-ellipsis">
											{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
											{assign var=HELPINFO_LABEL value=$MODULE|cat:'|'|cat:$FIELD_MODEL->getFieldLabel()}
											<label class="muted small font-weight-bold px-2">
												{if $FIELD_MODEL->isMandatory() eq true}
													<span class="redColor">*</span>
												{/if}
												{if in_array($VIEW,$HELPINFO) && \App\Language::translate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
													<a href="#" class="js-help-info float-right" title=""
													   data-placement="top"
													   data-content="{\App\Language::translate($HELPINFO_LABEL, 'HelpInfo')}"
													   data-original-title='{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}'>
														<span class="fas fa-info-circle"></span>
													</a>
												{/if}
												{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}
											</label>
										</div>
										<div class="fieldValue col-lg-12 col-xl-9 px-0 px-sm-1 align-items-center">
											{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE)}
										</div>
									</div>
									{/foreach}
									{if $COUNTER eq 1}
										<div class="col-md-6 form-row align-items-center p-1 {$WIDTHTYPE}"></div>
									{/if}
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<link rel="stylesheet" type="text/css"
		  href="{\App\Layout::getLayoutFile('modules/OSSPasswords/resources/validate_pass.css')}">
	<script type="text/javascript"
			src="{\App\Layout::getLayoutFile('modules/OSSPasswords/resources/gen_pass.js')}"></script>
{/strip}
