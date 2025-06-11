<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250403102308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE localities ADD province_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE localities ADD CONSTRAINT FK_41E780E9E946114A FOREIGN KEY (province_id) REFERENCES provinces (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_41E780E9E946114A ON localities (province_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE postal_code ADD locality_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE postal_code ADD CONSTRAINT FK_EA98E37688823A92 FOREIGN KEY (locality_id) REFERENCES localities (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_EA98E37688823A92 ON postal_code (locality_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD province_id INT NOT NULL, ADD locality_id INT NOT NULL, ADD postal_code_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D649E946114A FOREIGN KEY (province_id) REFERENCES provinces (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D64988823A92 FOREIGN KEY (locality_id) REFERENCES localities (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D649BDBA6A61 FOREIGN KEY (postal_code_id) REFERENCES postal_code (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8D93D649E946114A ON user (province_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8D93D64988823A92 ON user (locality_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8D93D649BDBA6A61 ON user (postal_code_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE localities DROP FOREIGN KEY FK_41E780E9E946114A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_41E780E9E946114A ON localities
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE localities DROP province_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE postal_code DROP FOREIGN KEY FK_EA98E37688823A92
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_EA98E37688823A92 ON postal_code
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE postal_code DROP locality_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D649E946114A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D64988823A92
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D649BDBA6A61
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8D93D649E946114A ON user
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8D93D64988823A92 ON user
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8D93D649BDBA6A61 ON user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP province_id, DROP locality_id, DROP postal_code_id
        SQL);
    }
}
