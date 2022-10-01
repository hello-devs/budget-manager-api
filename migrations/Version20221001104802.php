<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221001104802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE budget_transaction_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE budget_transaction (id INT NOT NULL, budget_id INT NOT NULL, transaction_id INT NOT NULL, date_immutable TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_negative BOOLEAN NOT NULL, is_recurrent BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_43D438E36ABA6B8 ON budget_transaction (budget_id)');
        $this->addSql('CREATE INDEX IDX_43D438E2FC0CB0F ON budget_transaction (transaction_id)');
        $this->addSql('COMMENT ON COLUMN budget_transaction.date_immutable IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE budget_transaction ADD CONSTRAINT FK_43D438E36ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_transaction ADD CONSTRAINT FK_43D438E2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE budget_transaction_id_seq CASCADE');
        $this->addSql('DROP TABLE budget_transaction');
    }
}
