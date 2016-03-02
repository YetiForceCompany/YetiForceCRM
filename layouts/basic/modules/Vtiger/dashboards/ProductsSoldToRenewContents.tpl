{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
<div class="col-sm-12">

	{* Comupte the nubmer of columns required *}
	{assign var="SPANSIZE" value=12}
	{if $WIDGET_MODEL->getHeaderCount()}
		{assign var="SPANSIZE" value=12/$WIDGET_MODEL->getHeaderCount()}
	{/if}

	<div class="row">
		{foreach item=FIELD from=$WIDGET_MODEL->getHeaders()}
			<div class="col-sm-{$SPANSIZE}"><strong>{vtranslate($FIELD->get('label'),$BASE_MODULE)} </strong></div>
		{/foreach}
	</div>
	{assign var="WIDGET_RECORDS" value=$WIDGET_MODEL->getRecords($OWNER)}
	{foreach item=RECORD from=$WIDGET_RECORDS}
		<div class="row rowAction cursorPointer" data-modalid="modal-{$RECORD->getId()}">
			{foreach item=FIELD from=$WIDGET_MODEL->getHeaders()}
				<div class="col-sm-{$SPANSIZE} textOverflowEllipsis" title="{strip_tags($RECORD->get($FIELD->get('name')))}">
					{if $RECORD->get($FIELD->get('name'))}
						<span class="pull-left">{vtranslate($RECORD->get($FIELD->get('name')), $BASE_MODULE)}</span>
					{else}
						&nbsp;
					{/if}
				</div>
			{/foreach}
			<div id="modal-{$RECORD->getId()}" class="modal fade">
				<div class="modal-dialog modal-sm">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title">{vtranslate('LBL_SELECT_ACTION', $BASE_MODULE)}</h4>
						</div>
						<div class="modal-body btn-elements text-center">
							{if $RECORD->editFieldByModalPermission(true)}
								<button class="showModal btn btn-danger btn-lg" title="{vtranslate('LBL_SET_RECORD_STATUS', $BASE_MODULE)}" data-url="{$RECORD->getEditFieldByModalUrl()}">
									<span class="glyphicon glyphicon-modal-window"></span>
								</button>&nbsp;
								<button class="showModal btn-lg btn btn-primary" title="{vtranslate('LBL_SET_RENEWAL', $BASE_MODULE)}" data-url="{$RECORD->getEditFieldByModalUrl()|cat:'&changeEditFieldByModal=assets_renew'}">
									<span class="glyphicon glyphicon-repeat"></span>
								</button>&nbsp;
							{elseif $RECORD->isViewable()}
								<a href="{$RECORD->getDetailViewUrl()}" title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS',$BASE_MODULE)}" class="btn btn-default btn-lg"><span class="glyphicon glyphicon-th-list"></span></a>
							{/if}
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{vtranslate('LBL_CLOSE', $BASE_MODULE)}</button>
						</div>
					</div>
				</div>
			 </div>
		</div>
	{/foreach}

	{if count($WIDGET_RECORDS) >= $WIDGET_MODEL->getRecordLimit()}
		<div class="">
			<a class="pull-right" href="index.php?module={$WIDGET_MODEL->getTargetModule()}&view=List&mode=showListViewRecords&viewname={$WIDGET->get('filterid')}">{vtranslate('LBL_MORE')}</a>
		</div>
	{/if}

</div>
