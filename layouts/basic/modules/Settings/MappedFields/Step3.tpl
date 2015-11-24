{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="mfTemplateContents leftRightPadding3p">
		<form name="editMFTemplate" action="index.php" method="post" id="mf_step3" class="form-horizontal">
			<input type="hidden" name="module" value="{$MAPPEDFIELDS_MODULE_MODEL->getName()}">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="mode" value="Step4" />
			<input type="hidden" name="parent" value="{$MAPPEDFIELDS_MODULE_MODEL->getParentName()}" />
			<input type="hidden" class="step" value="3" />
			<input type="hidden" name="record" value="{$RECORDID}" />
			<input type="hidden" name="conditions" id="advanced_filter" value='' />
			{include file='AdvanceFilterExpressions.tpl'|@vtemplate_path}
			<br>
			<div class="pull-right">
				<button class="btn btn-danger backStep" type="button"><strong>{vtranslate('LBL_BACK', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
				<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
				<button class="btn btn-warning cancelLink" type="reset">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
			</div>
		</form>
	</div>
{/strip}
