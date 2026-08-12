<?php

declare(strict_types=1);

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

namespace Plugin\ProductReview44\Tests\Web;

use Eccube\Entity\Master\Sex;
use Eccube\Entity\Product;
use Eccube\Repository\Master\SexRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use Faker\Generator;
use Plugin\ProductReview44\Entity\ProductReview;
use Plugin\ProductReview44\Entity\ProductReviewStatus;
use Plugin\ProductReview44\Repository\ProductReviewRepository;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ReviewAdminControllerTest.
 */
final class ReviewAdminControllerTest extends AbstractAdminWebTestCase
{
    protected ?Generator $faker = null;

    protected ?ProductRepository $productRepo = null;

    protected ?SexRepository $sexMasterRepo = null;

    protected ?ProductReviewRepository $productReviewRepo = null;

    /**
     * Setup method.
     */
    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->faker = $this->getFaker();
        $this->deleteAllRows(['plg_product_review']);

        /** @var ProductRepository $productRepo */
        $productRepo = $this->entityManager->getRepository(Product::class);
        $this->productRepo = $productRepo;
        /** @var SexRepository $sexMasterRepo */
        $sexMasterRepo = $this->entityManager->getRepository(Sex::class);
        $this->sexMasterRepo = $sexMasterRepo;
        /** @var ProductReviewRepository $productReviewRepo */
        $productReviewRepo = $this->entityManager->getRepository(ProductReview::class);
        $this->productReviewRepo = $productReviewRepo;
    }

    /**
     * Search list.
     */
    public function testReviewList(): void
    {
        $number = 5;
        $this->createProductReviewByNumber($number);
        $crawler = $this->client->request(Request::METHOD_GET, $this->generateUrl('product_review_admin_product_review'));
        $this->assertStringContainsString('レビュー管理', $crawler->html());
        $form = $crawler->selectButton('検索')->form();
        $crawlerSearch = $this->client->submit($form);
        $this->assertStringContainsString('検索結果', $crawlerSearch->html());
        /* @var $crawlerSearch Crawler */
        $actual = $crawlerSearch->filter('form#search_form span#search-result')->html();

        $this->actual = preg_replace('/\D/', '', $actual);
        $this->expected = $number;

        $this->assertStringContainsString((string) $this->expected, (string) $this->actual);
    }

    /**
     * test delete.
     */
    public function testReviewDeleteIdNotFound(): void
    {
        $this->client->request(
            Request::METHOD_DELETE,
            $this->generateUrl('product_review_admin_product_review_delete', ['id' => 99999])
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    /**
     * test delete.
     */
    public function testReviewDelete(): void
    {
        $Review = $this->createProductReviewData();
        $productReviewId = $Review->getId();
        $this->client->request(
            Request::METHOD_DELETE,
            $this->generateUrl('product_review_admin_product_review_delete', ['id' => $productReviewId])
        );
        $this->assertTrue($this->client->getResponse()->isRedirection());

        $this->assertNull($this->productReviewRepo->find($productReviewId));
    }

    /**
     * Test edit.
     */
    public function testReviewEditWithIdInvalid(): void
    {
        /*
         * @var $crawler Crawler
         */
        $this->client->request(
            Request::METHOD_GET,
            $this->generateUrl('product_review_admin_product_review_edit', ['id' => 99999])
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test edit.
     */
    public function testReviewEditWithProductReviewDeleted(): void
    {
        $Review = $this->createProductReviewData();
        $reviewId = $Review->getId();

        $this->productReviewRepo->delete($Review);
        $this->entityManager->flush();
        $this->entityManager->detach($Review);

        $this->client->request(
            Request::METHOD_GET,
            $this->generateUrl('product_review_admin_product_review_edit', ['id' => $reviewId])
        );
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test edit.
     */
    public function testReviewEditSuccess(): void
    {
        $Review = $this->createProductReviewData();
        $reviewId = $Review->getId();
        $fakeTitle = $this->faker->word;

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->generateUrl('product_review_admin_product_review_edit', ['id' => $reviewId])
        );
        $form = $crawler->selectButton('登録')->form();
        $form['product_review[recommend_level]'] = (string) 1;
        $form['product_review[title]'] = $fakeTitle;
        $crawler = $this->client->submit($form);

        $crawler = $this->client->followRedirect();
        // check message.
        $this->expected = '登録しました。';
        $this->actual = $crawler->filter('.alert')->html();
        $this->assertStringContainsString($this->expected, $this->actual);

        // Check entity
        $this->expected = $fakeTitle;
        $Review = $this->productReviewRepo->find($reviewId);
        self::assertNotNull($Review);
        $this->actual = $Review->getTitle();
        $this->verify();

        // Stay in edit page
        $this->assertStringContainsString('レビュー管理', $crawler->html());
    }

    /**
     * Test edit.
     *
     * 空白のみの入力は TrimListener で空文字になり、必須項目の setter へ null が渡る.
     * データマッピングはバリデーションより前に行われるため、setter が null を受け付けないと
     * NotBlank のエラー表示に到達せず TypeError になる.
     */
    public function testReviewEditWithBlankOnlyRequiredValue(): void
    {
        $Review = $this->createProductReviewData();

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->generateUrl('product_review_admin_product_review_edit', ['id' => $Review->getId()])
        );
        $form = $crawler->selectButton('登録')->form();
        $form['product_review[reviewer_name]'] = '   ';
        $form['product_review[title]'] = '   ';
        $form['product_review[comment]'] = '   ';
        $crawler = $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertStringContainsString('入力されていません。', $crawler->html());
    }

    /**
     * Search test.
     */
    public function testReviewSearch(): void
    {
        $review = $this->createProductReviewData();
        $form = $this->initForm($review);

        $crawler = $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('product_review_admin_product_review'),
            ['product_review_search' => $form]
        );

        $this->assertStringContainsString('検索', $crawler->html());

        $numberResult = $crawler->filter('#search_form #search-result')->html();

        $numberResult = preg_replace('/\D/', '', $numberResult);
        $this->assertStringContainsString('1', (string) $numberResult);

        $table = $crawler->filter('.table tbody');
        $this->assertStringContainsString($review->getReviewerName(), $table->html());
    }

    /**
     * Search test.
     */
    public function testReviewSearchWithPaging(): void
    {
        $number = 51;
        $this->createProductReviewByNumber($number);

        $crawler = $this->client->request(Request::METHOD_GET, $this->generateUrl('product_review_admin_product_review'));
        $this->assertStringContainsString('検索', $crawler->html());
        $form = $crawler->selectButton('検索')->form();
        $crawlerSearch = $this->client->submit($form);

        $numberResult = $crawlerSearch->filter('form#search_form #search-result');
        $numberResult = preg_replace('/\D/', '', $numberResult->html());
        $this->assertStringContainsString((string) $number, (string) $numberResult);

        /* @var $crawler Crawler */
        $crawler = $this->client->request(Request::METHOD_GET, $this->generateUrl('product_review_admin_product_review_page', ['page_no' => 2]));

        // page 2
        $paging = $crawler->filter('ul.pagination .page-item')->last();

        // Current active on page 2.
        $this->assertStringContainsString('active', $paging->ancestors()->html());
        $this->expected = 2;
        $this->actual = intval($paging->text());
        $this->verify();
    }

    /**
     * Download csv test.
     */
    public function testDownloadCsv(): void
    {
        $Product = $this->createProduct();
        $review = $this->createProductReviewData($Product->getId());
        $form = $this->initForm($review);
        $crawler = $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('product_review_admin_product_review'),
            ['product_review_search' => $form]
        );

        $this->assertStringContainsString('検索', $crawler->html());
        $numberResult = $crawler->filter('form#search_form span#search-result')->html();

        $numberResult = preg_replace('/\D/', '', $numberResult);
        $this->assertStringContainsString('1', (string) $numberResult);

        $table = $crawler->filter('.table tbody');

        $this->assertStringContainsString($review->getReviewerName(), $table->html());

        $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('product_review_admin_product_review_download')
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $content = $this->client->getInternalResponse()->getContent();
        $content = mb_convert_encoding($content, 'UTF-8', 'SJIS-win');
        $this->assertMatchesRegularExpression("/{$review->getTitle()}/", $content);
    }

    /**
     * Search form.
     *
     * @return array<string, mixed>
     */
    private function initForm(ProductReview $review): array
    {
        return [
            '_token' => 'dummy',
            'multi' => $review->getReviewerName(),
            'product_name' => $review->getProduct()->getName(),
            'product_code' => $review->getProduct()->getCodeMax(),
            'sex' => [$review->getSex()->getId()],
            'recommend_level' => $review->getRecommendLevel(),
            // getCreateDate() は同一インスタンスを返すため、modify で共有状態を壊さないよう clone する
            'review_start' => (clone $review->getCreateDate())->modify('-2 days')->format('Y-m-d'),
            'review_end' => (clone $review->getCreateDate())->modify('+2 days')->format('Y-m-d'),
        ];
    }

    private function createProductReviewByNumber(int $number, int $productId = 1): void
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
     */
    private function createProductReviewData(int|Product $product = 1): ProductReview
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
        $this->entityManager->flush();

        return $Review;
    }
}
