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

use Eccube\Common\Constant;
use Eccube\Entity\Master\Disp;
use Eccube\Entity\Product;
use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use Faker\Generator;
use Plugin\ProductReview\Entity\ProductReview;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Client;

/**
 * Class ReviewAdminControllerTest.
 */
class ReviewAdminControllerTest extends AbstractAdminWebTestCase
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
        $this->deleteAllRows(array('plg_product_review'));
    }

    /**
     * Search list.
     */
    public function testReviewList()
    {
        $number = 5;
        $this->createProductReviewByNumber($number);
        $crawler = $this->client->request('GET', $this->app->url('plugin_admin_product_review'));
        $this->assertContains('検索する', $crawler->html());
        $form = $crawler->selectButton('検索する')->form();
        $crawlerSearch = $this->client->submit($form);
        $this->assertContains('検索結果', $crawlerSearch->html());
        /* @var $crawlerSearch Crawler */
        $actual = $crawlerSearch->filter('.box-title span strong')->html();

        $this->actual = $actual;

        $this->expected = 5;

        $this->assertContains((string) $this->expected, $this->actual);
    }

    /**
     * test delete.
     */
    public function testReviewDeleteIdNotFound()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $this->client->request('DELETE',
            $this->app->url('plugin_admin_product_review_delete', array('id' => 99999))
        );
    }

    /**
     * test delete.
     */
    public function testReviewDelete()
    {
        $Review = $this->createProductReviewData();
        $productReviewId = $Review->getId();
        $this->client->request('DELETE',
            $this->app->url('plugin_admin_product_review_delete', array('id' => $productReviewId))
        );
        $this->assertTrue($this->client->getResponse()->isRedirection());

        $this->expected = Constant::ENABLED;
        $this->actual = $Review->getDelFlg();
        $this->verify();
    }

    /**
     * Test edit.
     */
    public function testReviewEditWithIdInvalid()
    {
        /*
         * @var $crawler Crawler
         */
        $this->client->request(
            'GET',
            $this->app->url('plugin_admin_product_review_edit', array('id' => 99999))
        );
        /**
         * @var Client
         */
        $client = $this->client;
        $this->assertTrue($client->getResponse()->isRedirect($this->app->url('plugin_admin_product_review')));

        $crawler = $client->followRedirect();

        $this->expected = '商品レビューは見つかりませんでした。';
        $this->actual = $crawler->filter('.alert')->html();
        $this->assertContains($this->expected, $this->actual);
    }

    /**
     * Test edit.
     */
    public function testReviewEditWithProductReviewDeleted()
    {
        $Review = $this->createProductReviewData();
        $reviewId = $Review->getId();

        $this->app['product_review.repository.product_review']->delete($Review);
        $this->app['orm.em']->detach($Review);

        $crawler = $this->client->request(
            'GET',
            $this->app->url('plugin_admin_product_review_edit', array('id' => $reviewId))
        );
        /**
         * @var Client
         */
        $client = $this->client;
        $this->assertTrue($client->getResponse()->isRedirect($this->app->url('plugin_admin_product_review')));

        $crawler = $client->followRedirect();

        $this->expected = '商品レビューは見つかりませんでした。';
        $this->actual = $crawler->filter('.alert')->html();
        $this->assertContains($this->expected, $this->actual);
    }

    /**
     * Test edit.
     */
    public function testReviewEditSuccess()
    {
        $Review = $this->createProductReviewData();
        $reviewId = $Review->getId();
        $fakeTitle = $this->faker->word;

        $crawler = $this->client->request(
            'GET',
            $this->app->url('plugin_admin_product_review_edit', array('id' => $reviewId))
        );
        $form = $crawler->selectButton('登録')->form();
        $form['admin_product_review[recommend_level]'] = 1;
        $form['admin_product_review[title]'] = $fakeTitle;
        $crawler = $this->client->submit($form);

        // check message.
        $this->expected = '商品レビューを保存しました。';
        $this->actual = $crawler->filter('.alert')->html();
        $this->assertContains($this->expected, $this->actual);

        // Check entity
        $this->expected = $fakeTitle;
        $this->actual = $Review->getTitle();
        $this->verify();

        // Stay in edit page
        $this->assertContains('商品レビュー管理', $crawler->html());
    }

    /**
     * Search test.
     */
    public function testReviewSearch()
    {
        $review = $this->createProductReviewData();
        $form = $this->initForm($review);
        $crawler = $this->client->request(
            'POST',
            $this->app->url('plugin_admin_product_review'),
            array('admin_product_review_search' => $form)
        );

        $this->assertContains('検索する', $crawler->html());
        $numberResult = $crawler->filter('.box-title span strong');
        $this->assertContains('1', $numberResult->html());

        $table = $crawler->filter('.table-striped tbody');

        $this->assertContains($review->getReviewerName(), $table->html());
    }

    /**
     * Search test.
     */
    public function testReviewSearchWithPaging()
    {
        $number = 11;
        $this->createProductReviewByNumber($number);

        $crawler = $this->client->request('GET', $this->app->url('plugin_admin_product_review'));
        $this->assertContains('検索する', $crawler->html());
        $form = $crawler->selectButton('検索する')->form();
        $crawlerSearch = $this->client->submit($form);

        $numberResult = $crawlerSearch->filter('.box-title span strong');
        $this->assertContains((string) $number, $numberResult->html());

        /* @var $crawler Crawler */
        $crawler = $this->client->request('GET', $this->app->url('plugin_admin_product_review_page', array('page_no' => 2)));

        // page 2
        $paging = $crawler->filter('#pagination_wrap .pagenation__item')->last();

        // Current active on page 2.
        $this->assertContains('active', $paging->parents()->html());

        $this->expected = 2;
        $this->actual = $paging->text();
        $this->verify();
    }

    /**
     * Download csv test.
     */
    public function testDownloadCsv()
    {
        $Product = $this->createProduct();
        $review = $this->createProductReviewData($Product->getId());
        $form = $this->initForm($review);
        $crawler = $this->client->request(
            'POST',
            $this->app->url('plugin_admin_product_review'),
            array('admin_product_review_search' => $form)
        );

        $this->assertContains('検索する', $crawler->html());
        $numberResult = $crawler->filter('.box-title span strong');
        $this->assertContains('1', $numberResult->html());

        $table = $crawler->filter('.table-striped tbody');

        $this->assertContains($review->getReviewerName(), $table->html());

        $this->expectOutputRegex("/{$review->getTitle()}/");

        $this->client->request(
            'POST',
            $this->app->url('plugin_admin_product_review_download')
        );
    }

    /**
     * Search form.
     *
     * @param ProductReview $review
     *
     * @return array
     */
    private function initForm(ProductReview $review)
    {
        return array(
            '_token' => 'dummy',
            'multi' => $review->getReviewerName(),
            'product_name' => $review->getProduct()->getName(),
            'product_code' => $review->getProduct()->getCodeMax(),
            'sex' => array($review->getSex()->getId()),
            'recommend_level' => $review->getRecommendLevel(),
            'review_start' => $review->getCreateDate()->modify('-2 days')->format('Y-m-d'),
            'review_end' => $review->getCreateDate()->modify('+2 days')->format('Y-m-d'),
        );
    }

    /**
     * @param $number
     * @param int $productId
     */
    private function createProductReviewByNumber($number, $productId = 1)
    {
        $Product = $this->app['eccube.repository.product']->find($productId);
        if (!$Product) {
            $Product = $this->createProduct();
        }

        for ($i = 0; $i < $number; ++$i) {
            $this->createProductReviewData($Product);
        }
    }

    /**
     * Create data.
     *
     * @param int|Product $product
     * @param int         $delFlg
     *
     * @return ProductReview
     */
    private function createProductReviewData($product = 1, $delFlg = Constant::DISABLED)
    {
        if ($product instanceof Product) {
            $Product = $product;
        } else {
            $Product = $this->app['eccube.repository.product']->find($product);
        }

        $Disp = $this->app['eccube.repository.master.disp']->find(Disp::DISPLAY_SHOW);
        $Sex = $this->app['eccube.repository.master.sex']->find(1);
        $Customer = $this->createCustomer();

        $Review = new ProductReview();
        $Review->setComment($this->faker->word);
        $Review->setTitle($this->faker->word);
        $Review->setProduct($Product);
        $Review->setRecommendLevel($this->faker->numberBetween(1, 5));
        $Review->setReviewerName($this->faker->word);
        $Review->setReviewerUrl($this->faker->url);
        $Review->setStatus($Disp);
        $Review->setDelFlg($delFlg);
        $Review->setSex($Sex);
        $Review->setCustomer($Customer);

        $this->app['orm.em']->persist($Review);
        $this->app['orm.em']->flush($Review);

        return $Review;
    }
}
