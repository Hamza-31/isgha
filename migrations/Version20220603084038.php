<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220603084038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE note ADD user_noted_id INT NOT NULL');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14D1157E70 FOREIGN KEY (user_noted_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_CFBDFA14D1157E70 ON note (user_noted_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14D1157E70');
        $this->addSql('DROP INDEX IDX_CFBDFA14D1157E70 ON note');
        $this->addSql('ALTER TABLE note DROP user_noted_id');
    }
}
