<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220923110204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `character` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, game_id INT DEFAULT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, age INT NOT NULL, disease VARCHAR(255) NOT NULL, story LONGTEXT DEFAULT NULL, powers LONGTEXT DEFAULT NULL, money INT NOT NULL, bag LONGTEXT DEFAULT NULL, stamina INT NOT NULL, strength INT NOT NULL, agility INT NOT NULL, speed INT NOT NULL, charisma INT NOT NULL, intelligence INT NOT NULL, resilience INT NOT NULL, luck INT NOT NULL, INDEX IDX_937AB034A76ED395 (user_id), INDEX IDX_937AB034E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB034A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB034E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB034A76ED395');
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB034E48FD905');
        $this->addSql('DROP TABLE `character`');
    }
}
