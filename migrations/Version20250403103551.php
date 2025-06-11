<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250403103551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE subcategory_activity ADD category_activity_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subcategory_activity ADD CONSTRAINT FK_19047EBC365B22FD FOREIGN KEY (category_activity_id) REFERENCES category_activity (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_19047EBC365B22FD ON subcategory_activity (category_activity_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE subcategory_activity DROP FOREIGN KEY FK_19047EBC365B22FD
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_19047EBC365B22FD ON subcategory_activity
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subcategory_activity DROP category_activity_id
        SQL);
    }
}
