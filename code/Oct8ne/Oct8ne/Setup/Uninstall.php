<?php
/**
 * Created by PhpStorm.
 * User: migue
 * Date: 22/05/2017
 * Time: 16:24
 */

namespace Oct8ne\Oct8ne\Setup;

use \Magento\Framework\Setup\UninstallInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\Setup\ModuleContextInterface;

/**
 * Borrar tablas instaladas en la base de datos
 * Class Uninstall
 * @package Oct8ne\Oct8ne\Setup
 */
class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        //si existe la borro
        if ($setup->tableExists('oct8nehistory')) {

            $setup->getConnection()->dropTable('oct8nehistory');
        }
    }
}



