<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201105133856 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD statut_id INT NOT NULL DEFAULT 2, DROP statut');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649F6203804 FOREIGN KEY (statut_id) REFERENCES statut (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649F6203804 ON user (statut_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649F6203804');
        $this->addSql('DROP INDEX IDX_8D93D649F6203804 ON user');
        $this->addSql('ALTER TABLE user ADD statut VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'2\' NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP statut_id');
    }
}
