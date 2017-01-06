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
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Entity\Csv;
use Eccube\Entity\Master\CsvType;
use Eccube\Repository\Master\CsvTypeRepository;
use Eccube\Repository\PageLayoutRepository;
use Plugin\ProductReview\Entity\ProductReviewConfig;
use Plugin\ProductReview\Util\Version;

/**
 * Auto-generated migration: Please modify to your needs!
 */
class Version201612201830 extends AbstractMigration
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
        $this->createData($schema);
    }

    /**
     * Down method.
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->deleteData();
    }

    /**
     * create data.
     *
     * @param Schema $schema
     *
     * @return bool
     */
    protected function createData(Schema $schema)
    {
        if (Version::isSupportMethod()) {
            $app = Application::getInstance();
        } else {
            $app = new Application();
            $app->initialize();
            $app->boot();
        }

        $CsvType = $this->createCsvData($app);

        $seq = $this->sequence;
        $firstSequence = array_shift($seq);
        if (!$schema->hasTable(self::TABLE)) {
            return true;
        }

        // Insert data
        $csvId = $CsvType->getId();
        $table = self::TABLE;

        $sql = "INSERT INTO {$table} (id, review_max, create_date, update_date, csv_id) VALUES (1, 5, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, {$csvId});";
        $this->addSql($sql);

        if ($this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            if ($schema->hasSequence($firstSequence)) {
                // increase seq
                $this->addSql("SELECT setval('{$firstSequence}', 2);");
            }
        }

        return true;
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
        $repos = $em->getRepository('Eccube\Entity\Master\CsvType');
        $csvTypeId = $repos->createQueryBuilder('ct')
            ->select('Max(ct.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $CsvType = new CsvType();
        $CsvType->setName('商品レビューCSV')
            ->setId($csvTypeId + 1)
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
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('Status')
            ->setReferenceFieldName('name')
            ->setDispName('公開・非公開')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('create_date')
            ->setReferenceFieldName('create_date')
            ->setDispName('投稿日')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('reviewer_name')
            ->setReferenceFieldName('reviewer_name')
            ->setDispName('投稿者名')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('reviewer_url')
            ->setReferenceFieldName('reviewer_url')
            ->setDispName('投稿者URL')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('Sex')
            ->setReferenceFieldName('name')
            ->setDispName('性別')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        $Csv = new Csv();
        ++$rank;
        $Csv->setCsvType($CsvType)
            ->setCreator($Member)
            ->setEntityName('Plugin\ProductReview\Entity\ProductReview')
            ->setFieldName('recommend_level')
            ->setReferenceFieldName('recommend_level')
            ->setDispName('おすすめレベル')
            ->setRank($rank)
            ->setEnableFlg(Constant::ENABLED);
        $em->persist($Csv);
        $em->flush($Csv);

        $Csv = new Csv();
        ++$rank;
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
        ++$rank;
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
     * Delete data.
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
                $em->flush($value);
            }
        }

        $em->remove($Config);
        $em->flush($Config);
        $em->remove($CsvType);
        $em->flush($CsvType);
    }
}
