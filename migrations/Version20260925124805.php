<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260925124805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assigment (id INT AUTO_INCREMENT NOT NULL, date_start DATETIME NOT NULL, date_end DATETIME NOT NULL, date_create DATETIME NOT NULL, date_update DATETIME NOT NULL, user_id INT DEFAULT NULL, restaurant_id INT DEFAULT NULL, fonction_id INT DEFAULT NULL, INDEX IDX_7DE1FD5AA76ED395 (user_id), INDEX IDX_7DE1FD5AB1E7706E (restaurant_id), INDEX IDX_7DE1FD5A57889920 (fonction_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE fonction (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, date_create DATETIME NOT NULL, date_update DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE restaurant (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, zip_code INT NOT NULL, city VARCHAR(255) NOT NULL, date_create DATETIME NOT NULL, date_update DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, date_recruitment DATETIME NOT NULL, date_create DATETIME NOT NULL, date_update DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE assigment ADD CONSTRAINT FK_7DE1FD5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE assigment ADD CONSTRAINT FK_7DE1FD5AB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE assigment ADD CONSTRAINT FK_7DE1FD5A57889920 FOREIGN KEY (fonction_id) REFERENCES fonction (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE assigment DROP FOREIGN KEY FK_7DE1FD5AA76ED395');
        $this->addSql('ALTER TABLE assigment DROP FOREIGN KEY FK_7DE1FD5AB1E7706E');
        $this->addSql('ALTER TABLE assigment DROP FOREIGN KEY FK_7DE1FD5A57889920');
        $this->addSql('DROP TABLE assigment');
        $this->addSql('DROP TABLE fonction');
        $this->addSql('DROP TABLE restaurant');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
