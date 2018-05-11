<?php

namespace Plugin\ProductReview;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Application;
use Eccube\Entity\Layout;
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
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{
    private $originalDir = __DIR__ . '/Resource/template/default/';

    private $template1 = 'index.twig';
//    private $template2 = 'coupon_shopping_item_confirm.twig';
//    private $template3 = 'mypage_history_coupon.twig';

    private $urls = [
        'plugin_products_detail_review' => 'レビューを書く1',
        'plugin_products_detail_review_complete' => 'レビューを書く2',
        'plugin_products_detail_review_error' => 'レビューを書く3'
    ];

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

        $this->copyBlock($container);
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
        $this->removeBlock($container);
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
     * Copy block template.
     *
     * @param ContainerInterface $container
     */
    private function copyBlock(ContainerInterface $container)
    {
        $templateDir = $container->getParameter('eccube_theme_front_dir');
        // ファイルコピー
        $file = new Filesystem();
        // ブロックファイルをコピー
        $file->copy($this->originalDir . $this->template1, $templateDir . '/ProductReview/' . $this->template1);
    }

    /**
     * Remove block template.
     *
     * @param ContainerInterface $container
     */
    private function removeBlock(ContainerInterface $container)
    {
        $templateDir = $container->getParameter('eccube_theme_front_dir');
        $file = new Filesystem();
        $file->remove($templateDir . '/ProductReview/' . $this->template1);
    }
}