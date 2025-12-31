<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251231032020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_TASK_USER');
        $this->addSql('DROP INDEX fk_task_user ON task');
        $this->addSql('CREATE INDEX IDX_527EDB25A76ED395 ON task (user_id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_TASK_USER FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP INDEX uniq_identifier_email ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE messenger_messages CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25A76ED395');
        $this->addSql('DROP INDEX idx_527edb25a76ed395 ON task');
        $this->addSql('CREATE INDEX FK_TASK_USER ON task (user_id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP INDEX uniq_8d93d649e7927c74 ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
    }
}
