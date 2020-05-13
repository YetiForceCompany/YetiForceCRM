{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-OSSPasswrds-Detail-BlocksView -->
{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
	{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
	{if $BLOCK eq null or $FIELD_MODEL_LIST|@count lte 0}
		{continue}
	{/if}
	{assign var=BLOCKS_HIDE value=$BLOCK->isHideBlock($RECORD,$VIEW)}
	{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
	{assign var=IS_DYNAMIC value=$BLOCK->isDynamic()}
	{if $BLOCKS_HIDE}
		{include file=\App\Layout::getTemplatePath('Detail/BlocksView.tpl', $MODULE_NAME)}
	{/if}
{/foreach}
<div class="contentHeader form-row m-0">
	<div class="col-12 px-0">
		<div class="float-right">
			<button class="btn btn-success d-none" data-copy-target="detailPassword" id="copy-button" type="button"
					title="{\App\Language::translate('LBL_CopyToClipboardTitle', $MODULE_NAME)}"><span
						class="fas fa-copy"></span> {\App\Language::translate('LBL_CopyToClipboard', $MODULE_NAME)}
			</button>&nbsp;&nbsp;
			<button class="btn btn-warning" onclick="PasswordHelper.showDetailsPassword('{$smarty.get.record}');return false;"
					id="show-btn">
				<span class="fas fa-eye u-mr-5px"></span>{\App\Language::translate('LBL_ShowPassword', $MODULE_NAME)}
			</button>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<!-- /tpl-OSSPasswrds-Detail-BlocksView -->
{/strip}
