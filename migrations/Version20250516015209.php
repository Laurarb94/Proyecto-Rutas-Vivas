<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250516015209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE user_name user_name VARCHAR(100) DEFAULT NULL, CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE first_last_name first_last_name VARCHAR(100) DEFAULT NULL, CHANGE second_last_name second_last_name VARCHAR(100) DEFAULT NULL, CHANGE phone phone VARCHAR(100) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `user` CHANGE user_name user_name VARCHAR(100) NOT NULL, CHANGE name name VARCHAR(100) NOT NULL, CHANGE first_last_name first_last_name VARCHAR(100) NOT NULL, CHANGE second_last_name second_last_name VARCHAR(100) NOT NULL, CHANGE phone phone VARCHAR(100) NOT NULL
        SQL);
    }
}
