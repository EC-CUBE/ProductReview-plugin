<?php
/**
 * This file is part of the ProductReview plugin.
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview\Entity;

use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Customer;
use Eccube\Entity\Master\Disp;
use Eccube\Entity\Master\Sex;
use Eccube\Entity\Product;

/**
 * Class ProductReview Entity.
 */
class ProductReview extends AbstractEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $reviewer_name;

    /**
     * @var string
     */
    private $reviewer_url;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var Sex
     */
    private $Sex;

    /**
     * @var int
     */
    private $recommend_level;

    /**
     * @var Disp
     */
    private $Status;

    /**
     * @var Product
     */
    private $Product;

    /**
     * @var Customer
     */
    private $Customer;

    /**
     * @var int
     */
    private $del_flg;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;

    /**
     * Set product_review id.
     *
     * @param string $id
     *
     * @return ProductReview
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get reviewer_name.
     *
     * @return string
     */
    public function getReviewerName()
    {
        return $this->reviewer_name;
    }

    /**
     * Set reviewer_name.
     *
     * @param string $reviewer_name
     *
     * @return ProductReview
     */
    public function setReviewerName($reviewer_name)
    {
        $this->reviewer_name = $reviewer_name;

        return $this;
    }

    /**
     * Get reviewer_url.
     *
     * @return string
     */
    public function getReviewerUrl()
    {
        return $this->reviewer_url;
    }

    /**
     * Set reviewer_url.
     *
     * @param string $reviewer_url
     *
     * @return ProductReview
     */
    public function setReviewerUrl($reviewer_url)
    {
        $this->reviewer_url = $reviewer_url;

        return $this;
    }

    /**
     * Get recommend_level.
     *
     * @return int
     */
    public function getRecommendLevel()
    {
        return $this->recommend_level;
    }

    /**
     * Set recommend_level.
     *
     * @param int $recommend_level
     *
     * @return ProductReview
     */
    public function setRecommendLevel($recommend_level)
    {
        $this->recommend_level = $recommend_level;

        return $this;
    }

    /**
     * Set Sex.
     *
     * @param Sex $Sex
     *
     * @return ProductReview
     */
    public function setSex(Sex $Sex = null)
    {
        $this->Sex = $Sex;

        return $this;
    }

    /**
     * Get Sex.
     *
     * @return Sex
     */
    public function getSex()
    {
        return $this->Sex;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return ProductReview
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return ProductReview
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Set Status.
     *
     * @param Disp $Status
     *
     * @return ProductReview
     */
    public function setStatus(Disp $Status = null)
    {
        $this->Status = $Status;

        return $this;
    }

    /**
     * Get Status.
     *
     * @return Disp
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * Set Product.
     *
     * @param Product $Product
     *
     * @return $this
     */
    public function setProduct(Product $Product)
    {
        $this->Product = $Product;

        return $this;
    }

    /**
     * Get Product.
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->Product;
    }

    /**
     * Set Customer.
     *
     * @param Customer $Customer
     *
     * @return $this
     */
    public function setCustomer(Customer $Customer)
    {
        $this->Customer = $Customer;

        return $this;
    }

    /**
     * Get Customer.
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->Customer;
    }

    /**
     * Set del_flg.
     *
     * @param int $delFlg
     *
     * @return $this
     */
    public function setDelFlg($delFlg)
    {
        $this->del_flg = $delFlg;

        return $this;
    }

    /**
     * Get del_flg.
     *
     * @return int
     */
    public function getDelFlg()
    {
        return $this->del_flg;
    }

    /**
     * Set create_date.
     *
     * @param \DateTime $createDate
     *
     * @return $this
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Get create_date.
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Set update_date.
     *
     * @param \DateTime $updateDate
     *
     * @return $this
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

    /**
     * Get update_date.
     *
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }
}
