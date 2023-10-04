<?php

require __DIR__ . '/vendor/autoload.php';

class IntegrationClass {
	
	private $error = "";
	private $success = false;
	private $data = [
		'title' => '{COMPANY}',
		'connected_user' => '{NAME} ({EMAIL})',
		'connection' => [],
		'oauth2' => [],
	];	
	
	//env file
	private $clientId;
	private $clientSecret;
	private $redirectUri;
	
	private $scopeList = 'openid email profile offline_access accounting.transactions accounting.contacts accounting.reports.read';
	
	function __construct() {
		
		//for state token storage between callback!
		if (!isset($_SESSION)) session_start();
		
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
		$dotenv->load();
		$this->clientId = getenv('CLIENT_ID');
		$this->clientSecret = getenv('CLIENT_SECRET');
		$this->redirectUri = getenv('REDIRECT_URI');
    }
	
	function getSuccess() : bool {
		return $this->success;		
	}
	
	function getError() : string {
		return $this->error;
	}	
	
	function callback($callbackData){	

		$provider = $this->getProvider();

		// If we don't have an authorization code then get one
		if (!isset($callbackData['code'])) {			
			$this->error = "NO CODE";
			return;
		}
		
		// Check given state against previously stored one to mitigate CSRF attack
		if (empty($callbackData['state']) || ($callbackData['state'] !== $_SESSION['oauth2state'])) {			
			unset($_SESSION['oauth2state']);		
			$this->error = "Invalid State";
			return;
		}
				
		try {
			
			// Try to get an access token using the authorization code grant.
			$accessToken = $provider->getAccessToken('authorization_code', ['code' => $callbackData['code']]);
		  		
			$jwt = new XeroAPI\XeroPHP\JWTClaims();
			$jwt->setTokenId($accessToken->getValues()["id_token"]);
			$jwt->decode();
			
			//get connected user information!
			$username = trim($jwt->getGivenName() . ' ' . $jwt->getFamilyName());
            $email = $jwt->getEmail();

			$config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken( (string)$accessToken->getToken() );
			$identityInstance = new XeroAPI\XeroPHP\Api\IdentityApi($this->getGuzzle(),$config);
			
			// Get Array of Tenant Ids
			$result = $identityInstance->getConnections();
			if(!isset($result[0])){
				$this->error = "Invalid connection";
				return;
			}
			
			$this->data['title'] = str_replace(['{COMPANY}'],[$result[0]->getTenantName()], $this->data['title']);	
			
			$this->data['connected_user'] = str_replace(['{NAME}','{EMAIL}'],[$username, $email], $this->data['connected_user']);	

			$this->data['connection'] = [
                'id' 	=> $result[0]->getTenantId(),
                'type' 	=> $result[0]->getTenantType(),
                'name' 	=> $result[0]->getTenantName(),
            ];
							
			$this->data['oauth2'] = [
				'token' => $accessToken->getToken(),
				'expires' => $accessToken->getExpires(),
				'tenant_id' => $result[0]->getTenantId(),
				'refresh_token' => $accessToken->getRefreshToken(),
				'id_token' => $accessToken->getValues()["id_token"],
			];
					
			$this->success = true;
	 
		} catch ( \Exception $e ) {
			// Failed to get the access token or user details.
			$this->success = false;				
			$this->error = $e->getMessage();
		}		
	}

	
	function connect(){
		
		$provider = $this->getProvider();

		// If we don't have an authorization code then get one
		if (!isset($_GET['code'])) {

			// Fetch the authorization URL from the provider; this returns the urlAuthorize option and generates and applies any necessary parameters (e.g. state).
			$authorizationUrl = $provider->getAuthorizationUrl(['scope' => [$this->scopeList]]);

			// Get the state generated for you and store it to the session.
			$_SESSION['oauth2state'] = $provider->getState();

			// Redirect the user to the authorization URL.
			header('Location: ' . $authorizationUrl);
			exit();

		// Check given state against previously stored one to mitigate CSRF attack
		} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
			unset($_SESSION['oauth2state']);
			exit('Invalid state');
		} else {

		}
		
	}	
	
	function getTenantId(){
		return $this->data['connection']['id'] ?? "";
	}
	
	function getAccountingInstance(array $data){
	
		$this->data = $data;
		
		
		$provider = $this->getProvider();
		
		//$this->success = true;
		
		//HOW DO WE REFRESH THE TOKEN EXTERNALLY?
		
		$newAccessToken = $provider->getAccessToken('refresh_token', ['refresh_token' => $this->data['oauth2']['refresh_token']]);
		
		//set new data -- caller updates storage :(
		$this->data['oauth2'] = [
			'token' => $newAccessToken->getToken(),
			'expires' => $newAccessToken->getExpires(),
			'tenant_id' => $this->getTenantId(),
			'refresh_token' => $newAccessToken->getRefreshToken(),
			'id_token' => $newAccessToken->getValues()["id_token"],
		];

		$config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken($newAccessToken->getToken());	
		
		
		return new XeroAPI\XeroPHP\Api\AccountingApi($this->getGuzzle(), $config);	
	}
	
	function getInvoices(array $data){
		
		$accountingApi = $this->getAccountingInstance($data);
		
		$result = $accountingApi->getInvoices($this->getTenantId()); 	

		print_r($result);
		
		// READ only ACTIVE
		$where = 'Status=="VOIDED"';
		$result2 = $accountingApi->getInvoices($this->getTenantId(), null, $where); 
		
		print_r($result2);
		
	}
	
	function disconnect(array $data){

		$provider = $this->getProvider();
		
		$newAccessToken = $provider->getAccessToken('refresh_token', ['refresh_token' => $data['oauth2']['refresh_token']]);

		$config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken($newAccessToken->getToken());	
			
		$identityApi = new XeroAPI\XeroPHP\Api\IdentityApi($this->getGuzzle(), $config);
		
		//this actually deletes all linked connections, not a bad thing for now!?
		$connections = $identityApi->getConnections();
		if(!count($connections)){
			$this->success = false;				
			$this->error = "invalid/blank connections";
			return;
		}

		try {			
			
			//delete every listed connection!
			foreach($connections as $conn){
				$identityApi->deleteConnection($conn->getId());
			}
			
		} catch ( \Exception $e ) {
			// Failed to get the access token or user details.
			$this->success = false;				
			$this->error = $e->getMessage();
			return;
		}
		
		$this->success = true;		
	}
		
	private function getGuzzle(){
		return new GuzzleHttp\Client([
			'defaults' => [
			\GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 5,
			 \GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => true],
			 \GuzzleHttp\RequestOptions::VERIFY => false,
		]);
	}
	
	private function getProvider(){
		$provider = new \League\OAuth2\Client\Provider\GenericProvider([
			'clientId'                => $this->clientId,   
			'clientSecret'            => $this->clientSecret,
			'redirectUri'             => $this->redirectUri,
			'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
			'urlAccessToken'          => 'https://identity.xero.com/connect/token',
			'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
		]);
		
		//blah certs not working on windows host
		
		$provider->setHttpClient($this->getGuzzle());
		return $provider;
	}
	
	function getIntegrationArr() : array{				
		return $this->data;	
	}
	
}
