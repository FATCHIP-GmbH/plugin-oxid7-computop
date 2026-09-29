<?php

namespace Fatchip\ComputopPayments\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\DatabaseProvider;
use Fatchip\ComputopPayments\Model\BaseMigration;
use Fatchip\ComputopPayments\Model\Cronjob;

class Version20262209120000 extends BaseMigration
{
    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        $this->createTableIfNotExists($schema, Cronjob::$sTableName, Cronjob::getTableCreateQuery());
    }
}
