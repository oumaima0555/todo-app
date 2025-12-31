<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251231000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user table and add user_id relation to task';
    }

    public function up(Schema $schema): void
    {
        // Création table user
        $this->addSql("
            CREATE TABLE user (
                id INT AUTO_INCREMENT NOT NULL,
                email VARCHAR(180) NOT NULL,
                roles JSON NOT NULL,
                password VARCHAR(255) NOT NULL,
                is_verified TINYINT(1) NOT NULL,
                UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");

        // Ajout user_id dans task
        $this->addSql("
            ALTER TABLE task
            ADD user_id INT NOT NULL
        ");

        $this->addSql("
            ALTER TABLE task
            ADD CONSTRAINT FK_TASK_USER
            FOREIGN KEY (user_id) REFERENCES user (id)
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE task DROP FOREIGN KEY FK_TASK_USER");
        $this->addSql("ALTER TABLE task DROP COLUMN user_id");
        $this->addSql("DROP TABLE user");
    }
}
