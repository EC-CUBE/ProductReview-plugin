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

class ProductReviewNav implements EccubeNav
{
    public static function getNav()
    {
        return [
            'product' => [
                'children' => [
                    'product_review' => [
                        'name' => 'product_review.admin.product_review.title',
                        'url' => 'product_review_admin_product_review',
                    ],
                ],
            ],
        ];
    }
}
