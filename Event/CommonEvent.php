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

use Eccube\Application;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class AbstractEvent.
 */
class CommonEvent
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var string target render on the front-end
     */
    protected $pluginTag = '<!--# product-review-plugin-tag #-->';

    /**
     * AbstractEvent constructor.
     * @param \Silex\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Render position
     *
     * @param string $html
     * @param string $part
     * @param string $markTag
     *
     * @return mixed
     */
    protected function renderPosition($html, $part, $markTag = '')
    {
        if (!$markTag) {
            $markTag = $this->pluginTag;
        }
        // for plugin tag
        if (strpos($html, $markTag)) {
            $newHtml = $markTag.$part;
            $html = str_replace($markTag, $newHtml, $html);
        } else {
            $html = $html->getContent();
            $crawler = new Crawler($html);

            $oldElement = $crawler
                ->filter('#item_detail_area .item_detail');

            $oldHtml = $oldElement->html();
            $oldHtml = html_entity_decode($oldHtml, ENT_NOQUOTES, 'UTF-8');
            $newHtml = $oldHtml.$part;

            $html = $this->getHtml($crawler);
            $html = str_replace($oldHtml, $newHtml, $html);

//            $response->setContent($html);
//            $event->setResponse($response);

            // For old and new ec-cube version
//            $search = '/(<div id="relative_category_box")|(<div class="relative_cat")/';
//            $newHtml = $part.'<div id="relative_category_box" class="relative_cat"';
//            $html = preg_replace($search, $newHtml, $html);
        }

        return $html;
    }

    /**
     * 解析用HTMLを取得
     *
     * @param Crawler $crawler
     * @return string
     */
    protected function getHtml(Crawler $crawler)
    {
        $html = '';
        foreach ($crawler as $domElement) {
            $domElement->ownerDocument->formatOutput = true;
            $html .= $domElement->ownerDocument->saveHTML();
        }

        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }
}
