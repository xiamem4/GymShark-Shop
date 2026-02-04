<?php
namespace App\DataFixtures;

use Psr\Log\LoggerInterface;

class Spotify{
	//private $api_endpoint = "https://api.spotify.com/v1" ;
	private string $api_search_endpoint = "https://la-mmi-ac.univ-lemans.fr/spotify/v1" ;
	// https://developer.spotify.com/dashboard
    private string $clientId = "";
	private string $clientSecret = "" ;
	private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger){
		$this->logger = $logger;
    }
	
    /**
     * Search albums by artist
     *
     * @param string $artist
     *            
     * @return array $albums
     */
    public function searchAlbumsByArtist(string $artist): array
	{
		$url = $this->api_search_endpoint . "/search?q=artist:" . urlencode($artist) . "&type=album";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false) ;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		
		curl_close($ch);
		
		$results = json_decode($response) ;
		
		$albums = array() ;
		if(! isset($results->error)) { // {"error": {"status": 400, "message": "Query exceeds maximum length of 250 characters" } }
			if(isset($results->albums)) {
				foreach($results->albums->items as $item) {
					$album = new \stdClass();
					$album->id = $item->id ;
					$album->name = $item->name ;
					$albums[] = $album ;
				}
			}
		}

		return $albums;
	}
	
	/**
     * Search tracks by album
     *
     * @param string $idAlbum
     *            
     * @return array $tracks
     */
	public function searchTracksByAlbum(string $idAlbum): array
	{
		$url = $this->api_search_endpoint . "/albums/" . urlencode($idAlbum) . "/tracks?market=FR";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false) ;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		curl_close($ch);
		
		$results = json_decode($response) ;
				
		$tracks = array() ;
		if(! isset($results->error)) { // {"error": {"status": 400, "message": "Query exceeds maximum length of 250 characters" } }
			foreach($results->items as $item) {
				$track = new \stdClass();
				$track->name = $item->name ;
				$track->preview_url = $item->preview_url ;
				$tracks[] = $track ;
			}
		}

		return $tracks;
	}
}
?>