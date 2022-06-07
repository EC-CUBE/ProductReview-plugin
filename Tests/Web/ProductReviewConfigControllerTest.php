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

namespace Plugin\ProductReview4\Tests\Web;

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
        $crawler = $this->client->request('GET', $this->generateUrl('product_review4_admin_config'));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $min = $this->eccubeConfig['product_review_display_count_min'];
        $max = $this->eccubeConfig['product_review_display_count_max'];
        $this->assertContains('レビューの表示件数('.$min.'〜'.$max.')', $crawler->html());
    }

    /**
     * Config submit.
     */
    public function testMin()
    {
        $min = $this->eccubeConfig['product_review_display_count_min'];
        /**
         * @var Client
         */
        $client = $this->client;
        /**
         * @var Crawler
         */
        $crawler = $this->client->request('GET', $this->generateUrl('product_review4_admin_config'));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->selectButton('登録')->form();

        $form['product_review_config[review_max]'] = $this->faker->numberBetween(-10, $min - 1);
        $crawler = $client->submit($form);

        $this->assertContains($min.'以上', $crawler->html());
    }

    /**
     * Config submit.
     */
    public function testMax()
    {
        $max = $this->eccubeConfig['product_review_display_count_max'];
        /**
         * @var Client
         */
        $client = $this->client;
        /**
         * @var Crawler
         */
        $crawler = $this->client->request('GET', $this->generateUrl('product_review4_admin_config'));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->selectButton('登録')->form();

        $form['product_review_config[review_max]'] = $this->faker->numberBetween($max + 1, 100);
        $crawler = $client->submit($form);

        $this->assertContains($max.'以下でなければなりません。', $crawler->html());
    }

    /**
     * Config submit.
     */
    public function testSuccess()
    {
        $min = $this->eccubeConfig['product_review_display_count_min'];
        $max = $this->eccubeConfig['product_review_display_count_max'];
        /**
         * @var Client
         */
        $client = $this->client;
        /**
         * @var Crawler
         */
        $crawler = $this->client->request('GET', $this->generateUrl('product_review4_admin_config'));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->selectButton('登録')->form();

        $form['product_review_config[review_max]'] = $this->faker->numberBetween($min, $max);
        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isRedirection($this->generateUrl('product_review4_admin_config')));

        $crawler = $client->followRedirect();
        $this->assertContains('登録しました。', $crawler->html());
    }
}
