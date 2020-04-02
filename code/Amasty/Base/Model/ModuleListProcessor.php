<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


namespace Amasty\Base\Model;

use Magento\Framework\Module\ModuleListInterface;
use Amasty\Base\Helper\Module;

class ModuleListProcessor
{
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    /**
     * @var \Amasty\Base\Helper\Module
     */
    private $moduleHelper;

    /**
     * @var array
     */
    private $modules;

    public function __construct(
        ModuleListInterface $moduleList,
        Module $moduleHelper
    ) {
        $this->moduleList = $moduleList;
        $this->moduleHelper = $moduleHelper;
    }

    /**
     * @return array
     */
    public function getModuleList()
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        $this->modules = [
            'lastVersion' => [],
            'hasUpdate' => []
        ];

        $modules = $this->moduleList->getNames();
        sort($modules);

        foreach ($modules as $moduleName) {
            if ($moduleName === 'Amasty_Base'
                || strpos($moduleName, 'Amasty_') === false
                || in_array($moduleName, $this->moduleHelper->getRestrictedModules(), true)
            ) {
                continue;
            }

            try {
                if (!is_array($module = $this->getModuleInfo($moduleName))) {
                    continue;
                }
            } catch (\Exception $e) {
                continue;
            }

            if (empty($module['hasUpdate'])) {
                $this->modules['lastVersion'][] = $module;
            } else {
                $this->modules['hasUpdate'][] = $module;
            }
        }

        return $this->modules;
    }

    /**
     * @param $moduleCode
     * @return array|mixed|string
     */
    protected function getModuleInfo($moduleCode)
    {
        $module = $this->moduleHelper->getModuleInfo($moduleCode);

        if (!is_array($module)
            || !isset($module['version'])
            || !isset($module['description'])
        ) {
            return '';
        }

        $currentVer = $module['version'];
        $module['description'] = $this->replaceAmastyText($module['description']);

        $allExtensions = $this->moduleHelper->getAllExtensions();
        if ($allExtensions && isset($allExtensions[$moduleCode])) {
            $ext = end($allExtensions[$moduleCode]);

            $lastVer = $ext['version'];
            $module['lastVersion'] = $lastVer;
            $module['hasUpdate'] = version_compare($currentVer, $lastVer, '<');
            $module['description'] = $this->replaceAmastyText($ext['name']);
            $module['url'] = !empty($ext['url']) ? $ext['url'] : '';

            return $module;
        }

        return '';
    }

    /**
     * @param string $moduleName
     *
     * @return string
     */
    protected function replaceAmastyText($moduleName)
    {
        return str_replace(['for Magento 2', 'by Amasty'], '', $moduleName);
    }
}
