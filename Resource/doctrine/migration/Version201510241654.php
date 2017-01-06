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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Eccube\Application;
use Eccube\Entity\PageLayout;
use Eccube\Entity\Master\DeviceType;
use Eccube\Repository\PageLayoutRepository;
use Plugin\ProductReview\Util\Version;

/**
 * Auto-generated migration: Please modify to your needs!
 */
class Version201510241654 extends AbstractMigration
{
    /**
     * @var string table name
     */
    const TABLE = 'plg_product_review';

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Plugin\ProductReview\Entity\ProductReview',
    );

    /**
     * @var array sequence
     */
    protected $sequence = array(
        'plg_product_review_product_review_id_seq',
    );

    /**
     * Create.
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (Version::isSupportMethod()) {
            $this->createPlgProductReview($schema);
        } else {
            $this->createPlgProductReviewForOldVersion($schema);
        }
        $this->createPageLayout();
    }

    /**
     * Down method.
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        if (Version::isSupportMethod()) {
            $app = Application::getInstance();
            $meta = $this->getMetadata($app['orm.em']);
            $tool = new SchemaTool($app['orm.em']);
            $schemaFromMetadata = $tool->getSchemaFromMetadata($meta);
            // テーブル削除
            foreach ($schemaFromMetadata->getTables() as $table) {
                if ($schema->hasTable($table->getName())) {
                    $schema->dropTable($table->getName());
                }
            }

            // シーケンス削除
            foreach ($schemaFromMetadata->getSequences() as $sequence) {
                if ($schema->hasSequence($sequence->getName())) {
                    $schema->dropSequence($sequence->getName());
                }
            }
        } else {
            if ($schema->hasTable(self::TABLE)) {
                $schema->dropTable(self::TABLE);
            }
        }

        // Drop sequence on pgsql
        if ($this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            foreach ($this->sequence as $sequence) {
                if ($schema->hasSequence($sequence)) {
                    $schema->dropSequence($sequence);
                }
            }
        }

        $this->deletePageLayout();
    }

    /**
     * Create product review table from doctrine yaml file.
     * For new version.
     *
     * @param Schema $schema
     *
     * @return bool
     */
    protected function createPlgProductReview(Schema $schema)
    {
        if ($schema->hasTable(self::TABLE)) {
            return true;
        }

        $app = Application::getInstance();
        $em = $app['orm.em'];
        $entityName = $this->entities;
        $firstEntityName = array_shift($entityName);
        $classes = array(
            $em->getClassMetadata($firstEntityName),
        );
        $tool = new SchemaTool($em);
        $tool->createSchema($classes);

        return true;
    }

    /**
     * Create product review table from doctrine schema.
     * For old version.
     *
     * @param Schema $schema
     */
    protected function createPlgProductReviewForOldVersion(Schema $schema)
    {
        $table = $schema->createTable(self::TABLE);
        $table->addColumn('product_review_id', 'integer', array(
            'autoincrement' => true,
            'unsigned' => true,
            'notnull' => true,
        ));

        $table->addColumn('reviewer_name', 'string', array(
            'notnull' => true,
            'length' => 50,
        ));

        $table->addColumn('reviewer_url', 'text', array(
            'notnull' => false,
        ));

        $table->addColumn('recommend_level', 'smallint', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('sex', 'smallint', array(
            'notnull' => false,
            'unsigned' => false,
        ));

        $table->addColumn('title', 'string', array(
            'notnull' => true,
            'length' => 50,
        ));

        $table->addColumn('comment', 'text', array(
            'notnull' => true,
        ));

        $table->addColumn('status', 'smallint', array(
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

    /**
     * Create page layout.
     */
    protected function createPageLayout()
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

        $PageLayout = new PageLayout();
        $PageLayout->setDeviceType($DeviceType);
        $PageLayout->setName('レビューを書く');
        $PageLayout->setUrl('products_detail_review');
        $PageLayout->setMetaRobots('noindex');
        $PageLayout->setEditFlg(PageLayout::EDIT_FLG_DEFAULT);
        $em->persist($PageLayout);
        $em->flush($PageLayout);

        $PageLayoutComplete = new PageLayout();
        $PageLayoutComplete->setDeviceType($DeviceType);
        $PageLayoutComplete->setName('レビューを書く - 完了');
        $PageLayoutComplete->setUrl('products_detail_review_complete');
        $PageLayoutComplete->setMetaRobots('noindex');
        $PageLayoutComplete->setEditFlg(PageLayout::EDIT_FLG_DEFAULT);
        $em->persist($PageLayoutComplete);
        $em->flush($PageLayoutComplete);

        $PageLayoutError = new PageLayout();
        $PageLayoutError->setDeviceType($DeviceType);
        $PageLayoutError->setName('レビューを書く - エラー');
        $PageLayoutError->setUrl('products_detail_review_error');
        $PageLayoutError->setMetaRobots('noindex');
        $PageLayoutError->setEditFlg(PageLayout::EDIT_FLG_DEFAULT);
        $em->persist($PageLayoutError);
        $em->flush($PageLayoutError);
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

        $PageLayout = $this->findPageLayout($repos, $DeviceType, 'products_detail_review');
        if ($PageLayout instanceof PageLayout) {
            $em->remove($PageLayout);
            $em->flush($PageLayout);
        }

        $PageLayout = $this->findPageLayout($repos, $DeviceType, 'products_detail_review_complete');
        if ($PageLayout instanceof PageLayout) {
            $em->remove($PageLayout);
            $em->flush($PageLayout);
        }

        $PageLayout = $this->findPageLayout($repos, $DeviceType, 'products_detail_review_error');
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

    /**
     * Get metadata.
     *
     * @param EntityManager $em
     *
     * @return array
     */
    protected function getMetadata(EntityManager $em)
    {
        $meta = array();
        foreach ($this->entities as $entity) {
            $meta[] = $em->getMetadataFactory()->getMetadataFor($entity);
        }

        return $meta;
    }
}
