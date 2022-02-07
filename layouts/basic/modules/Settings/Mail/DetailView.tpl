{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="o-breadcrumb widget_header row">
		<div class="col-md-8">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
		<div class="col-md-4">
			{if $RECORD_MODEL}
				<div class="float-right btn-toolbar my-2">
					<button class="btn btn-info sendManually">
						<span class="fas fa-paper-plane mr-1"></span>
						<strong>{App\Language::translate('LBL_MANUAL_SENDING', $QUALIFIED_MODULE)}</strong>
					</button>
					{if $RECORD_MODEL->get('status') eq 0}
						<button class="btn btn-success acceptanceRecord marginLeft5">
							<span class="fas fa-check mr-1"></span>
							<strong>{App\Language::translate('LBL_ACCEPTANCE_RECORD', $QUALIFIED_MODULE)}</strong>
						</button>
					{/if}
					<button class="btn btn-danger marginLeft5 js-delete" data-js="click">
						<span class="fas fa-trash-alt mr-1"></span>
						<strong>{App\Language::translate('LBL_DELETE_RECORD', $QUALIFIED_MODULE)}</strong>
					</button>
				</div>
			{/if}
		</div>
	</div>
	{if {$RECORD_MODEL->get('status')}==2 }
		<div class="alert alert-warning">
			{$RECORD_MODEL->getDisplayValue('error')}
		</div>
	{/if}
	<div class="detailViewInfo">
		{if $RECORD_MODEL}
			<input type="hidden" value="{$RECORD_MODEL->getId()}" id="recordId">
			<table class="table table-bordered">
				<thead>
					<tr class="blockHeader">
						<th colspan="2" class="{$WIDTHTYPE}"><strong>{App\Language::translate('LBL_EMAIL_DETAIL',$QUALIFIED_MODULE)}</strong></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="{$WIDTHTYPE} w-25 text-right"><label>{App\Language::translate('LBL_SMTP_NAME', $QUALIFIED_MODULE)} </label></td>
						<td class="{$WIDTHTYPE} w-75">
							{$RECORD_MODEL->getDisplayValue('smtp_id')}
						</td>
					</tr>
					<tr>
						<td class="{$WIDTHTYPE} w-25 text-right"><label>{App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)} </label></td>
						<td class="{$WIDTHTYPE} w-75">
							{$RECORD_MODEL->getDisplayValue('date')}
						</td>
					</tr>
					<tr>
						<td class="{$WIDTHTYPE} w-25 text-right"><label>{App\Language::translate('LBL_CREATED_BY', $QUALIFIED_MODULE)} </label></td>
						<td class="{$WIDTHTYPE} w-75">
							{$RECORD_MODEL->getDisplayValue('owner')}
						</td>
					</tr>
					<tr>
						<td class="{$WIDTHTYPE} w-25 text-right"><label>{App\Language::translate('LBL_PRIORITY', $QUALIFIED_MODULE)} </label></td>
						<td class="{$WIDTHTYPE} w-75">
							{App\Language::translate($RECORD_MODEL->getDisplayValue('priority'))}
						</td>
					</tr>
					<tr>
						<td class="{$WIDTHTYPE} w-25 text-right"><label>{App\Language::translate('LBL_STATUS', $QUALIFIED_MODULE)} </label></td>
						<td class="{$WIDTHTYPE} w-75">
							{$RECORD_MODEL->getDisplayValue('status')}
						</td>
					</tr>
					{if !empty($RECORD_MODEL->getDisplayValue('from'))}
						<tr>
							<td class="{$WIDTHTYPE} w-25 text-right"><label>{App\Language::translate('LBL_FROM', $QUALIFIED_MODULE)} </label></td>
							<td class="{$WIDTHTYPE} w-75">
								{$RECORD_MODEL->getDisplayValue('from')}
							</td>
						</tr>
					{/if}
					{if !empty($RECORD_MODEL->getDisplayValue('to'))}
						<tr>
							<td class="{$WIDTHTYPE} w-25 text-right"><label>{App\Language::translate('LBL_TO', $QUALIFIED_MODULE)} </label></td>
							<td class="{$WIDTHTYPE} w-75">
								{$RECORD_MODEL->getDisplayValue('to')}
							</td>
						</tr>
					{/if}
					{if !empty($RECORD_MODEL->getDisplayValue('cc'))}
						<tr>
							<td class="{$WIDTHTYPE} w-25 text-right "><label>{App\Language::translate('LBL_CC', $QUALIFIED_MODULE)} </label></td>
							<td class="{$WIDTHTYPE} w-75">
								{$RECORD_MODEL->getDisplayValue('cc')}
							</td>
						</tr>
					{/if}
					{if !empty($RECORD_MODEL->getDisplayValue('bcc'))}
						<tr>
							<td class="{$WIDTHTYPE} w-25 text-right"><label>{App\Language::translate('LBL_BCC', $QUALIFIED_MODULE)} </label></td>
							<td class="{$WIDTHTYPE} w-75">
								{$RECORD_MODEL->getDisplayValue('bcc')}
							</td>
						</tr>
					{/if}
					<tr>
						<td class="{$WIDTHTYPE} w-25 text-right"><label>{App\Language::translate('LBL_SUBJECT', $QUALIFIED_MODULE)} </label></td>
						<td class="{$WIDTHTYPE} w-75">
							{$RECORD_MODEL->getDisplayValue('subject')}
						</td>
					</tr>
					{if !empty($RECORD_MODEL->getDisplayValue('attachments'))}
						<tr>
							<td class="{$WIDTHTYPE} w-25 text-right"><label>{App\Language::translate('LBL_ATTACHMENTS', $QUALIFIED_MODULE)} </label></td>
							<td class="{$WIDTHTYPE} w-75">
								{$RECORD_MODEL->getDisplayValue('attachments')}
							</td>
						</tr>
					{/if}
					<tr>
						<td class="{$WIDTHTYPE} w-25 text-right"><label>{App\Language::translate('LBL_CONTENT', $QUALIFIED_MODULE)} </label></td>
						<td class="{$WIDTHTYPE} w-75">
							{$RECORD_MODEL->getDisplayValue('content')}
						</td>
					</tr>
				</tbody>
			</table>
		{else}
			<div class="alert alert-block alert-info">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<strong>
					<p>{App\Language::translate('LBL_EMAIL_WAS_SENT', $QUALIFIED_MODULE)}</p>
				</strong>
				<a class="btn btn-info" role="button" href="{$MODULE_MODEL->getDefaultUrl()}">{App\Language::translate('LBL_BACK', $QUALIFIED_MODULE)}</a>
			</div>
		{/if}
	</div>
{/strip}
