<?php
/**
 * Created by PhpStorm.
 * User: chihiro_adachi
 * Date: 2018/02/26
 * Time: 16:14
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
                'name' => '商品レビュー',
                'url' => 'homepage',
            ],
        ];
    }
}
