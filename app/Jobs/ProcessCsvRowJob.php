<?php


namespace App\Jobs;

use App\Models\Upload;
use App\Models\ImportLog;
use App\Models\Product;
use App\Services\ShopifyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProcessCsvRowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $uploadId;
    protected $rowData;
    protected $rowNumber;

    public function __construct($uploadId, array $rowData, int $rowNumber)
    {
        $this->uploadId = $uploadId;
        $this->rowData = $rowData;
        $this->rowNumber = $rowNumber;
    }

    public function handle()
    {
        $upload = Upload::find($this->uploadId);
        if (!$upload) {
            return;
        }

        $normalizedData = [];
        foreach ($this->rowData as $key => $value) {
            $withUnderscores = preg_replace('/\s+/', '_', $key);
            $snakeKey = strtolower($withUnderscores);
            $normalizedData[$snakeKey] = $value;
        }
        $this->rowData = $normalizedData;

        // Check if product already exists in local DB by handle
        $handle = $this->rowData['handle'] ?? null;
        $product = Product::where('handle', $handle)->first();

        // If found, update local DB
        if ($product) {
            $product->update(
                [
                    'title' => $this->rowData['title'] ?? $product->title,
                    'body_html' => $this->rowData['body_html'] ?? $product->body_html,
                    'vendor' => $this->rowData['vendor'] ?? $product->vendor,
                    'product_type' => $this->rowData['product_type'] ?? $product->product_type,
                    'tags' => $this->rowData['tags'] ?? $product->tags,

                    'published' => (!empty($this->rowData['published']) && strtoupper(
                            $this->rowData['published']
                        ) === 'TRUE'),

                    'variant_sku' => $this->rowData['variant_sku'] ?? $product->variant_sku,
                    'variant_price' => $this->rowData['variant_price'] ?? $product->variant_price,
                    'variant_compare_price' => $this->rowData['variant_compare_at_price'] ?? $product->variant_compare_price,
                    'variant_requires_shipping' => (!empty($this->rowData['variant_requires_shipping'])
                        && strtoupper($this->rowData['variant_requires_shipping']) === 'TRUE'),
                    'variant_taxable' => (!empty($this->rowData['variant_taxable'])
                        && strtoupper($this->rowData['variant_taxable']) === 'TRUE'),
                    'variant_inventory_tracker' => (!empty($this->rowData['variant_inventory_tracker'])
                        && strtoupper($this->rowData['variant_inventory_tracker']) === 'TRUE'),
                    'variant_inventory_qty' => $this->rowData['variant_inventory_qty'] ?? $product->variant_inventory_qty,
                    'variant_inventory_policy' => $this->rowData['variant_inventory_policy'] ?? $product->variant_inventory_policy,
                    'variant_fulfillment_service' => $this->rowData['variant_fulfillment_service'] ?? $product->variant_fulfillment_service,
                    'variant_weight' => $this->rowData['variant_weight'] ?? $product->variant_weight,
                    'variant_weight_unit' => $this->rowData['variant_weight_unit'] ?? $product->variant_weight_unit,
                    'image_src' => $this->rowData['image_src'] ?? $product->image_src,
                    'image_position' => $this->rowData['image_position'] ?? $product->image_position,
                    'image_alt_text' => $this->rowData['image_alt_text'] ?? $product->image_alt_text,
                ]
            );
        } // Create new local product
        else {
            $product = Product::create(
                [
                    'handle' => $this->rowData['handle'] ?? null,
                    'title' => $this->rowData['title'] ?? null,
                    'body_html' => $this->rowData['body_html'] ?? null,
                    'vendor' => $this->rowData['vendor'] ?? null,
                    'product_type' => $this->rowData['product_type'] ?? null,
                    'tags' => $this->rowData['tags'] ?? null,

                    'published' => (!empty($this->rowData['published']) && strtoupper(
                            $this->rowData['published']
                        ) === 'TRUE'),

                    'variant_sku' => $this->rowData['variant_sku'] ?? null,
                    'variant_price' => $this->rowData['variant_price'] ?? null,
                    'variant_compare_at_price' => $this->rowData['variant_compare_at_price'] ?? null,
                    'variant_requires_shipping' => (!empty($this->rowData['variant_requires_shipping'])
                        && strtoupper($this->rowData['variant_requires_shipping']) === 'TRUE'),
                    'variant_taxable' => (!empty($this->rowData['variant_taxable'])
                        && strtoupper($this->rowData['variant_taxable']) === 'TRUE'),
                    'variant_inventory_tracker' => $this->rowData['variant_inventory_tracker'] ?? null,
                    'variant_inventory_qty' => $this->rowData['variant_inventory_qty'] ?? 0,
                    'variant_inventory_policy' => $this->rowData['variant_inventory_policy'] ?? null,
                    'variant_fulfillment_service' => $this->rowData['variant_fulfillment_service'] ?? null,
                    'variant_weight' => $this->rowData['variant_weight'] ?? null,
                    'variant_weight_unit' => $this->rowData['variant_weight_unit'] ?? null,
                    'image_src' => $this->rowData['image_src'] ?? null,
                    'image_position' => $this->rowData['image_position'] ?? null,
                    'image_alt_text' => $this->rowData['image_alt_text'] ?? null,
                ]
            );
        }

        //  If local product has shopify_product_id => update, else create
        $operation = $product->shopify_product_id ? 'update' : 'create';

        // Create a log record with status=processing
        $log = ImportLog::create(
            [
                'upload_id' => $upload->id,
                'product_id' => $product->id,
                'status' => 'processing',
                'operation' => $operation,
                'message' => "Row #{$this->rowNumber}: Starting {$operation} in Shopify..."
            ]
        );

        $shopifyService = new ShopifyService();

        try {
            // ProductInput for Shopify
            $productInput = [];

            $productInput['handle'] = $product->handle;
            $productInput['title'] = $product->title;
            $productInput['bodyHtml'] = $product->body_html;
            $productInput['vendor'] = $product->vendor;
            $productInput['productType'] = $product->product_type;

            // Tags array
            if ($product->tags) {
                $productInput['tags'] = array_map('trim', explode(',', $product->tags));
            }

            // Published => interpret as "ACTIVE" if true, or "DRAFT" if false
            $productInput['status'] = $product->published ? 'ACTIVE' : 'DRAFT';

            // Single variant data
            $variant = [];
            if ($product->variant_sku) {
                $variant['sku'] = $product->variant_sku;
            }
            if ($product->variant_price) {
                $variant['price'] = (string)$product->variant_price;
            }
            if ($product->variant_compare_price) {
                $variant['compareAtPrice'] = (string)$product->variant_compare_price;
            }
            $variant['requiresShipping'] = $product->variant_requires_shipping;
            $variant['taxable'] = $product->variant_taxable;

            if ($product->variant_inventory_tracker) {
                $variant['inventoryManagement'] = 'SHOPIFY';
            } else {
                $variant['inventoryManagement'] = 'NOT_MANAGED';
            }

            if ($product->variant_inventory_policy) {
                $variant['inventoryPolicy'] = strtoupper($product->variant_inventory_policy);
            }

            if ($product->variant_weight) {
                $variant['weight'] = (float)$product->variant_weight;
            }
            if ($product->variant_weight_unit) {
                $variant['weightUnit'] = $this->convertToFullWeightUnit($product->variant_weight_unit);
            }

            if (!empty($variant)) {
                $productInput['variants'] = [$variant];
            }

            // add collection
            $collectionId = 'gid://shopify/Collection/' . config('services.shopify.collection_id');
            $productInput['collectionsToJoin'] = [$collectionId];

            // Single image assumption right now(media)
            if ($product->image_src) {
                $mediaItem = [
                    'mediaContentType' => 'IMAGE',
                    'originalSource' => $product->image_src,
                ];

                if ($product->image_alt_text) {
                    $mediaItem['alt'] = $product->image_alt_text;
                }
            }

            // Create or Update in Shopify
            if ($operation === 'update') {
                // Update
                $productInput['id'] = $product->shopify_product_id;
                $response = $shopifyService->updateProduct($productInput);

                $userErrors = $response['data']['productUpdate']['userErrors'] ?? [];
                if (!empty($userErrors)) {
                    $messages = array_map(fn($e) => $e['message'] ?? 'Error', $userErrors);
                    throw new \Exception(implode('; ', $messages));
                }

                $log->update(
                    [
                        'status' => 'successful',
                        'message' => "Row #{$this->rowNumber}: Shopify update successful."
                    ]
                );
            } else {
                // Create
                $response = $shopifyService->createProduct($productInput);

                $userErrors = $response['data']['productCreate']['userErrors'] ?? [];
                if (!empty($userErrors)) {
                    $messages = array_map(fn($e) => $e['message'] ?? 'Error', $userErrors);
                    throw new \Exception(implode('; ', $messages));
                }

                $newProd = $response['data']['productCreate']['product'] ?? null;
                $shopifyId = $newProd['id'] ?? null;

                // Store the new ID in local DB
                $product->update(
                    [
                        'shopify_product_id' => $shopifyId
                    ]
                );

                // Upload image
                $shopifyService->uploadProductMedia($shopifyId, [$mediaItem]);

                $log->update(
                    [
                        'status' => 'successful',
                        'message' => "Row #{$this->rowNumber}: Shopify create successful."
                    ]
                );
            }
        } catch (\Exception $e) {
            // If anything fails, mark log as failed with reason
            $log->update(
                [
                    'status' => 'failed',
                    'message' => "Row #{$this->rowNumber} failed: " . $e->getMessage()
                ]
            );
        }

        $upload->increment('processed_rows');
        if ($upload->processed_rows >= $upload->total_rows) {
            $upload->update(['status' => 'completed']);
        }
    }

    function convertToFullWeightUnit($unit)
    {
        $map = [
            'kg' => 'KILOGRAMS',
            'g' => 'GRAMS',
            'lb' => 'POUNDS',
            'oz' => 'OUNCES',
        ];

        $unit = strtolower($unit);

        return $map[$unit];
    }
}
