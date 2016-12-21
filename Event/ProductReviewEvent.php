<?php
/**
 * This file is part of the ProductReview plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\ProductReview\Event;

use Eccube\Entity\Product;
use Eccube\Event\TemplateEvent;
use Plugin\ProductReview\Entity\ProductReviewConfig;
use Plugin\ProductReview\Repository\ProductReviewRepository;

/**
 * Class Event
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
         * @var Product $Product
         */
        $Product = $parameters['Product'];

        if (!$Product) {
            return;
        }
        // Get show max number.
        /* @var $config ProductReviewConfig */
        $config = $this->app['product_review.repository.product_review_config']->find(1);
        $max = $config->getReviewMax();

        /**
         * @var $repository ProductReviewRepository
         */
        $repository = $this->app['product_review.repository.product_review'];

        $arrProductReview = $repository->findBy(array('Product' => $Product), array('update_date' => 'DESC'), $max);

        /**
         * @var $twig \Twig_Environment
         */
        $twig = $this->app['twig'];

        $twigAppend = $twig->getLoader()->getSource('ProductReview/Resource/template/default/product_review.twig');

        /**
         * @var string $twigSource twig template.
         */
        $twigSource = $event->getSource();

        $twigSource = $this->renderPosition($twigSource, $twigAppend, $this->pluginTag);

        $event->setSource($twigSource);

        $parameters['ProductReviews'] = $arrProductReview;
        $event->setParameters($parameters);

        log_info('Event: product review render success.', array('Product id' => $Product->getId()));
        log_info('Event: product review hook into the product detail end.');
    }
}
