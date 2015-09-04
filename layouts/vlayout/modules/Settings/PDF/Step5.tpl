{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
    <div class="pdfTemplateContents leftRightPadding3p">
        <form name="EditPdfTemplate" action="index.php" method="post" id="pdf_step5" class="form-horizontal">
            <input type="hidden" name="module" value="PDF">
            <input type="hidden" name="view" value="Edit">
            <input type="hidden" name="mode" value="Step6" />
            <input type="hidden" name="parent" value="Settings" />
            <input type="hidden" class="step" value="5" />
            <input type="hidden" name="record" value="{$RECORDID}" />
			{foreach from=$PDF_MODEL->getData() key=NAME item=VALUE}
				<input type="hidden" name="{$NAME}" value="{$VALUE}" />
			{/foreach}

            <div class="padding1per stepBorder">
                <label>
                    <strong>{vtranslate('LBL_STEP_N',$QUALIFIED_MODULE, 2)}: {vtranslate('LBL_ENTER_BASIC_DETAILS',$QUALIFIED_MODULE)}</strong>
                </label>
                <br>
                <div class="form-group">
                    <label class="col-sm-3 control-label">
                        {vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}
                    </label>
                    <div class="col-sm-6 controls">
                        <input type="text" name="summary" class="form-control" value="{$PDF_MODEL->get('summary')}" id="summary" />
                    </div>
                </div>
            </div>
			<br>
			<div class="pull-right">
				<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
				<button class="btn btn-warning cancelLink" type="reset">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
			</div>
		</form>
	</div>
{/strip}
