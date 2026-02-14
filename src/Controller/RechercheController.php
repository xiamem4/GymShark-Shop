<?php

namespace App\Controller;

use App\Entity\Catalogue\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class RechercheController extends AbstractController
{
	private EntityManagerInterface $entityManager;
	private LoggerInterface $logger;

	public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
	{
		$this->entityManager = $entityManager;
		$this->logger = $logger;
	}

	#[Route('/afficheRecherche', name: 'afficheRecherche')]
    public function afficheRechercheAction(Request $request): Response
    {
        // 1. Configuration Pagination
        $limit = 15; 
        $page = $request->query->getInt('page', 1); // Page 1 par défaut
        $offset = ($page - 1) * $limit;

        // 2. Requête
        $query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Article a");
        
        // 3. Application des limites
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        // 4. Outil Paginator
        $paginator = new Paginator($query);
        $totalArticles = count($paginator);
        $nombreDePages = ceil($totalArticles / $limit);

        return $this->render('recherche.html.twig', [
            'articles' => $paginator, // On passe le paginator au lieu du tableau simple
            'nombreDePages' => $nombreDePages,
            'page' => $page,
        ]);
    }

    #[Route('/afficheRechercheParMotCle', name: 'afficheRechercheParMotCle')]
    public function afficheRechercheParMotCleAction(Request $request): Response
    {
        // 1. Configuration Pagination
        $limit = 15;
        $page = $request->query->getInt('page', 1);
        $offset = ($page - 1) * $limit;
        
        $motCle = $request->query->get("motCle");

        // 2. Requête
        $query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Article a WHERE a.titre LIKE :motCle");
        $query->setParameter("motCle", "%" . $motCle . "%");

        // 3. Application des limites
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        // 4. Outil Paginator
        $paginator = new Paginator($query);
        $totalArticles = count($paginator);
        $nombreDePages = ceil($totalArticles / $limit);

        return $this->render('recherche.html.twig', [
            'articles' => $paginator,
            'nombreDePages' => $nombreDePages,
            'page' => $page,
            'motCle' => $motCle // Important pour garder la recherche en changeant de page
        ]);
    }
}
