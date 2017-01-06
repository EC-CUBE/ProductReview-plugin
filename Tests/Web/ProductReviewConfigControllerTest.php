<?php
/**
 * This file is part of the ProductReview plugin.
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview\Tests\Web;

use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use Faker\Generator;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Client;

/**
 * Class ProductReviewConfigControllerTest.
 */
class ProductReviewConfigControllerTest extends AbstractAdminWebTestCase
{
    /**
     * @var Generator
     */
    protected $faker;

    /**
     * Setup method.
     */
    public function setUp()
    {
        parent::setUp();
        $this->faker = $this->getFaker();
    }

    /**
     * Config routing.
     */
    public function testRouting()
    {
        /**
         * @var Client
         */
        $client = $this->client;
        /**
         * @var Crawler
         */
        $crawler = $this->client->request('GET', $this->app->url('plugin_ProductReview_config'));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertContains('レビューの表示件数(1～30)', $crawler->html());
    }

    /**
     * Config submit.
     */
    public function testMin()
    {
        $min = $this->app['config']['ProductReview']['const']['review_regist_min'];
        /**
         * @var Client
         */
        $client = $this->client;
        /**
         * @var Crawler
         */
        $crawler = $this->client->request('GET', $this->app->url('plugin_ProductReview_config'));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->selectButton('設定')->form();

        $form['admin_product_review_config[review_max]'] = $this->faker->numberBetween(-10, $min - 1);
        $crawler = $client->submit($form);

        $this->assertContains($min.'以上でなければなりません。', $crawler->html());
    }

    /**
     * Config submit.
     */
    public function testMax()
    {
        $max = $this->app['config']['ProductReview']['const']['review_regist_max'];
        /**
         * @var Client
         */
        $client = $this->client;
        /**
         * @var Crawler
         */
        $crawler = $this->client->request('GET', $this->app->url('plugin_ProductReview_config'));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->selectButton('設定')->form();

        $form['admin_product_review_config[review_max]'] = $this->faker->numberBetween($max + 1, 100);
        $crawler = $client->submit($form);

        $this->assertContains($max.'以下でなければなりません。', $crawler->html());
    }

    /**
     * Config submit.
     */
    public function testSuccess()
    {
        $min = $this->app['config']['ProductReview']['const']['review_regist_min'];
        $max = $this->app['config']['ProductReview']['const']['review_regist_max'];
        /**
         * @var Client
         */
        $client = $this->client;
        /**
         * @var Crawler
         */
        $crawler = $this->client->request('GET', $this->app->url('plugin_ProductReview_config'));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->selectButton('設定')->form();

        $form['admin_product_review_config[review_max]'] = $this->faker->numberBetween($min, $max);
        $crawler = $client->submit($form);

        $this->assertContains('商品レビュー設定が保存されました。', $crawler->html());
    }
}
