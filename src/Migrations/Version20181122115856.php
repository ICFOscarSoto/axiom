<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181122115856 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE email_subjects ADD attachments INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD email_default_account_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D61D57D5 FOREIGN KEY (email_default_account_id) REFERENCES email_accounts (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9D61D57D5 ON users (email_default_account_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE email_subjects DROP attachments');
        $this->addSql('ALTER TABLE global_menuoptions CHANGE parent parent INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_groups CHANGE roles roles LONGTEXT DEFAULT NULL COLLATE utf8mb4_bin');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9D61D57D5');
        $this->addSql('DROP INDEX UNIQ_1483A5E9D61D57D5 ON users');
        $this->addSql('ALTER TABLE users DROP email_default_account_id, CHANGE roles roles LONGTEXT NOT NULL COLLATE utf8mb4_bin');
    }
}
