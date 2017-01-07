{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	<div class="widget_header row">
		<div class="col-md-8">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{if isset($SELECTED_PAGE)}
				{App\Language::translate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
			{/if}
		</div>
		<div class="col-md-4 marginbottomZero">
			<div class="pull-right btn-toolbar"><span class="actionImages">
					<button class="btn btn-info sendManually">
						<span class="glyphicon glyphicon-send"></span>
						<strong class="marginLeft5">{App\Language::translate('LBL_MANUAL_SENDING', $QUALIFIED_MODULE)}</strong>
					</button>
					{if $RECORD_MODEL->get('status') eq 0}
						<button class="btn btn-success acceptanceRecord marginLeft5">
							<strong>{App\Language::translate('LBL_ACCEPTANCE_RECORD', $QUALIFIED_MODULE)}</strong>
						</button>
					{/if}
					<a class="btn btn-danger marginLeft5" href="{$RECORD_MODEL->getDeleteActionUrl()}">
						<strong>{App\Language::translate('LBL_DELETE_RECORD', $QUALIFIED_MODULE)}</strong>
					</a>
			</div>
		</div>
	</div>
	<input type="hidden" value="{$RECORD_MODEL->getId()}" id="recordId">
	<div class="detailViewInfo">
		<table class="table table-bordered">
			<thead>
				<tr class="blockHeader">
					<th colspan="2" class="col-md-8"><strong>{App\Language::translate('LBL_EMAIL_DETAIL',$QUALIFIED_MODULE)}</strong></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="col-md-3" ><label class="pull-right">{App\Language::translate('LBL_SMTP_NAME', $QUALIFIED_MODULE)} </label></td>
					<td class="col-md-8">
						{$RECORD_MODEL->getDisplayValue('smtp_id')}
					</td>
				</tr>
				<tr>
					<td class="col-md-3" ><label class="pull-right">{App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}  </label></td>
					<td class="col-md-8">
						{$RECORD_MODEL->getDisplayValue('date')}
					</td>
				</tr>
				<tr>
					<td class="col-md-3" ><label class="pull-right">{App\Language::translate('LBL_CREATED_BY', $QUALIFIED_MODULE)}  </label></td>
					<td class="col-md-8">
						{$RECORD_MODEL->getDisplayValue('owner')}
					</td>
				</tr>
				<tr>
					<td class="col-md-3" ><label class="pull-right">{App\Language::translate('LBL_PRIORITY', $QUALIFIED_MODULE)}  </label></td>
					<td class="col-md-8">
						{App\Language::translate($RECORD_MODEL->getDisplayValue('priority'))}
					</td>
				</tr>
				<tr>
					<td class="col-md-3" ><label class="pull-right">	{App\Language::translate('LBL_STATUS', $QUALIFIED_MODULE)}  </label></td>
					<td class="col-md-8">
						{$RECORD_MODEL->getDisplayValue('status')}
					</td>
				</tr>
				{if !empty($RECORD_MODEL->getDisplayValue('from'))}
					<tr>
						<td class="col-md-3" ><label class="pull-right">	{App\Language::translate('LBL_FROM', $QUALIFIED_MODULE)}  </label></td>
						<td class="col-md-8">
							{$RECORD_MODEL->getDisplayValue('from')}
						</td>
					</tr>
				{/if}
				{if !empty($RECORD_MODEL->getDisplayValue('to'))}
					<tr>
						<td class="col-md-3" ><label class="pull-right">{App\Language::translate('LBL_TO', $QUALIFIED_MODULE)}  </label></td>
						<td class="col-md-8">
							{$RECORD_MODEL->getDisplayValue('to')}
						</td>
					</tr>
				{/if}
				{if !empty($RECORD_MODEL->getDisplayValue('cc'))}
					<tr>
						<td class="col-md-3 "><label class="pull-right">{App\Language::translate('LBL_CC', $QUALIFIED_MODULE)}  </label></td>
						<td class="col-md-8">
							{$RECORD_MODEL->getDisplayValue('cc')}
						</td>
					</tr>
				{/if}
				{if !empty($RECORD_MODEL->getDisplayValue('bcc'))}
					<tr>
						<td class="col-md-3" ><label class="pull-right">{App\Language::translate('LBL_BCC', $QUALIFIED_MODULE)}  </label></td>
						<td class="col-md-8">
							{$RECORD_MODEL->getDisplayValue('bcc')}
						</td>
					</tr>
				{/if}
				<tr>
					<td class="col-md-3" ><label class="pull-right">{App\Language::translate('LBL_SUBJECT', $QUALIFIED_MODULE)}  </label></td>
					<td class="col-md-8">
						{$RECORD_MODEL->getDisplayValue('subject')}
					</td>
				</tr>
				<tr>
					<td class="col-md-3" ><label class="pull-right">	{App\Language::translate('LBL_ATTACHMENTS', $QUALIFIED_MODULE)}  </label></td>
					<td class="col-md-8">
						{$RECORD_MODEL->getDisplayValue('attachments')}
					</td>
				</tr>
				<tr>
					<td class="col-md-3" ><label class="pull-right">{App\Language::translate('LBL_CONTENT', $QUALIFIED_MODULE)}  </label></td>
					<td class="col-md-8">
						{$RECORD_MODEL->getDisplayValue('content')}
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	{strip}
