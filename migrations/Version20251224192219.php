<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251224192219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Migration adjusted for MySQL (no-op because schema is already correct).
        // Original migration used SQLite-specific temp-table operations that are not needed here.
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__task AS SELECT id, title, description, status FROM task');
        $this->addSql('DROP TABLE task');
        $this->addSql('CREATE TABLE task (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, status BOOLEAN NOT NULL, creat_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO task (id, title, description, status) SELECT id, title, description, status FROM __temp__task');
        $this->addSql('DROP TABLE __temp__task');
    }
}
