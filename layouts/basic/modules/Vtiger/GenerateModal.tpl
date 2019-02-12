{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-header align-items-center">
		<h5 class="modal-title">
			<span class="fas fa-plus-circle mr-2"></span>
			{\App\Language::translate('LBL_GENERATE_RECORD_FOR_MODULE', $BASE_MODULE_NAME)}
		</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body">
		{if $VIEW eq 'List'}
			<input type="hidden" name="all_records" id="all_records" value="{\App\Purifier::encodeHtml(\App\Json::encode($ALL_RECORDS))}" />
			<div class="form-group form-row d-flex justify-content-center">
				<div class="radio-inline mr-3">
					<label>
						<input type="radio" class="mr-1" name="method" id="optionsRadios1" value="0">
						{\App\Language::translate('LBL_AUTOGENERATE',$BASE_MODULE_NAME)}&nbsp;
					</label>
					<span class="js-popover-tooltip delay0" data-js="popover"  data-placement="top"
						  data-content="{\App\Language::translate('LBL_AUTOGENERATE_INFO',$BASE_MODULE_NAME)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</div>
				<div class="radio-inline">
					<label>
						<input type="radio" class="mr-1" name="method" id="optionsRadios2" value="1" checked>
						{\App\Language::translate('LBL_OPEN_NEW_WINDOWS',$BASE_MODULE_NAME)}&nbsp;
					</label>
					<span class="js-popover-tooltip delay0" data-js="popover"  data-placement="top"
						  data-content="{\App\Language::translate('LBL_OPEN_NEW_WINDOWS_INFO',$BASE_MODULE_NAME)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</div>
			</div>
		{/if}
		<div class="btn-elements text-center">
			{foreach item=TEMPLATE from=$TEMPLATES}
				{assign var=RELATED_MODEL value=$TEMPLATE->getRelatedModule()}
				<button class="btn btn-light js-genetate-button border" data-js="click" data-id="{$TEMPLATE->getId()}" data-name="{$RELATED_MODEL->getName()}" data-url="{$RELATED_MODEL->getCreateRecordUrl()|cat:"&reference_id=$RECORD"}">
					<span class="userIcon-{$TEMPLATE->getRelatedName()}"></span>
					&nbsp;{\App\Language::translate($TEMPLATE->getRelatedName(), $TEMPLATE->getRelatedName())}
				</button>
			{/foreach}
		</div>
	</div>
	<div class="modal-footer">
		<div class="float-right">
			<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}</button>
		</div>
	</div>
{/strip}
