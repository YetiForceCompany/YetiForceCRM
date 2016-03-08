{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="validationEngineContainer" tabindex="-1">
		<div  class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header row no-margin">
						<div class="col-xs-12 paddingLRZero">
							<div class="col-xs-8 paddingLRZero">
								<h4>{vtranslate('LBL_TITLE_ADDED', $QUALIFIED_MODULE)}</h4>
							</div>
							<div class="pull-right">
								<button class="btn btn-warning marginLeft10" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
							</div>
						</div>
					</div>
					<div class="modal-body row">
						<div class="col-xs-12 form-horizontal">
							<div class="form-group">
								<div class="col-md-5 control-label">
									<label>{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}</label>
								</div>
								<div class="col-md-7">
									<input name="name" value="{$RECORD->getName()}" data-validation-engine="validate[required]" class="form-control"> 
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-5 control-label">
									<label>{vtranslate('LBL_WIDTH', $QUALIFIED_MODULE)}</label>
								</div>
								<div class="col-md-7">
									{assign var=WIDTH value=$RECORD->get('width')}
									<select class="width form-control pull-left" name="width">
										{foreach from=$LIST_SIZE item=SIZE}
											<option value="{$SIZE}" {if $WIDTH eq $SIZE} selected {/if} >{$SIZE}</option>
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-5 control-label">
									<label>{vtranslate('LBL_HEIGHT', $QUALIFIED_MODULE)}</label>
								</div>
								<div class="col-md-7">
									{assign var=HEIGHT value=$RECORD->get('height')}
									<select class="width form-control pull-left" name="height">
										{foreach from=$LIST_SIZE item=SIZE}
											<option value="{$SIZE}" {if $HEIGHT eq $SIZE} selected {/if} >{$SIZE}</option>
										{/foreach}
									</select>
								</div>
							</div>
						</div>
					</div>
					{include file='ModalFooter.tpl'|@vtemplate_path}
				</div>
			</div>
		</div>
	</div>
{/strip}
