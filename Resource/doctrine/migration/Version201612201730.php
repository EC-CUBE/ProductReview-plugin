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
use Doctrine\ORM\Tools\SchemaTool;
use Eccube\Application;
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
