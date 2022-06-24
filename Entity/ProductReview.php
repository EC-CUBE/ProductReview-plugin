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
use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Customer;
use Eccube\Entity\Master\Sex;
use Eccube\Entity\Product;

/**
 * ProductReview
 *
 * @ORM\Table(name="plg_product_review")
 * @ORM\Entity(repositoryClass="Plugin\ProductReview42\Repository\ProductReviewRepository")
 */
class ProductReview extends AbstractEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="reviewer_name", type="string")
     */
    private $reviewer_name;

    /**
     * @var string
     *
     * @ORM\Column(name="reviewer_url", type="text", nullable=true)
     */
    private $reviewer_url;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=50)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;

    /**
     * @var Sex
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Sex")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sex_id", referencedColumnName="id")
     * })
     */
    private $Sex;

    /**
     * @var int
     *
     * @ORM\Column(name="recommend_level", type="smallint")
     */
    private $recommend_level;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Product")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     * })
     */
    private $Product;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $Customer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz")
     */
    private $create_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetimetz")
     */
    private $update_date;

    /**
     * @var \Plugin\ProductReview42\Entity\ProductReviewStatus
     *
     * @ORM\ManyToOne(targetEntity="Plugin\ProductReview42\Entity\ProductReviewStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $Status;

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
     * @return \Plugin\ProductReview42\Entity\ProductReviewStatus
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * @param \Plugin\ProductReview42\Entity\ProductReviewStatus $status
     */
    public function setStatus(\Plugin\ProductReview42\Entity\ProductReviewStatus $Status)
    {
        $this->Status = $Status;
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
