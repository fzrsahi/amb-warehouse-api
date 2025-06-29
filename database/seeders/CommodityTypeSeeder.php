<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CommodityType;

class CommodityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $commodityTypes = [
            'Electronics',
            'Textiles & Clothing',
            'Machinery & Equipment',
            'Automotive Parts',
            'Pharmaceuticals',
            'Food & Beverages',
            'Chemicals',
            'Construction Materials',
            'Agricultural Products',
            'Furniture & Home Goods',
            'Jewelry & Precious Metals',
            'Books & Publications',
            'Sports Equipment',
            'Medical Supplies',
            'Cosmetics & Personal Care',
            'Industrial Raw Materials',
            'Plastics & Rubber',
            'Metals & Alloys',
            'Paper & Packaging',
            'Tools & Hardware',
        ];

        foreach ($commodityTypes as $type) {
            CommodityType::firstOrCreate([
                'name' => $type
            ]);
        }
    }
}
