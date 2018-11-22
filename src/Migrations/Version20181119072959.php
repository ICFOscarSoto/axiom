<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181119072959 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE email_accounts ADD inbox_folder_id INT DEFAULT NULL, ADD sent_folder_id INT DEFAULT NULL, ADD trash_folder_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE email_accounts ADD CONSTRAINT FK_C1AE81E56C6BD431 FOREIGN KEY (inbox_folder_id) REFERENCES email_folders (id)');
        $this->addSql('ALTER TABLE email_accounts ADD CONSTRAINT FK_C1AE81E563CB685A FOREIGN KEY (sent_folder_id) REFERENCES email_folders (id)');
        $this->addSql('ALTER TABLE email_accounts ADD CONSTRAINT FK_C1AE81E5826E9C4B FOREIGN KEY (trash_folder_id) REFERENCES email_folders (id)');
        $this->addSql('CREATE INDEX IDX_C1AE81E56C6BD431 ON email_accounts (inbox_folder_id)');
        $this->addSql('CREATE INDEX IDX_C1AE81E563CB685A ON email_accounts (sent_folder_id)');
        $this->addSql('CREATE INDEX IDX_C1AE81E5826E9C4B ON email_accounts (trash_folder_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE email_accounts DROP FOREIGN KEY FK_C1AE81E56C6BD431');
        $this->addSql('ALTER TABLE email_accounts DROP FOREIGN KEY FK_C1AE81E563CB685A');
        $this->addSql('ALTER TABLE email_accounts DROP FOREIGN KEY FK_C1AE81E5826E9C4B');
        $this->addSql('DROP INDEX IDX_C1AE81E56C6BD431 ON email_accounts');
        $this->addSql('DROP INDEX IDX_C1AE81E563CB685A ON email_accounts');
        $this->addSql('DROP INDEX IDX_C1AE81E5826E9C4B ON email_accounts');
        $this->addSql('ALTER TABLE email_accounts DROP inbox_folder_id, DROP sent_folder_id, DROP trash_folder_id');
        $this->addSql('ALTER TABLE global_menuoptions CHANGE rute rute VARCHAR(150) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE icon icon VARCHAR(150) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE parent parent INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_groups CHANGE roles roles LONGTEXT DEFAULT NULL COLLATE utf8mb4_bin');
        $this->addSql('ALTER TABLE users CHANGE roles roles LONGTEXT NOT NULL COLLATE utf8mb4_bin');
    }
}
