<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Eccube\Entity\PageLayout;
use Eccube\Entity\Master\DeviceType;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version201510241654 extends AbstractMigration
{

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->createPlgProductReview($schema);
        $this->createPageLayout();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('plg_product_review');
        $this->deletePageLayout();
    }

    protected function createPlgProductReview(Schema $schema)
    {
        $table = $schema->createTable("plg_product_review");
        $table->addColumn('product_review_id', 'integer', array(
            'autoincrement' => true,
        ));

        $table->addColumn('reviewer_name', 'text', array(
            'notnull' => true,
        ));

        $table->addColumn('reviewer_url', 'text', array(
            'notnull' => false,
        ));

        $table->addColumn('recommend_level', 'integer', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('sex', 'integer', array(
            'notnull' => false,
            'unsigned' => false,
        ));

        $table->addColumn('title', 'text', array(
            'notnull' => true,
        ));

        $table->addColumn('comment', 'text', array(
            'notnull' => true,
        ));

        $table->addColumn('status', 'integer', array(
            'notnull' => true,
            'unsigned' => false,
            'default' => 2,
        ));

        $table->addColumn('del_flg', 'smallint', array(
            'notnull' => true,
            'unsigned' => false,
            'default' => 0,
        ));

        $table->addColumn('product_id', 'integer', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('customer_id', 'integer', array(
            'notnull' => false,
            'unsigned' => false,
        ));

        $table->addColumn('create_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('update_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->setPrimaryKey(array('product_review_id'));

        $targetTable = $schema->getTable('dtb_product');
        $table->addForeignKeyConstraint(
            $targetTable,
            array('product_id'),
            array('product_id')
        );
        $targetTable = $schema->getTable('dtb_customer');
        $table->addForeignKeyConstraint(
            $targetTable,
            array('customer_id'),
            array('customer_id')
        );
    }

    protected function createPageLayout() {

        if (version_compare(\Eccube\Common\Constant::VERSION, '3.0.8', '<=')) {
            $app = new \Eccube\Application();
            $app->initialize();
            $app->boot();
        } else {
            $app = \Eccube\Application::getInstance();
        }
        $em = $app['orm.em'];

        $DeviceType = $app['eccube.repository.master.device_type']
            ->find(DeviceType::DEVICE_TYPE_PC);

        $PageLayout = new PageLayout();
        $PageLayout->setDeviceType($DeviceType);
        $PageLayout->setName( 'お客様の声書き込み');
        $PageLayout->setUrl('products_detail_review');
        $PageLayout->setMetaRobots('noindex');
        $PageLayout->setEditFlg(PageLayout::EDIT_FLG_DEFAULT);
        $em->persist($PageLayout);

        $PageLayout = new PageLayout();
        $PageLayout->setDeviceType($DeviceType);
        $PageLayout->setName( 'お客様の声書き込み完了');
        $PageLayout->setUrl('products_detail_review_complete');
        $PageLayout->setMetaRobots('noindex');
        $PageLayout->setEditFlg(PageLayout::EDIT_FLG_DEFAULT);
        $em->persist($PageLayout);

        $PageLayout = new PageLayout();
        $PageLayout->setDeviceType($DeviceType);
        $PageLayout->setName( 'お客様の声書き込みエラー');
        $PageLayout->setUrl('products_detail_review_error');
        $PageLayout->setMetaRobots('noindex');
        $PageLayout->setEditFlg(PageLayout::EDIT_FLG_DEFAULT);
        $em->persist($PageLayout);
        $em->flush();
    }

    protected function deletePageLayout() {
        if (version_compare(\Eccube\Common\Constant::VERSION, '3.0.8', '<=')) {
            $app = new \Eccube\Application();
            $app->initialize();
            $app->boot();
        } else {
            $app = \Eccube\Application::getInstance();
        }

        $em = $app['orm.em'];

        /** @var $repos \Eccube\Repository\PageLayoutRepository */
        $repos = $em->getRepository('Eccube\Entity\PageLayout');

        $DeviceType = $app['eccube.repository.master.device_type']
            ->find(DeviceType::DEVICE_TYPE_PC);

        $PageLayout = $this->findPageLayout($repos, $DeviceType, 'products_detail_review');
        $em->remove($PageLayout);
        $PageLayout = $this->findPageLayout($repos, $DeviceType, 'products_detail_review_complete');
        $em->remove($PageLayout);
        $PageLayout = $this->findPageLayout($repos, $DeviceType, 'products_detail_review_error');
        $em->remove($PageLayout);

        $em->flush();
    }

    /**
     * @param $DeviceType
     * @return mixed
     */
    protected function findPageLayout($repos, $DeviceType, $url)
    {
        $PageLayout = $repos->createQueryBuilder('p')
            ->where('p.DeviceType = :DeviceType AND p.url = :url')
            ->getQuery()
            ->setParameters(array(
                'DeviceType' => $DeviceType,
                'url' => $url,
            ))
            ->getSingleResult();
        return $PageLayout;
    }

}
