<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migration\BaseMigration;
use Doctrine\DBAL\Schema\Schema;
/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261013142948 extends BaseMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assignment (id INT AUTO_INCREMENT NOT NULL, date_start DATE NOT NULL, date_end DATE NOT NULL, date_create DATETIME NOT NULL, date_update DATETIME NOT NULL, user_id INT DEFAULT NULL, restaurant_id INT DEFAULT NULL, fonction_id INT DEFAULT NULL, INDEX IDX_30C544BAA76ED395 (user_id), INDEX IDX_30C544BAB1E7706E (restaurant_id), INDEX IDX_30C544BA57889920 (fonction_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BAB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BA57889920 FOREIGN KEY (fonction_id) REFERENCES fonction (id)');
        $this->addSql('ALTER TABLE assigment DROP FOREIGN KEY `FK_7DE1FD5A57889920`');
        $this->addSql('ALTER TABLE assigment DROP FOREIGN KEY `FK_7DE1FD5AA76ED395`');
        $this->addSql('ALTER TABLE assigment DROP FOREIGN KEY `FK_7DE1FD5AB1E7706E`');
        $this->addSql('DROP TABLE assigment');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assigment (id INT AUTO_INCREMENT NOT NULL, date_start DATE NOT NULL, date_end DATE NOT NULL, date_create DATETIME NOT NULL, date_update DATETIME NOT NULL, user_id INT DEFAULT NULL, restaurant_id INT DEFAULT NULL, fonction_id INT DEFAULT NULL, INDEX IDX_7DE1FD5A57889920 (fonction_id), INDEX IDX_7DE1FD5AA76ED395 (user_id), INDEX IDX_7DE1FD5AB1E7706E (restaurant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_uca1400_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE assigment ADD CONSTRAINT `FK_7DE1FD5A57889920` FOREIGN KEY (fonction_id) REFERENCES fonction (id)');
        $this->addSql('ALTER TABLE assigment ADD CONSTRAINT `FK_7DE1FD5AA76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE assigment ADD CONSTRAINT `FK_7DE1FD5AB1E7706E` FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE assignment DROP FOREIGN KEY FK_30C544BAA76ED395');
        $this->addSql('ALTER TABLE assignment DROP FOREIGN KEY FK_30C544BAB1E7706E');
        $this->addSql('ALTER TABLE assignment DROP FOREIGN KEY FK_30C544BA57889920');
        $this->addSql('DROP TABLE assignment');
    }
}
