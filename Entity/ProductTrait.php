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
    private $ProductReviews;

    /**
     * @return ProductReview[]|ArrayCollection|Collection
     */
    public function getProductReviews()
    {
        if (null === $this->ProductReviews) {
            $this->ProductReviews = new ArrayCollection();
        }

        return $this->ProductReviews;
    }

    /**
     * @param ProductReview $ProductReview
     *
     * @return $this
     */
    public function addProductReview(ProductReview $ProductReview)
    {
        $ProductReviews = $this->getProductReviews();
        $ProductReviews[] = $ProductReview;

        return $this;
    }

    /**
     * @param ProductReview $ProductReview
     *
     * @return bool
     */
    public function removeProductReview(ProductReview $ProductReview)
    {
        $ProductReviews = $this->getProductReviews();

        return $ProductReviews->removeElement($ProductReview);
    }
}
