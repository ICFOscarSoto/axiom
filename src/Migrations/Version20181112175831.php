<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181112175831 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE email_accounts CHANGE protocol protocol VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE email_subjects CHANGE subject subject VARCHAR(255) DEFAULT NULL, CHANGE from_email from_email VARCHAR(255) DEFAULT NULL, CHANGE to_email to_email VARCHAR(255) DEFAULT NULL, CHANGE size size DOUBLE PRECISION DEFAULT NULL, CHANGE uid uid INT DEFAULT NULL, CHANGE msgno msgno INT DEFAULT NULL, CHANGE recent recent TINYINT(1) DEFAULT NULL, CHANGE flagged flagged TINYINT(1) DEFAULT NULL, CHANGE answered answered TINYINT(1) DEFAULT NULL, CHANGE deleted deleted TINYINT(1) DEFAULT NULL, CHANGE seen seen TINYINT(1) DEFAULT NULL, CHANGE draft draft TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE roles roles JSON NOT NULL, CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE firstname firstname VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE notifications CHANGE user_id user_id INT DEFAULT NULL, CHANGE usergroup_id usergroup_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE readed readed TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_groups CHANGE company_id company_id INT DEFAULT NULL, CHANGE roles roles JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE global_menuoptions CHANGE id_company id_company INT DEFAULT NULL');
        $this->addSql('ALTER TABLE global_menuoptions RENAME INDEX fk_80a2105e9122a03f TO FK_MENUOPTIONS_COMPANIES');
        $this->addSql('ALTER TABLE companies CHANGE name name VARCHAR(120) DEFAULT NULL, CHANGE address address VARCHAR(150) DEFAULT NULL, CHANGE city city VARCHAR(70) DEFAULT NULL, CHANGE state state VARCHAR(125) DEFAULT NULL, CHANGE postcode postcode VARCHAR(12) DEFAULT NULL, CHANGE phone phone VARCHAR(32) DEFAULT NULL, CHANGE mobile mobile VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE currencies CHANGE charcode charcode VARCHAR(1) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE companies CHANGE name name VARCHAR(120) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE address address VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE city city VARCHAR(70) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE state state VARCHAR(125) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE postcode postcode VARCHAR(12) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE phone phone VARCHAR(32) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE mobile mobile VARCHAR(32) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE currencies CHANGE charcode charcode VARCHAR(1) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE email_accounts CHANGE protocol protocol VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE email_subjects CHANGE subject subject VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE from_email from_email VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE to_email to_email VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE size size DOUBLE PRECISION DEFAULT \'NULL\', CHANGE uid uid INT DEFAULT NULL, CHANGE msgno msgno INT DEFAULT NULL, CHANGE recent recent TINYINT(1) DEFAULT \'NULL\', CHANGE flagged flagged TINYINT(1) DEFAULT \'NULL\', CHANGE answered answered TINYINT(1) DEFAULT \'NULL\', CHANGE deleted deleted TINYINT(1) DEFAULT \'NULL\', CHANGE seen seen TINYINT(1) DEFAULT \'NULL\', CHANGE draft draft TINYINT(1) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE global_menuoptions CHANGE id_company id_company INT DEFAULT NULL');
        $this->addSql('ALTER TABLE global_menuoptions RENAME INDEX fk_menuoptions_companies TO FK_80A2105E9122A03F');
        $this->addSql('ALTER TABLE notifications CHANGE user_id user_id INT DEFAULT NULL, CHANGE usergroup_id usergroup_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE readed readed TINYINT(1) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user_groups CHANGE company_id company_id INT DEFAULT NULL, CHANGE roles roles LONGTEXT DEFAULT NULL COLLATE utf8mb4_bin');
        $this->addSql('ALTER TABLE users CHANGE roles roles LONGTEXT NOT NULL COLLATE utf8mb4_bin, CHANGE name name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE firstname firstname VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
