# SilverStripe GridfieldLimitItems

Simple component which automatically limits the maximum number of items displayed in a GridField (including modifying
actual relations).

**Important:** This works well as a centralized method to maintain an actual hard limit on the number of `has_many` and
`many_many` relations. Therefore, this will modify those relations and doesn't (yet) simply limit the number of items displayed in a grid field.


## Installation

1. Run `composer require patricknelson/silverstripe-gridfieldlimititems`
2. Run `sake dev/build`


## Example Usage

**Quick Start**

Start managing a relation using the `GridFieldConfig_LimitedRelationEditor` like so:

```php
// Setup a new relation editor with an upper hard limit of 10 items. Items past this amount will be automatically
// removed by GridFieldLimitItems (setup in this relation editor).
$gridConfig = GridFieldConfig_LimitedRelationEditor::create(10);
$gridConfig->enforceLimit(); // remove, if you don't want to enforce this limitation
$gridField = new GridField(
	'RelationName',
	'Relation Title',
	$this->MyRelation()->sort('Sort'),
	$gridConfig
);
$fields->addFieldToTab('Root.main', $gridField);
```

You can setup extra configuration options as well (most options have been included):

```php
// Setup a brand new limiter...
$limiter = new GridFieldLimitItems(10); // Limit to max of 10.
$gridConfig->addComponent($limiter);

// ... or get from an existing configuration.
$limiter = $gridConfig->getComponentByType('GridFieldLimitItems');

// Setup the limiter with a few extra options.
$limiter->setNoteBelow(); // Ensure note displays below grid field instead of on top (default).
$limiter->setRemoveFromTop(true); // Removes excess items from the top of the list instead of the bottom (default).

// You can even setup a callbacks to prevent any manipulation from taking place under certain circumstances.
$limiter->onBeforeManipulate(function(GridField $grid, SS_List $list) use($disableLimiting) {
	// Will prevent manipulation if you return false from this closure, otherwise operates as normal.
	if ($disableLimiting) return false;
});

// Or do something after list manipulation takes place, like so...
$limiter->onBeforeManipulate(function(GridField $grid, SS_List $list) {
	SS_Log::log(print_r($list->map()), SS_Log::DEBUG);
});
```

**Warning:** Since this will modify a relation as a component, it's best to ensure that the relation itself is not being modified by any other components, such as a aginator. If you are using the standard `GridFieldConfig_RelationEditor` you
will need to remove that component. For example:

```php
// Setup a new relation editor.
$gridConfig = GridFieldConfig_RelationEditor::create();

// Since GridFieldLimitItems is not yet compatible with GridFieldPaginator, remove that now.
$gridConfig->removeComponentsByType("GridFieldPaginator");
$gridConfig->removeComponentsByType("GridFieldPageCount");

// Now we can add our GridFieldLimitItems component.
$limiter = new GridFieldLimitItems(10); // Limit to max of 10.
$gridConfig->addComponent($limiter);

// ... continue below with adding your new GridField instance with this $gridConfig...
```


## Known Issues

- This could be vulnerable to issues relating to not runing in the proper order, in case you are sorting fields and the
  newly sorted field is not yet properly intialized (e.g. starts out at 0 but should be set to 11 prior to modification).
  In this scenario, you should be sure that your sorting component is added to the grid configuration PRIOR to this
  component so that the sort can process first and then the pruning performed by this component can be done. This should
  only be an issue if you are not using the built-in `GridFieldConfig_RelationEditor` which already ensures that it will
  process the relation last.


## To Do

 - Setup ability to customize notes.
 - Setup the ability to modify the returned list without actually affecting relations (i.e. read only, no writing at all to the database).
 - Unit tests to cover current functionality.
