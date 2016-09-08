<?php
/**
 * Simple component which enables you to easily limit the maximum number of items that are setup in a relation that is
 * being managed by a GridField instance.
 *
 * @author	Patrick Nelson, pat@catchyour.com
 *
 * @since	2016-02-24
 */
class GridFieldLimitItems implements GridField_HTMLProvider, GridField_DataManipulator
{
    /** @var int */
    protected $maxItems;

    /** @var string */
    protected $noteLocation = 'before';

    /** @var string 	Name of a template (ending with .ss, e.g. template.ss) or a string for sprintf with %d param */
    protected $noteTemplate = 'gridfield_limiteditems_note.ss';

    /** @var bool */
    protected $removeFromTop = false;

    /** @var callable */
    protected $onBeforeManipulate;

    /** @var callable */
    protected $onAfterManipulate;

    /**
     * @param int $maxItems The maximum number of items you wish to allow in this grid field.
     */
    public function __construct($maxItems)
    {
        $this->setMaxItems($maxItems);
    }

    /**
     * The maximum number of items you wish to allow in this grid field.
     *
     * @param int $maxItems
     */
    public function setMaxItems($maxItems)
    {
        if ($maxItems < 1) {
            throw new InvalidArgumentException('Maximum items must be at least 1 or greater.');
        }
        $this->maxItems = (int) $maxItems;
    }

    /**
     * Indicate the position of the note. Either above or below the grid field.
     *
     * @return GridFieldLimitItems
     */
    public function setNoteAbove()
    {
        $this->noteLocation = 'before';

        return $this;
    }

    /**
     * Indicate the position of the note. Either above or below the grid field.
     *
     * @return GridFieldLimitItems
     */
    public function setNoteBelow()
    {
        $this->noteLocation = 'after';

        return $this;
    }

    /**
     * Filename of the template for note. If empty, note will be hidden.
     *
     * @param string $noteTemplate
     */
    public function setNoteTemplate($noteTemplate)
    {
        $this->noteTemplate = $noteTemplate;
    }

    /**
     * By default, items are removed from the bottom of the list, but this allows you to configure it to remove from
     * the top instead.
     *
     * @param bool $removeFromTop
     *
     * @return GridFieldLimitItems
     */
    public function setRemoveFromTop($removeFromTop)
    {
        $this->removeFromTop = (bool) $removeFromTop;

        return $this;
    }

    /**
     * Allows you to perform some sort of action BEFORE any sort of manipulation is performed.
     *
     * @param callable $callback(GridField $grid, SS_List $list): bool
     *
     * 						The callback can accept a GridField and SS_List instance. You can also return false from
     * 						your callback to prevent any sort of manipulation from taking place!
     *
     * @return GridFieldLimitItems
     */
    public function onBeforeManipulate(callable $callback)
    {
        $this->onBeforeManipulate = $callback;

        return $this;
    }

    /**
     * Allows you to perform some sort of action AFTER any sort of manipulation is performed.
     *
     * @param callable $callback(GridField $grid, SS_List $list)
     *
     * 						The callback can accept a GridField and SS_List instance.
     *
     * @return GridFieldLimitItems
     */
    public function onAfterManipulate(callable $callback)
    {
        $this->onAfterManipulate = $callback;

        return $this;
    }

    /**
     * Generates HTML responsible for note above/below the grid field.
     *
     * @return array
     */
    public function getHTMLFragments($gridField)
    {
        if ($this->noteTemplate) {
            if (stripos(strrev($this->noteTemplate), strrev('.ss')) === 0) { // string ends .ss ?
                $note = ArrayData::create(['maxItems' => $this->maxItems])->renderWith(substr($this->noteTemplate, 0, -3));
            } else {
                $note = sprintf($this->noteTemplate, $this->maxItems);
            }

            return [
                $this->noteLocation => $note,
            ];
        }
    }

    /**
     * Manipulate the {@link DataList} as needed by this grid modifier.
     *
     * @param GridField
     * @param SS_List
     *
     * @return DataList
     */
    public function getManipulatedData(GridField $gridField, SS_List $dataList)
    {
        // Allow custom action prior to manipulation and, if false is returned, avoid doing anything at all.
        if (isset($this->onBeforeManipulate)) {
            $result = call_user_func($this->onBeforeManipulate, $gridField, $dataList);
            if ($result === false) {
                return $dataList;
            }
        }

        // Can't do anything to unsaved relation lists.
        if (($dataList instanceof UnsavedRelationList)) {
            return $dataList;
        }

        // Not compatible with paginator.
        if ($gridField->getConfig()->getComponentByType('GridFieldPaginator')) {
            $this->debug('GridFieldLimitItems is not compatible with GridFieldPaginator.');

            return $dataList;
        }

        // Remove the add new / link existing buttons if the max items reached
        $total = $dataList->count();
        if ($total >= $this->maxItems) {
            $gridField->getConfig()->removeComponentsByType('GridFieldAddNewButton');
            $gridField->getConfig()->removeComponentsByType('GridFieldAddExistingAutocompleter');
        }

        // Allow custom action after manipulation.
        if (isset($this->onAfterManipulate)) {
            call_user_func($this->onAfterManipulate, $gridField, $dataList);
        }

        return $dataList;
    }

    /**
     * For internal debug use only.
     *
     * @param mixed $message
     */
    protected function debug($message)
    {
        SS_Log::log(print_r($message, true), SS_Log::DEBUG);
    }

    public function getMaxItems()
    {
        return $this->maxItems;
    }
}
