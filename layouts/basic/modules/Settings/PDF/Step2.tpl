{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="pdfTemplateContents">
		<form name="EditPdfTemplate" action="index.php" method="post" id="pdf_step2" class="form-horizontal">
			<input type="hidden" name="module" value="PDF">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="mode" value="Step3" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" class="step" value="2" />
			<input type="hidden" name="record" value="{$RECORDID}" />

			<div class="padding1per stepBorder">
				<label>
					<strong>{vtranslate('LBL_STEP_N',$QUALIFIED_MODULE, 2)}: {vtranslate('LBL_DOCUMENT_SETTINGS_DETAILS',$QUALIFIED_MODULE)}</strong>
				</label>
				<br>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_PAGE_FORMAT', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="col-sm-6 controls">
						<select class="select2 form-control rtl" id="page_format" name="page_format" data-validation-engine="validate[required]">
							<option value="" selected="">{vtranslate('LBL_SELECT', $QUALIFIED_MODULE)}</option>
							{foreach item=FORMAT from=Settings_PDF_Module_Model::getPageFormats()}
								<option value="{$FORMAT}" {if $PDF_MODEL->get('page_format') eq $FORMAT} selected="selected" {/if}>
									{vtranslate($FORMAT, $QUALIFIED_MODULE)}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_MAIN_MARGIN', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 row">
						{if $PDF_MODEL->get('margin_chkbox') === 0}
							{assign 'MARGIN_CHECKED' false}
						{else}
							{assign 'MARGIN_CHECKED' true}
						{/if}
						<div class="col-sm-1">
							<input type="checkbox" id="margin_chkbox" name="margin_chkbox" value="1" {if $MARGIN_CHECKED eq 'true'}checked="checked"{/if} />
						</div>
						<div class="col-sm-2">
							<input type="text" class="form-control margin_inputs {if $MARGIN_CHECKED eq 'true'}hide{/if}" name="margin_top" id="margin_top" value="{$PDF_MODEL->get('margin_top')}" placeholder="{vtranslate('LBL_TOP', $QUALIFIED_MODULE)}" title="{vtranslate('LBL_TOP_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
						</div>
						<div class="col-sm-2">
							<input type="text" class="form-control margin_inputs {if $MARGIN_CHECKED eq 'true'}hide{/if}" name="margin_right" id="margin_right" value="{$PDF_MODEL->get('margin_right')}" placeholder="{vtranslate('LBL_RIGHT', $QUALIFIED_MODULE)}" title="{vtranslate('LBL_RIGHT_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
						</div>
						<div class="col-sm-2">
							<input type="text" class="form-control margin_inputs {if $MARGIN_CHECKED eq 'true'}hide{/if}" name="margin_bottom" id="margin_bottom" value="{$PDF_MODEL->get('margin_bottom')}" placeholder="{vtranslate('LBL_BOTTOM', $QUALIFIED_MODULE)}" title="{vtranslate('LBL_BOTTOM_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
						</div>
						<div class="col-sm-2">
							<input type="text" class="form-control margin_inputs {if $MARGIN_CHECKED eq 'true'}hide{/if}" name="margin_left" id="margin_left" value="{$PDF_MODEL->get('margin_left')}" placeholder="{vtranslate('LBL_LEFT', $QUALIFIED_MODULE)}" title="{vtranslate('LBL_LEFT_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">&nbsp;</label>
					<div class="col-sm-9 row">
						<div class="col-sm-offset-1 col-sm-2">
							<input type="text" class="form-control margin_inputs {if $MARGIN_CHECKED eq 'true'}hide{/if}" name="header_height" id="header_height" value="{$PDF_MODEL->get('header_height')}" placeholder="{vtranslate('LBL_HEADER_HEIGHT', $QUALIFIED_MODULE)}" title="{vtranslate('LBL_HEADER_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
						</div>
						<div class="col-sm-2">
							<input type="text" class="form-control margin_inputs {if $MARGIN_CHECKED eq 'true'}hide{/if}" name="footer_height" id="footer_height" value="{$PDF_MODEL->get('footer_height')}" placeholder="{vtranslate('LBL_FOOTER_HEIGHT', $QUALIFIED_MODULE)}" title="{vtranslate('LBL_FOOTER_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_PAGE_ORIENTATION', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<select class="select2 form-control" id="page_orientation" name="page_orientation">
							<option value="PLL_PORTRAIT" {if $PDF_MODEL->get('page_orientation') eq 'PLL_PORTRAIT'} selected="selected" {/if}>
								{vtranslate('PLL_PORTRAIT', $QUALIFIED_MODULE)}
							</option>
							<option value="PLL_LANDSCAPE" {if $PDF_MODEL->get('page_orientation') eq 'PLL_LANDSCAPE'} selected="selected" {/if}>
								{vtranslate('PLL_LANDSCAPE', $QUALIFIED_MODULE)}
							</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_LANGUAGE_CHOICE', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<select class="select2 form-control" id="language" name="language">
							<option value="">{\App\Language::translate('LBL_DEFAULT')}</option>
							{foreach from=vtlib\Language::getAll() key=CODE item=NAME}
								<option value="{$CODE}" {if $PDF_MODEL->get('language') eq $CODE} selected="selected" {/if}>
									{vtranslate($NAME, $QUALIFIED_MODULE)}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_FILENAME', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<input type="text" name="filename" class="form-control" value="{$PDF_MODEL->get('filename')}" id="filename" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_VISIBILITY', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="col-sm-6 controls">
						{assign 'VISIBILITY' explode(',',$PDF_MODEL->get('visibility'))}
						<select class="select2 form-control rtl" data-tags="false" id="visibility" name="visibility" multiple="multiple" data-validation-engine="validate[required]">
							<option value="PLL_LISTVIEW" {if in_array('PLL_LISTVIEW', $VISIBILITY)}selected="selected"{/if}>{vtranslate('PLL_LISTVIEW', $QUALIFIED_MODULE)}</option> 
							<option value="PLL_DETAILVIEW" {if in_array('PLL_DETAILVIEW', $VISIBILITY)}selected="selected"{/if}>{vtranslate('PLL_DETAILVIEW', $QUALIFIED_MODULE)}</option> 
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_DEFAULT_TPL', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6">
						{if $PDF_MODEL->get('default') === 0}
							{assign 'DEFAULT' false}
						{else}
							{assign 'DEFAULT' true}
						{/if}
						<input type="checkbox" id="default" name="default" value="1" {if $DEFAULT eq 'true'}checked="checked"{/if} />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_GENERATE_ONE_PDF', $QUALIFIED_MODULE)}
						<span class="popoverTooltip delay0"  data-placement="top"
							  data-content="{vtranslate('LBL_GENERATE_ONE_PDF_INFO',$QUALIFIED_MODULE)}">
							<span class="glyphicon glyphicon-info-sign"></span>
						</span>
					</label>
					<div class="col-sm-6">
						{if $PDF_MODEL->get('one_pdf') == 0}
							{assign 'ONE_PDF' false}
						{else}
							{assign 'ONE_PDF' true}
						{/if}
						<input type="checkbox" id="one_pdf" name="one_pdf" value="1" {if $ONE_PDF eq 'true'}checked="checked"{/if} />
					</div>
				</div>
			</div>
			<br>
			<div class="pull-right">
				<button class="btn btn-danger backStep" type="button"><strong>{vtranslate('LBL_BACK', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
				<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
				<button class="btn btn-warning cancelLink" type="reset">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
			</div>
		</form>
	</div>
{/strip}
