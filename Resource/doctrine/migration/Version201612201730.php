<?php
/**
 * This file is part of the ProductReview plugin
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
use Eccube\Common\Constant;
use Eccube\Entity\Csv;
use Eccube\Entity\Master\CsvType;
use Eccube\Entity\Member;
use Eccube\Entity\PageLayout;
use Eccube\Entity\Master\DeviceType;
use Eccube\Repository\Master\CsvTypeRepository;
use Eccube\Repository\PageLayoutRepository;
use Plugin\ProductReview\Entity\ProductReviewConfig;
use Plugin\ProductReview\Util\Version;

/**
 * Auto-generated migration: Please modify to your needs!
 */
class Version201612201730 extends AbstractMigration
{
    /**
     * @var string table name
     */
    const TABLE = 'plg_product_review_config';

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Plugin\ProductReview\Entity\ProductReviewConfig',
    );

    /**
     * @var array sequence
     */
    protected $sequence = array(
        'plg_product_review_config_id_seq',
    );

    /**
     * Create.
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (Version::isSupportMethod()) {
            $this->createPlgProductReviewConfig($schema);
        } else {
            $this->createPlgProductReviewConfigForOldVersion($schema);
        }

        $this->createData();
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
    }

    /**
     * Create product review table from doctrine yaml file.
     * For new version.
     *
     * @param Schema $schema
     *
     * @return bool
     */
    protected function createPlgProductReviewConfig(Schema $schema)
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
    protected function createPlgProductReviewConfigForOldVersion(Schema $schema)
    {
        $table = $schema->createTable(self::TABLE);
        $table->addColumn('id', 'integer', array(
            'autoincrement' => true,
            'unsigned' => true,
            'notnull' => true,
        ));

        $table->addColumn('csv_id', 'smallint', array(
            'notnull' => false,
            'unsigned' => true,
        ));

        $table->addColumn('review_max', 'smallint', array(
            'notnull' => false,
            'unsigned' => true,
        ));

        $table->addColumn('create_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('update_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->setPrimaryKey(array('id'));
    }

    /**
     * Init data
     */
    protected function createData()
    {
        if (Version::isSupportMethod()) {
            $app = Application::getInstance();
        } else {
            $app = new Application();
            $app->initialize();
            $app->boot();
        }

        $CsvType = $this->createCsvData($app);

        $Config = new ProductReviewConfig();
        $Config->setCsvType($CsvType)
            ->setReviewMax(5);
        $app['orm.em']->persist($Config);
        $app['orm.em']->flush($Config);
    }

    /**
     * Create csv data.
     *
     * @param Application $app
     * @return CsvType
     */
    protected function createCsvData(Application $app)
    {
        /** @var $em EntityManager */
        $em = $app['orm.em'];

        // Create csv type master
        /** @var $repos CsvTypeRepository */
        $repos = $em->getRepository('Eccube\Entity\Master\CsvType');
        $csvTypeId = $repos->createQueryBuilder('ct')
            ->select('Max(ct.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $CsvType = new CsvType();
        $CsvType->setName('製品レビューCSV')
            ->setId($csvTypeId+1)
            ->setRank(999);
        $em->persist($CsvType);
        $em->flush($CsvType);

        // Create csv data
        $Member = $app['eccube.repository.member']->find(2);
        $rank = 1;
        $Csv = new Csv();
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('Product')
            ->setReferenceFieldName('name')
            ->setDispName('商品名')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        $Csv = new Csv();
        $rank++;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('Status')
            ->setReferenceFieldName('name')
            ->setDispName('公開ステータス(名称)')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        $Csv = new Csv();
        $rank++;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('create_date')
            ->setReferenceFieldName('create_date')
            ->setDispName('登録日')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        $Csv = new Csv();
        $rank++;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('reviewer_name')
            ->setReferenceFieldName('reviewer_name')
            ->setDispName('レビュー担当者名')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        $Csv = new Csv();
        $rank++;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('reviewer_url')
            ->setReferenceFieldName('reviewer_url')
            ->setDispName('レビューアーのURL')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        $Csv = new Csv();
        $rank++;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('Sex')
            ->setReferenceFieldName('name')
            ->setDispName('性別(名称)')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        $Csv = new Csv();
        $rank++;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('recommend_level')
            ->setReferenceFieldName('recommend_level')
            ->setDispName('階層')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        $Csv = new Csv();
        $rank++;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('title')
            ->setReferenceFieldName('title')
            ->setDispName('タイトル')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        $Csv = new Csv();
        $rank++;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('comment')
            ->setReferenceFieldName('comment')
            ->setDispName('コメント')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        return $CsvType;
    }

    /**
     * Delete data
     */
    protected function deleteData()
    {
        if (Version::isSupportMethod()) {
            $app = Application::getInstance();
        } else {
            $app = new Application();
            $app->initialize();
            $app->boot();
        }
        /** @var $em EntityManager */
        $em = $app['orm.em'];

        /** @var $repos PageLayoutRepository */
        $repos = $em->getRepository('Plugin\ProductReview\Entity\ProductReviewConfig');
        /* @var $Config ProductReviewConfig */
        $Config = $repos->find(1);
        $CsvType = $Config->getCsvType();

        $arrCsv = $app['eccube.repository.csv']->findBy(array('CsvType' => $CsvType));
        foreach ($arrCsv as $value) {
            if ($value instanceof Csv) {
                $em->remove($value);
                $em->flush($value);
            }
        }

        $em->remove($CsvType);
        $em->flush($CsvType);
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
