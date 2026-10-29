# silverstripe-gridfieldlimititems
Simple component which enforces a maximum number of items in a Silverstripe GridField relation.

**Important:** This modifies `has_many` and `many_many` relations. It does not only limit the number of items displayed.


## Installation

1. Run `composer require patricknelson/silverstripe-gridfieldlimititems`
2. Run `sake dev/build`


## Example Usage

Use `GridFieldConfig_LimitedRelationEditor` to create a relation editor with a hard item limit:

```php
$gridConfig = GridFieldConfig_LimitedRelationEditor::create(10);
$gridField = new GridField('RelationName', 'Relation Title', $this->MyRelation()->sort('Sort'), $gridConfig);
$fields->addFieldToTab('Root.main', $gridField);
```

## Advanced Usage

You can customize the limiter directly:

```php
$limiter = new GridFieldLimitItems(10); // Limit to max of 10.
$gridConfig->addComponent($limiter);

$limiter->setNoteBelow(); // Ensure note displays below grid field instead of on top (default).

$limiter->setNote('<p><strong>Note:</strong> Add up to 10 featured items.</p>'); // Write a custom note.

$limiter->setRemoveFromTop(true); // Removes excess items from the top of the list instead of the bottom (default).
```

Or get the limiter from an existing config:

```php
$limiter = $gridConfig->getComponentByType(GridFieldLimitItems::class);
```

You can even setup a callbacks to prevent any manipulation from taking place under certain circumstances.

```php
// Will prevent manipulation if you return false from this closure, otherwise operates as normal.
$limiter->onBeforeManipulate(function(GridField $grid, SS_List $list) use($disableLimiting) {
	if ($disableLimiting) return false;
});

// Or do something after list manipulation takes place, like so...
$limiter->onAfterManipulate(function(GridField $grid, SS_List $list) {
	SS_Log::log(print_r($list->map()), SS_Log::DEBUG);
});
```
**Warning:** Since this will modify a relation as a component, it's best to ensure that the relation itself is not being modified by any other components.
If you are using `GridFieldConfig_RelationEditor` instead of `GridFieldConfig_LimitedRelationEditor`, remove paginator components before adding the limiter:

```php
$gridConfig = GridFieldConfig_RelationEditor::create();
$gridConfig->removeComponentsByType(GridFieldPaginator::class);
$gridConfig->removeComponentsByType(GridFieldPageCount::class);
$gridConfig->addComponent(new GridFieldLimitItems(10));
```


## Known Issues

- When using custom sorting components, make sure they run before `GridFieldLimitItems`. Otherwise, the limiter may remove items before the new sort values are applied.


## To Do

- Setup the ability to modify the returned list without affecting relations (i.e. read only, no writing at all to the database).
- Unit tests to cover current functionality.
