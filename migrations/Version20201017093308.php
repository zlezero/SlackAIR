<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201017093308 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE departement (id INT AUTO_INCREMENT NOT NULL, id_responsable_id INT NOT NULL, nom VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_C1765B636EA32074 (id_responsable_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groupe (id INT AUTO_INCREMENT NOT NULL, type_groupe_id_id INT NOT NULL, id_proprietaire_id INT NOT NULL, nom VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, INDEX IDX_4B98C21ADAAEA02 (type_groupe_id_id), INDEX IDX_4B98C219F9BCDC2 (id_proprietaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invitation (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, groupe_id_id INT NOT NULL, date DATETIME NOT NULL, statut TINYINT(1) NOT NULL, INDEX IDX_F11D61A29D86650F (user_id_id), INDEX IDX_F11D61A22AE95007 (groupe_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, type_mimeid_id INT NOT NULL, message_id_id INT NOT NULL, filename VARCHAR(255) NOT NULL, size DOUBLE PRECISION NOT NULL, hash VARCHAR(255) NOT NULL, INDEX IDX_6A2CA10C3B79525B (type_mimeid_id), INDEX IDX_6A2CA10C80E261BC (message_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, groupe_id_id INT NOT NULL, texte VARCHAR(255) NOT NULL, date_envoi DATETIME NOT NULL, est_efface TINYINT(1) NOT NULL, INDEX IDX_B6BD307F9D86650F (user_id_id), INDEX IDX_B6BD307F2AE95007 (groupe_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_groupe (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(25) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_mime (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, extension VARCHAR(5) NOT NULL, type_mime VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, departement_id_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, pseudo VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, age INT DEFAULT NULL, sexe VARCHAR(255) DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', api_token VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D6497BA2F5EB (api_token), INDEX IDX_8D93D649EAE6F2D2 (departement_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE departement ADD CONSTRAINT FK_C1765B636EA32074 FOREIGN KEY (id_responsable_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE groupe ADD CONSTRAINT FK_4B98C21ADAAEA02 FOREIGN KEY (type_groupe_id_id) REFERENCES type_groupe (id)');
        $this->addSql('ALTER TABLE groupe ADD CONSTRAINT FK_4B98C219F9BCDC2 FOREIGN KEY (id_proprietaire_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A29D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A22AE95007 FOREIGN KEY (groupe_id_id) REFERENCES groupe (id)');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C3B79525B FOREIGN KEY (type_mimeid_id) REFERENCES type_mime (id)');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C80E261BC FOREIGN KEY (message_id_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F2AE95007 FOREIGN KEY (groupe_id_id) REFERENCES groupe (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649EAE6F2D2 FOREIGN KEY (departement_id_id) REFERENCES departement (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649EAE6F2D2');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A22AE95007');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F2AE95007');
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C80E261BC');
        $this->addSql('ALTER TABLE groupe DROP FOREIGN KEY FK_4B98C21ADAAEA02');
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C3B79525B');
        $this->addSql('ALTER TABLE departement DROP FOREIGN KEY FK_C1765B636EA32074');
        $this->addSql('ALTER TABLE groupe DROP FOREIGN KEY FK_4B98C219F9BCDC2');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A29D86650F');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9D86650F');
        $this->addSql('DROP TABLE departement');
        $this->addSql('DROP TABLE groupe');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE type_groupe');
        $this->addSql('DROP TABLE type_mime');
        $this->addSql('DROP TABLE user');
    }
}
