# silverstripe-gridfieldlimititems
Simple component which automatically limits the maximum number of items displayed in a GridField (including modifying actual relations). 

**Important:** This works well as a centralized method to maintain an actual hard limit on the number of `has_many` and `many_many` relations. Therefore, this will modify those relations and doesn't (yet) simply limit the number of items displayed in a grid field. 

## Example Usage

Below most options have been included. Also available are `->setNoteAbove()` (already enabled by default) 

```php
// Setup a new relation editor.
$gridConfig = GridFieldConfig_RelationEditor::create();

// Since GridFieldLimitItems is not yet compatible with GridFieldPaginator, remove that now.
$gridConfig->removeComponentsByType("GridFieldPaginator");
$gridConfig->removeComponentsByType("GridFieldPageCount");

// Setup GridFieldLimitItems with a few example options.
$limiter = new GridFieldLimitItems(10); // Limit to max of 10.
$limiter->setNoteBelow(); // Ensure note displays below grid field instead of on top (default).
$limiter->setRemoveFromTop(true); // Removes new items from the top of the list instead of the bottom (default).

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

## To Do

 - Need to setup a new custom gridfield config which makes it easier to limit relations without conflicting with the standard pagination class.
 - Setup the ability to modify the returned list without actually affecting relations (i.e. read only, no writing at all to the database).
 - Unit tests to cover current functionality.
