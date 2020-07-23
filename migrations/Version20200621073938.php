<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\DBALException;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200621073938 extends AbstractMigration
{
    /**
     * Get the description.
     *
     * @return string
     */
    public function getDescription() : string
    {
        return '';
    }

    /**
     * Migrate.
     *
     * @param Schema $schema
     *
     * @throws DBALException
     *
     * @return void
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', login VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', token VARCHAR(255) DEFAULT NULL, until DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649AA08CB10 (login), UNIQUE INDEX UNIQ_8D93D6495F37A13B (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', status_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', payload LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, INDEX IDX_D8698A76A76ED395 (user_id), INDEX IDX_D8698A766BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE status (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', title LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A766BF700BD FOREIGN KEY (status_id) REFERENCES status (id) ON DELETE SET NULL');
    }

    /**
     * Rollback.
     *
     * @param Schema $schema
     *
     * @throws DBALException
     *
     * @return void
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76A76ED395');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A766BF700BD');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE status');
    }
}
