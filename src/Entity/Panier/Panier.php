<?php

namespace App\Entity\Panier;

use ArrayObject;
use App\Entity\Catalogue\Article;

class Panier
{
    private float $total;

	private float $sousTotal = 0.0;
    private float $montantFraisPort = 0.0;

    private ArrayObject $lignesPanier;

	public function __construct()
    {
		$this->lignesPanier = new ArrayObject();
    }

	public function getSousTotal(): float
    {
        return $this->sousTotal;
    }

    public function getMontantFraisPort(): float
    {
        return $this->montantFraisPort;
    }

	public function setTotal(): void
	{
		$this->recalculer();
    }
	
	public function getTotal(): ?float
	{
		return $this->total;
    }
	
	public function getLignesPanier(): ?ArrayObject
	{
		return $this->lignesPanier;
	}
	
	private float $tauxFraisPort = 0.0;

    public function getTauxFraisPort(): float
    {
        return $this->tauxFraisPort;
    }

	public function recalculer(): void
    {
        $it = $this->getLignesPanier()->getIterator();
        $this->sousTotal = 0.0 ;
        while ($it->valid()) {
            $ligne = $it->current();
            $ligne->recalculer() ;
            $this->sousTotal += $ligne->getPrixTotal() ;
            $it->next();
        }

        $this->tauxFraisPort = random_int(-1000, 1000) / 10.0;

        $this->montantFraisPort = round($this->sousTotal * ($this->tauxFraisPort / 100), 2);
		$this->total = round($this->sousTotal + $this->montantFraisPort, 2);

		if ($this->total < 0) $this->total = 0;
    }
	
	public function ajouterLigne(Article $article, int $qty = 1): void
	{
		$lp = $this->chercherLignePanier($article) ;
        if ($lp == null) {
            $lp = new LignePanier() ;
            $lp->setArticle($article) ; 
            $lp->setQuantite($qty) ;
            $this->lignesPanier->append($lp) ;
        }
        else {
            $lp->setQuantite($lp->getQuantite() + $qty) ;
        }
        $this->recalculer() ;
    }
	
	public function chercherLignePanier(Article $article): ?LignePanier
	{
		$lignePanier = null ;
		$it = $this->getLignesPanier()->getIterator();
		while ($it->valid()) {
			$ligne = $it->current();
			if ($ligne->getArticle()->getId() == $article->getId())
				$lignePanier = $ligne ;
			$it->next();
		}
		return $lignePanier ;
	}
	
	public function supprimerLigne(int $id): void
	{
		$existe = false ;
		$it = $this->getLignesPanier()->getIterator();
		while ($it->valid()) {
			$ligne = $it->current();
			if ($ligne->getArticle()->getId() == $id) {
				$existe = true ;
				$key = $it->key();
			}
			$it->next();
		}
		if ($existe) {
			$this->getLignesPanier()->offsetUnset($key);
			$this->recalculer();
		}
	}
}

