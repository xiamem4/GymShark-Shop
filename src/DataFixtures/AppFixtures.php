<?php

namespace App\DataFixtures;

use App\Entity\Catalogue\Vetement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

class AppFixtures extends Fixture
{
	protected $logger;

	public function __construct(?LoggerInterface $logger = null)
	{
		$this->logger = $logger;
	}

	public function load(ObjectManager $manager): void
	{
		if (count($manager->getRepository('App\Entity\Catalogue\Article')->findAll()) == 0) {
			$ebay = new Ebay($this->logger);
			$ebay->setCategory('Vêtements');
			$keywords = 'GymShark';

			$itemSummaries = $ebay->searchItemSummaries($keywords, 200);

			if ($itemSummaries !== false) {
				foreach ($itemSummaries as $itemSummary) {
					$id = explode('|', $itemSummary['itemId'])[1];

					if ($ebay->categoryInCategories('Vêtements', $itemSummary['categories']) &&
							stripos($itemSummary['title'], 'gymshark') !== false) {
						$vetement = new Vetement();
						$vetement->setId((int) $id);
						$vetement->setTitre($itemSummary['title']);
						$vetement->setMarque('Gymshark');
						$vetement->setPrix((float) $itemSummary['price']['value']);
						$vetement->setDisponibilite(1);

						// 1. Image principale et images supplémentaires
						if (isset($itemSummary['image']['imageUrl'])) {
							$vetement->setImage($itemSummary['image']['imageUrl']);
						}
						if (isset($itemSummary['additionalImages'])) {
							$additionalUrls = array_map(fn($img) => $img['imageUrl'], $itemSummary['additionalImages']);
							$vetement->setImagesSupplementaires($additionalUrls);
						}

						// 2. Lieu d'expédition
						$lieu = [];

						// 2a. On essaie d'abord de choper la ville
						if (!empty($itemSummary['itemLocation']['city'])) {
							$lieu[] = $itemSummary['itemLocation']['city'];
						}
						// 2b. Sinon, on essaie la région / province
						elseif (!empty($itemSummary['itemLocation']['stateOrProvince'])) {
							$lieu[] = $itemSummary['itemLocation']['stateOrProvince'];
						}

						// 2c. On ajoute le code postal s'il existe
						if (!empty($itemSummary['itemLocation']['postalCode'])) {
							$lieu[] = $itemSummary['itemLocation']['postalCode'];
						}

						// 2d. On ajoute le pays (avec une traduction des codes les plus courants)
						if (!empty($itemSummary['itemLocation']['country'])) {
							$codePays = $itemSummary['itemLocation']['country'];

							// Tableau de traduction des codes pays ISO
							$nomsPays = [
								'FR' => 'France',
								'GB' => 'Royaume-Uni',
								'US' => 'États-Unis',
								'DE' => 'Allemagne',
								'IT' => 'Italie',
								'ES' => 'Espagne',
								'BE' => 'Belgique',
								'CH' => 'Suisse'
							];

							// Si le code est dans notre tableau, on met le nom complet, sinon on garde le code
							$lieu[] = $nomsPays[$codePays] ?? $codePays;
						}

						// On assemble le tout séparé par des virgules (ex: "Paris, 75000, France")
						if (!empty($lieu)) {
							$vetement->setLieuExpedition(implode(', ', $lieu));
						}

						// 3. Taille et autres détails (Gestion du bilinguisme eBay)
						$taille = $ebay->getItem('Taille', $id);
						$vetement->setTaille(empty($taille) ? $ebay->getItem('Size', $id) : $taille);

						$couleur = $ebay->getItem('Couleur', $id);
						$vetement->setCouleur(empty($couleur) ? $ebay->getItem('Color', $id) : $couleur);

						$genre = $ebay->getItem('Département', $id);
						$vetement->setGenre(empty($genre) ? $ebay->getItem('Department', $id) : $genre);

						$matiere = $ebay->getItem('Matière', $id);
						$vetement->setMatiere(empty($matiere) ? $ebay->getItem('Material', $id) : $matiere);

						// 4. Estimation livraison (Formatage de la date)
						if (isset($itemSummary['shippingOptions'][0]['maxEstimatedDeliveryDate'])) {
							$dateBrute = $itemSummary['shippingOptions'][0]['maxEstimatedDeliveryDate'];
							try {
								$dateObj = new \DateTime($dateBrute);
								$dateFormatee = $dateObj->format('d/m/Y');
								$vetement->setDelaiLivraison($dateFormatee);
							} catch (\Exception $e) {
								// En cas d'erreur de parsing, on met la date brute
								$vetement->setDelaiLivraison($dateBrute);
							}
						}

						$manager->persist($vetement);
					}
				}
				$manager->flush();
			}
		}
	}

	public function extractWords(string $text): array
	{
		$text = mb_strtolower($text);
		$text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
		$words = preg_split('/\s+/', trim($text));
		return $words;
	}
}
