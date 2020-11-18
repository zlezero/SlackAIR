<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201115211636 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C80E261BC');
        $this->addSql('DROP INDEX IDX_6A2CA10C80E261BC ON media');
        $this->addSql('ALTER TABLE media DROP message_id_id, DROP hash');
        $this->addSql('ALTER TABLE message ADD media_id INT DEFAULT NULL, DROP media_filename');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6BD307FEA9FDD75 ON message (media_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media ADD message_id_id INT NOT NULL, ADD hash VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C80E261BC FOREIGN KEY (message_id_id) REFERENCES message (id)');
        $this->addSql('CREATE INDEX IDX_6A2CA10C80E261BC ON media (message_id_id)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FEA9FDD75');
        $this->addSql('DROP INDEX UNIQ_B6BD307FEA9FDD75 ON message');
        $this->addSql('ALTER TABLE message ADD media_filename VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP media_id');
    }
}
