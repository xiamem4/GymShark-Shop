<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Catalogue\Article;
use App\Entity\User;

class AdminController extends AbstractController
{
	private EntityManagerInterface $entityManager;
	private LoggerInterface $logger;

	public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)  {
		$this->entityManager = $entityManager;
		$this->logger = $logger;
	}

    #[Route('/admin/musiques', name: 'adminMusiques')]
    public function adminMusiquesAction(Request $request): Response
    {
		$query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Musique a");
		$articles = $query->getResult();
		return $this->render('admin.musiques.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/admin/livres', name: 'adminLivres')]
    public function adminLivresAction(Request $request): Response
    {
		$query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Livre a");
		$articles = $query->getResult();
		return $this->render('admin.livres.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/admin/musiques/supprimer', name: 'adminMusiquesSupprimer')]
    public function adminMusiquesSupprimerAction(Request $request): Response
    {
		$entityArticle = $this->entityManager->getReference("App\Entity\Catalogue\Article", $request->query->get("id"));
		if ($entityArticle !== null) {
			$this->entityManager->remove($entityArticle);
			$this->entityManager->flush();
		}
		return $this->redirectToRoute("adminMusiques") ;
    }

    #[Route('/admin/livres/supprimer', name: 'adminLivresSupprimer')]
    public function adminLivresSupprimerAction(Request $request): Response
    {
		$entityArticle = $this->entityManager->getReference("App\Entity\Catalogue\Article", $request->query->get("id"));
		if ($entityArticle !== null) {
			$this->entityManager->remove($entityArticle);
			$this->entityManager->flush();
		}
		return $this->redirectToRoute("adminLivres") ;
    }

    #[Route('/admin/livres/ajouter', name: 'adminLivresAjouter')]
    public function adminLivresAjouterAction(Request $request): Response
    {
		$entity = new Livre() ;
		$formBuilder = $this->createFormBuilder($entity);
		$formBuilder->add("titre", TextType::class) ;
		$formBuilder->add("auteur", TextType::class) ;
		$formBuilder->add("prix", NumberType::class) ;
		$formBuilder->add("disponibilite", IntegerType::class) ;
		$formBuilder->add("image", TextType::class) ;
		$formBuilder->add("ISBN", TextType::class) ;
		$formBuilder->add("nbPages", IntegerType::class) ;
		$formBuilder->add("dateDePublication", TextType::class) ;
		$formBuilder->add("valider", SubmitType::class) ;
		// Generate form
		$form = $formBuilder->getForm();

		$form->handleRequest($request) ;

		if ($form->isSubmitted()) {
			$entity = $form->getData() ;
			$entity->setId(hexdec(uniqid()));
			$this->entityManager->persist($entity);
			$this->entityManager->flush();
			return $this->redirectToRoute("adminLivres") ;
		}
		else {
			return $this->render('admin.form.html.twig', [
				'form' => $form->createView(),
			]);
		}
    }

    #[Route('/admin/musiques/ajouter', name: 'adminMusiquesAjouter')]
    public function adminMusiquesAjouterAction(Request $request): Response
    {
		$entity = new Musique() ;
		$formBuilder = $this->createFormBuilder($entity);
		$formBuilder->add("titre", TextType::class) ;
		$formBuilder->add("artiste", TextType::class) ;
		$formBuilder->add("prix", NumberType::class) ;
		$formBuilder->add("disponibilite", IntegerType::class) ;
		$formBuilder->add("image", TextType::class) ;
		$formBuilder->add("dateDeSortie", TextType::class) ;
		$formBuilder->add("valider", SubmitType::class) ;
		// Generate form
		$form = $formBuilder->getForm();

		$form->handleRequest($request) ;

		if ($form->isSubmitted()) {
			$entity = $form->getData() ;
			$entity->setId(hexdec(uniqid()));
			$this->entityManager->persist($entity);
			$this->entityManager->flush();
			return $this->redirectToRoute("adminMusiques") ;
		}
		else {
			return $this->render('admin.form.html.twig', [
				'form' => $form->createView(),
			]);
		}
    }

    #[Route('/admin/livres/modifier', name: 'adminLivresModifier')]
    public function adminLivresModifierAction(Request $request): Response
    {
		$entity = $this->entityManager->getReference("App\Entity\Catalogue\Livre", $request->query->get("id"));
		if ($entity === null)
			$entity = $this->entityManager->getReference("App\Entity\Catalogue\Livre", $request->request->get("id"));
		if ($entity !== null) {
			$formBuilder = $this->createFormBuilder($entity);
			$formBuilder->add("id", HiddenType::class) ;
			$formBuilder->add("titre", TextType::class) ;
			$formBuilder->add("auteur", TextType::class) ;
			$formBuilder->add("prix", NumberType::class) ;
			$formBuilder->add("disponibilite", IntegerType::class) ;
			$formBuilder->add("image", TextType::class) ;
			$formBuilder->add("ISBN", TextType::class) ;
			$formBuilder->add("nbPages", IntegerType::class) ;
			$formBuilder->add("dateDePublication", TextType::class) ;
			$formBuilder->add("valider", SubmitType::class) ;
			// Generate form
			$form = $formBuilder->getForm();

			$form->handleRequest($request) ;

			if ($form->isSubmitted()) {
				$entity = $form->getData() ;
				$this->entityManager->persist($entity);
				$this->entityManager->flush();
				return $this->redirectToRoute("adminLivres") ;
			}
			else {
				return $this->render('admin.form.html.twig', [
					'form' => $form->createView(),
				]);
			}
		}
		else {
			return $this->redirectToRoute("adminLivres") ;
		}
    }

    #[Route('/admin/musiques/modifier', name: 'adminMusiquesModifier')]
    public function adminMusiquesModifierAction(Request $request): Response
    {
		$entity = $this->entityManager->getReference("App\Entity\Catalogue\Musique", $request->query->get("id"));
		if ($entity === null)
			$entity = $this->entityManager->getReference("App\Entity\Catalogue\Musique", $request->request->get("id"));
		if ($entity !== null) {
			$formBuilder = $this->createFormBuilder($entity);
			$formBuilder->add("id", HiddenType::class) ;
			$formBuilder->add("titre", TextType::class) ;
			$formBuilder->add("artiste", TextType::class) ;
			$formBuilder->add("prix", NumberType::class) ;
			$formBuilder->add("disponibilite", IntegerType::class) ;
			$formBuilder->add("image", TextType::class) ;
			$formBuilder->add("dateDeSortie", TextType::class) ;
			$formBuilder->add("valider", SubmitType::class) ;
			// Generate form
			$form = $formBuilder->getForm();

			$form->handleRequest($request) ;

			if ($form->isSubmitted()) {
				$entity = $form->getData() ;
				$this->entityManager->persist($entity);
				$this->entityManager->flush();
				return $this->redirectToRoute("adminMusiques") ;
			}
			else {
				return $this->render('admin.form.html.twig', [
					'form' => $form->createView(),
				]);
			}
		}
		else {
			return $this->redirectToRoute("adminMusiques") ;
		}
    }

	#[Route('/randomizeStock', name: 'randomizeStock')]
    public function randomizeStockAction(EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(\App\Entity\Catalogue\Article::class);

        // Récupère tous les articles de la base de données
        $articles = $repository->findAll();

        $compteur = 0;

        foreach ($articles as $article) {
            $stockAleatoire = random_int(0, 1000);

            // Met à jour la disponibilité de l'article
            $article->setDisponibilite($stockAleatoire);

            $compteur++;
        }

        $entityManager->flush();

        return new Response("Succès ! Les stocks de " . $compteur . " articles ont été modifiés de manière aléatoire.");
    }

	#[Route('/admin/utilisateurs', name: 'admin_utilisateurs')]
    public function listeUtilisateursAction(EntityManagerInterface $entityManager): Response
    {
        $utilisateurs = $entityManager->getRepository(User::class)->findAll();

        // Initialisation des compteurs
        $nbTotal = count($utilisateurs);
        $nbAdmins = 0;
        $nbClients = 0;

        foreach ($utilisateurs as $u) {
            // Dans Symfony, un admin a généralement ROLE_ADMIN et ROLE_USER
            // Un client n'a que ROLE_USER
            if (in_array('ROLE_ADMIN', $u->getRoles())) {
                $nbAdmins++;
            } else {
                $nbClients++;
            }
        }

        return $this->render('admin.utilisateurs.html.twig', [
            'utilisateurs' => $utilisateurs,
            'nbTotal' => $nbTotal,
            'nbAdmins' => $nbAdmins,
            'nbClients' => $nbClients,
        ]);
    }

	#[Route('/admin/utilisateurs/modifier', name: 'admin_utilisateurs_modifier')]
    public function modifierUtilisateurAction(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $id = $request->query->get('id');
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        if ($request->isMethod('POST')) {
            // 1. Modification de l'Email
            $user->setEmail($request->request->get('email'));

            // 2. Modification du Rôle
            $nouveauRole = $request->request->get('role');
            $user->setRoles($nouveauRole === 'ROLE_ADMIN' ? ['ROLE_ADMIN', 'ROLE_USER'] : ['ROLE_USER']);

            // 3. Modification du Mot de passe (seulement si le champ n'est pas vide)
            $plainPassword = $request->request->get('password');
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();
            return $this->redirectToRoute('admin_utilisateurs');
        }

        return $this->render('admin.utilisateur.modifier.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/utilisateurs/supprimer', name: 'admin_utilisateurs_supprimer')]
    public function supprimerUtilisateurAction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $id = $request->query->get('id');
        $user = $entityManager->getRepository(User::class)->find($id);

        // Sécurité : on vérifie que l'utilisateur existe et qu'on ne se supprime pas soi-même
        if ($user && $user !== $this->getUser()) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_utilisateurs');
    }

	#[Route('/admin/produits', name: 'admin_produits')]
    public function listeProduitsAction(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager->getRepository(Article::class)->findAll();

        $nbProduitsDifferents = count($produits);
        $produitsARestock = [];
        $valeurTotaleStock = 0;

        foreach ($produits as $p) {
            // Calcul de la valeur totale (Prix * Quantité en stock)
            $valeurTotaleStock += ($p->getPrix() * $p->getDisponibilite());

            // Identification des produits à restocker (stock = 0)
            if ($p->getDisponibilite() <= 0) {
                $produitsARestock[] = $p;
            }
        }

        return $this->render('admin.produits.html.twig', [
            'produits' => $produits,
            'nbProduitsDifferents' => $nbProduitsDifferents,
            'produitsARestock' => $produitsARestock,
            'valeurTotaleStock' => $valeurTotaleStock,
        ]);
    }
}
