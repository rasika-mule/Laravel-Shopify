<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'handle',
        'title',
        'body_html',
        'vendor',
        'product_type',
        'tags',
        'published',
        'variant_sku',
        'variant_price',
        'variant_compare_at_price',
        'variant_requires_shipping',
        'variant_taxable',
        'variant_inventory_tracker',
        'variant_inventory_qty',
        'variant_inventory_policy',
        'variant_fulfillment_service',
        'variant_weight',
        'variant_weight_unit',
        'image_src',
        'image_position',
        'image_alt_text',
        'shopify_product_id'
    ];
}
