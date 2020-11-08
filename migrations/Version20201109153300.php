<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201109153300 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE groupe ADD description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE invitation ADD non_lus INT NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE user ADD dernier_groupe_id INT DEFAULT NULL, CHANGE statut_id statut_id INT NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649280CBC02 FOREIGN KEY (dernier_groupe_id) REFERENCES groupe (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649280CBC02 ON user (dernier_groupe_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE groupe DROP description');
        $this->addSql('ALTER TABLE invitation DROP non_lus');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649280CBC02');
        $this->addSql('DROP INDEX IDX_8D93D649280CBC02 ON user');
        $this->addSql('ALTER TABLE user DROP dernier_groupe_id, CHANGE statut_id statut_id INT DEFAULT 2 NOT NULL');
    }
}
