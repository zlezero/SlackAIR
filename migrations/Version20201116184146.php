<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201116184146 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO `type_mime` (`id`, `type_mime`, `label_id`) VALUES
        (1, 'text/rtf' , 1),
        (2, 'text/html' , 1),
        (3, 'application/msword' , 1),
        (4, 'application/vnd.ms-excel' , 1),
        (5, 'application/vnd.ms-powerpoint' , 1),
        (6, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 1),
        (7, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 1),
        (8, 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 1),
        (9, 'application/json', 1),
        (10, 'application/pdf', 2),
        (11, 'application/gzip', 3),
        (12, 'application/x-7z-compressed', 3),
        (13, 'application/x-rar-compressed', 3),
        (14, 'image/png', 4),
        (15, 'image/jpeg', 4),
        (16, 'image/gif', 4),
        (17, 'image/svg+xml', 4),
        (18, 'audio/mpeg', 5),
        (19, 'audio/3gpp', 5),
        (20, 'audio/3gpp2', 5),
        (21, 'audio/webm', 5),
        (22, 'audio/ogg', 5),
        (23, 'video/webm', 6),
        (24, 'video/ogg', 6),
        (25, 'video/mpeg', 6),
        (26, 'video/3gpp', 6),
        (27, 'video/3gpp2', 6),
        (28, 'video/mp4', 6),
        (29, 'application/x-zip-compressed', 3),
        (30, 'text/text', 1)");
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
