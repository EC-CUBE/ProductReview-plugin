<?php
/**
 * This file is part of the ProductReview plugin.
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview\Event;

use Eccube\Entity\Master\Disp;
use Eccube\Entity\Product;
use Eccube\Event\TemplateEvent;
use Plugin\ProductReview\Entity\ProductReviewConfig;
use Plugin\ProductReview\Repository\ProductReviewRepository;

/**
 * Class Event.
 */
class ProductReviewEvent extends CommonEvent
{
    /**
     * New event function on version >= 3.0.9 (new hook point)
     * Product detail render (front).
     *
     * @param TemplateEvent $event
     */
    public function onProductDetailRender(TemplateEvent $event)
    {
        log_info('Event: product review hook into the product detail start.');

        $parameters = $event->getParameters();
        /**
         * @var Product
         */
        $Product = $parameters['Product'];

        if (!$Product) {
            return;
        }
        // Get show max number.
        /* @var $config ProductReviewConfig */
        $config = $this->app['product_review.repository.product_review_config']->find(1);
        $max = $config->getReviewMax();

        $Disp = $this->app['eccube.repository.master.disp']->find(Disp::DISPLAY_SHOW);

        /**
         * @var ProductReviewRepository
         */
        $repository = $this->app['product_review.repository.product_review'];

        // Get review
        $arrProductReview = $repository->findBy(array('Product' => $Product, 'Status' => $Disp), array('create_date' => 'DESC'), $max);

        // Get rate
        $rate = $repository->getAvgAll($Product, $Disp);
        $avgRecommend = round($rate['recommend_avg']);
        $reviewNumber = intval($rate['review_num']);

        /**
         * @var \Twig_Environment
         */
        $twig = $this->app['twig'];

        $twigAppend = $twig->getLoader()->getSource('ProductReview/Resource/template/default/product_review.twig');

        /**
         * @var string twig template
         */
        $twigSource = $event->getSource();

        $twigSource = $this->renderPosition($twigSource, $twigAppend, $this->pluginTag);

        $event->setSource($twigSource);

        $parameters['id'] = $Product->getId();
        $parameters['ProductReviews'] = $arrProductReview;
        $parameters['avg'] = $avgRecommend;
        $parameters['number'] = $reviewNumber;
        $event->setParameters($parameters);

        log_info('Event: product review render success.', array('Product id' => $Product->getId()));
        log_info('Event: product review hook into the product detail end.');
    }
}
