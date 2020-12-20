<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201220160237 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE departement DROP FOREIGN KEY FK_C1765B636EA32074');
        $this->addSql('DROP INDEX UNIQ_C1765B636EA32074 ON departement');
        $this->addSql('ALTER TABLE departement DROP id_responsable_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE departement ADD id_responsable_id INT NOT NULL');
        $this->addSql('ALTER TABLE departement ADD CONSTRAINT FK_C1765B636EA32074 FOREIGN KEY (id_responsable_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C1765B636EA32074 ON departement (id_responsable_id)');
    }
}
