<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240208102711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B784183256915B');
        $this->addSql('ALTER TABLE photo CHANGE relation_id relation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B784183256915B FOREIGN KEY (relation_id) REFERENCES album (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B784183256915B');
        $this->addSql('ALTER TABLE photo CHANGE relation_id relation_id INT NOT NULL');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B784183256915B FOREIGN KEY (relation_id) REFERENCES album (id)');
    }
}
