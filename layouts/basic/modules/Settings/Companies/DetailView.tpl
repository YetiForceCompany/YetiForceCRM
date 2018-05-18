{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="tpl-Settings-Companies-DetailView widget_header row">
		<div class="col-md-8">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			{if isset($SELECTED_PAGE)}
				{App\Language::translate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
			{/if}
		</div>
		<div class="col-md-4 mt-2">
			<a href="{$RECORD_MODEL->getEditViewUrl()}" class="btn btn-info float-right ml-2">
				<span class="fas fa-edit mr-1"></span>
				<strong>{App\Language::translate('LBL_EDIT_RECORD', $QUALIFIED_MODULE)}</strong>
			</a>
			{if $RECORD_MODEL->get('default') eq 0}
				<button type="button" class="btn btn-danger float-right js-remove" data-js="click" data-record-id="{$RECORD_MODEL->getId()}">
					<span class="fas fa-trash-alt mr-1"></span>
					<strong>{App\Language::translate('LBL_DELETE_RECORD', $QUALIFIED_MODULE)}</strong>
				</button>
			{/if}
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
									<tr class="row">
										<td class="{$WIDTHTYPE} col-md-3"><label class="float-right">{App\Language::translate('LBL_'|cat:$COLUMN|upper, $QUALIFIED_MODULE)}</label></td>
										<td class="{$WIDTHTYPE} col-md-9" >
											{$RECORD_MODEL->getDisplayValue($COLUMN)}
										</td>
									</tr>
								{else}
									<tr class="row">
										<td class="{$WIDTHTYPE} col-md-3"><label class="float-right">{App\Language::translate('LBL_'|cat:$COLUMN|upper, $QUALIFIED_MODULE)}</label></td>
										<td class="{$WIDTHTYPE} col-md-9">
											{$RECORD_MODEL->getDisplayValue($COLUMN)}
										</td>
									</tr>

								{/if}
							{/foreach}
						</tbody>
					</table>
				{/if}
			</form>
		</div>
	</div>
	{strip}
