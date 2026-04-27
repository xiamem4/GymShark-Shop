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
        $limit = 15; 
        $page = $request->query->getInt('page', 1);
        $offset = ($page - 1) * $limit;

        // --- NOUVEAU : Logique de tri ---
        $tri = $request->query->get('tri', 'titre_asc'); // 'titre_asc' par défaut
        switch ($tri) {
            case 'prix_asc': $sort = 'prix'; $order = 'ASC'; break;
            case 'prix_desc': $sort = 'prix'; $order = 'DESC'; break;
            case 'stock_desc': $sort = 'disponibilite'; $order = 'DESC'; break; // Plus grand stock d'abord
            case 'stock_asc': $sort = 'disponibilite'; $order = 'ASC'; break; // Plus petit stock d'abord
            case 'titre_desc': $sort = 'titre'; $order = 'DESC'; break;
            case 'titre_asc':
            default: $sort = 'titre'; $order = 'ASC'; break;
        }

        // 2. Requête avec ORDER BY
        $query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Article a ORDER BY a.$sort $order");
        
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        $paginator = new Paginator($query);
        $totalArticles = count($paginator);
        $nombreDePages = ceil($totalArticles / $limit);

        return $this->render('recherche.html.twig', [
            'articles' => $paginator,
            'nombreDePages' => $nombreDePages,
            'page' => $page,
            'totalArticles' => $totalArticles
        ]);
    }

    #[Route('/afficheRechercheParMotCle', name: 'afficheRechercheParMotCle')]
    public function afficheRechercheParMotCleAction(Request $request): Response
    {
        $limit = 15;
        $page = $request->query->getInt('page', 1);
        $offset = ($page - 1) * $limit;
        
        $motCle = $request->query->get("motCle");

        // --- NOUVEAU : Logique de tri ---
        $tri = $request->query->get('tri', 'titre_asc');
        switch ($tri) {
            case 'prix_asc': $sort = 'prix'; $order = 'ASC'; break;
            case 'prix_desc': $sort = 'prix'; $order = 'DESC'; break;
            case 'stock_desc': $sort = 'disponibilite'; $order = 'DESC'; break;
            case 'stock_asc': $sort = 'disponibilite'; $order = 'ASC'; break;
            case 'titre_desc': $sort = 'titre'; $order = 'DESC'; break;
            case 'titre_asc':
            default: $sort = 'titre'; $order = 'ASC'; break;
        }

        // 2. Requête avec ORDER BY
        $query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Article a WHERE a.titre LIKE :motCle ORDER BY a.$sort $order");
        $query->setParameter("motCle", "%" . $motCle . "%");

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        $paginator = new Paginator($query);
        $totalArticles = count($paginator);
        $nombreDePages = ceil($totalArticles / $limit);

        return $this->render('recherche.html.twig', [
            'articles' => $paginator,
            'nombreDePages' => $nombreDePages,
            'page' => $page,
            'motCle' => $motCle
        ]);
    }

    #[Route('/detailArticle', name: 'detailArticle')]
    public function detailArticleAction(Request $request): Response
    {
        $id = $request->query->get("id");

        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException("L'article demandé n'existe pas.");
        }

        return $this->render('detail.html.twig', [
            'article' => $article,
        ]);
    }

}
