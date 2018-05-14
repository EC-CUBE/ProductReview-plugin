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

use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\Master\SexRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Tests\Web\AbstractWebTestCase;
use Faker\Generator;
use Plugin\ProductReview\Entity\ProductReview;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ReviewControllerTest front.
 */
class ReviewControllerTest extends AbstractWebTestCase
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
    protected $productStatusRepo;

    /**
     * Setup method.
     */
    public function setUp()
    {
        parent::setUp();
        $this->faker = $this->getFaker();
        $this->deleteAllRows(array('plg_product_review'));

        $this->productRepo = $this->container->get(ProductRepository::class);
        $this->sexMasterRepo = $this->container->get(SexRepository::class);
        $this->productStatusRepo = $this->container->get(ProductStatusRepository::class);
    }

    /**
     * Add product review.
     */
    public function testProductReviewAddConfirmComplete()
    {
        $productId = 1;
        $crawler = $this->client->request(
            'POST',
            $this->generateUrl('plugin_products_detail_review', array('id' => $productId)),
            array(
                'product_review' => array(
                    'comment' => $this->faker->text(2999),
                    'title' => $this->faker->word,
                    'sex' => 1,
                    'recommend_level' => $this->faker->numberBetween(1, 5),
                    'reviewer_url' => $this->faker->url,
                    'reviewer_name' => $this->faker->word,
                    '_token' => 'dummy',
                ),
                'mode' => 'confirm',
            )
        );
        $this->assertContains('送信する', $crawler->html());

        // Complete
        $form = $crawler->selectButton('送信する')->form();
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect($this->generateUrl('plugin_products_detail_review_complete', array('id' => $productId))));

        // Verify back to product detail link.
        /**
         * @var Crawler
         */
        $crawler = $this->client->followRedirect();
        $link = $crawler->selectLink('商品ページに戻る')->link();

        $this->actual = $link->getUri();

        $this->expected = $this->generateUrl('product_detail', array('id' => $productId), UrlGeneratorInterface::ABSOLUTE_URL);
        $this->verify();
    }

    /**
     * Back test.
     */
    public function testProductReviewAddConfirmBack()
    {
        $productId = 1;
        $inputForm = array(
            'comment' => $this->faker->text(2999),
            'title' => $this->faker->word,
            'sex' => 1,
            'recommend_level' => $this->faker->numberBetween(1, 5),
            'reviewer_url' => $this->faker->url,
            'reviewer_name' => $this->faker->word,
            '_token' => 'dummy',
        );
        $crawler = $this->client->request(
            'POST',
            $this->generateUrl('plugin_products_detail_review', array('id' => $productId)),
            array('product_review' => $inputForm,
                'mode' => 'confirm',
            )
        );
        $this->assertContains('送信する', $crawler->html());

        // Back click
        $form = $crawler->selectButton('戻る')->form();
        $crawlerConfirm = $this->client->submit($form);
        $html = $crawlerConfirm->html();
        $this->assertContains('確認ページヘ', $html);

        // Verify data
        $this->assertContains($inputForm['comment'], $html);
    }

    /**
     * review list.
     */
    public function testProductReview()
    {
        $productId = 1;
        $ProductReview = $this->createProductReviewData($productId);
        $crawler = $this->client->request(
            'GET',
            $this->generateUrl('product_detail', array('id' => $productId))
        );

        // review area
        $this->assertContains('id="product_review_area"', $crawler->html());

        // review content
        $reviewArea = $crawler->filter('#product_review_area');
        $this->assertContains($ProductReview->getComment(), $reviewArea->html());

        // review total
        $totalNum = $reviewArea->filter('.heading02')->html();
        $this->assertContains('1', $totalNum);
    }

    /**
     * review list.
     */
    public function testProductReviewMaxNumber()
    {
        $max = 31;
        $Product = $this->createProduct();
        $productId = $Product->getId();
        $this->createProductReviewByNumber($max, $productId);
        $crawler = $this->client->request(
            'GET',
            $this->generateUrl('product_detail', array('id' => $productId))
        );

        // review area
        $this->assertContains('id="product_review_area"', $crawler->html());

        // review content
        $reviewArea = $crawler->filter('#product_review_area');

        // review total
        $totalHtml = $reviewArea->filter('.heading02')->html();
        $this->assertContains((string) $max, $totalHtml);
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
     * @param int|Product $product
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

        $Display = $this->productStatusRepo->find(ProductStatus::DISPLAY_SHOW);
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
        $Review->setEnabled(true);
        $Review->setCustomer($Customer);

        $this->entityManager->persist($Review);
        $this->entityManager->flush($Review);

        return $Review;
    }
}
