<?php

class GridFieldLimitItemsDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest
{
    public function doSave($data, $form)
    {
        if (!$this->canSave($form)) {
            return false;
        }

        return parent::doSave($data, $form);
    }

    public function doSaveAndQuit($data, $form)
    {
        if (!$this->canSave($form)) {
            return false;
        }

        return parent::doSaveAndQuit($data, $form);
    }

    /**
     * Whether or not the user is allowed to save the record.
     *
     * @param $form
     * @return bool|SS_HTTPResponse
     */
    protected function canSave($form)
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

        return true;
    }
}
