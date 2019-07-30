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

namespace Plugin\ProductReview4\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Master\CsvType;

/**
 * ProductReviewConfig
 *
 * @ORM\Table(name="plg_product_review_config")
 * @ORM\Entity(repositoryClass="Plugin\ProductReview4\Repository\ProductReviewConfigRepository")
 */
class ProductReviewConfig extends AbstractEntity
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
     * @var int
     *
     * @ORM\Column(name="review_max", type="smallint", nullable=true, options={"unsigned":true, "default":5})
     */
    private $review_max;

    /**
     * @var \Eccube\Entity\Master\CsvType
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\CsvType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="csv_type_id", nullable=true, referencedColumnName="id")
     * })
     */
    private $CsvType;

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
     * Set product_review config id.
     *
     * @param string $id
     *
     * @return ProductReviewConfig
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
     * Get ReviewMax.
     *
     * @return int
     */
    public function getReviewMax()
    {
        return $this->review_max;
    }

    /**
     * Set max.
     *
     * @param int $max
     *
     * @return ProductReview
     */
    public function setReviewMax($max)
    {
        $this->review_max = $max;

        return $this;
    }

    /**
     * Get CsvType
     *
     * @return \Eccube\Entity\Master\CsvType
     */
    public function getCsvType()
    {
        return $this->CsvType;
    }

    /**
     * Set CsvType
     *
     * @param CsvType $CsvType
     *
     * @return $this
     */
    public function setCsvType(CsvType $CsvType = null)
    {
        $this->CsvType = $CsvType;

        return $this;
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
