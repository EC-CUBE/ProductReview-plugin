<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview42\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Master\AbstractMasterEntity;

/**
 * ProductReviewStatus
 *
 * @ORM\Table(name="plg_product_review_status")
 * @ORM\Entity(repositoryClass="Plugin\ProductReview42\Repository\ProductReviewStatusRepository")
 */
class ProductReviewStatus extends AbstractMasterEntity
{
    /**
     * 表示
     */
    const SHOW = 1;

    /**
     * 非表示
     */
    const HIDE = 2;
}
