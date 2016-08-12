{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
<div class="modal fade" tabindex="-1">
	<div class="modal-dialog">
        <div class="modal-content">
			<form class="form-modalAddWidget">  
				<input type="hidden" name="wid" value="{$WID}">
				<input type="hidden" name="type" value="{$TYPE}">
				<div class="modal-header">
					<button type="button" data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE', $QUALIFIED_MODULE)}">×</button>
					<h3 id="massEditHeader" class="modal-title">{vtranslate('Add widget', $QUALIFIED_MODULE)}</h3>
				</div>
				<div class="modal-body">
					<div class="modal-Fields">
						<div class="row">
							<div class="col-md-3 marginLeftZero"><strong>{vtranslate('Type widget', $QUALIFIED_MODULE)}</strong>:</div>
							<div class="col-md-7">
								{vtranslate($TYPE, $QUALIFIED_MODULE)}
							</div>
							<div class="col-md-3 marginLeftZero"><label class="">{vtranslate('Label', $QUALIFIED_MODULE)}:</label></div>
							<div class="col-md-7"><input name="label" class="form-control" type="text" value="{$WIDGETINFO['label']}" /></div>
							<div class="col-md-3 marginLeftZero"><label class="">{vtranslate('No left margin', $QUALIFIED_MODULE)}:</label></div>
							<div class="col-md-7">
								<input name="nomargin" class="" type="checkbox" value="1" {if $WIDGETINFO['nomargin'] == 1}checked{/if}/>
								<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{vtranslate('No left margin info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('No left margin', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>
							</div>
							<div class="clearfix"></div>
							<div class="col-md-3 marginLeftZero"><label class="">{vtranslate('Limit entries', $QUALIFIED_MODULE)}:</label></div>
							<div class="col-md-7">
								<div class="col-xs-3 paddingLRZero">
								<input name="limit" class="form-control" type="text" value="{$WIDGETINFO['data']['limit']}"/>
								</div>
								<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{vtranslate('Limit entries info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Limit entries', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>
							</div>
						</div>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			</form>
		</div>
	</div>
</div>
{/strip}
