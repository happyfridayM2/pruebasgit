<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Functions;

/**
 * Delete action
 */
abstract class AbstractFunctions extends \Wyomind\OrdersExportTool\Controller\Adminhtml\AbstractAction
{
    public $title = 'Orders Export Tool > Custom Functions';
    public $breadcrumbFirst = 'Orders Export Tool';
    public $breadcrumbSecond = 'Manage Custom Functions';
    public $model = 'Wyomind\OrdersExportTool\Model\Functions';
    public $errorDoesntExist = "This function doesn't exist anymore.";
    public $successDelete = 'The function has been deleted.';
    public $msgModify = 'Modify custom function';
    public $msgNew = 'New custom function';
    public $registryName = 'function';
    public $menu = 'functions';
}