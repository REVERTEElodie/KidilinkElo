<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
<<<<<<< HEAD:KidiLink/migrations/Version20240207134331.php
final class Version20240207134331 extends AbstractMigration
=======
final class Version20240207144624 extends AbstractMigration
>>>>>>> 39082da90a9867cf92415180052aee73fd91b175:KidiLink/migrations/Version20240207144624.php
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
<<<<<<< HEAD:KidiLink/migrations/Version20240207134331.php
        $this->addSql('CREATE TABLE album (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
=======
        $this->addSql('ALTER TABLE album DROP classe_id');
>>>>>>> 39082da90a9867cf92415180052aee73fd91b175:KidiLink/migrations/Version20240207144624.php
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
<<<<<<< HEAD:KidiLink/migrations/Version20240207134331.php
        $this->addSql('DROP TABLE album');
=======
        $this->addSql('ALTER TABLE album ADD classe_id INT NOT NULL');
>>>>>>> 39082da90a9867cf92415180052aee73fd91b175:KidiLink/migrations/Version20240207144624.php
    }
}
