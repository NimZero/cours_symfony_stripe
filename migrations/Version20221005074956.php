<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221005074956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD shipping_option_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398E1314C58 FOREIGN KEY (shipping_option_id) REFERENCES shipping_rate (id)');
        $this->addSql('CREATE INDEX IDX_F5299398E1314C58 ON `order` (shipping_option_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398E1314C58');
        $this->addSql('DROP INDEX IDX_F5299398E1314C58 ON `order`');
        $this->addSql('ALTER TABLE `order` DROP shipping_option_id');
    }
}
