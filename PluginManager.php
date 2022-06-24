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

namespace Plugin\ProductReview42;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Csv;
use Eccube\Entity\Layout;
use Eccube\Entity\Master\CsvType;
use Eccube\Entity\Page;
use Eccube\Entity\PageLayout;
use Eccube\Plugin\AbstractPluginManager;
use Eccube\Repository\PageRepository;
use Plugin\ProductReview42\Entity\ProductReviewConfig;
use Plugin\ProductReview42\Entity\ProductReviewStatus;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{
    private $pages = [
        [
            'name' => 'レビューを表示',
            'url' => 'product_review_display',
            'filename' => 'ProductReview42/Resource/template/default/review',
        ],
        [
            'name' => 'レビューを投稿',
            'url' => 'product_review_index',
            'filename' => 'ProductReview42/Resource/template/default/index',
        ],
        [
            'name' => 'レビューを投稿(確認)',
            'url' => 'product_review_confirm',
            'filename' => 'ProductReview42/Resource/template/default/confirm',
        ],
        [
            'name' => 'レビューを投稿(完了)',
            'url' => 'product_review_complete',
            'filename' => 'ProductReview42/Resource/template/default/complete',
        ],
    ];

    public function enable(array $meta, ContainerInterface $container)
    {
        $em = $container->get('doctrine')->getManager();

        // プラグイン設定を追加
        $Config = $this->createConfig($em);

        // レビューステータス(公開・非公開)を追加
        $this->createStatus($em);

        // CSV出力項目設定を追加
        $CsvType = $Config->getCsvType();
        if (null === $CsvType) {
            $CsvType = $this->createCsvType($em);
            $this->createCsvData($em, $CsvType);

            $Config->setCsvType($CsvType);
            $em->flush($Config);
        }

        // ページを追加
        foreach ($this->pages as $pageInfo) {
            $Page = $em->getRepository(Page::class)->findOneBy(['url' => $pageInfo['url']]);
            if (null === $Page) {
                $this->createPage($em, $pageInfo['name'], $pageInfo['url'], $pageInfo['filename']);
            }
        }

        $this->copyTwigFiles($container);
    }

    public function disable(array $meta, ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.entity_manager');

        // ページを削除
        foreach ($this->pages as $pageInfo) {
            $this->removePage($em, $pageInfo['url']);
        }
    }

    public function uninstall(array $meta, ContainerInterface $container)
    {
        $em = $container->get('doctrine')->getManager();

        // ページを削除
        foreach ($this->pages as $pageInfo) {
            $this->removePage($em, $pageInfo['url']);
        }

        $this->removeTwigFiles($container);

        $Config = $em->find(ProductReviewConfig::class, 1);
        if ($Config) {
            $CsvType = $Config->getCsvType();

            // CSV出力項目設定を削除
            $this->removeCsvData($em, $CsvType);

            $Config->setCsvType(null);
            $em->flush($Config);

            $em->remove($CsvType);
            $em->flush($CsvType);
        }
    }

    protected function createConfig(EntityManagerInterface $em)
    {
        $Config = $em->find(ProductReviewConfig::class, 1);
        if ($Config) {
            return $Config;
        }
        $Config = new ProductReviewConfig();
        $Config->setReviewMax(5);

        $em->persist($Config);
        $em->flush($Config);

        return $Config;
    }

    protected function createStatus(EntityManagerInterface $em)
    {
        $Status = $em->find(ProductReviewStatus::class, 1);
        if ($Status) {
            return;
        }

        $Status = new ProductReviewStatus();
        $Status->setId(1);
        $Status->setName('公開');
        $Status->setSortNo(1);

        $em->persist($Status);
        $em->flush($Status);

        $Status = new ProductReviewStatus();
        $Status->setId(2);
        $Status->setName('非公開');
        $Status->setSortNo(2);

        $em->persist($Status);
        $em->flush($Status);
    }

    protected function createCsvType(EntityManagerInterface $em)
    {
        $result = $em->createQueryBuilder('ct')
            ->select('COALESCE(MAX(ct.id), 0) AS id, COALESCE(MAX(ct.sort_no), 0) AS sort_no')
            ->from(CsvType::class, 'ct')
            ->getQuery()
            ->getSingleResult();

        $result['id']++;
        $result['sort_no']++;

        $CsvType = new CsvType();
        $CsvType
            ->setId($result['id'])
            ->setName('商品レビューCSV')
            ->setSortNo($result['sort_no']);
        $em->persist($CsvType);
        $em->flush($CsvType);

        return $CsvType;
    }

    protected function createPage(EntityManagerInterface $em, $name, $url, $filename)
    {
        $Page = new Page();
        $Page->setEditType(Page::EDIT_TYPE_DEFAULT);
        $Page->setName($name);
        $Page->setUrl($url);
        $Page->setFileName($filename);

        // DB登録
        $em->persist($Page);
        $em->flush($Page);
        $Layout = $em->find(Layout::class, Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE);
        $PageLayout = new PageLayout();
        $PageLayout->setPage($Page)
            ->setPageId($Page->getId())
            ->setLayout($Layout)
            ->setLayoutId($Layout->getId())
            ->setSortNo(0);
        $em->persist($PageLayout);
        $em->flush($PageLayout);
    }

    protected function copyTwigFiles(ContainerInterface $container)
    {
        $templatePath = $container->getParameter('eccube_theme_front_dir')
            .'/ProductReview42/Resource/template/default';
        $fs = new Filesystem();
        if ($fs->exists($templatePath)) {
            return;
        }
        $fs->mkdir($templatePath);
        $fs->mirror(__DIR__.'/Resource/template/default', $templatePath);
    }

    protected function createCsvData(EntityManagerInterface $em, CsvType $CsvType)
    {
        $rank = 1;
        $Csv = new Csv();
        $Csv->setCsvType($CsvType)
            ->setEntityName('Plugin\ProductReview42\Entity\ProductReview')
            ->setFieldName('Product')
            ->setReferenceFieldName('name')
            ->setDispName('商品名')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setEntityName('Plugin\ProductReview42\Entity\ProductReview')
            ->setFieldName('Status')
            ->setReferenceFieldName('name')
            ->setDispName('公開・非公開')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setEntityName('Plugin\ProductReview42\Entity\ProductReview')
            ->setFieldName('create_date')
            ->setReferenceFieldName('create_date')
            ->setDispName('投稿日')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setEntityName('Plugin\ProductReview42\Entity\ProductReview')
            ->setFieldName('reviewer_name')
            ->setReferenceFieldName('reviewer_name')
            ->setDispName('投稿者名')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setEntityName('Plugin\ProductReview42\Entity\ProductReview')
            ->setFieldName('reviewer_url')
            ->setReferenceFieldName('reviewer_url')
            ->setDispName('投稿者URL')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setEntityName('Plugin\ProductReview42\Entity\ProductReview')
            ->setFieldName('Sex')
            ->setReferenceFieldName('name')
            ->setDispName('性別')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setEntityName('Plugin\ProductReview42\Entity\ProductReview')
            ->setFieldName('recommend_level')
            ->setReferenceFieldName('recommend_level')
            ->setDispName('おすすめレベル')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setEntityName('Plugin\ProductReview42\Entity\ProductReview')
            ->setFieldName('title')
            ->setReferenceFieldName('title')
            ->setDispName('タイトル')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setEntityName('Plugin\ProductReview42\Entity\ProductReview')
            ->setFieldName('comment')
            ->setReferenceFieldName('comment')
            ->setDispName('コメント')
            ->setSortNo($rank);
        $em->persist($Csv);
        $em->flush();

        return $CsvType;
    }

    protected function removePage(EntityManagerInterface $em, $url)
    {
        $Page = $em->getRepository(Page::class)->findOneBy(['url' => $url]);

        if (!$Page) {
            return;
        }
        foreach ($Page->getPageLayouts() as $PageLayout) {
            $em->remove($PageLayout);
            $em->flush($PageLayout);
        }

        $em->remove($Page);
        $em->flush($Page);
    }

    protected function removeTwigFiles(ContainerInterface $container)
    {
        $templatePath = $container->getParameter('eccube_theme_front_dir')
            .'/ProductReview42';
        $fs = new Filesystem();
        $fs->remove($templatePath);
    }

    protected function removeCsvData(EntityManagerInterface $em, CsvType $CsvType)
    {
        $CsvData = $em->getRepository(Csv::class)->findBy(['CsvType' => $CsvType]);
        foreach ($CsvData as $Csv) {
            $em->remove($Csv);
            $em->flush($Csv);
        }
    }
}
