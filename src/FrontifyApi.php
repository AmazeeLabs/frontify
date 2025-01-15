<?php declare(strict_types = 1);

namespace Drupal\frontify;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Http\Client\ClientInterface;

/**
 * Executes Frontify GraphQL API queries.
 */
final class FrontifyApi {

  use StringTranslationTrait;

  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly MessengerInterface $messenger,
  ) {}

  public function frontifyQuery(string $query): array {
    // Default is a readonly token for Frontify API. Safe to use in code.
    // If more capabilities are needed, a separate token should be created.
    $token = $this->configFactory->get('frontify.settings')->get('frontify_api_token');
    if (empty($token)) {
      $this->messenger->addError('Frontify API token is not set.');
    }

    $apiUrl = $this->configFactory->get('frontify.settings')->get('frontify_api_url');
    if (empty($apiUrl)) {
      $this->messenger->addError('Frontify API url is not set.');
    }

    $result = [];
    try {
      $response = $this->httpClient->post($apiUrl . '/graphql', [
        'headers' => [
          'Authorization' => 'Bearer ' . $token,
          'Content-Type' => 'application/json',
        ],
        'json' => [
          'query' => $query,
        ],
      ]);
      $result = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }
    catch (\Exception $e) {
      $this->messenger->addError('Error: @message', ['@message' => $e->getMessage()]);
    }

    return $result;
  }

  /**
   * GraphQL query to get custom metadata for a Frontify asset.
   */
  public function getCustomMetaData(string $frontify_asset_id): array {
    try {
      $query = 'query GetCustomMetadata {
        asset(id: "' . $frontify_asset_id . '") {
          id
          customMetadata {
            ... on CustomMetadataValue {
              property {
                id
                name
              }
              value
            }
          }
        }
      }';

      $queryResult = $this->frontifyQuery($query);
      if (!empty($queryResult['data']['asset']['customMetadata'])) {
        return $queryResult['data']['asset']['customMetadata'];
      }
    } catch (\Exception $e) {
      $this->messenger->addError('Error: @message', ['@message' => $e->getMessage()]);
    }

    return [];
  }

}
