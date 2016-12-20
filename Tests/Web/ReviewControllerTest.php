<?php
/**
 * This file is part of the ProductReview plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\ProductReview\Tests\Web;

use Eccube\Common\Constant;
use Eccube\Tests\Web\AbstractWebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class ReviewControllerTest extends AbstractWebTestCase
{
    /**
     * please ensure have 1 or more order in database before testing
     */
    public function setUp()
    {
        parent::setUp();
    }

    public function testProductReviewAddConfirmComplete()
    {
        $productId = 1;
        $fake = $this->getFaker();
        $crawler = $this->client->request(
            'POST',
            $this->app->url('products_detail_review', array('id' => $productId)),
            array('product_review' => array(
                'comment' => $fake->word,
                'title' => $fake->word,
                'sex' => 1,
                'recommend_level' => 4,
                'reviewer_url' => '',
                'reviewer_name' => $fake->word,
                '_token' => 'dummy',

            ), 'mode' => 'confirm')
        );
        $this->assertContains('完了ページヘ', $crawler->html());

        $form = $crawler->selectButton('完了ページヘ')->form();
        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect($this->app->url('products_detail_review_complete', array('id' => $productId))));

    }

    public function testProductReviewAddConfirmBack()
    {
        $productId = 1;
        $fake = $this->getFaker();
        $crawler = $this->client->request(
            'POST',
            $this->app->url('products_detail_review', array('id' => $productId)),
            array('product_review' => array(
                'comment' => $fake->word,
                'title' => $fake->word,
                'sex' => 1,
                'recommend_level' => 4,
                'reviewer_url' => '',
                'reviewer_name' => $fake->word,
                '_token' => 'dummy',

            ), 'mode' => 'confirm')
        );
        $this->assertContains('完了ページヘ', $crawler->html());

        $form = $crawler->selectButton('戻る')->form();
        $crawlerConfirm = $this->client->submit($form);
        $this->assertContains('確認ページヘ', $crawlerConfirm->html());

    }

    private function initReviewData($productId)
    {
        $fake = $this->getFaker();
        $Product = $this->app['eccube.repository.product']->find($productId);

        $Review = new \Plugin\ProductReview\Entity\ProductReview();
        $Review->setComment($fake->word);
        $Review->setTitle($fake->word);
        $Review->setProduct($Product);
        $Review->setRecommendLevel(3);
        $Review->setReviewerName($fake->word);
        $Disp = $this->app['eccube.repository.master.disp']
            ->find(\Eccube\Entity\Master\Disp::DISPLAY_SHOW);
        $Review->setStatus($Disp);
        $Review->setDelFlg(Constant::DISABLED);

        $resultReview = $this->app['eccube.plugin.product_review.repository.product_review']->save($Review);
        
        if ($resultReview) {
            return $Review;
        }
        return false;
    }

    public function testProductReview()
    {
        $productId = 1;
        $ProductReview = $this->initReviewData($productId);
        $crawler = $this->client->request(
            'GET',
            $this->app->url('product_detail', array('id' => $productId))
        );

        $this->assertContains('id="review_area"', $crawler->html());
        $this->assertContains($ProductReview->getComment(), $crawler->html());

    }

}
