{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-header">
		<button class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">x</button>
		<h3 class="modal-title">{\App\Language::translate('LBL_GENERATE_RECORD_FOR_MODULE', $BASE_MODULE_NAME)}</h3>
	</div>
	<div class="modal-body text-center">
		{if $VIEW eq 'List'}
			<input type="hidden" name="all_records" id="all_records" value="{\App\Purifier::encodeHtml(\App\Json::encode($ALL_RECORDS))}" />
			<div class="form-group">
				<div class="radio-inline">
					<label>
						<input type="radio" name="method" id="optionsRadios1" value="0">
						{\App\Language::translate('LBL_AUTOGENERATE',$BASE_MODULE_NAME)}&nbsp;
					</label>
					<span class="popoverTooltip delay0"  data-placement="top"
						  data-content="{\App\Language::translate('LBL_AUTOGENERATE_INFO',$BASE_MODULE_NAME)}">
						<span class="fa fa-info-circle"></span>
					</span>
				</div>
				<div class="radio-inline">
					<label>
						<input type="radio" name="method" id="optionsRadios2" value="1" checked>
						{\App\Language::translate('LBL_OPEN_NEW_WINDOWS',$BASE_MODULE_NAME)}&nbsp;
					</label>
					<span class="popoverTooltip delay0"  data-placement="top"
						  data-content="{\App\Language::translate('LBL_OPEN_NEW_WINDOWS_INFO',$BASE_MODULE_NAME)}">
						<span class="fa fa-info-circle"></span>
					</span>
				</div>
			</div>
		{/if}
		<div class="btn-elements text-center">
			{foreach item=TEMPLATE from=$TEMPLATES}
				{assign var=RELATED_MODEL value=$TEMPLATE->getRelatedModule()}
				<button class="btn btn-light genetateButton" data-id="{$TEMPLATE->getId()}" data-name="{$RELATED_MODEL->getName()}" data-url="{$RELATED_MODEL->getCreateRecordUrl()|cat:"&reference_id=$RECORD"}">
					<span class="userIcon-{$TEMPLATE->getRelatedName()}"></span>
					&nbsp;{\App\Language::translate($TEMPLATE->getRelatedName(), $TEMPLATE->getRelatedName())}
				</button>
			{/foreach}
		</div>
	</div>
	<div class="modal-footer">
		<div class="pull-right">
			<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}</button>
		</div>
	</div>
{/strip}
