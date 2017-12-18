<?php

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
     * @return bool
     */
    public function removeProductReview(ProductReview $ProductReview)
    {
        $ProductReviews = $this->getProductReviews();

        return $ProductReviews->removeElement($ProductReview);
    }
}