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

namespace Plugin\ProductReview4;

use Eccube\Entity\Product;
use Eccube\Event\TemplateEvent;
use Eccube\Repository\Master\ProductStatusRepository;
use Plugin\ProductReview4\Entity\ProductReviewStatus;
use Plugin\ProductReview4\Repository\ProductReviewConfigRepository;
use Plugin\ProductReview4\Repository\ProductReviewRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductReviewEvent implements EventSubscriberInterface
{
    /**
     * @var ProductReviewConfigRepository
     */
    protected $productReviewConfigRepository;
    
    /**
     * @var ProductStatusRepository
     */
    protected $productStatusRepository;

    /**
     * @var ProductReviewRepository
     */
    protected $productReviewRepository;

    /**
     * ProductReview constructor.
     *
     * @param ProductReviewConfigRepository $productReviewConfigRepository
     * @param ProductStatusRepository $productStatusRepository
     * @param ProductReviewRepository $productReviewRepository
     */
    public function __construct(
        ProductReviewConfigRepository $productReviewConfigRepository,
        ProductStatusRepository $productStatusRepository,
        ProductReviewRepository $productReviewRepository
    ) {
        $this->productReviewConfigRepository = $productReviewConfigRepository;
        $this->productStatusRepository = $productStatusRepository;
        $this->productReviewRepository = $productReviewRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Product/detail.twig' => 'detail',
        ];
    }

    /**
     * @param TemplateEvent $event
     */
    public function detail(TemplateEvent $event)
    {
        $event->addSnippet('ProductReview4/Resource/template/default/review.twig');

        $Config = $this->productReviewConfigRepository->get();

        /** @var Product $Product */
        $Product = $event->getParameter('Product');

        $ProductReviews = $this->productReviewRepository->findBy(['Status' => ProductReviewStatus::SHOW, 'Product' => $Product], ['id' => 'DESC'], $Config->getReviewMax());

        $rate = $this->productReviewRepository->getAvgAll($Product);
        $avg = round($rate['recommend_avg']);
        $count = intval($rate['review_count']);

        $parameters = $event->getParameters();
        $parameters['ProductReviews'] = $ProductReviews;
        $parameters['ProductReviewAvg'] = $avg;
        $parameters['ProductReviewCount'] = $count;
        $event->setParameters($parameters);
    }
}
