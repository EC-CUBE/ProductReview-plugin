<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview;

use Eccube\Common\EccubeNav;

class Nav implements EccubeNav
{
    public static function getNav()
    {
        return [
            'product' => [
                'id' => 'product_review',
                'name' => 'plugin.admin.product_review.nav',
                'url' => 'plugin_admin_product_review',
            ],
        ];
    }
}
