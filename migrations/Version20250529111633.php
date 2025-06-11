<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250529111633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_notification ADD related_follow_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC82FD08FA7 FOREIGN KEY (related_follow_id) REFERENCES user_follow (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3F980AC82FD08FA7 ON user_notification (related_follow_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_notification DROP FOREIGN KEY FK_3F980AC82FD08FA7
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3F980AC82FD08FA7 ON user_notification
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_notification DROP related_follow_id
        SQL);
    }
}
