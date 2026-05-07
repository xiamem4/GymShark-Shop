<?php

namespace App\Controller;

use App\Entity\Catalogue\Article;
use App\Entity\Commande\Commande;
use App\Entity\Commande\LigneCommande;
use App\Entity\Panier\LignePanier;
use App\Entity\Panier\Panier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PanierController extends AbstractController
{
	private EntityManagerInterface $entityManager;
	private LoggerInterface $logger;
	private Panier $panier;

	public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
	{
		$this->entityManager = $entityManager;
		$this->logger = $logger;
	}

	#[Route('/ajouterLigne', name: 'ajouterLigne')]
	public function ajouterLigneAction(Request $request): Response
	{
		$session = $request->getSession();
		if ($session->has('panier'))
			$this->panier = $session->get('panier');
		else
			$this->panier = new Panier();

		$qty = $request->query->getInt('qty', 1);

		$article = $this->entityManager->getRepository(Article::class)->find($request->query->get('id'));

		if ($article) {
			$this->panier->ajouterLigne($article, $qty);
			$session->set('panier', $this->panier);
		}

		return $this->render('panier.html.twig', [
			'panier' => $this->panier,
		]);
	}

	#[Route('/supprimerLigne', name: 'supprimerLigne')]
	public function supprimerLigneAction(Request $request): Response
	{
		$session = $request->getSession();
		if ($session->has('panier'))
			$this->panier = $session->get('panier');
		else
			$this->panier = new Panier();

		$this->panier->supprimerLigne($request->query->get('id'));
		$session->set('panier', $this->panier);
		
		return $this->render('panier.html.twig', [
			'panier' => $this->panier,
		]);
	}

	#[Route('/recalculerPanier', name: 'recalculerPanier')]
	public function recalculerPanierAction(Request $request): Response
	{
		$session = $request->getSession();
		if ($session->has('panier'))
			$this->panier = $session->get('panier');
		else
			$this->panier = new Panier();

		$it = $this->panier->getLignesPanier()->getIterator();
		while ($it->valid()) {
			$ligne = $it->current();
			$article = $ligne->getArticle();
			$ligne->setQuantite($request->request->all()['panier']['lignesPanier'][$article->getId()]['qty']);
			$ligne->recalculer();
			$it->next();
		}
		$this->panier->recalculer();
		$session->set('panier', $this->panier);
		return $this->render('panier.html.twig', [
			'panier' => $this->panier,
		]);
	}

	#[Route('/accederAuPanier', name: 'accederAuPanier')]
	#[IsGranted('ROLE_USER')]
	public function accederAuPanierAction(Request $request): Response
	{
		$session = $request->getSession();
		if ($session->has('panier'))
			$this->panier = $session->get('panier');
		else
			$this->panier = new Panier();
			
		if (sizeof($this->panier->getLignesPanier()) === 0)
			return $this->render('panier.vide.html.twig');
		else
			return $this->render('panier.html.twig', [
				'panier' => $this->panier,
			]);
	}

	#[Route('/commanderPanier', name: 'commanderPanier')]
	#[IsGranted('ROLE_USER')]
	public function commanderPanierAction(Request $request): Response
	{
		$user = $this->getUser();
		$session = $request->getSession();
		$panier = $session->get('panier');

		if (!$panier || count($panier->getLignesPanier()) === 0) {
			return $this->redirectToRoute('accederAuPanier');
		}

		$commande = new Commande();
		$commande->setUtilisateur($user);
		$commande->setDateCreation(new \DateTime());
		$commande->setTotal($panier->getTotal());
		$commande->setReference(uniqid('CMD_'));

		foreach ($panier->getLignesPanier() as $lignePanier) {
			$ligneCommande = new LigneCommande();

			$articleGere = $this->entityManager->getRepository(Article::class)->find($lignePanier->getArticle()->getId());

			if ($articleGere) {
				$ligneCommande->setArticle($articleGere);
				$ligneCommande->setQuantite($lignePanier->getQuantite());
				$ligneCommande->setPrixUnitaire($articleGere->getPrix());
				$ligneCommande->setCommande($commande);

				$this->entityManager->persist($ligneCommande);
			}
		}

		$this->entityManager->persist($commande);
		$this->entityManager->flush();

		// Nettoyage du panier en session après la commande
		$session->remove('panier');

		return $this->render('commande.html.twig', [
			'panier' => $panier,
			'reference' => $commande->getReference()
		]);
	}
}