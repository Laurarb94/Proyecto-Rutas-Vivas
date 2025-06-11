<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250516001259 extends AbstractMigration
{
     public function getDescription(): string
    {
        return 'Añade is_profile_completed, modifica clerk_id, crea índice único si no existe, elimina is_approved de user_follow';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE user 
            ADD COLUMN IF NOT EXISTS is_profile_completed TINYINT(1) NOT NULL DEFAULT 0
        ");

        $this->addSql("
            ALTER TABLE user 
            MODIFY clerk_id VARCHAR(255) DEFAULT NULL
        ");

        $this->addSql("
            CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_8D93D649D95C1FC6 ON user (clerk_id)
        ");

        $this->addSql("
            ALTER TABLE user_follow 
            DROP COLUMN IF EXISTS is_approved
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DROP INDEX IF EXISTS UNIQ_8D93D649D95C1FC6 ON user
        ");

        $this->addSql("
            ALTER TABLE user 
            DROP COLUMN IF EXISTS is_profile_completed,
            MODIFY clerk_id VARCHAR(255) NOT NULL
        ");

        $this->addSql("
            ALTER TABLE user_follow 
            ADD COLUMN IF NOT EXISTS is_approved TINYINT(1) NOT NULL DEFAULT 0
        ");
    }
}
