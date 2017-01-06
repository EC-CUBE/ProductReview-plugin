<?php
/**
 * This file is part of the ProductReview plugin.
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityRepository;
use Eccube\Application;
use Eccube\Entity\PageLayout;
use Eccube\Entity\Master\DeviceType;
use Eccube\Repository\PageLayoutRepository;
use Plugin\ProductReview\Util\Version;

/**
 * Auto-generated migration: Please modify to your needs!
 */
class Version201612201630 extends AbstractMigration
{
    /**
     * Create.
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->updateLayout();
    }

    /**
     * Down method.
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->deletePageLayout();
    }

    /**
     * Create page layout.
     */
    protected function updateLayout()
    {
        if (Version::isSupportMethod()) {
            $app = Application::getInstance();
        } else {
            $app = new Application();
            $app->initialize();
            $app->boot();
        }
        $em = $app['orm.em'];

        $DeviceType = $app['eccube.repository.master.device_type']
            ->find(DeviceType::DEVICE_TYPE_PC);

        /** @var $repos PageLayoutRepository */
        $repos = $em->getRepository('Eccube\Entity\PageLayout');
        $PageLayout = $this->findPageLayout($repos, $DeviceType, 'products_detail_review');
        if ($PageLayout) {
            $PageLayout->setUrl('plugin_products_detail_review');
            $em->persist($PageLayout);
            $em->flush($PageLayout);
        }

        $PageLayoutComplete = $this->findPageLayout($repos, $DeviceType, 'products_detail_review_complete');
        if ($PageLayoutComplete) {
            $PageLayoutComplete->setUrl('plugin_products_detail_review_complete');
            $em->persist($PageLayoutComplete);
            $em->flush($PageLayoutComplete);
        }

        $PageLayoutError = $this->findPageLayout($repos, $DeviceType, 'products_detail_review_error');
        if ($PageLayoutError) {
            $PageLayoutError->setUrl('plugin_products_detail_review_error');
            $em->persist($PageLayoutError);
            $em->flush($PageLayoutError);
        }
    }

    /**
     * Delete page layout.
     */
    protected function deletePageLayout()
    {
        if (Version::isSupportMethod()) {
            $app = Application::getInstance();
        } else {
            $app = new Application();
            $app->initialize();
            $app->boot();
        }
        $em = $app['orm.em'];

        /** @var $repos PageLayoutRepository */
        $repos = $em->getRepository('Eccube\Entity\PageLayout');

        $DeviceType = $app['eccube.repository.master.device_type']
            ->find(DeviceType::DEVICE_TYPE_PC);

        $PageLayout = $this->findPageLayout($repos, $DeviceType, 'plugin_products_detail_review');
        if ($PageLayout instanceof PageLayout) {
            $em->remove($PageLayout);
            $em->flush($PageLayout);
        }

        $PageLayout = $this->findPageLayout($repos, $DeviceType, 'plugin_products_detail_review_complete');
        if ($PageLayout instanceof PageLayout) {
            $em->remove($PageLayout);
            $em->flush($PageLayout);
        }

        $PageLayout = $this->findPageLayout($repos, $DeviceType, 'plugin_products_detail_review_error');
        if ($PageLayout instanceof PageLayout) {
            $em->remove($PageLayout);
            $em->flush($PageLayout);
        }
    }

    /**
     * Find page layout.
     *
     * @param EntityRepository $repos
     * @param DeviceType       $DeviceType
     * @param string           $url
     *
     * @return mixed
     */
    protected function findPageLayout($repos, $DeviceType, $url)
    {
        try {
            $PageLayout = $repos->createQueryBuilder('p')
                ->where('p.DeviceType = :DeviceType AND p.url = :url')
                ->getQuery()
                ->setParameters(array(
                    'DeviceType' => $DeviceType,
                    'url' => $url,
                ))
                ->getSingleResult();

            return $PageLayout;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
