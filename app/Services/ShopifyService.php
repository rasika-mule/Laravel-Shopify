<?php

namespace App\Services;

use GuzzleHttp\Client;

class ShopifyService
{
    protected $client;
    protected $storeDomain;
    protected $apiKey;

    public function __construct()
    {
        $this->storeDomain = config('services.shopify.store_domain');
        $this->apiKey = config('services.shopify.api_key');

        // For GraphQL:
        $this->client = new Client(
            [
                'base_uri' => "https://{$this->storeDomain}/admin/api/2023-04/graphql.json",
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Shopify-Access-Token' => $this->apiKey,
                ]
            ]
        );
    }

    public function createProduct(array $productInput)
    {
        $mutation = <<<'GRAPHQL'
            mutation productCreate($input: ProductInput!) {
              productCreate(input: $input) {
                product {
                  id
                  handle
                  variants(first: 10) {
                    edges {
                      node {
                        id
                        sku
                      }
                    }
                  }
                }
                userErrors {
                  field
                  message
                }
              }
            }
        GRAPHQL;

        $response = $this->client->post('', [
            'json' => [
                'query' => $mutation,
                'variables' => [
                    'input' => $productInput
                ]
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function updateProduct(array $productInput)
    {
        $mutation = <<<'GRAPHQL'
            mutation productUpdate($input: ProductInput!) {
              productUpdate(input: $input) {
                product {
                  id
                  handle
                  variants(first: 10) {
                    edges {
                      node {
                        id
                        sku
                      }
                    }
                  }
                }
                userErrors {
                  field
                  message
                }
              }
            }
        GRAPHQL;

        $response = $this->client->post('', [
            'json' => [
                'query' => $mutation,
                'variables' => [
                    'input' => $productInput,
                ]
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function uploadProductMedia(string $shopifyProductGid, array $mediaItems)
    {
        $mutation = <<<'GRAPHQL'
        mutation productCreateMedia($productId: ID!, $media: [CreateMediaInput!]!) {
          productCreateMedia(productId: $productId, media: $media) {
            media {
              ... on MediaImage {
                id
                alt
                image {
                  originalSrc
                }
              }
            }
            mediaUserErrors {
              field
              message
            }
          }
        }
    GRAPHQL;

        $variables = [
            'productId' => $shopifyProductGid,
            'media' => $mediaItems,
        ];

        $response = $this->client->post('', [
            'json' => [
                'query' => $mutation,
                'variables' => $variables,
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
}
