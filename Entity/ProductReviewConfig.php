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
use Eccube\Entity\Master\CsvType;

/**
 * Class ProductReviewConfig Entity.
 */
class ProductReviewConfig extends AbstractEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var CsvType
     */
    private $CsvType;

    /**
     * @var int
     */
    private $review_max;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
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
     * Get csv_id.
     *
     * @return CsvType
     */
    public function getCsvType()
    {
        return $this->CsvType;
    }

    /**
     * Set csv id.
     *
     * @param CsvType $cid
     *
     * @return ProductReviewConfig
     */
    public function setCsvType(CsvType $cid)
    {
        $this->CsvType = $cid;

        return $this;
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
