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

namespace Plugin\ProductReview42\Tests\Web;

use Eccube\Entity\Master\Sex;
use Eccube\Entity\Product;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\Master\SexRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use Faker\Generator;
use Plugin\ProductReview42\Entity\ProductReview;
use Plugin\ProductReview42\Entity\ProductReviewStatus;
use Plugin\ProductReview42\Repository\ProductReviewRepository;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;


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
     * @var ProductRepository
     */
    protected $productRepo;

    /**
     * @var SexRepository
     */
    protected $sexMasterRepo;

    /**
     * @var ProductStatusRepository
     */
    protected $productReviewRepo;

    /**
     * Setup method.
     */
    public function setUp():void
    {
        parent::setUp();
        $this->faker = $this->getFaker();
        $this->deleteAllRows(['plg_product_review']);

        $this->productRepo = $this->entityManager->getRepository(Product::class);
        $this->sexMasterRepo = $this->entityManager->getRepository(Sex::class);
        $this->productReviewRepo = $this->entityManager->getRepository(ProductReview::class);
    }

    /**
     * Search list.
     */
    public function testReviewList()
    {
        $number = 5;
        $this->createProductReviewByNumber($number);
        $crawler = $this->client->request('GET', $this->generateUrl('product_review_admin_product_review'));
        $this->assertStringContainsString('レビュー管理', $crawler->html());
        $form = $crawler->selectButton('検索')->form();
        $crawlerSearch = $this->client->submit($form);
        $this->assertStringContainsString('検索結果', $crawlerSearch->html());
        /* @var $crawlerSearch Crawler */
        $actual = $crawlerSearch->filter('form#search_form span#search-result')->html();

        $this->actual = preg_replace('/\D/', '', $actual);
        $this->expected = $number;

        $this->assertStringContainsString((string) $this->expected, $this->actual);
    }

    /**
     * test delete.
     */
    public function testReviewDeleteIdNotFound()
    {
        $this->client->request(
            'DELETE',
            $this->generateUrl('product_review_admin_product_review_delete', ['id' => 99999])
        );

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * test delete.
     */
    public function testReviewDelete()
    {
        $Review = $this->createProductReviewData();
        $productReviewId = $Review->getId();
        $this->client->request(
            'DELETE',
            $this->generateUrl('product_review_admin_product_review_delete', ['id' => $productReviewId])
        );
        $this->assertTrue($this->client->getResponse()->isRedirection());

        $this->assertNull($this->productReviewRepo->find($productReviewId));
    }

    /**
     * Test edit.
     */
    public function testReviewEditWithIdInvalid()
    {
        /*
         * @var $crawler Crawler
         */
        $crawler = $this->client->request(
            'GET',
            $this->generateUrl('product_review_admin_product_review_edit', ['id' => 99999])
        );

        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_NOT_FOUND);
    }

    /**
     * Test edit.
     */
    public function testReviewEditWithProductReviewDeleted()
    {
        $Review = $this->createProductReviewData();
        $reviewId = $Review->getId();

        $this->productReviewRepo->delete($Review);
        $this->entityManager->flush();
        $this->entityManager->detach($Review);

        $this->client->request(
            'GET',
            $this->generateUrl('product_review_admin_product_review_edit', ['id' => $reviewId])
        );
        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_NOT_FOUND);
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
            $this->generateUrl('product_review_admin_product_review_edit', ['id' => $reviewId])
        );
        $form = $crawler->selectButton('登録')->form();
        $form['product_review[recommend_level]'] = 1;
        $form['product_review[title]'] = $fakeTitle;
        $crawler = $this->client->submit($form);

        $crawler = $this->client->followRedirect();
        // check message.
        $this->expected = '登録しました。';
        $this->actual = $crawler->filter('.alert')->html();
        $this->assertStringContainsString($this->expected, $this->actual);

        // Check entity
        $this->expected = $fakeTitle;
        $this->actual = $this->productReviewRepo->find($reviewId)->getTitle();
        $this->verify();

        // Stay in edit page
        $this->assertStringContainsString('レビュー管理', $crawler->html());
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
            $this->generateUrl('product_review_admin_product_review'),
            ['product_review_search' => $form]
        );

        $this->assertStringContainsString('検索', $crawler->html());

        $numberResult = $crawler->filter('#search_form #search-result')->html();

        $numberResult = preg_replace('/\D/', '', $numberResult);
        $this->assertStringContainsString('1', $numberResult);

        $table = $crawler->filter('.table tbody');
        $this->assertStringContainsString($review->getReviewerName(), $table->html());
    }

    /**
     * Search test.
     */
    public function testReviewSearchWithPaging()
    {
        $number = 51;
        $this->createProductReviewByNumber($number);

        $crawler = $this->client->request('GET', $this->generateUrl('product_review_admin_product_review'));
        $this->assertStringContainsString('検索', $crawler->html());
        $form = $crawler->selectButton('検索')->form();
        $crawlerSearch = $this->client->submit($form);

        $numberResult = $crawlerSearch->filter('form#search_form #search-result');
        $numberResult = preg_replace('/\D/', '', $numberResult->html());
        $this->assertStringContainsString((string) $number, $numberResult);

        /* @var $crawler Crawler */
        $crawler = $this->client->request('GET', $this->generateUrl('product_review_admin_product_review_page', ['page_no' => 2]));

        // page 2
        $paging = $crawler->filter('ul.pagination .page-item')->last();

        // Current active on page 2.
        $this->assertStringContainsString('active', $paging->parents()->html());
        $this->expected = 2;
        $this->actual = intval($paging->text());
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
            $this->generateUrl('product_review_admin_product_review'),
            ['product_review_search' => $form]
        );

        $this->assertStringContainsString('検索', $crawler->html());
        $numberResult = $crawler->filter('form#search_form span#search-result')->html();

        $numberResult = preg_replace('/\D/', '', $numberResult);
        $this->assertStringContainsString('1', $numberResult);

        $table = $crawler->filter('.table tbody');

        $this->assertStringContainsString($review->getReviewerName(), $table->html());

        $this->expectOutputRegex("/{$review->getTitle()}/");

        $this->client->request(
            'POST',
            $this->generateUrl('product_review_admin_product_review_download')
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
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
        return [
            '_token' => 'dummy',
            'multi' => $review->getReviewerName(),
            'product_name' => $review->getProduct()->getName(),
            'product_code' => $review->getProduct()->getCodeMax(),
            'sex' => [$review->getSex()->getId()],
            'recommend_level' => $review->getRecommendLevel(),
            'review_start' => $review->getCreateDate()->modify('-2 days')->format('Y-m-d'),
            'review_end' => $review->getCreateDate()->modify('+2 days')->format('Y-m-d'),
        ];
    }

    /**
     * @param $number
     * @param int $productId
     */
    private function createProductReviewByNumber($number, $productId = 1)
    {
        $Product = $this->productRepo->find($productId);
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
     * @param int $product
     *
     * @return ProductReview
     */
    private function createProductReviewData($product = 1)
    {
        if ($product instanceof Product) {
            $Product = $product;
        } else {
            $Product = $this->productRepo->find($product);
        }

        $Display = $this->entityManager->find(ProductReviewStatus::class, ProductReviewStatus::SHOW);
        $Sex = $this->sexMasterRepo->find(1);
        $Customer = $this->createCustomer();

        $Review = new ProductReview();
        $Review->setComment($this->faker->word);
        $Review->setTitle($this->faker->word);
        $Review->setProduct($Product);
        $Review->setRecommendLevel($this->faker->numberBetween(1, 5));
        $Review->setReviewerName($this->faker->word);
        $Review->setReviewerUrl($this->faker->url);
        $Review->setStatus($Display);
        $Review->setSex($Sex);
        $Review->setCustomer($Customer);

        $this->entityManager->persist($Review);
        $this->entityManager->flush($Review);

        return $Review;
    }
}
