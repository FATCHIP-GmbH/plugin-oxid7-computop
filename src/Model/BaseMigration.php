<?php

namespace Fatchip\ComputopPayments\Model;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use OxidEsales\Eshop\Core\DatabaseProvider;

abstract class BaseMigration extends AbstractMigration
{
    /**
     * @param  Schema $oSchema
     * @param  string $sTableName
     * @param  string $sCreateSql
     * @return bool
     */
    protected function createTableIfNotExists(Schema $oSchema, $sTableName, $sCreateSql): bool
    {
        if (!$oSchema->hasTable($sTableName)) {
            $this->addSql($sCreateSql);
            return true;
        }
        return false;
    }

    /**
     * @param  Schema $oSchema
     * @param  string $sTableName
     * @param  string $sColumnName
     * @param  string $sTypeName
     * @param  array  $aOptions
     * @param  string $aFollowupQueries
     * @return bool
     */
    protected function addColumnIfNotExists(Schema $oSchema, $sTableName, $sColumnName, $sTypeName, $aOptions, $aFollowupQueries = []): bool
    {
        $table = $oSchema->getTable($sTableName);
        if (!$table->hasColumn($sColumnName)) {
            $table->addColumn($sColumnName, $sTypeName, $aOptions);

            $this->handleFollowupQueries($aFollowupQueries);
            return true;
        }
        return false;
    }

    /**
     * @param  Schema $oSchema
     * @param  string $sTableName
     * @param  string $sColumnName
     * @param  string $sAlterSql
     * @param  array  $aFollowupQueries
     * @return bool
     */
    protected function addColumnBySqlIfNotExists(Schema $oSchema, $sTableName, $sColumnName, $sAlterSql, $aFollowupQueries = []): bool
    {
        $table = $oSchema->getTable($sTableName);
        if (!$table->hasColumn($sColumnName)) {
            $this->addSql($sAlterSql);

            $this->handleFollowupQueries($aFollowupQueries);
            return true;
        }
        return false;
    }

    protected function handleFollowupQueries($aFollowupQueries)
    {
        foreach ($aFollowupQueries as $sQuery) {
            $this->addSql($sQuery);
        }
    }
}
