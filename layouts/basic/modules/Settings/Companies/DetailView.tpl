{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Companies-DetailView -->
	<div class="widget_header row">
		<div class="col-md-12 align-items-center flex-wrap">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			<div class="ml-auto">
				<a href="{$RECORD_MODEL->getEditViewUrl()}" class="btn btn-info float-right ml-2" role="button">
					<span class="fas fa-edit"></span> {App\Language::translate('LBL_EDIT_RECORD', $QUALIFIED_MODULE)}
				</a>
				{if $REMOVE_BTN}
					<button type="button" class="btn btn-danger float-right js-remove" data-js="click"
							data-record-id="{$RECORD_MODEL->getId()}">
						<span class="fas fa-trash-alt mr-1"></span>
						{App\Language::translate('LBL_DELETE_RECORD', $QUALIFIED_MODULE)}
					</button>
				{/if}
			</div>
		</div>
	</div>
	<div class="detailViewInfo" id="groupsDetailContainer">
		<div class="">
			<form id="detailView" class="form-horizontal" method="POST">
				{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
				<table class="table table-bordered">
					<thead>
					<tr class="blockHeader">
						<th colspan="2"
							class="{$WIDTHTYPE}">{App\Language::translate('LBL_COMPANY_INFORMATION',$QUALIFIED_MODULE)}</th>
					</tr>
					</thead>
					<tbody>
					{foreach from=$RECORD_MODEL->getModule()->getNameFields() item=COLUMN}
						<tr>
							<td class="{$WIDTHTYPE} w-25"><label
										class="float-right">{App\Language::translate('LBL_'|cat:$COLUMN|upper, $QUALIFIED_MODULE)}</label>
							</td>
							<td class="{$WIDTHTYPE}">
								{$RECORD_MODEL->getDisplayValue($COLUMN)}
							</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</form>
		</div>
	</div>
	<!-- /tpl-Settings-Companies-DetailView -->
{/strip}
