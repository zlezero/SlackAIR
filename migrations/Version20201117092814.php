<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201117092814 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE type_notification (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notification ADD type_notification_id INT NOT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA182E09F0 FOREIGN KEY (type_notification_id) REFERENCES type_notification (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA182E09F0 ON notification (type_notification_id)');
        $this->addSql("INSERT INTO `type_notification` (`id`, `label`) VALUES
        (1, 'Invitation de discussion'),
        (2, 'Nouveau(x) message(s) non lu(s)')");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA182E09F0');
        $this->addSql('DROP TABLE type_notification');
        $this->addSql('DROP INDEX IDX_BF5476CA182E09F0 ON notification');
        $this->addSql('ALTER TABLE notification DROP type_notification_id');
    }
}
