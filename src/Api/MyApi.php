<?php

namespace Drupal\my_api_module\Api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Http\ClientInterface;
use Drupal\http_client_extender\Plugin\Discovery\HttpClientExtenderDiscovery;

/**
 * Defines a service class for interacting with the My API.
 */
class MyApi {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The HTTP client.
   *
   * @var \Drupal\Core\Http\ClientInterface
   */
  protected $httpClient;

  /**
   * The HTTP client extender discovery service.
   *
   * @var \Drupal\http_client_extender\Plugin\Discovery\HttpClientExtenderDiscovery
   */
  protected $httpClientExtenderDiscovery;

  /**
   * Constructs a new MyApi object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Http\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\http_client_extender\Plugin\Discovery\HttpClientExtenderDiscovery $http_client_extender_discovery
   *   The HTTP client extender discovery service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ClientInterface $http_client, HttpClientExtenderDiscovery $http_client_extender_discovery) {
    $this->configFactory = $config_factory;
    $this->httpClient = $http_client;
    $this->httpClientExtenderDiscovery = $http_client_extender_discovery;
  }

  /**
   * Fetches data from the My API endpoint.
   *
   * @param string $endpoint
   *   The API endpoint to make the request to.
   *
   * @return array|null
   *   The decoded API response data or null on failure.
   */
  public function fetchData($endpoint) {
    $config = $this->configFactory->get('my_api_module.settings');
    $api_url = $config->get('api_url');
    $api_key = $config->get('api_key');

    // Build the request options with authentication (if applicable)
    $request_options = [
      'headers' => [
        'Authorization' => 'Bearer ' . $api_key, // Replace with your authentication method
      ],
    ];

    // Apply any configured HTTP client extenders for customization
    $this->httpClientExtenderDiscovery->alterRequestOptions($request_options, $endpoint);

    try {
      $response = $this->httpClient->get($api_url . $endpoint, $request_options);
      if ($response->getStatusCode() === 200) {
        $data = json_decode($response->getBody(), TRUE);
        return $data;
      }
    } catch (\Exception $e) {
      // Handle potential exceptions during the request
      watchdog_error('my_api_module', 'Error fetching data from My API: {message}', ['@message' => $e->getMessage()]);
    }

    return null;
  }
}
