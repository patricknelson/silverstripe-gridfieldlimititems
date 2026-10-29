<?php

namespace PatrickNelson\GridFieldLimitItems;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridField_DataManipulator;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\UnsavedRelationList;

/**
 * Simple component which enables you to easily limit the maximum number of items that are setup in a relation that is
 * being managed by a GridField instance.
 *
 * @author  Patrick Nelson, pat@catchyour.com
 * @since   2016-02-24
 */

class GridFieldLimitItems implements GridField_HTMLProvider, GridField_DataManipulator {

    /**
     * @var int
     */
    protected $maxItems;

    /**
     * @var string[]
     */
    protected $componentsToRemoveAtLimit = [GridFieldAddNewButton::class];

    /**
     * @var string
     */
    protected $note;

    /**
     * @var string
     */
    protected $noteLocation = 'before';

    /**
     * @var boolean
     */
    protected $removeFromTop = false;

    /**
     * @var callable
     */
    protected $onBeforeManipulate;

    /**
     * @var callable
     */
    protected $onAfterManipulate;

    /**
     * @param   int     $maxItems   The maximum number of items you wish to allow in this grid field.
     */
    public function __construct($maxItems) {
        $this->setMaxItems($maxItems);
    }

    /**
     * The maximum number of items you wish to allow in this grid field.
     *
     * @param   int     $maxItems
     */
    public function setMaxItems($maxItems) {
        if ($maxItems < 1) throw new InvalidArgumentException('Maximum items must be at least 1 or greater.');
        $this->maxItems = (int) $maxItems;
    }

    /**
     * The maximum number of items you wish to allow in this grid field.
     *
     * @return int $maxItems
     */
    public function getMaxItems() {
        return $this->maxItems;
    }

    /**
     * Backward compatible toggle for removing add controls once limit is reached.
     *
     * @param bool $removeButton
     */
    public function setRemoveButton($removeButton) {
        if ($removeButton) {
            $this->componentsToRemoveAtLimit[] = GridFieldAddNewButton::class;
        } else {
            $this->componentsToRemoveAtLimit = array_diff($this->componentsToRemoveAtLimit, [GridFieldAddNewButton::class]);
        }
    }

    /**
     * Components that should be removed once the item limit has been reached.
     *
     * @param string[] $components
     */
    public function setComponentsToRemoveAtLimit(array $components) {
        $this->componentsToRemoveAtLimit = $components;
    }

    /**
     * Components that should be removed once the item limit has been reached.
     *
     * @return string[]
     */
    public function getComponentsToRemoveAtLimit() {
        return $this->componentsToRemoveAtLimit;
    }

    /**
     * Indicate the position of the note. Either above or below the grid field.
     *
     * @return GridFieldLimitItems
     */
    public function setNoteAbove() {
        $this->noteLocation = 'before';
        return $this;
    }

    /**
     * Indicate the position of the note. Either above or below the grid field.
     *
     * @return GridFieldLimitItems
     */
    public function setNoteBelow() {
        $this->noteLocation = 'after';
        return $this;
    }

    /**
     * Note HTML displayed above or below the grid field.
     *
     * @param   string  $note
     * @return  GridFieldLimitItems
     */
    public function setNote($note) {
        $this->note = $note;
        return $this;
    }

    /**
     * Note HTML displayed above or below the grid field.
     *
     * @return string
     */
    public function getNote() {
        return $this->note ?? "<p style='margin-top: 16px;'><strong>Note:</strong> This grid is limited to a maximum of $this->maxItems items.</p>";
    }

    /**
     * By default, items are removed from the bottom of the list, but this allows you to configure it to remove from
     * the top instead.
     *
     * @param   bool    $removeFromTop
     * @return  GridFieldLimitItems
     */
    public function setRemoveFromTop($removeFromTop) {
        $this->removeFromTop = (bool) $removeFromTop;
        return $this;
    }

    /**
     * Allows you to perform some sort of action BEFORE any sort of manipulation is performed.
     *
     * @param   callable    $callback(GridField $grid, SS_List $list): bool
     *
     *                      The callback can accept a GridField and SS_List instance. You can also return false from
     *                      your callback to prevent any sort of manipulation from taking place!
     *
     * @return  GridFieldLimitItems
     */
    public function onBeforeManipulate(callable $callback) {
        $this->onBeforeManipulate = $callback;
        return $this;
    }

    /**
     * Allows you to perform some sort of action AFTER any sort of manipulation is performed.
     *
     * @param   callable    $callback(GridField $grid, SS_List $list)
     *
     *                      The callback can accept a GridField and SS_List instance.
     *
     * @return  GridFieldLimitItems
     */
    public function onAfterManipulate(callable $callback) {
        $this->onAfterManipulate = $callback;
        return $this;
    }

    /**
     * Generates HTML responsible for note above/below the grid field.
     *
     * @return array
     */
    public function getHTMLFragments($gridField) {
        return [
            $this->noteLocation => $this->getNote(),
        ];
    }

    /**
     * Manipulate the {@link DataList} as needed by this grid modifier.
     *
     * @param   GridField
     * @param   SS_List
     * @return  DataList|SS_List
     */
    public function getManipulatedData(GridField $gridField, SS_List $dataList) {
        // Allow custom action prior to manipulation and, if false is returned, avoid doing anything at all.
        if (isset($this->onBeforeManipulate)) {
            $result = call_user_func($this->onBeforeManipulate, $gridField, $dataList);
            if ($result === false) return $dataList;
        }

        // Can't do anything to unsaved relation lists.
        if(($dataList instanceof UnsavedRelationList)) return $dataList;

        // Not compatible with paginator.
        if ($gridField->getConfig()->getComponentByType(GridFieldPaginator::class)) {
            $this->debug('GridFieldLimitItems is not compatible with GridFieldPaginator.');
            return $dataList;
        }

        // See if any action needs to be taken...
        $total = $dataList->count();
        if ($total > $this->maxItems) {
            $index = 0;
            $lowerLimit = ($total - $this->maxItems);
            $this->debug("List of $total is beyond threshold of $this->maxItems.");
            $this->debug("Lower limit is: $lowerLimit");
            foreach($dataList as $item) {
                // Remove items beyond threshold.
                $index++;
                $itemName = "item #$index";
                if (is_object($item) && isset($item->ID)) $itemName = "item ID #$item->ID";
                if ($this->removeFromTop) {
                    if ($index <= $lowerLimit) {
                        $this->debug("Removed item $itemName from top.");
                        $dataList->remove($item);
                    }
                } else {
                    if ($index > $this->maxItems) {
                        $this->debug("Removed item $itemName from bottom.");
                        $dataList->remove($item);
                    }
                }
            }
        }

        // Remove specified components once the item limit has been reached.
        if ($total >= $this->maxItems) {
            // ... obviously shouldn't be null, but just in case.
            $gridConfig = $gridField->getConfig();
            if ($gridConfig) {
                foreach ($this->componentsToRemoveAtLimit as $type) {
                    $components = $gridConfig->getComponentsByType($type);
                    foreach ($components as $component) {
                        $gridConfig->removeComponent($component);
                    }
                }
            }
        }

        // Allow custom action after manipulation.
        if (isset($this->onAfterManipulate)) call_user_func($this->onAfterManipulate, $gridField, $dataList);

        return $dataList;
    }

    /**
     * For internal debug use only.
     *
     * @param mixed $message
     */
    protected function debug($message) {
        Injector::inst()->get(LoggerInterface::class)->debug($message);
    }
}
