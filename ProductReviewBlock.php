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
use Eccube\Common\EccubeTwigBlock;

class ProductReviewBlock implements EccubeTwigBlock
{
    public static function getTwigBlock()
    {
        return [
            '@ProductReview/default/review.twig',
        ];
    }
}
