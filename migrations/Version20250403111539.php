<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250403111539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE images_activity ADD personal_activity_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE images_activity ADD CONSTRAINT FK_F577430575613D9 FOREIGN KEY (personal_activity_id) REFERENCES personal_activities (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F577430575613D9 ON images_activity (personal_activity_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities ADD user_id INT NOT NULL, ADD province_id INT NOT NULL, ADD locality_id INT NOT NULL, ADD category_activity_id INT NOT NULL, ADD subcategory_activity_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities ADD CONSTRAINT FK_F5210845A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities ADD CONSTRAINT FK_F5210845E946114A FOREIGN KEY (province_id) REFERENCES provinces (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities ADD CONSTRAINT FK_F521084588823A92 FOREIGN KEY (locality_id) REFERENCES localities (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities ADD CONSTRAINT FK_F5210845365B22FD FOREIGN KEY (category_activity_id) REFERENCES category_activity (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities ADD CONSTRAINT FK_F52108451CD4523C FOREIGN KEY (subcategory_activity_id) REFERENCES subcategory_activity (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F5210845A76ED395 ON personal_activities (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F5210845E946114A ON personal_activities (province_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F521084588823A92 ON personal_activities (locality_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F5210845365B22FD ON personal_activities (category_activity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F52108451CD4523C ON personal_activities (subcategory_activity_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE images_activity DROP FOREIGN KEY FK_F577430575613D9
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F577430575613D9 ON images_activity
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE images_activity DROP personal_activity_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities DROP FOREIGN KEY FK_F5210845A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities DROP FOREIGN KEY FK_F5210845E946114A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities DROP FOREIGN KEY FK_F521084588823A92
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities DROP FOREIGN KEY FK_F5210845365B22FD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities DROP FOREIGN KEY FK_F52108451CD4523C
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F5210845A76ED395 ON personal_activities
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F5210845E946114A ON personal_activities
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F521084588823A92 ON personal_activities
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F5210845365B22FD ON personal_activities
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F52108451CD4523C ON personal_activities
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personal_activities DROP user_id, DROP province_id, DROP locality_id, DROP category_activity_id, DROP subcategory_activity_id
        SQL);
    }
}
