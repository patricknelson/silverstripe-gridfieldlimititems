# silverstripe-gridfieldlimititems
Simple component which automatically limits the maximum number of items displayed in a GridField (including modifying
actual relations). 

**Important:** This works well as a centralized method to maintain an actual hard limit on the number of `has_many` and
`many_many` relations. Therefore, this will modify those relations and doesn't (yet) simply limit the number of items displayed in a grid field.


## Example Usage

**Quick Start**

Start managing a relation using the `GridFieldConfig_LimitedRelationEditor` like so:  

```php
// Setup a new relation editor with an upper hard limit of 10 items. Items past this amount will be automatically
// removed by GridFieldLimitItems (setup in this relation editor).
$gridConfig = GridFieldConfig_LimitedRelationEditor::create(10);
$gridField = new GridField('RelationName', 'Relation Title', $this->MyRelation(), $gridConfig);
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


**Warning:** Since this will modify a relation as a component, it's best to ensure that the relation itself is not being 
modified by any other components, such as a paginator. If you are using the standard `GridFieldConfig_RelationEditor` you
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


## To Do

 - Setup ability to customize notes.
 - Setup the ability to modify the returned list without actually affecting relations (i.e. read only, no writing at all to the database).
 - Unit tests to cover current functionality.
