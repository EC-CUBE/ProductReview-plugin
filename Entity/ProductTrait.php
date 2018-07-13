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

namespace Plugin\ProductReview\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\Product")
 */
trait ProductTrait
{
    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Plugin\ProductReview\Entity\ProductReview", mappedBy="Product", cascade={"remove"})
     * @ORM\OrderBy({"create_date" = "DESC"})
     */
    private $ProductReviewProductReviews;

    /**
     * @return ProductReview[]|ArrayCollection|Collection
     */
    public function getLockonProductReviews()
    {
        if (null === $this->ProductReviewProductReviews) {
            $this->ProductReviewProductReviews = new ArrayCollection();
        }

        return $this->ProductReviewProductReviews;
    }

    /**
     * @param ProductReview $ProductReview
     *
     * @return $this
     */
    public function addProductReviewProductReview(ProductReview $ProductReview)
    {
        $ProductReviews = $this->getProductReviewProductReviews();
        $ProductReviews[] = $ProductReview;

        return $this;
    }

    /**
     * @param ProductReview $ProductReview
     *
     * @return bool
     */
    public function removeProductReviewProductReview(ProductReview $ProductReview)
    {
        $ProductReviews = $this->getProductReviews();

        return $ProductReviews->removeElement($ProductReview);
    }
}
