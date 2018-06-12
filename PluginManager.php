<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview;

use Eccube\Application;
use Eccube\Entity\Csv;
use Eccube\Entity\Layout;
use Eccube\Entity\Master\CsvType;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\Page;
use Eccube\Entity\PageLayout;
use Eccube\Plugin\AbstractPluginManager;
use Eccube\Repository\CsvRepository;
use Eccube\Repository\LayoutRepository;
use Eccube\Repository\Master\DeviceTypeRepository;
use Eccube\Repository\MemberRepository;
use Eccube\Repository\PageLayoutRepository;
use Eccube\Repository\PageRepository;
use Plugin\ProductReview\Entity\ProductReviewConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PluginManager extends AbstractPluginManager
{
    /**
     * @var array
     */
    private $urls = [
        'plugin_products_detail_review' => 'レビューを書く1',
        'plugin_products_detail_review_complete' => 'レビューを書く2',
        'plugin_products_detail_review_error' => 'レビューを書く3',
    ];

    /**
     * @var array plugin entity
     */
    protected $entities = [
        'Plugin\ProductReview\Entity\ProductReviewConfig',
    ];

    /**
     * @param null $meta
     * @param Application|null $app
     * @param ContainerInterface $container
     */
    public function enable($meta = null, Application $app = null, ContainerInterface $container)
    {
        $CsvType = $this->createCsvData($container);

        $entityManager = $container->get('doctrine.orm.entity_manager');
        $ProductReviewConfig = $entityManager->find(ProductReviewConfig::class, 1);
        if (null === $ProductReviewConfig) {
            $ProductReviewConfig = new ProductReviewConfig();
            $ProductReviewConfig->setReviewMax(10);
        }
        $ProductReviewConfig->setCsvTypeId($CsvType);
        $entityManager->persist($ProductReviewConfig);
        $entityManager->flush();

        foreach ($this->urls as $url => $name) {
            $PageLayout = $container->get(PageRepository::class)->findOneBy(['url' => $url]);
            if (is_null($PageLayout)) {
                // pagelayoutの作成
                $this->createPageLayout($container, $name, $url);
            }
        }
    }

    /**
     * @param null $meta
     * @param Application|null $app
     * @param ContainerInterface $container
     */
    public function disable($meta = null, Application $app = null, ContainerInterface $container)
    {
        // pagelayoutの削除
        foreach ($this->urls as $url => $name) {
            $this->removePageLayout($container, $url);
        }

        $this->deleteData($container);
    }

    /**
     * @param null $meta
     * @param Application|null $app
     * @param ContainerInterface $container
     */
    public function update($meta = null, Application $app = null, ContainerInterface $container)
    {
        $PageLayout = $container->get(PageRepository::class)->findOneBy(['url' => 'plugin_coupon_shopping']);
        if (is_null($PageLayout)) {
            // pagelayoutの作成
            $this->pageLayout($container);
        }
    }

    /**
     * @param ContainerInterface $container
     * @param $name
     * @param $url
     */
    private function createPageLayout(ContainerInterface $container, $name, $url)
    {
        // ページレイアウトにプラグイン使用時の値を代入
        $DeviceType = $container->get(DeviceTypeRepository::class)->find(DeviceType::DEVICE_TYPE_PC);
        /** @var \Eccube\Entity\Page $Page */
        $Page = $container->get(PageRepository::class)->findOrCreate(null, $DeviceType);
        $Page->setEditType(Page::EDIT_TYPE_DEFAULT);
        $Page->setName($name);
        $Page->setUrl($url);
        $Page->setFileName('../../Plugin/ProductReview/Resource/template/default/index');
        $Page->setMetaRobots('noindex');
        // DB登録
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $entityManager->persist($Page);
        $entityManager->flush($Page);
        $Layout = $container->get(LayoutRepository::class)->find(Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE);
        $PageLayout = new PageLayout();
        $PageLayout->setPage($Page)
            ->setPageId($Page->getId())
            ->setLayout($Layout)
            ->setLayoutId($Layout->getId())
            ->setSortNo(0);
        $entityManager->persist($PageLayout);
        $entityManager->flush($PageLayout);
    }

    /**
     * クーポン用ページレイアウトを削除.
     *
     * @param ContainerInterface $container
     * @param $url
     */
    private function removePageLayout(ContainerInterface $container, $url)
    {
        // ページ情報の削除
        $Page = $container->get(PageRepository::class)->findOneBy(['url' => $url]);
        if ($Page) {
            $Layout = $container->get(LayoutRepository::class)->find(Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE);
            $PageLayout = $container->get(PageLayoutRepository::class)->findOneBy([
                'Page' => $Page,
                'Layout' => $Layout,
            ]);
            // Blockの削除
            $entityManager = $container->get('doctrine.orm.entity_manager');
            $entityManager->remove($PageLayout);
            $entityManager->remove($Page);
            $entityManager->flush();
        }
    }

    /**
     * Create csv data.
     *
     * @param ContainerInterface $container
     *
     * @return CsvType
     */
    protected function createCsvData(ContainerInterface $container)
    {
        /** @var $em EntityManager */
        $em = $container->get('doctrine.orm.entity_manager');

        // Create csv type master
        /** @var $repos CsvTypeRepository */
        $repos = $em->getRepository('Eccube\Entity\Master\CsvType');
        $csvTypeId = $repos->createQueryBuilder('ct')
            ->select('Max(ct.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $CsvType = new CsvType();
        $CsvType->setName('商品レビューCSV')
            ->setId($csvTypeId + 1)
            ->setSortNo(1);
        $em->persist($CsvType);
        $em->flush();

        // Create csv data
        $Member = $container->get(MemberRepository::class)->find(2);
        $rank = 1;
        $Csv = new Csv();
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('Product')
            ->setReferenceFieldName('name')
            ->setDispName('商品名')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('Status')
            ->setReferenceFieldName('name')
            ->setDispName('公開・非公開')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('create_date')
            ->setReferenceFieldName('create_date')
            ->setDispName('投稿日')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('reviewer_name')
            ->setReferenceFieldName('reviewer_name')
            ->setDispName('投稿者名')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('reviewer_url')
            ->setReferenceFieldName('reviewer_url')
            ->setDispName('投稿者URL')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('Sex')
            ->setReferenceFieldName('name')
            ->setDispName('性別')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('recommend_level')
            ->setReferenceFieldName('recommend_level')
            ->setDispName('おすすめレベル')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('title')
            ->setReferenceFieldName('title')
            ->setDispName('タイトル')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('comment')
            ->setReferenceFieldName('comment')
            ->setDispName('コメント')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        return $CsvType;
    }

    /**
     * Delete data
     *
     * @param ContainerInterface $container
     */
    protected function deleteData(ContainerInterface $container)
    {
        //$app = new Application();
        //$app->initialize();
        //$app->boot();

        /* @var $Config ProductReviewConfig */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $Config = $entityManager->find(ProductReviewConfig::class, 1);
        if (!$Config) {
            return;
        }

        $CsvType = $Config->getCsvTypeId();

        $arrCsv = $container->get(CsvRepository::class)->findBy(['CsvType' => $CsvType]);
        foreach ($arrCsv as $value) {
            if ($value instanceof Csv) {
                $entityManager->remove($value);
                $entityManager->flush();
            }
        }

        $Config->setCsvTypeId(null);
        $entityManager->persist($Config);
        $entityManager->flush();

//        $entityManager->remove($CsvType);
//        $entityManager->flush();
    }
}
