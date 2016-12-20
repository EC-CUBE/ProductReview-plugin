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
use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class ReviewAdminControllerTest extends AbstractAdminWebTestCase
{
    /**
     * please ensure have 1 or more order in database before testing
     */
    private $dummyData;
    private $commonTitle = 'just test Title';

    public function setUp()
    {
        parent::setUp();
        $this->deleteAllRows(array('plg_product_review'));
        $this->dummyData = array();
        $this->dummyData[] = $this->initDummyData(1, 0);
        $this->dummyData[] = $this->initDummyData(2, 0);
        $this->dummyData[] = $this->initDummyData(1, 1);
        $this->dummyData[] = $this->initDummyData(2, 1);
    }

    private function initDummyData($productId, $del_flg)
    {
        $fake = $this->getFaker();
        $Product = $this->app['eccube.repository.product']->find($productId);

        $Review = new \Plugin\ProductReview\Entity\ProductReview();
        $Review->setComment($fake->word);
        $Review->setTitle($this->commonTitle);
        $Review->setProduct($Product);
        $Review->setRecommendLevel(3);
        $Review->setReviewerName($fake->word);
        $Disp = $this->app['eccube.repository.master.disp']
            ->find(\Eccube\Entity\Master\Disp::DISPLAY_SHOW);
        $Review->setStatus($Disp);
        $Review->setDelFlg($del_flg);

        $resultReview = $this->app['eccube.plugin.product_review.repository.product_review']->save($Review);

        if ($resultReview) {
            return $Review;
        }
        return false;
    }

    public function testReviewList()
    {
        $crawler = $this->client->request('GET', $this->app->url('admin_product_review')
        );
        $this->assertContains('検索する', $crawler->html());
        $form = $crawler->selectButton('検索する')->form();
        $crawlerSearch = $this->client->submit($form);
        $this->assertContains('検索結果 ', $crawlerSearch->html());

        $tr = $crawlerSearch->filter('.table-striped tbody');
        foreach($this->dummyData as $dummy) { //echo '=='.$dummy->getDelFlg();
            if ($dummy->getDelFlg()) {
                continue;
            }
            $this->assertContains($dummy->getReviewerName(), $tr->html());
        }
    }

    public function testReviewDelete()
    {
        $Review = array_shift($this->dummyData);
        $productId = $Review->getId();
        $this->client->request('DELETE',
            $this->app->url('admin_product_review_delete', array('id' => $productId))
        );
        $this->assertTrue($this->client->getResponse()->isRedirect($this->app->url('admin_product_review')));
        $ProductNew = $this->app['eccube.plugin.product_review.repository.product_review']->find($productId);
        $this->expected = 1;
        $this->actual = $ProductNew->getDelFlg();
        $this->verify();
    }

    public function testReviewEdit()
    {
        $fake = $this->getFaker();
        $Review = array_shift($this->dummyData);
        $reviewId = $Review->getId();
        $fakeTitle = $fake->word;

        $crawler = $this->client->request('GET',
            $this->app->url('admin_product_review_edit', array('id' => $reviewId))
        );
        $form = $crawler->selectButton('登録')->form();
        $form['admin_product_review[recommend_level]'] = 1;
        $form['admin_product_review[title]'] = $fakeTitle;
        $this->client->submit($form);

        $this->expected = $fakeTitle;
        $this->actual = $Review->getTitle();
        $this->verify();
    }

    /**
     * @param $type
     * @param $expected
     * @dataProvider dataRoutingProvider
     */
    public function testReviewSearch($multi, $productName, $productCode, $sex, $recomendLevel, $postDateFrom, $postDateTo, $expectNumber)
    {
        $dataArray = $this->initForm($multi, $productName, $productCode, $sex, $recomendLevel, $postDateFrom, $postDateTo);
        $crawler = $this->client->request('POST', $this->app->url('admin_product_review'), $dataArray
        );

        $this->assertContains('検索する', $crawler->html());
        if (!$expectNumber) {
            $this->assertContains('検索条件に該当するデータがありませんでした。', $crawler->html());
        } else {
            $numberResult = $crawler->filter('.box-title span strong');
            $this->assertContains($expectNumber, $numberResult->html());
        }
    }

    public function dataRoutingProvider()
    {
        return array(
            array('', '', '', '', '', '', '', '2'),
            array($this->commonTitle, '', '', '', '', '', '', null),
            array('', 'ディナーフォーク', '', '', '', '', '', '1'),
            array('', '', 'cafe-01', '', '', '', '', '1'),
            array('', '', '', '1', '', '', '', null),
//            array('multi', 'productName', 'productCode', 'sex', 'recommendLevel', 'postDateFrom', 'postDateTo','expectNumber'),
        );
    }

    private function initForm($multi, $productName, $productCode, $sex, $recomendLevel, $postDateFrom, $postDateTo)
    {
        return array('admin_product_review_search' => array(
            '_token' => 'dummy',
            'multi' => $multi,
            'product_name' => $productName,
            'product_code' => $productCode,
            'sex' => array($sex),
            'recommend_level' => $recomendLevel,
            'review_start' => $postDateFrom,
            'review_end' => $postDateTo,
        ));
    }
}
