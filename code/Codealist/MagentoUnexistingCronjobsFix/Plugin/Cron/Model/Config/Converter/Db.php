<?php
/**
 * Created by PhpStorm.
 * User: fsspencer
 * Date: 8/4/16
 * Time: 11:44
 */

namespace Codealist\MagentoUnexistingCronjobsFix\Plugin\Cron\Model\Config\Converter;


class Db
{
    public function beforeConvert($context, $source)
    {
        if (isset($source['crontab']) && !empty($source['crontab'])) {
            foreach ($source['crontab'] as $groupName => $groupConfig) {
                if (!isset($groupConfig['jobs'])) unset($source['crontab'][$groupName]);
            }
        }
        return [ $source ];
    }
}