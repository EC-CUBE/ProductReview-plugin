<?php

namespace Plugin\ProductReview;


use Doctrine\ORM\EntityManagerInterface;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Entity\Csv;
use Eccube\Entity\Layout;
use Eccube\Entity\Master\CsvType;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\Page;
use Eccube\Entity\PageLayout;
use Eccube\Plugin\AbstractPluginManager;
use Eccube\Repository\LayoutRepository;
use Eccube\Repository\Master\DeviceTypeRepository;
use Eccube\Repository\PageLayoutRepository;
use Eccube\Repository\PageRepository;
use Plugin\ProductReview\Entity\ProductReviewConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PluginManager extends AbstractPluginManager
{
    private $urls = [
        'plugin_products_detail_review' => 'レビューを書く1',
        'plugin_products_detail_review_complete' => 'レビューを書く2',
        'plugin_products_detail_review_error' => 'レビューを書く3'
    ];

    /** @var  EntityManagerInterface */
    private $entityManager;

    /**
     * PluginManager constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @param null $meta
     * @param Application|null $app
     * @param ContainerInterface $container
     */
    public function enable($meta = null, Application $app = null, ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $ProductReviewConfig = $entityManager->find(ProductReviewConfig::class, 1);
        if (null === $ProductReviewConfig) {
            $ProductReviewConfig = new ProductReviewConfig();
            $ProductReviewConfig->setId(1);
            $ProductReviewConfig->setReviewMax(10);
            $entityManager->persist($ProductReviewConfig);
            $entityManager->flush();
        }

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
            $this->removePageLayout($container);
        }
    }

    /**
     * @param null $meta
     * @param Application|null $app
     * @param ContainerInterface $container
     */
    public function update($meta = null, Application $app = null, ContainerInterface $container)
    {
        $PageLayout = $container->get(PageRepository::class)->findOneBy(array('url' => 'plugin_coupon_shopping'));
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
        $Page = $container->get(PageRepository::class)->findOneBy(array('url' => $url));
        if ($Page) {
            $Layout = $container->get(LayoutRepository::class)->find(Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE);
            $PageLayout = $container->get(PageLayoutRepository::class)->findOneBy([
                'Page' => $Page,
                'Layout' => $Layout
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
     * @param Application $app
     *
     * @return CsvType
     */
    protected function createCsvData(Application $app)
    {
        /** @var $em EntityManager */
        $em = $app['orm.em'];

        // Create csv type master
        /** @var $repos CsvTypeRepository */
        $repos = $this->entityManager->getRepository('Eccube\Entity\Master\CsvType');
        $csvTypeId = $repos->createQueryBuilder('ct')
            ->select('Max(ct.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $CsvType = new CsvType();
        $CsvType->setName('商品レビューCSV')
            ->setId($csvTypeId + 1);
        $this->entityManager->persist($CsvType);
        $this->entityManager->flush();

        // Create csv data
        $Member = $app['eccube.repository.member']->find(2);
        $rank = 1;
        $Csv = new Csv();
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('Product')
            ->setReferenceFieldName('name')
            ->setDispName('商品名');
        $this->entityManager->persist($Csv);
        $this->entityManager->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('Status')
            ->setReferenceFieldName('name')
            ->setDispName('公開・非公開');
        $this->entityManager->persist($Csv);
        $this->entityManager->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('create_date')
            ->setReferenceFieldName('create_date')
            ->setDispName('投稿日');
        $this->entityManager->persist($Csv);
        $this->entityManager->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('reviewer_name')
            ->setReferenceFieldName('reviewer_name')
            ->setDispName('投稿者名');
        $this->entityManager->persist($Csv);
        $this->entityManager->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('reviewer_url')
            ->setReferenceFieldName('reviewer_url')
            ->setDispName('投稿者URL');
        $this->entityManager->persist($Csv);
        $this->entityManager->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('Sex')
            ->setReferenceFieldName('name')
            ->setDispName('性別');
        $this->entityManager->persist($Csv);
        $this->entityManager->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('recommend_level')
            ->setReferenceFieldName('recommend_level')
            ->setDispName('おすすめレベル');
        $this->entityManager->persist($Csv);
        $this->entityManager->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('title')
            ->setReferenceFieldName('title')
            ->setDispName('タイトル');
        $this->entityManager->persist($Csv);
        $this->entityManager->flush();

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('comment')
            ->setReferenceFieldName('comment')
            ->setDispName('コメント');
        $this->entityManager->persist($Csv);
        $this->entityManager->flush();

        return $CsvType;
    }

    /**
     * Delete data.
     */
    protected function deleteData()
    {
        $app = new Application();
        $app->initialize();
        $app->boot();
        /** @var $em EntityManager */
        $em = $this->entityManager;

        $entity = $this->entities;
        $entityName = array_shift($entity);
        /** @var $repos PageLayoutRepository */
        $repos = $em->getRepository($entityName);
        /* @var $Config ProductReviewConfig */
        $Config = $repos->find(1);
        if (!$Config) {
            return;
        }
        $CsvType = $Config->getCsvType();

        $arrCsv = $app['eccube.repository.csv']->findBy(array('CsvType' => $CsvType));
        foreach ($arrCsv as $value) {
            if ($value instanceof Csv) {
                $em->remove($value);
                $em->flush();
            }
        }

        $em->remove($Config);
        $em->flush();
        $em->remove($CsvType);
        $em->flush();
    }
}