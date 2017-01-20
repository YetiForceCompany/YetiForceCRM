{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="pdfTemplateContents">
		<form name="EditPdfTemplate" action="index.php" method="post" id="pdf_step1" class="form-horizontal">
			<input type="hidden" name="module" value="PDF">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="mode" value="Step2" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" class="step" value="1" />
			<input type="hidden" name="record" value="{$RECORDID}" />

			<div class="padding1per stepBorder">
				<label>
					<strong>{vtranslate('LBL_STEP_N',$QUALIFIED_MODULE, 1)}: {vtranslate('LBL_ENTER_BASIC_DETAILS',$QUALIFIED_MODULE)}</strong>
				</label>
				<br>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="col-sm-6 controls">
						<select class="chzn-select form-control" id="status" name="status" required="true">
							<option value="1" {if $PDF_MODEL->get('status') eq 1} selected {/if}>
								{vtranslate('PLL_ACTIVE', $QUALIFIED_MODULE)}
							</option>
							<option value="0" {if $PDF_MODEL->get('status') eq 0} selected {/if}>
								{vtranslate('PLL_INACTIVE', $QUALIFIED_MODULE)}
							</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_PRIMARY_NAME', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="col-sm-6 controls">
						<input type="text" name="primary_name" class="form-control" data-validation-engine='validate[required]' value="{$PDF_MODEL->get('primary_name')}" id="primary_name" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_SECONDARY_NAME', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="col-sm-6 controls">
						<input type="text" name="secondary_name" class="form-control" data-validation-engine='validate[required]' value="{$PDF_MODEL->get('secondary_name')}" id="secondary_name" />
					</div>
				</div>
				<!--
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_FOLDER_NAME', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						todo: browse folders for templates
					</div>
				</div>
				-->
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="col-sm-6 controls">
						<select class="chzn-select form-control" id="moduleName" name="module_name" required="true" data-validation-engine='validate[required]' >
							{foreach from=$ALL_MODULES key=TABID item=MODULE_MODEL}
								<option value="{$MODULE_MODEL->getName()}" {if $SELECTED_MODULE == $MODULE_MODEL->getName()} selected {/if}>
									{if $MODULE_MODEL->getName() eq 'Calendar'}
										{vtranslate('LBL_TASK', $MODULE_MODEL->getName())}
									{else}
										{vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}
									{/if}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_METATAGS', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<input type="checkbox" name="metatags_status" id="metatags_status" value="1" class="checkboxForm" {if $PDF_MODEL->get('metatags_status') eq true || $RECORDID eq ''}checked="checked"{/if} />
					</div>
				</div>
				<div class="form-group metatags {if $PDF_MODEL->get('metatags_status') eq true || $RECORDID eq ''}hide{/if}">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_META_TITLE', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<input type="text" name="meta_title" class="form-control" value="{$PDF_MODEL->get('meta_title')}" id="meta_title" />
					</div>
				</div>
				<div class="form-group metatags {if $PDF_MODEL->get('metatags_status') eq true || $RECORDID eq ''}hide{/if}">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_META_AUTHOR', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<select class="chzn-select form-control" id="meta_author" name="meta_author">
							<option value="" selected="">{vtranslate('LBL_SELECT', $QUALIFIED_MODULE)}</option>
							<option value="PLL_COMPANY_NAME" {if $PDF_MODEL->get('meta_author') eq 'PLL_COMPANY_NAME'} selected {/if}>
								{vtranslate('PLL_COMPANY_NAME', $QUALIFIED_MODULE)}
							</option>
							<option value="PLL_USER_CREATING" {if $PDF_MODEL->get('meta_author') eq 'PLL_USER_CREATING'} selected {/if}>
								{vtranslate('PLL_USER_CREATING', $QUALIFIED_MODULE)}
							</option>
						</select>
					</div>
				</div>
				<div class="form-group metatags {if $PDF_MODEL->get('metatags_status') eq true || $RECORDID eq ''}hide{/if}">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_META_CREATOR', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<select class="chzn-select form-control" id="meta_creator" name="meta_creator">
							<option value="" selected="">{vtranslate('LBL_SELECT', $QUALIFIED_MODULE)}</option>
							<option value="PLL_COMPANY_NAME" {if $PDF_MODEL->get('meta_creator') eq 'PLL_COMPANY_NAME'} selected {/if}>
								{vtranslate('PLL_COMPANY_NAME', $QUALIFIED_MODULE)}
							</option>
							<option value="PLL_USER_CREATING" {if $PDF_MODEL->get('meta_creator') eq 'PLL_USER_CREATING'} selected {/if}>
								{vtranslate('PLL_USER_CREATING', $QUALIFIED_MODULE)}
							</option>
						</select>
					</div>
				</div>
				<div class="form-group metatags {if $PDF_MODEL->get('metatags_status') eq true || $RECORDID eq ''}hide{/if}">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_META_SUBJECT', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<input type="text" name="meta_subject" class="form-control" value="{$PDF_MODEL->get('meta_subject')}" id="meta_subject" />
					</div>
				</div>
				<div class="form-group metatags {if $PDF_MODEL->get('metatags_status') eq true || $RECORDID eq ''}hide{/if}">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_META_KEYWORDS', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						{assign 'KEYWORDS' explode(',',$PDF_MODEL->get('meta_keywords'))}
						<select class="select2 form-control" data-tags="false" id="meta_keywords" name="meta_keywords" multiple="multiple">
							{foreach item=KEYWORD from=$KEYWORDS}
								<option value="{$KEYWORD}" selected="selected">{$KEYWORD}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			<br>
			<div class="pull-right">
				<button class="btn btn-success" type="submit" disabled="disabled"><strong>{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
				<button class="btn btn-warning cancelLink" type="reset">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
			</div>
		</form>
	</div>
{/strip}
