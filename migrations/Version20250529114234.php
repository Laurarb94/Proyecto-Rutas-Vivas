<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250529114234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_notification ADD initiator_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC87DB3B714 FOREIGN KEY (initiator_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3F980AC87DB3B714 ON user_notification (initiator_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_notification DROP FOREIGN KEY FK_3F980AC87DB3B714
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3F980AC87DB3B714 ON user_notification
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_notification DROP initiator_id
        SQL);
    }
}
