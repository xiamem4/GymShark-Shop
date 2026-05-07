<?php

namespace App\DataFixtures;

use App\Entity\Catalogue\Article;
use App\Entity\User;
use App\Entity\Catalogue\Vetement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private ?LoggerInterface $logger;

    public function __construct(UserPasswordHasherInterface $passwordHasher, ?LoggerInterface $logger = null)
    {
        $this->passwordHasher = $passwordHasher;
        $this->logger = $logger;
    }

    public function load(ObjectManager $manager): void
    {
        // On ne charge les données que si la table est vide
        if (count($manager->getRepository('App\Entity\Catalogue\Article')->findAll()) == 0) {
            $ebay = new Ebay($this->logger);
            $ebay->setCategory('Vêtements');
            $keywords = 'GymShark';

            // Récupération de la liste simplifiée (Recherche)
            $itemSummaries = $ebay->searchItemSummaries($keywords, 200);

            if ($itemSummaries !== false) {
                foreach ($itemSummaries as $itemSummary) {
                    $id = explode('|', $itemSummary['itemId'])[1];

                    // Filtrage pour ne garder que les vêtements Gymshark
                    if ($ebay->categoryInCategories('Vêtements', $itemSummary['categories']) &&
                            stripos($itemSummary['title'], 'gymshark') !== false) {
                        $vetement = new Vetement();
                        $vetement->setId((int) $id);
                        $vetement->setTitre($itemSummary['title']);
                        $vetement->setMarque('Gymshark');
                        $vetement->setPrix((float) $itemSummary['price']['value']);
                        $vetement->setDisponibilite(rand(1, 10));  // Stock aléatoire pour la démo

                        // --- 1. GESTION DES IMAGES ---
                        if (isset($itemSummary['image']['imageUrl'])) {
                            $vetement->setImage($itemSummary['image']['imageUrl']);
                        }
                        if (isset($itemSummary['additionalImages'])) {
                            $additionalUrls = array_map(fn($img) => $img['imageUrl'], $itemSummary['additionalImages']);
                            $vetement->setImagesSupplementaires($additionalUrls);
                        }

                        // --- 2. RÉCUPÉRATION DES DÉTAILS COMPLETS (Ville, Taille, etc.) ---
                        // On utilise getItemFullDetails pour être sûr d'avoir la ville ('city')
                        $details = $ebay->getItemFullDetails($id);

                        if ($details) {
                            // A. Localisation : "Ville (Dép) - Pays"
                            $loc = $details['itemLocation'] ?? [];
                            $ville = $loc['city'] ?? $loc['stateOrProvince'] ?? '';
                            $cp = $loc['postalCode'] ?? '';
                            $pays = $loc['country'] ?? '';

                            $dept = !empty($cp) ? substr($cp, 0, 2) : '';

                            $lieuFinal = '';
                            if (!empty($ville)) {
                                $lieuFinal = $ville;
                                if (!empty($dept)) {
                                    $lieuFinal .= ' (' . $dept . ')';
                                }
                            } elseif (!empty($dept)) {
                                $lieuFinal = 'Département ' . $dept;
                            }

                            if (!empty($pays)) {
                                $lieuFinal .= (!empty($lieuFinal) ? ' - ' : '') . $pays;
                            }
                            $vetement->setLieuExpedition($lieuFinal);

                            // B. Caractéristiques (Taille, Couleur, etc.)
                            if (isset($details['localizedAspects'])) {
                                foreach ($details['localizedAspects'] as $aspect) {
                                    $name = strtolower($aspect['name']);
                                    if ($name === 'taille' || $name === 'size') {
                                        $vetement->setTaille($aspect['value']);
                                    }
                                    if ($name === 'couleur' || $name === 'color') {
                                        $vetement->setCouleur($aspect['value']);
                                    }
                                    if ($name === 'matière' || $name === 'material') {
                                        $vetement->setMatiere($aspect['value']);
                                    }
                                }
                            }

                            // C. Délai de livraison estimé
                            if (isset($itemSummary['shippingOptions'][0]['maxEstimatedDeliveryDate'])) {
                                $dateBrute = $itemSummary['shippingOptions'][0]['maxEstimatedDeliveryDate'];
                                try {
                                    $dateObj = new \DateTime($dateBrute);
                                    $vetement->setDelaiLivraison($dateObj->format('d/m/Y'));
                                } catch (\Exception $e) {
                                    $vetement->setDelaiLivraison($dateBrute);
                                }
                            }
                        }

                        $manager->persist($vetement);
                    }
                }
                $existingUser = $manager->getRepository(User::class)->findOneBy(['email' => 'admin@gmail.com']);

                if (!$existingUser) {
                    $admin = new User();
                    $admin->setEmail('admin@gmail.com');
                    $admin->setNomUtilisateur('Administrateur'); 
                    $admin->setPrenom('Admin');
                    $admin->setNom('GymShark');
                    $admin->setRoles(['ROLE_ADMIN']);

                    $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin0');
                    $admin->setPassword($hashedPassword);

                    $manager->persist($admin);
                }
                $manager->flush();
            }
        }
    }
}
