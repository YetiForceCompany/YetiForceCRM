{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	<div class="widget_header row">
		<div class="col-md-8">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{if isset($SELECTED_PAGE)}
				{App\Language::translate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
			{/if}
		</div>
		<div class="col-md-4 ">
			<a href="{$RECORD_MODEL->getEditViewUrl()}" class="btn btn-info pull-right">
				<strong>{App\Language::translate('LBL_EDIT_RECORD', $QUALIFIED_MODULE)}</strong>
			</a>
		</div>
	</div>
	<div class="detailViewInfo" id="groupsDetailContainer">
		<div class="">
			<form id="detailView" class="form-horizontal" method="POST">
				{if $COMPANY_COLUMNS}
					{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
					<table class="table table-bordered">
						<thead>
							<tr class="blockHeader">
								<th colspan="2" class="{$WIDTHTYPE}"><strong>{App\Language::translate('LBL_COMPANY_INFORMATION',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$COMPANY_COLUMNS item=COLUMN}
								{if $COLUMN neq 'logo_login' && $COLUMN neq 'logo_main'  && $COLUMN neq 'logo_mail'}
									<tr>
										<td class="{$WIDTHTYPE} col-md-3"><label class="pull-right">{App\Language::translate('LBL_'|cat:$COLUMN|upper, $QUALIFIED_MODULE)}</label></td>
										<td class="{$WIDTHTYPE} col-md-8" >
											{$RECORD_MODEL->getDisplayValue($COLUMN)}
										</td>
									</tr>
								{else}
									<tr>
										<td class="{$WIDTHTYPE} col-md-3"><label class="pull-right">{App\Language::translate('LBL_'|cat:$COLUMN|upper, $QUALIFIED_MODULE)}</label></td>
										<td class="{$WIDTHTYPE} col-md-8" >
											{$RECORD_MODEL->getDisplayValue($COLUMN)}
										</td>
									</tr>
									
								{/if}
							{/foreach}
						</tbody>
					</table>


				{else}
					brak kolumns
				{/if}
			</form>
		</div>
	</div>
	{strip}
