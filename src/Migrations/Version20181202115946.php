<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181202115946 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE calendar_calendars (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(100) NOT NULL, color VARCHAR(7) DEFAULT NULL, dateadd DATETIME NOT NULL, dateupd DATETIME NOT NULL, active TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_C7B30945A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE calendar_events (id INT AUTO_INCREMENT NOT NULL, calendar_id INT NOT NULL, title VARCHAR(150) NOT NULL, all_day TINYINT(1) NOT NULL, start DATETIME DEFAULT NULL, end DATETIME DEFAULT NULL, color VARCHAR(7) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, dateadd DATETIME NOT NULL, dateupd DATETIME NOT NULL, active TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_F9E14F16A40A2C8 (calendar_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE calendar_calendars ADD CONSTRAINT FK_C7B30945A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE calendar_events ADD CONSTRAINT FK_F9E14F16A40A2C8 FOREIGN KEY (calendar_id) REFERENCES calendar_calendars (id)');
        $this->addSql('ALTER TABLE companies CHANGE name name VARCHAR(120) DEFAULT NULL, CHANGE address address VARCHAR(150) DEFAULT NULL, CHANGE city city VARCHAR(70) DEFAULT NULL, CHANGE state state VARCHAR(125) DEFAULT NULL, CHANGE postcode postcode VARCHAR(12) DEFAULT NULL, CHANGE phone phone VARCHAR(32) DEFAULT NULL, CHANGE mobile mobile VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE currencies CHANGE charcode charcode VARCHAR(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE menu_options CHANGE company_id company_id INT DEFAULT NULL, CHANGE rute rute VARCHAR(150) DEFAULT NULL, CHANGE roles roles JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\', CHANGE icon icon VARCHAR(150) DEFAULT NULL, CHANGE parent parent INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notifications CHANGE user_id user_id INT DEFAULT NULL, CHANGE usergroup_id usergroup_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE readed readed TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_groups CHANGE company_id company_id INT DEFAULT NULL, CHANGE roles roles JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE email_default_account_id email_default_account_id INT DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE firstname firstname VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE email_accounts CHANGE inbox_folder_id inbox_folder_id INT DEFAULT NULL, CHANGE sent_folder_id sent_folder_id INT DEFAULT NULL, CHANGE trash_folder_id trash_folder_id INT DEFAULT NULL, CHANGE protocol protocol VARCHAR(10) DEFAULT NULL, CHANGE smtp_server smtp_server VARCHAR(150) DEFAULT NULL, CHANGE smtp_port smtp_port VARCHAR(4) DEFAULT NULL, CHANGE smtp_username smtp_username VARCHAR(150) DEFAULT NULL, CHANGE smtp_password smtp_password VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE email_subjects CHANGE subject subject VARCHAR(255) DEFAULT NULL, CHANGE from_email from_email VARCHAR(255) DEFAULT NULL, CHANGE to_email to_email VARCHAR(255) DEFAULT NULL, CHANGE size size DOUBLE PRECISION DEFAULT NULL, CHANGE uid uid INT DEFAULT NULL, CHANGE msgno msgno INT DEFAULT NULL, CHANGE recent recent TINYINT(1) DEFAULT NULL, CHANGE flagged flagged TINYINT(1) DEFAULT NULL, CHANGE answered answered TINYINT(1) DEFAULT NULL, CHANGE deleted deleted TINYINT(1) DEFAULT NULL, CHANGE seen seen TINYINT(1) DEFAULT NULL, CHANGE draft draft TINYINT(1) DEFAULT NULL, CHANGE attachments attachments INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE calendar_events DROP FOREIGN KEY FK_F9E14F16A40A2C8');
        $this->addSql('DROP TABLE calendar_calendars');
        $this->addSql('DROP TABLE calendar_events');
        $this->addSql('ALTER TABLE companies CHANGE name name VARCHAR(120) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE address address VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE city city VARCHAR(70) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE state state VARCHAR(125) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE postcode postcode VARCHAR(12) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE phone phone VARCHAR(32) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE mobile mobile VARCHAR(32) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE currencies CHANGE charcode charcode VARCHAR(1) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE email_accounts CHANGE inbox_folder_id inbox_folder_id INT DEFAULT NULL, CHANGE sent_folder_id sent_folder_id INT DEFAULT NULL, CHANGE trash_folder_id trash_folder_id INT DEFAULT NULL, CHANGE protocol protocol VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE smtp_server smtp_server VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE smtp_port smtp_port VARCHAR(4) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE smtp_username smtp_username VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE smtp_password smtp_password VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE email_subjects CHANGE subject subject VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE from_email from_email VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE to_email to_email VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE size size DOUBLE PRECISION DEFAULT \'NULL\', CHANGE uid uid INT DEFAULT NULL, CHANGE msgno msgno INT DEFAULT NULL, CHANGE recent recent TINYINT(1) DEFAULT \'NULL\', CHANGE flagged flagged TINYINT(1) DEFAULT \'NULL\', CHANGE answered answered TINYINT(1) DEFAULT \'NULL\', CHANGE deleted deleted TINYINT(1) DEFAULT \'NULL\', CHANGE seen seen TINYINT(1) DEFAULT \'NULL\', CHANGE draft draft TINYINT(1) DEFAULT \'NULL\', CHANGE attachments attachments INT DEFAULT NULL');
        $this->addSql('ALTER TABLE menu_options CHANGE company_id company_id INT DEFAULT NULL, CHANGE rute rute VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE roles roles JSON DEFAULT \'NULL\' COLLATE utf8mb4_bin COMMENT \'(DC2Type:json_array)\', CHANGE icon icon VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE parent parent INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notifications CHANGE user_id user_id INT DEFAULT NULL, CHANGE usergroup_id usergroup_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE readed readed TINYINT(1) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user_groups CHANGE company_id company_id INT DEFAULT NULL, CHANGE roles roles LONGTEXT DEFAULT NULL COLLATE utf8mb4_bin');
        $this->addSql('ALTER TABLE users CHANGE email_default_account_id email_default_account_id INT DEFAULT NULL, CHANGE roles roles LONGTEXT NOT NULL COLLATE utf8mb4_bin, CHANGE name name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE firstname firstname VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
