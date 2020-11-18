<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201116190730 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media ADD mime_type_id INT NOT NULL, DROP mime_type');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C7AFA98 FOREIGN KEY (mime_type_id) REFERENCES type_mime (id)');
        $this->addSql('CREATE INDEX IDX_6A2CA10C7AFA98 ON media (mime_type_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C7AFA98');
        $this->addSql('DROP INDEX IDX_6A2CA10C7AFA98 ON media');
        $this->addSql('ALTER TABLE media ADD mime_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP mime_type_id');
    }
}
