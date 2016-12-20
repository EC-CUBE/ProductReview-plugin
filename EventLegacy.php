<?php
/**
 * This file is part of the ProductReview plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\ProductReview;

use Eccube\Event\RenderEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\DomCrawler\Crawler;

class EventLegacy
{

    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * フロント：商品詳細画面に商品レビューを表示します.
     * @param FilterResponseEvent $event
     */
    public function onRenderProductsDetailBefore(FilterResponseEvent $event)
    {
        // カート内でも呼ばれるためGETに限定
        if ($event->getRequest()->getMethod() === 'GET') {
            $app = $this->app;

            $limit = $app['config']['review_regist_max'];
            $id = $app['request']->attributes->get('id');
            $Product = $app['eccube.repository.product']->find($id);
            $Disp = $app['eccube.repository.master.disp']
                ->find(\Eccube\Entity\Master\Disp::DISPLAY_SHOW);
            $ProductReviews = $app['eccube.plugin.product_review.repository.product_review']
                ->findBy(array(
                    'Product' => $Product,
                    'Status' => $Disp
                ),
                array('create_date' => 'DESC'),
                $limit === null ? 5 : $limit
            );

            $twig = $app->renderView(
                'ProductReview/Resource/template/default/product_review.twig',
                array(
                    'id' => $id,
                    'ProductReviews' => $ProductReviews,
                )
            );

            $response = $event->getResponse();

            $html = $response->getContent();
            $crawler = new Crawler($html);

            $oldElement = $crawler
                ->filter('#item_detail_area .item_detail');

            $oldHtml = $oldElement->html();
            $oldHtml = html_entity_decode($oldHtml, ENT_NOQUOTES, 'UTF-8');
            $newHtml = $oldHtml.$twig;

            $html = $this->getHtml($crawler);
            $html = str_replace($oldHtml, $newHtml, $html);

            $response->setContent($html);
            $event->setResponse($response);
        }
    }

    /**
     * 解析用HTMLを取得
     *
     * @param Crawler $crawler
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
