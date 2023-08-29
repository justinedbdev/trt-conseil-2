<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230829134926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recruiter ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recruiter ADD CONSTRAINT FK_DE8633D8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_DE8633D8A76ED395 ON recruiter (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recruiter DROP FOREIGN KEY FK_DE8633D8A76ED395');
        $this->addSql('DROP INDEX IDX_DE8633D8A76ED395 ON recruiter');
        $this->addSql('ALTER TABLE recruiter DROP user_id');
    }
}
