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
use Plugin\ProductReview\Entity\ProductReviewConfig;
use Plugin\ProductReview\Repository\ProductReviewRepository;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ProductReviewEventLegacy.
 *
 * @deprecated since 3.0.0 - 3.0.8
 */
class ProductReviewEventLegacy extends CommonEvent
{
    /**
     * フロント：商品詳細画面に商品レビューを表示します.
     *
     * @param FilterResponseEvent $event
     */
    public function onRenderProductsDetailBefore(FilterResponseEvent $event)
    {
        log_info('EventLegacy: The product review hook into the product detail start');
        // カート内でも呼ばれるためGETに限定
        if ($event->getRequest()->getMethod() === 'GET') {
            $app = $this->app;

            // Get show max number.
            /* @var $config ProductReviewConfig */
            $config = $app['product_review.repository.product_review_config']->find(1);
            $limit = $config->getReviewMax();

            /**
             * @var ProductReviewRepository
             */
            $repository = $app['product_review.repository.product_review'];

            $id = $app['request']->attributes->get('id');
            $Product = $app['eccube.repository.product']->find($id);
            $Disp = $app['eccube.repository.master.disp']
                ->find(Disp::DISPLAY_SHOW);
            $arrProductReview = $repository->findBy(array('Product' => $Product, 'Status' => $Disp), array('create_date' => 'DESC'), $limit);

            // Get rate
            $rate = $repository->getAvgAll($Product, $Disp);
            $avgRecommend = round($rate['recommend_avg']);
            $reviewNumber = intval($rate['review_num']);

            $twig = $app->renderView(
                'ProductReview/Resource/template/default/product_review.twig',
                array(
                    'id' => $id,
                    'ProductReviews' => $arrProductReview,
                    'avg' => $avgRecommend,
                    'number' => $reviewNumber,
                )
            );

            $response = $event->getResponse();

            // Source
            $html = $response->getContent();

            // Crawler
            $crawler = new Crawler($html);
            $oldElement = $crawler
                ->filter('#item_detail')->last();

            $oldHtml = $oldElement->html();

            $oldHtml = html_entity_decode($oldHtml, ENT_NOQUOTES, 'UTF-8');

            $newHtml = $oldHtml.$twig;

            $html = $this->getHtml($crawler);

            $html = str_replace($oldHtml, $newHtml, $html);

            $response->setContent($html);
            $event->setResponse($response);
        }

        log_info('EventLegacy: The product review hook into the product detail end');
    }

    /**
     * 解析用HTMLを取得.
     *
     * @param Crawler $crawler
     *
     * @return string
     */
    private function getHtml(Crawler $crawler)
    {
        $html = '';
        foreach ($crawler as $domElement) {
            $domElement->ownerDocument->formatOutput = true;
            $html .= $domElement->ownerDocument->saveHTML();
        }

        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }
}
