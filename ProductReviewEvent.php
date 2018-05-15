<?php

namespace Plugin\ProductReview;

use Eccube\Entity\Product;
use Eccube\Event\TemplateEvent;
use Eccube\Repository\Master\ProductStatusRepository;
use Plugin\ProductReview\Entity\ProductReview;
use Plugin\ProductReview\Entity\ProductReviewConfig;
use Plugin\ProductReview\Repository\ProductReviewConfigRepository;
use Plugin\ProductReview\Repository\ProductReviewRepository;
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
    public function __construct(ProductReviewConfigRepository $productReviewConfigRepository, ProductStatusRepository $productStatusRepository, ProductReviewRepository $productReviewRepository)
    {
        $this->productReviewConfigRepository = $productReviewConfigRepository;
        $this->productStatusRepository = $productStatusRepository;
        $this->productReviewRepository = $productReviewRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Product/detail.twig' => 'detail',
        ];
    }

    public function detail(TemplateEvent $event)
    {
        /** @var ProductReviewConfig $ProductReviewConfig */
        $ProductReviewConfig = $this->productReviewConfigRepository->find(1);

        /** @var Product $Product */
        $Product = $event->getParameter('Product');

        /** @var ProductReview[] $ProductReviews */
        $ProductReviews = $Product->getProductReviews()
            ->slice(0, $ProductReviewConfig->getReviewMax());

        $rate = $this->productReviewRepository->getAvgAll($Product);
        $avgRecommend = round($rate['recommend_avg']);
        $reviewNumber = intval($rate['review_num']);

        $parameters = $event->getParameters();
        $parameters['ProductReviews'] = $ProductReviews;
        $parameters['avg'] = $avgRecommend;
        $parameters['number'] = $reviewNumber;
        $event->setParameters($parameters);
    }
}
