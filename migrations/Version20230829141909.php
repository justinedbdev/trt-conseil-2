<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230829141909 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE apply ADD job_offer_id INT NOT NULL');
        $this->addSql('ALTER TABLE apply ADD CONSTRAINT FK_BD2F8C1F3481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id)');
        $this->addSql('CREATE INDEX IDX_BD2F8C1F3481D195 ON apply (job_offer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE apply DROP FOREIGN KEY FK_BD2F8C1F3481D195');
        $this->addSql('DROP INDEX IDX_BD2F8C1F3481D195 ON apply');
        $this->addSql('ALTER TABLE apply DROP job_offer_id');
    }
}
