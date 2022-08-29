{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-Tabs-Inventory -->
	{assign var='INVENTORY_BLOKS' value=$INVENTORY_MODEL->getFieldsByBlocks()}
	{function SHOW_FIELD_ITEM}
		<li>
			<div class="opacity editFields border mb-1 u-cursor-grab"
				data-id="{$FIELD_MODEL->get('id')}" data-name="{$NAME}"
				data-sequence="{$FIELD_MODEL->get('sequence')}"
				data-type="{$FIELD_MODEL->getType()}">
				<div class="d-flex">
					<div class="col-12 pr-0 pl-0" style=" word-wrap: break-word;">
						<a>
							<img class="mb-1 mr-2" src="{\App\Layout::getImagePath('drag.png')}" border="0" title="{App\Language::translate('LBL_DRAG', $QUALIFIED_MODULE)}" />
						</a>
						<span class="fieldLabel">
							{App\Language::translate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}</span>
						<span class="ml-3 badge badge-secondary">{$NAME}</span>
						<div class="float-right actions">
							<button class="btn btn-success btn-xs editInventoryField ml-1" title="
											{App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}">
								<span class="yfi yfi-full-editing-view"></span>
							</button>
							<button class="btn btn-danger btn-xs deleteInventoryField ml-1" title="
											{App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}">
								<span class="fas fa-trash-alt"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</li>
	{/function}
	<div class="moduleBlocks inventoryBlock" data-block-id="0">
		<div class="editFieldsTable block card card-default mb-2">
			<div class="card-header px-2">
				<div class="float-right">
					<button class="btn btn-sm btn-success pr-1 saveFieldSequence invisible inventorySequence"
						type="button">
						<strong>{App\Language::translate('LBL_SAVE_FIELD_SEQUENCE', $QUALIFIED_MODULE)}</strong>
					</button>
					<button class="btn btn-sm btn-success addInventoryField" type="button">
						<strong><span
								class="fas fa-plus fa-fw"></span>{App\Language::translate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}
						</strong>
					</button>
				</div>
				{App\Language::translate('LBL_HEADLINE', $QUALIFIED_MODULE)}
			</div>
			<div class="blockFieldsList card-body">
				<ul name="sortable1" class="connectedSortable m-0 list-unstyled">
					{if isset($INVENTORY_BLOKS[0])}
						{foreach item=FIELD_MODEL key=NAME from=$INVENTORY_BLOKS[0]}
							{SHOW_FIELD_ITEM FIELD_MODEL=$FIELD_MODEL}
						{/foreach}
					{/if}
				</ul>
			</div>
		</div>
	</div>
	<div class="moduleBlocks inventoryBlock" data-block-id="1">
		<div class="editFieldsTable block card card-default mb-2">
			<div class="card-header px-2">
				<div class="float-right">
					<button class="btn btn-sm btn-success saveFieldSequence invisible inventorySequence mr-1"
						type="button">
						<strong>{App\Language::translate('LBL_SAVE_FIELD_SEQUENCE', $QUALIFIED_MODULE)}</strong>
					</button>
					<button class="btn btn-sm btn-success addInventoryField" type="button">
						<strong>
							<span class="fas fa-plus fa-fw"></span>
							{App\Language::translate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}
						</strong>
					</button>
				</div>
				{App\Language::translate('LBL_BASIC_VERSE', $QUALIFIED_MODULE)}
			</div>
			<div class="blockFieldsList card-body">
				<ul name="sortable1" class="connectedSortable m-0 list-unstyled">
					{if isset($INVENTORY_BLOKS[1])}
						{foreach item=FIELD_MODEL key=NAME from=$INVENTORY_BLOKS[1]}
							{SHOW_FIELD_ITEM FIELD_MODEL=$FIELD_MODEL}
						{/foreach}
					{/if}
				</ul>
			</div>
		</div>
	</div>
	<div class="moduleBlocks inventoryBlock" data-block-id="2">
		<div class="editFieldsTable block card card-default mb-2">
			<div class="card-header px-2">
				<div class="float-right">
					<button class="btn btn-sm btn-success saveFieldSequence invisible inventorySequence" type="button">
						<strong>{App\Language::translate('LBL_SAVE_FIELD_SEQUENCE', $QUALIFIED_MODULE)}</strong>
					</button>
					<button class="btn btn-sm btn-success addInventoryField" type="button">
						<strong><span class="fas fa-plus fa-fw"></span>
							{App\Language::translate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}
						</strong>
					</button>
				</div>
				{App\Language::translate('LBL_ADDITIONAL_VERSE', $QUALIFIED_MODULE)}
			</div>
			<div class="blockFieldsList card-body">
				<ul name="sortable1" class="connectedSortable m-0 list-unstyled">
					{if isset($INVENTORY_BLOKS[2])}
						{foreach item=FIELD_MODEL key=NAME from=$INVENTORY_BLOKS[2]}
							{SHOW_FIELD_ITEM FIELD_MODEL=$FIELD_MODEL}
						{/foreach}
					{/if}
				</ul>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-LayoutEditor-Tabs-Inventory -->
{/strip}
