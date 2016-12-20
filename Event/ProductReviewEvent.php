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

        /**
         * @var ProductMakerRepository $repository
         */
        $repository = $this->app['eccube.plugin.maker.repository.product_maker'];
        /**
         * @var ProductMaker $ProductMaker
         */
        $ProductMaker = $repository->find($Product);
        if (!$ProductMaker) {
            log_info('Event: product maker not found.', array('Product id' => $Product->getId()));

            return;
        }

        $Maker = $ProductMaker->getMaker();

        if (!$Maker) {
            log_info('Event: maker not found.', array('Product maker id' => $ProductMaker->getId()));
            // 商品メーカーマスタにデータが存在しないまたは削除されていれば無視する
            return;
        }

        /**
         * @var \Twig_Environment $twig
         */
        $twig = $this->app['twig'];

        $twigAppend = $twig->getLoader()->getSource('Maker/Resource/template/default/maker.twig');

        /**
         * @var string $twigSource twig template.
         */
        $twigSource = $event->getSource();

        $twigSource = $this->renderPosition($twigSource, $twigAppend, $this->makerTag);

        $event->setSource($twigSource);

        $parameters['maker_name'] = $ProductMaker->getMaker()->getName();
        $parameters['maker_url'] = $ProductMaker->getMakerUrl();
        $event->setParameters($parameters);
        log_info('Event: product maker render success.', array('Product id' => $ProductMaker->getId()));
        log_info('Event: product maker hook into the product detail end.');
    }
}
