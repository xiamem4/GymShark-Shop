<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use GuzzleHttp\Client;

use App\Entity\Catalogue\Vetement;

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
		if (count($manager->getRepository("App\Entity\Catalogue\Article")->findAll()) == 0) {
			$ebay = new Ebay($this->logger);
			$ebay->setCategory('Vêtements');
			$keywords = 'GymShark';

			$itemSummaries = $ebay->searchItemSummaries($keywords, 200);

			if ($itemSummaries !== false) {
				foreach ($itemSummaries as $itemSummary) {
					$id = explode("|", $itemSummary["itemId"])[1];
					if (
						$ebay->categoryInCategories('Vêtements', $itemSummary["categories"]) &&
						stripos($itemSummary["title"], 'gymshark') !== false
					) {
						$vetement = new Vetement();
						$vetement->setId((int) $id);
						$vetement->setTitre($itemSummary["title"]);
						$vetement->setMarque('Gymshark');
						$vetement->setPrix((float) $itemSummary["price"]["value"]);
						$vetement->setDisponibilite(1);

						if (isset($itemSummary["image"]["imageUrl"])) {
							$vetement->setImage($itemSummary["image"]["imageUrl"]);
						}

						$vetement->setTaille(
							$ebay->getItem("Size", $id)
						);

						$vetement->setCouleur(
							$ebay->getItem("Color", $id)
						);

						$vetement->setGenre(
							$ebay->getItem("Department", $id)
						);

						$manager->persist($vetement);
					}
				}
			}
			$manager->flush();
		}
	}

	public function extractWords(string $text): array
	{
		$text = mb_strtolower($text); // Convert to lowercase
		$text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);  // Remove punctuation
		$words = preg_split('/\s+/', trim($text)); // Split into words

		return $words;
	}
}
