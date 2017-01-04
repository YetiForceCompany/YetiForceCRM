{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}

{strip}
	<div class="">
		<div class="row widget_header">
			<div class="col-xs-12">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			</div>
		</div>
		<div>
			<div class="btn-group">
				<button type="button" class="btn btn-default">{vtranslate('LBL_ACTIONS', $MODULE)}</button>
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span class="caret"></span>
					<span class="sr-only">Toggle Dropdown</span>
				</button>
				<ul class="dropdown-menu">
					<li><a class="massDelete">{vtranslate('LBL_MASS_DELETE', $QUALIFIED_MODULE)} </a></li>
					<li><a class="massAccept">{vtranslate('LBL_MASS_ACCEPT', $QUALIFIED_MODULE)} </a></li>
					<li><a class="massSendEmailManually">{vtranslate('LBL_MANUAl_MASS_MAILING', $QUALIFIED_MODULE)} </a></li>
					
				</ul>
			</div>
			
		</div>
		<br>
		<div class="widget_header row">
			<div class="col-md-2 pull-left">
				<select class="chzn-select form-control" id="mailQueueFilter" >
					<option value="smtp_id" name="smtp_id" value="">{vtranslate('LBL_SMTP', $QUALIFIED_MODULE)}</option>
					<option value="status" name="status" value="">{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</option>
					<option value="priority" name="priority" value="">{vtranslate('LBL_PRIORITY', $QUALIFIED_MODULE)}</option>
				</select>
			</div>
			<div class="col-md-10 pull-right">
				{include file='ListViewActions.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="listViewContentDiv" id="listViewContents">
		<input type="hidden" id="selectedIds" name="selectedIds" />
		{/strip}
