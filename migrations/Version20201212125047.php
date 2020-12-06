<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201212125047 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE groupe ADD is_deleted TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE message CHANGE est_epingle est_epingle TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE groupe DROP is_deleted');
        $this->addSql('ALTER TABLE message CHANGE est_epingle est_epingle TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
