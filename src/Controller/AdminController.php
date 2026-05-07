<?php

namespace App\Controller;

use App\Entity\Catalogue\Article;
use App\Entity\User;

use App\Entity\Commande\Commande; 
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
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

        return new Response('Succès ! Les stocks de ' . $compteur . ' articles ont été modifiés de manière aléatoire.');
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

    #[Route('/admin/commandes', name: 'admin_commandes')]
    public function listeCommandes(EntityManagerInterface $em): Response
    {
        // On récupère toutes les commandes, idéalement de la plus récente à la plus ancienne
        $commandes = $em->getRepository(Commande::class)->findBy([], ['dateCreation' => 'DESC']);

        return $this->render('admin/commandes.html.twig', [
            'commandes' => $commandes,
        ]);
    }
}
