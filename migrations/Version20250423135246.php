<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250423135246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities ADD companion_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities ADD CONSTRAINT FK_F52108458227E3FD FOREIGN KEY (companion_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F52108458227E3FD ON personal_activities (companion_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities DROP FOREIGN KEY FK_F52108458227E3FD
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F52108458227E3FD ON personal_activities
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities DROP companion_id
        SQL);
    }
}
