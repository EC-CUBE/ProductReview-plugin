<?php
/**
 * This file is part of the ProductReview plugin.
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview\Util;

use Eccube\Common\Constant;

/**
 * Class Version.
 * Util to check version.
 */
class Version
{
    /**
     * Check version to support new log function.
     *
     * @return bool
     */
    public static function isSupportLogFunction()
    {
        return version_compare(Constant::VERSION, '3.0.12', '>=');
    }

    /**
     * Check version to support by Ec-cube.
     *
     * @param string $version
     * @param string $operation
     *
     * @return bool
     */
    public static function isSupportVersion($version = '3.0.9', $operation = '>=')
    {
        return version_compare(Constant::VERSION, $version, $operation);
    }

    /**
     * Check version to support method.
     *
     * @param string $class
     * @param string $method
     *
     * @return bool
     */
    public static function isSupportMethod($class = 'Eccube\Application', $method = 'getInstance')
    {
        return method_exists($class, $method);
    }

    /**
     * Check version to support new session function.
     *
     * @return bool
     */
    public static function isSupportNewSession()
    {
        return version_compare(Constant::VERSION, '3.0.15', '>=');
    }
}
