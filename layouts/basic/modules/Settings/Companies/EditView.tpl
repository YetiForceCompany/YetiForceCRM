{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	<div class="row widget_header">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{App\Language::translate('LBL_COMPANIES_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
	</div>
	<div class="editViewContainer">
		<form name="EditCompanies" action="index.php" method="post" id="EditView" class="form-horizontal" enctype="multipart/form-data">
			{if $COMPANY_COLUMNS}
				<input type="hidden" name="module" value="Companies">
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="action" value="SaveAjax">
				<input type="hidden" name="mode" value="updateCompany">
				<input type="hidden" name="record" value="{$RECORD_ID}">
				{foreach from=$COMPANY_COLUMNS item=COLUMN}
					<div class="form-group">
						{if $COLUMN eq 'default'}
							{if $RECORD_MODEL->get($COLUMN) eq 0}
								<label class="col-sm-2 control-label">
									{App\Language::translate('LBL_'|cat:$COLUMN|upper, $QUALIFIED_MODULE)}
								</label>
								<div class="col-sm-10">
									<input type="checkbox" name="{$COLUMN}" value="1" {if $RECORD_MODEL->get({$COLUMN}) eq 1}  checked {/if}>
								</div>
							{/if}
						{elseif $COLUMN eq 'industry'}
							<label class="col-sm-2 control-label">
								{App\Language::translate('LBL_INDUSTRY', $QUALIFIED_MODULE)}
							</label>
							<div class="col-sm-10">
								<select class="select2 form-control" name="industry">
									{foreach from=$INDUSTRY_LIST item=ITEM}
										<option value="{$ITEM}">{App\Language::translate($ITEM)}</option>
									{/foreach}
								</select>
							</div>
						{elseif $COLUMN neq 'logo_login' && $COLUMN neq 'logo_main' && $COLUMN neq 'logo_mail'}
							<label class="col-sm-2 control-label">
								{App\Language::translate('LBL_'|cat:$COLUMN|upper, $QUALIFIED_MODULE)}
							</label>
							<div class="col-sm-10">
								<input class="form-control" {if $COLUMN eq 'id'} readonly {/if}
									   {if $COLUMN eq 'name' }data-validation-engine="validate[required]"{/if}
									   name="{$COLUMN}" value="{$RECORD_MODEL->get($COLUMN)}" >
							</div>
						{else}
							<div class="col-sm-3">
								{$RECORD_MODEL->getDisplayValue($COLUMN)}
							</div>
							<div class="col-sm-9">
								<div class='col-xs-12'>
									<div class=''>
										<input type="file" name="{$COLUMN}" id="{$COLUMN}" {if !$RECORD_ID }data-validation-engine="validate[required]"{/if}/>&nbsp;&nbsp;
									</div>
									<div class=" col-xs-12 alert alert-info pull-right">
										{App\Language::translate('LBL_PANELLOGO_RECOMMENDED_MESSAGE',$QUALIFIED_MODULE)}
									</div>
								</div>
							</div>
						{/if}
					</div>
				{/foreach}
			{/if}
			<div class="row">
				<div class="col-md-5 pull-right">
					<span class="pull-right">
						<button class="btn btn-success" type="submit"><strong>{App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
						<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
					</span>
				</div>
			</div>
		</form>
	</div>
{/strip}
