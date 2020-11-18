<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201116184018 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mime_labels CHANGE label_name label_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE type_mime ADD label_id INT NOT NULL, DROP label, DROP extension');
        $this->addSql('ALTER TABLE type_mime ADD CONSTRAINT FK_958A455833B92F39 FOREIGN KEY (label_id) REFERENCES mime_labels (id)');
        $this->addSql('CREATE INDEX IDX_958A455833B92F39 ON type_mime (label_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mime_labels CHANGE label_name label_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE type_mime DROP FOREIGN KEY FK_958A455833B92F39');
        $this->addSql('DROP INDEX IDX_958A455833B92F39 ON type_mime');
        $this->addSql('ALTER TABLE type_mime ADD label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD extension VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP label_id');
    }
}
