<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240909100142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE organization (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, emblem VARCHAR(255) DEFAULT NULL, description LONGTEXT NOT NULL, overview LONGTEXT NOT NULL, members LONGTEXT NOT NULL, philosophy LONGTEXT NOT NULL, observances LONGTEXT NOT NULL, titles LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE derangement DROP type_bck');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE organization');
        $this->addSql('ALTER TABLE derangement ADD type_bck VARCHAR(255) DEFAULT NULL');
    }
}
