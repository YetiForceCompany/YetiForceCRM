{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
    <div class="pdfTemplateContents leftRightPadding3p">
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
                        {vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}
                    </label>
                    <div class="col-sm-6 controls">
                        {if $MODE eq 'edit'}
                            <input type='text' disabled='disabled' class="form-control" value="{vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}" >
                            <input type='hidden' name='module_name' value="{$MODULE_MODEL->get('name')}" >
                        {else}
                            <select class="chzn-select form-control" id="moduleName" name="module_name" required="true" data-placeholder="Select Module...">
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
                        {/if}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">
                        {vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}<span class="redColor">*</span>
                    </label>
                    <div class="col-sm-6 controls">
                        <input type="text" name="summary" class="form-control" data-validation-engine='validate[required]' value="{$PDF_MODEL->get('summary')}" id="summary" />
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
