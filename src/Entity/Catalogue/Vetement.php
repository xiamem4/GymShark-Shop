<?php

namespace App\Entity\Catalogue;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Vetement extends Article
{
    public function getTypeNom(): string
    {
        return 'Vêtement';
    }
    
    #[ORM\Column(length: 255)]
    private ?string $marque = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $taille = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $couleur = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $genre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $matiere = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieuExpedition = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $delaiLivraison = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $imagesSupplementaires = [];

    // ---------------- GETTERS / SETTERS ----------------

    public function getMarque(): ?string { return $this->marque; }
    public function setMarque(?string $marque): static { $this->marque = $marque; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): static { $this->type = $type; return $this; }

    public function getTaille(): ?string { return $this->taille; }
    public function setTaille(?string $taille): static { $this->taille = $taille; return $this; }

    public function getCouleur(): ?string { return $this->couleur; }
    public function setCouleur(?string $couleur): static { $this->couleur = $couleur; return $this; }

    public function getGenre(): ?string { return $this->genre; }
    public function setGenre(?string $genre): static { $this->genre = $genre; return $this; }

    public function getMatiere(): ?string { return $this->matiere; }
    public function setMatiere(?string $matiere): static { $this->matiere = $matiere; return $this; }

    public function getLieuExpedition(): ?string { return $this->lieuExpedition; }
    public function setLieuExpedition(?string $lieu): self { $this->lieuExpedition = $lieu; return $this; }

    public function getDelaiLivraison(): ?string { return $this->delaiLivraison; }
    public function setDelaiLivraison(?string $delai): self { $this->delaiLivraison = $delai; return $this; }

    public function getImagesSupplementaires(): ?array { return $this->imagesSupplementaires; }
    public function setImagesSupplementaires(?array $images): self { $this->imagesSupplementaires = $images; return $this; }
}