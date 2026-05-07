<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260507132819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__ligne_commande AS SELECT id, quantite, prix_unitaire, article_id, commande_id FROM ligne_commande');
        $this->addSql('DROP TABLE ligne_commande');
        $this->addSql('CREATE TABLE ligne_commande (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, quantite INTEGER NOT NULL, prix_unitaire DOUBLE PRECISION NOT NULL, article_id BIGINT NOT NULL, commande_id INTEGER NOT NULL, CONSTRAINT FK_3170B74B7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3170B74B82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO ligne_commande (id, quantite, prix_unitaire, article_id, commande_id) SELECT id, quantite, prix_unitaire, article_id, commande_id FROM __temp__ligne_commande');
        $this->addSql('DROP TABLE __temp__ligne_commande');
        $this->addSql('CREATE INDEX IDX_3170B74B7294869C ON ligne_commande (article_id)');
        $this->addSql('CREATE INDEX IDX_3170B74B82EA2E54 ON ligne_commande (commande_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__ligne_commande AS SELECT id, quantite, prix_unitaire, article_id, commande_id FROM ligne_commande');
        $this->addSql('DROP TABLE ligne_commande');
        $this->addSql('CREATE TABLE ligne_commande (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, quantite INTEGER NOT NULL, prix_unitaire DOUBLE PRECISION NOT NULL, article_id BIGINT NOT NULL, commande_id INTEGER NOT NULL, CONSTRAINT FK_3170B74B7294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3170B74B82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO ligne_commande (id, quantite, prix_unitaire, article_id, commande_id) SELECT id, quantite, prix_unitaire, article_id, commande_id FROM __temp__ligne_commande');
        $this->addSql('DROP TABLE __temp__ligne_commande');
        $this->addSql('CREATE INDEX IDX_3170B74B7294869C ON ligne_commande (article_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3170B74B82EA2E54 ON ligne_commande (commande_id)');
    }
}
