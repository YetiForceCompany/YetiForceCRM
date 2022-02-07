{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-Tabs-Inventory -->
	{assign var='INVENTORY_BLOKS' value=$INVENTORY_MODEL->getFieldsByBlocks()}
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
							<li>
								<div class="opacity editFields border mb-1 u-cursor-grab"
									data-id="{$FIELD_MODEL->get('id')}" data-name="{$NAME}"
									data-sequence="{$FIELD_MODEL->get('sequence')}"
									data-type="{$FIELD_MODEL->getType()}">
									<a>
										<img class="mb-1" src="{\App\Layout::getImagePath('drag.png')}" border="0" title="{App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}" />
									</a>&nbsp;&nbsp;
									<span class="fieldLabel">{App\Language::translate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}</span>
									<span class="ml-3 badge badge-secondary">{$NAME}</span>
									<div class="float-right actions">
										<a href="#" class="editInventoryField mr-1">
											<span class="yfi yfi-full-editing-view"
												title="{App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
										</a>
										<a class="deleteInventoryField mr-1" href="#"><span
												title="{App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"
												class="fas fa-trash-alt"></span></a>
									</div>
								</div>
							</li>
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
						<strong><span
								class="fas fa-plus fa-fw"></span>{App\Language::translate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}
						</strong>
					</button>
				</div>
				{App\Language::translate('LBL_BASIC_VERSE', $QUALIFIED_MODULE)}
			</div>
			<div class="blockFieldsList card-body">
				<ul name="sortable1" class="connectedSortable m-0 list-unstyled">
					{if isset($INVENTORY_BLOKS[1])}
						{foreach item=FIELD_MODEL key=NAME from=$INVENTORY_BLOKS[1]}
							<li>
								<div class="opacity editFields border mb-1" data-id="{$FIELD_MODEL->get('id')}"
									data-name="{$NAME}"
									data-sequence="{$FIELD_MODEL->get('sequence')}"
									data-type="{$FIELD_MODEL->getType()}">
									<a>
										<img class="mb-1" src="{\App\Layout::getImagePath('drag.png')}" border="0" title="{App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}" />
									</a>&nbsp;&nbsp;
									<span class="fieldLabel">{App\Language::translate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}</span>
									<span class="ml-3 badge badge-secondary">{$NAME}</span>
									<span class="btn-group float-right actions">
										<a href="#" class="editInventoryField mr-1">
											<span class="yfi yfi-full-editing-view"
												title="{App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
										</a>
										<a class="deleteInventoryField mr-1" href="#"><span
												title="{App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"
												class="fas fa-trash-alt"></span></a>
									</span>
								</div>
							</li>
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
					<button class="btn btn-sm btn-success saveFieldSequence invisible inventorySequence"
						type="button">
						<strong>{App\Language::translate('LBL_SAVE_FIELD_SEQUENCE', $QUALIFIED_MODULE)}</strong>
					</button>
					<button class="btn btn-sm btn-success addInventoryField" type="button">
						<strong><span
								class="fas fa-plus fa-fw"></span>{App\Language::translate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}
						</strong>
					</button>
				</div>
				{App\Language::translate('LBL_ADDITIONAL_VERSE', $QUALIFIED_MODULE)}
			</div>
			<div class="blockFieldsList card-body">
				<ul name="sortable1" class="connectedSortable m-0 list-unstyled">
					{if isset($INVENTORY_BLOKS[2])}
						{foreach item=FIELD_MODEL key=NAME from=$INVENTORY_BLOKS[2]}
							<li>
								<div class="opacity editFields border mb-1" data-id="{$FIELD_MODEL->get('id')}"
									data-name="{$NAME}"
									data-sequence="{$FIELD_MODEL->get('sequence')}"
									data-type="{$FIELD_MODEL->getType()}">
									<a>
										<img class="mb-1" src="{\App\Layout::getImagePath('drag.png')}" border="0" title="{App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}" />
									</a>&nbsp;&nbsp;
									<span class="fieldLabel">{App\Language::translate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}</span>
									<span class="ml-3 badge badge-secondary">{$NAME}</span>
									<span class="btn-group float-right actions">
										<a href="#" class="editInventoryField mr-1">
											<span class="yfi yfi-full-editing-view"
												title="{App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
										</a>
										<a class="deleteInventoryField mr-1" href="#"><span
												title="{App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"
												class="fas fa-trash-alt"></span></a>
									</span>
								</div>
							</li>
						{/foreach}
					{/if}
				</ul>
			</div>
		</div>
	</div>
	<li class="d-none newLiElement">
		<div class="opacity editFields border mb-1" data-name="" data-id="" data-sequence="" data-type="">
			<a>
				<img class="mb-1" src="{\App\Layout::getImagePath('drag.png')}" border="0"
					title="{App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}" />
			</a>&nbsp;&nbsp;
			<span class="fieldLabel"></span>
			<span class="btn-group float-right actions">
				<a href="#" class="editInventoryField mr-1">
					<span class="yfi yfi-full-editing-view" title="{App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
				</a>
				<a class="deleteInventoryField mr-1" href="#"><span
						title="{App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"
						class="fas fa-trash-alt"></span></a>
			</span>
		</div>
	</li>
	<!-- /tpl-Settings-LayoutEditor-Tabs-Inventory -->
{/strip}
