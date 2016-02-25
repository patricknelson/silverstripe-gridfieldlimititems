<?php
/**
 * Sets up a quick GridField configuration for modifying relations using the GridFieldLimitItems component.
 *
 * @author	Patrick Nelson, pat@catchyour.com
 * @since	2016-02-24
 */

class GridFieldConfig_LimitedRelationEditor extends GridFieldConfig_RelationEditor {

	/**
	 * Setup new configuration for a relation with a hard maximum limit of items.
	 *
	 * @param	int	$maxItems
	 */
	public function __construct($maxItems) {
		parent::__construct($maxItems);

		// Since GridFieldLimitItems is not yet compatible with GridFieldPaginator, remove that now.
		$this->removeComponentsByType('GridFieldPaginator');
		$this->removeComponentsByType('GridFieldPageCount');

		// Setup GridFieldLimitItems.
		$this->addComponent(new GridFieldLimitItems($maxItems));
	}


	/**
	 * Changing default 'insertBefore' value to ensure all new components added will run prior to the
	 * 'GridFieldLimitItems' component.
	 *
	 * @param	GridFieldComponent	$component
	 * @param	string				$insertBefore
	 * @return	GridFieldConfig_LimitedRelationEditor
	 */
	public function addComponent(GridFieldComponent $component, $insertBefore = 'GridFieldLimitItems') {
		return parent::addComponent($component, $insertBefore);
	}

}
