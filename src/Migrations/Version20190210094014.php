<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190210094014 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE share_shares (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, user_group_id INT DEFAULT NULL, class_name VARCHAR(125) NOT NULL, id_object INT NOT NULL, read_only TINYINT(1) NOT NULL, shareable TINYINT(1) NOT NULL, active TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, dateadd DATETIME NOT NULL, dateupd DATETIME NOT NULL, INDEX IDX_4E6D8155A76ED395 (user_id), INDEX IDX_4E6D81551ED93D47 (user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE io_tdata (id INT AUTO_INCREMENT NOT NULL, sensor_id INT NOT NULL, data VARCHAR(255) DEFAULT NULL, counter INT NOT NULL, dateadd DATETIME NOT NULL, dateupd DATETIME NOT NULL, active TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_AA4588A7A247991F (sensor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE io_tdevices (id INT AUTO_INCREMENT NOT NULL, company_id INT DEFAULT NULL, latitude VARCHAR(128) DEFAULT NULL, longitude VARCHAR(128) DEFAULT NULL, name VARCHAR(128) NOT NULL, token VARCHAR(255) NOT NULL, dateadd DATETIME NOT NULL, dateupd DATETIME NOT NULL, active TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_6968C685979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE io_tsensors (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, name VARCHAR(128) NOT NULL, description VARCHAR(255) DEFAULT NULL, type VARCHAR(64) NOT NULL, unit VARCHAR(64) DEFAULT NULL, unit_abrv VARCHAR(16) DEFAULT NULL, accuracy INT NOT NULL, dateadd DATETIME NOT NULL, dateupd DATETIME NOT NULL, active TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_A8BC728F94A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE share_shares ADD CONSTRAINT FK_4E6D8155A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE share_shares ADD CONSTRAINT FK_4E6D81551ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_groups (id)');
        $this->addSql('ALTER TABLE io_tdata ADD CONSTRAINT FK_AA4588A7A247991F FOREIGN KEY (sensor_id) REFERENCES io_tsensors (id)');
        $this->addSql('ALTER TABLE io_tdevices ADD CONSTRAINT FK_6968C685979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id)');
        $this->addSql('ALTER TABLE io_tsensors ADD CONSTRAINT FK_A8BC728F94A4C7D4 FOREIGN KEY (device_id) REFERENCES io_tdevices (id)');
        $this->addSql('ALTER TABLE companies CHANGE name name VARCHAR(120) DEFAULT NULL, CHANGE address address VARCHAR(150) DEFAULT NULL, CHANGE city city VARCHAR(70) DEFAULT NULL, CHANGE state state VARCHAR(125) DEFAULT NULL, CHANGE postcode postcode VARCHAR(12) DEFAULT NULL, CHANGE phone phone VARCHAR(32) DEFAULT NULL, CHANGE mobile mobile VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE countries ADD dateadd DATETIME NOT NULL, ADD dateupd DATETIME NOT NULL, ADD active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE currencies ADD dateadd DATETIME NOT NULL, ADD dateupd DATETIME NOT NULL, ADD active TINYINT(1) NOT NULL, ADD deleted TINYINT(1) NOT NULL, CHANGE charcode charcode VARCHAR(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE menu_options CHANGE company_id company_id INT DEFAULT NULL, CHANGE rute rute VARCHAR(150) DEFAULT NULL, CHANGE roles roles JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\', CHANGE icon icon VARCHAR(150) DEFAULT NULL, CHANGE parent parent INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notifications CHANGE user_id user_id INT DEFAULT NULL, CHANGE usergroup_id usergroup_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE readed readed TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_groups ADD dateadd DATETIME NOT NULL, ADD dateupd DATETIME NOT NULL, ADD active TINYINT(1) NOT NULL, ADD deleted TINYINT(1) NOT NULL, CHANGE company_id company_id INT DEFAULT NULL, CHANGE roles roles JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE email_default_account_id email_default_account_id INT DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE firstname firstname VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE email_accounts CHANGE inbox_folder_id inbox_folder_id INT DEFAULT NULL, CHANGE sent_folder_id sent_folder_id INT DEFAULT NULL, CHANGE trash_folder_id trash_folder_id INT DEFAULT NULL, CHANGE protocol protocol VARCHAR(10) DEFAULT NULL, CHANGE smtp_server smtp_server VARCHAR(150) DEFAULT NULL, CHANGE smtp_port smtp_port VARCHAR(4) DEFAULT NULL, CHANGE smtp_username smtp_username VARCHAR(150) DEFAULT NULL, CHANGE smtp_password smtp_password VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE email_subjects CHANGE subject subject VARCHAR(255) DEFAULT NULL, CHANGE from_email from_email VARCHAR(255) DEFAULT NULL, CHANGE to_email to_email VARCHAR(255) DEFAULT NULL, CHANGE size size DOUBLE PRECISION DEFAULT NULL, CHANGE uid uid INT DEFAULT NULL, CHANGE msgno msgno INT DEFAULT NULL, CHANGE recent recent TINYINT(1) DEFAULT NULL, CHANGE flagged flagged TINYINT(1) DEFAULT NULL, CHANGE answered answered TINYINT(1) DEFAULT NULL, CHANGE deleted deleted TINYINT(1) DEFAULT NULL, CHANGE seen seen TINYINT(1) DEFAULT NULL, CHANGE draft draft TINYINT(1) DEFAULT NULL, CHANGE attachments attachments INT DEFAULT NULL');
        $this->addSql('ALTER TABLE calendar_calendars CHANGE color color VARCHAR(7) DEFAULT NULL');
        $this->addSql('ALTER TABLE calendar_events CHANGE start start DATETIME DEFAULT NULL, CHANGE end end DATETIME DEFAULT NULL, CHANGE color color VARCHAR(7) DEFAULT NULL, CHANGE location location VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE io_tsensors DROP FOREIGN KEY FK_A8BC728F94A4C7D4');
        $this->addSql('ALTER TABLE io_tdata DROP FOREIGN KEY FK_AA4588A7A247991F');
        $this->addSql('DROP TABLE share_shares');
        $this->addSql('DROP TABLE io_tdata');
        $this->addSql('DROP TABLE io_tdevices');
        $this->addSql('DROP TABLE io_tsensors');
        $this->addSql('ALTER TABLE calendar_calendars CHANGE color color VARCHAR(7) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE calendar_events CHANGE start start DATETIME DEFAULT \'NULL\', CHANGE end end DATETIME DEFAULT \'NULL\', CHANGE color color VARCHAR(7) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE location location VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE companies CHANGE name name VARCHAR(120) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE address address VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE city city VARCHAR(70) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE state state VARCHAR(125) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE postcode postcode VARCHAR(12) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE phone phone VARCHAR(32) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE mobile mobile VARCHAR(32) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE countries DROP dateadd, DROP dateupd, DROP active');
        $this->addSql('ALTER TABLE currencies DROP dateadd, DROP dateupd, DROP active, DROP deleted, CHANGE charcode charcode VARCHAR(1) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE email_accounts CHANGE inbox_folder_id inbox_folder_id INT DEFAULT NULL, CHANGE sent_folder_id sent_folder_id INT DEFAULT NULL, CHANGE trash_folder_id trash_folder_id INT DEFAULT NULL, CHANGE protocol protocol VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE smtp_server smtp_server VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE smtp_port smtp_port VARCHAR(4) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE smtp_username smtp_username VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE smtp_password smtp_password VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE email_subjects CHANGE subject subject VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE from_email from_email VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE to_email to_email VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE size size DOUBLE PRECISION DEFAULT \'NULL\', CHANGE uid uid INT DEFAULT NULL, CHANGE msgno msgno INT DEFAULT NULL, CHANGE recent recent TINYINT(1) DEFAULT \'NULL\', CHANGE flagged flagged TINYINT(1) DEFAULT \'NULL\', CHANGE answered answered TINYINT(1) DEFAULT \'NULL\', CHANGE deleted deleted TINYINT(1) DEFAULT \'NULL\', CHANGE seen seen TINYINT(1) DEFAULT \'NULL\', CHANGE draft draft TINYINT(1) DEFAULT \'NULL\', CHANGE attachments attachments INT DEFAULT NULL');
        $this->addSql('ALTER TABLE menu_options CHANGE company_id company_id INT DEFAULT NULL, CHANGE rute rute VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE roles roles JSON DEFAULT \'NULL\' COLLATE utf8mb4_bin COMMENT \'(DC2Type:json_array)\', CHANGE icon icon VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE parent parent INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notifications CHANGE user_id user_id INT DEFAULT NULL, CHANGE usergroup_id usergroup_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE readed readed TINYINT(1) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user_groups DROP dateadd, DROP dateupd, DROP active, DROP deleted, CHANGE company_id company_id INT DEFAULT NULL, CHANGE roles roles LONGTEXT DEFAULT NULL COLLATE utf8mb4_bin');
        $this->addSql('ALTER TABLE users CHANGE email_default_account_id email_default_account_id INT DEFAULT NULL, CHANGE roles roles LONGTEXT NOT NULL COLLATE utf8mb4_bin, CHANGE name name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE firstname firstname VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
