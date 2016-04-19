<?php

class GridFieldLimitItemsDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest
{
    public function doSave($data, $form)
    {
        // Current items in grid & max allowed items
        $list     = $this->gridField->getList();
        $maxItems = $this->gridField->getConfig()->getComponentByType('GridFieldLimitItems')->getMaxItems();

        // Prevent form submission if items in grid reached the allowed limit
        if ($list->count() >= $maxItems) {
            $form->sessionMessage(
                'You have reached your maximum number of allowed items (' . $maxItems . ')',
                'bad',
                false
            );

            return $this->getToplevelController()->redirectBack();
        }

        return parent::doSave($data, $form);
    }
}
