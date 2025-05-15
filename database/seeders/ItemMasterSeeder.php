<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemMasterSeeder extends Seeder
{
    public function run()
    {
        $items = [];

        // Food Items with multilingual names
        $foodCategories = [
            'Dairy' => [
                ['en' => 'Milk', 'si' => 'කිරි', 'ta' => 'பால்', 'hi' => 'दूध'],
                ['en' => 'Cheese', 'si' => 'පැණිරස්', 'ta' => 'சீஸ்', 'hi' => 'पनीर'],
                ['en' => 'Yogurt', 'si' => 'දහි', 'ta' => 'தயிர்', 'hi' => 'दही'],
                ['en' => 'Butter', 'si' => 'බටර්', 'ta' => 'வெண்ணெய்', 'hi' => 'मक्खन'],
                ['en' => 'Cream', 'si' => 'ක්රීම්', 'ta' => 'கிரீம்', 'hi' => 'क्रीम']
            ],
            'Produce' => [
                ['en' => 'Apples', 'si' => 'ඇපල්', 'ta' => 'ஆப்பிள்', 'hi' => 'सेब'],
                ['en' => 'Bananas', 'si' => 'කෙසෙල්', 'ta' => 'வாழைப்பழம்', 'hi' => 'केले'],
                ['en' => 'Lettuce', 'si' => 'ලෙටිස්', 'ta' => 'லெட்டஸ்', 'hi' => 'लेटिष'],
                ['en' => 'Tomatoes', 'si' => 'තක්කාලි', 'ta' => 'தக்காளி', 'hi' => 'टमाटर'],
                ['en' => 'Carrots', 'si' => 'කැරට්', 'ta' => 'கேரட்', 'hi' => 'गाजर']
            ],
            'Prepared Foods' => [
                ['en' => 'Lamprais', 'si' => 'ලම්ප්රයිස්', 'ta' => 'லம்ப்ரைஸ்', 'hi' => 'लम्प्राइस'],
                ['en' => 'Fried Rice', 'si' => 'බැදපු බත්', 'ta' => 'வறுத்த அரிசி', 'hi' => 'फ्राइड राइस'],
                ['en' => 'Chicken Kottu', 'si' => 'කොත්තු කුකුල්', 'ta' => 'சிக்கன் கோத்து', 'hi' => 'चिकन कोट्टू'],
                ['en' => 'Egg Kottu', 'si' => 'කොත්තු බිත්තර', 'ta' => 'முட்டை கோத்து', 'hi' => 'अंडा कोट्टू'],
                ['en' => 'Vegetable Kottu', 'si' => 'කොත්තු එළවළු', 'ta' => 'காய்கறி கோத்து', 'hi' => 'सब्जी कोट्टू'],
                ['en' => 'Chicken Fried Rice', 'si' => 'කුකුල් බැදපු බත්', 'ta' => 'சிக்கன் வறுத்த அரிசி', 'hi' => 'चिकन फ्राइड राइस'],
                ['en' => 'Egg Fried Rice', 'si' => 'බිත්තර බැදපු බත්', 'ta' => 'முட்டை வறுத்த அரிசி', 'hi' => 'अंडा फ्राइड राइस']
            ],
            'Beverages' => [
                ['en' => 'Tea', 'si' => 'තේ', 'ta' => 'தேநீர்', 'hi' => 'चाय'],
                ['en' => 'Coffee', 'si' => 'කෝපි', 'ta' => 'காபி', 'hi' => 'कॉफी'],
                ['en' => 'Coca-Cola', 'si' => 'කොකා-කෝලා', 'ta' => 'கோகா-கோலா', 'hi' => 'कोका-कोला'],
                ['en' => 'Sprite', 'si' => 'ස්ප්රයිට්', 'ta' => 'ஸ்ப்ரைட்', 'hi' => 'स्प्राइट'],
                ['en' => 'Bottled Water', 'si' => 'බෝතල් ජලය', 'ta' => 'பாட்டில் தண்ணீர்', 'hi' => 'बोतलबंद पानी'],
                ['en' => 'Fresh Juice', 'si' => 'නැතිපහ සුවඳ', 'ta' => 'புதிய சாறு', 'hi' => 'ताजा रस']
            ],
            'Bakery' => [
                ['en' => 'Bun', 'si' => 'බන්', 'ta' => 'அப்பம்', 'hi' => 'बन'],
                ['en' => 'Plain Tea Bun', 'si' => 'සාදා තේ බන්', 'ta' => 'வெற்று தேநீர் அப்பம்', 'hi' => 'सादा चाय बन'],
                ['en' => 'Chocolate Bun', 'si' => 'චොකලට් බන්', 'ta' => 'சாக்லேட் அப்பம்', 'hi' => 'चॉकलेट बन'],
                ['en' => 'Biscuits', 'si' => 'බිස්කට්', 'ta' => 'பிஸ்கட்', 'hi' => 'बिस्कुट'],
                ['en' => 'Cake', 'si' => 'කේක්', 'ta' => 'கேக்', 'hi' => 'केक'],
                ['en' => 'Pastries', 'si' => 'පේස්ට්රි', 'ta' => 'பேஸ்ட்ரி', 'hi' => 'पेस्ट्री'],
                ['en' => 'Roti', 'si' => 'රොටි', 'ta' => 'ரொட்டி', 'hi' => 'रोटी']
            ],
            'Spices' => [
                ['en' => 'Chili Powder', 'si' => 'මිරිස් කුඩු', 'ta' => 'மிளகு தூள்', 'hi' => 'मिर्च पाउडर'],
                ['en' => 'Turmeric', 'si' => 'කහ', 'ta' => 'மஞ்சள்', 'hi' => 'हल्दी'],
                ['en' => 'Cumin', 'si' => 'සුදු', 'ta' => 'சீரகம்', 'hi' => 'जीरा'],
                ['en' => 'Cinnamon', 'si' => 'කුරුඳු', 'ta' => 'இலவங்கப்பட்டை', 'hi' => 'दालचीनी']
            ],
            'Meat' => [
                ['en' => 'Chicken', 'si' => 'කුකුල් මස්', 'ta' => 'சிக்கன்', 'hi' => 'चिकन'],
                ['en' => 'Beef', 'si' => 'ගව මස්', 'ta' => 'மாட்டிறைச்சி', 'hi' => 'गोमांस'],
                ['en' => 'Fish', 'si' => 'මාළු', 'ta' => 'மீன்', 'hi' => 'मछली'],
                ['en' => 'Prawns', 'si' => 'ඉස්සෝ', 'ta' => 'இரால்', 'hi' => 'झींगे']
            ],
            'Ready-to-Eat' => [
                ['en' => 'Sandwich', 'si' => 'සැන්ඩ්විච්', 'ta' => 'சாண்ட்விச்', 'hi' => 'सैंडविच'],
                ['en' => 'Salad', 'si' => 'සලාද', 'ta' => 'சாலட்', 'hi' => 'सलाद'],
                ['en' => 'Soup', 'si' => 'සුප්', 'ta' => 'சூப்', 'hi' => 'सूप'],
                ['en' => 'Pizza', 'si' => 'පිසා', 'ta' => 'பீட்சா', 'hi' => 'पिज़्ज़ा']
            ]
        ];

        foreach ($foodCategories as $category => $products) {
            foreach ($products as $product) {
                $items[] = [
                    'name' => $product['en'], // English name as primary
                    'sku' => 'F-' . strtoupper(substr($category, 0, 3)) . '-' . rand(1000, 9999),
                    'type' => 'food',
                    'reorder_level' => $this->getReorderLevel($category),
                    'organization_id' => rand(1, 5),
                    'branch_id' => rand(1, 5),
                    'attributes' => json_encode([
                        'category' => $category,
                        'shelf_life' => $this->getFoodShelfLife($category),
                        'supplier_id' => rand(1, 10),
                        'unit' => $this->getFoodUnit($product['en'], $category),
                        'is_prepared' => in_array($category, ['Prepared Foods', 'Bakery', 'Ready-to-Eat']),
                        'is_beverage' => $category === 'Beverages',
                        'is_ingredient' => in_array($category, ['Dairy', 'Produce', 'Spices', 'Meat']),
                        'name_translations' => [
                            'si' => $product['si'], // Sinhala
                            'ta' => $product['ta'], // Tamil
                            'hi' => $product['hi']  // Hindi
                        ],
                        'buy_price' => $this->getBuyPrice($category, $product['en']),
                        'sell_price' => $this->getSellPrice($category, $product['en'])
                    ]),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        // Inventory Items with multilingual names
        $inventoryCategories = [
            'Cleaning' => [
                ['en' => 'Bleach', 'si' => 'බ්ලීච්', 'ta' => 'ப்ளீச்', 'hi' => 'ब्लीच'],
                ['en' => 'Disinfectant Wipes', 'si' => 'ජීරණ මැණික්කන්', 'ta' => 'கிருமிநாசினி துடைப்பான்', 'hi' => 'कीटाणुनाशक पोंछे'],
                ['en' => 'Glass Cleaner', 'si' => 'කැඩපත් පිරිසිදු කාරක', 'ta' => 'கண்ணாடி துப்புரவு', 'hi' => 'ग्लास क्लीनर'],
                ['en' => 'Trash Bags', 'si' => 'කුණු බෑග්', 'ta' => 'குப்பை பைகள்', 'hi' => 'कचरा बैग'],
                ['en' => 'Paper Towels', 'si' => 'කඩදාසි තුවාය', 'ta' => 'காகித துண்டுகள்', 'hi' => 'पेपर टॉवल']
            ],
            'Office' => [
                ['en' => 'Pens', 'si' => 'පෑන', 'ta' => 'பேனா', 'hi' => 'कलम'],
                ['en' => 'Notepads', 'si' => 'සටහන් පොත්', 'ta' => 'குறிப்பேடுகள்', 'hi' => 'नोटपैड'],
                ['en' => 'Stapler', 'si' => 'ස්ටේප්ලර්', 'ta' => 'ஸ்டேப்லர்', 'hi' => 'स्टेपलर'],
                ['en' => 'Printer Paper', 'si' => 'මුද්‍රණ තිරුව', 'ta' => 'அச்சு காகிதம்', 'hi' => 'प्रिंटर पेपर'],
                ['en' => 'Folders', 'si' => 'ෆෝල්ඩර්', 'ta' => 'அடைவான்கள்', 'hi' => 'फ़ोल्डर्स']
            ]
        ];

        foreach ($inventoryCategories as $category => $products) {
            foreach ($products as $product) {
                $items[] = [
                    'name' => $product['en'],
                    'sku' => 'INV-' . strtoupper(substr($category, 0, 3)) . '-' . rand(1000, 9999),
                    'type' => 'inventory',
                    'reorder_level' => rand(3, 15),
                    'organization_id' => rand(1, 5),
                    'branch_id' => rand(1, 5),
                    'attributes' => json_encode([
                        'category' => $category,
                        'supplier_id' => rand(1, 10),
                        'location' => $this->getInventoryLocation($category),
                        'name_translations' => [
                            'si' => $product['si'],
                            'ta' => $product['ta'],
                            'hi' => $product['hi']
                        ]
                    ]),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        // Other Items with multilingual names
        $otherCategories = [
            'Miscellaneous' => [
                ['en' => 'Extension Cord', 'si' => 'විස්තීරණ කේබල්', 'ta' => 'நீட்டிப்பு கம்பி', 'hi' => 'एक्सटेंशन कॉर्ड'],
                ['en' => 'Light Bulbs', 'si' => 'ආලෝක බල්බ', 'ta' => 'ஒளி விளக்குகள்', 'hi' => 'लाइट बल्ब'],
                ['en' => 'Batteries', 'si' => 'බැටරි', 'ta' => 'பேட்டரிகள்', 'hi' => 'बैटरी'],
                ['en' => 'Tool Kit', 'si' => 'මෙවලම් කට්ටලය', 'ta' => 'கருவி கிட்', 'hi' => 'टूल किट'],
                ['en' => 'Step Ladder', 'si' => 'පියවර ගෙල්ල', 'ta' => 'படி ஏணி', 'hi' => 'सीढ़ी']
            ],
            'Decor' => [
                ['en' => 'Picture Frames', 'si' => 'ඡායාරූප රාමු', 'ta' => 'பட சட்டங்கள்', 'hi' => 'तस्वीर फ्रेम'],
                ['en' => 'Plants', 'si' => 'ශාක', 'ta' => 'செடிகள்', 'hi' => 'पौधे'],
                ['en' => 'Wall Art', 'si' => 'බිත්ති කලාව', 'ta' => 'சுவர் கலை', 'hi' => 'दीवार कला'],
                ['en' => 'Vases', 'si' => 'මල්දම්', 'ta' => 'வாசுகள்', 'hi' => 'फूलदान'],
                ['en' => 'Candles', 'si' => 'මිංචිල්', 'ta' => 'மெழுகுவர்த்திகள்', 'hi' => 'मोमबत्तियाँ']
            ]
        ];

        foreach ($otherCategories as $category => $products) {
            foreach ($products as $product) {
                $items[] = [
                    'name' => $product['en'],
                    'sku' => 'OTH-' . strtoupper(substr($category, 0, 3)) . '-' . rand(1000, 9999),
                    'type' => 'other',
                    'reorder_level' => rand(1, 10),
                    'organization_id' => rand(1, 5),
                    'branch_id' => rand(1, 5),
                    'attributes' => json_encode([
                        'category' => $category,
                        'supplier_id' => rand(1, 10),
                        'condition' => ['New', 'Used', 'Refurbished'][rand(0, 2)],
                        'name_translations' => [
                            'si' => $product['si'],
                            'ta' => $product['ta'],
                            'hi' => $product['hi']
                        ]
                    ]),
                    'is_active' => rand(0, 1) == 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        // Insert all items in batches
        foreach (array_chunk($items, 50) as $chunk) {
            DB::table('item_master')->insert($chunk);
        }
    }

    private function getReorderLevel($category)
    {
        return match ($category) {
            'Dairy', 'Beverages' => rand(10, 30),
            'Produce' => rand(5, 15),
            'Prepared Foods', 'Bakery', 'Ready-to-Eat' => rand(3, 10),
            'Meat' => rand(5, 20),
            default => rand(5, 15)
        };
    }

    private function getFoodShelfLife($category)
    {
        return match ($category) {
            'Dairy' => '7 days',
            'Produce' => '5-14 days',
            'Meat' => '3-5 days',
            'Bakery' => '3-7 days',
            'Frozen' => '6-12 months',
            default => '7 days'
        };
    }

    private function getFoodUnit($product, $category)
    {
        if (in_array($category, ['Beverages'])) {
            return in_array($product, ['Tea', 'Coffee']) ? 'cup' : 'bottle';
        }
        if (in_array($category, ['Prepared Foods', 'Ready-to-Eat'])) {
            return 'portion';
        }
        if (in_array($category, ['Bakery'])) {
            return in_array($product, ['Biscuits']) ? 'packet' : 'piece';
        }
        if (in_array($product, ['Milk', 'Cream', 'Yogurt', 'Fresh Juice'])) {
            return 'liter';
        }
        if (in_array($product, ['Cheese', 'Butter', 'Meat', 'Fish', 'Prawns'])) {
            return 'kg';
        }
        if (in_array($product, ['Apples', 'Bananas', 'Tomatoes', 'Carrots'])) {
            return 'dozen';
        }
        return 'unit';
    }

    private function getInventoryLocation($category)
    {
        return match ($category) {
            'Cleaning' => 'Storage Room',
            'Office' => 'Front Desk',
            'Kitchen' => 'Kitchen Storage',
            'Packaging' => 'Packaging Area',
            'Safety' => 'First Aid Station',
            default => 'Main Storage'
        };
    }

    private function getBuyPrice($category, $product)
    {
        $basePrice = match ($category) {
            'Dairy' => rand(100, 300) / 10,
            'Produce' => rand(50, 200) / 10,
            'Prepared Foods' => rand(150, 400) / 10,
            'Beverages' => in_array($product, ['Tea', 'Coffee']) ? rand(20, 50) / 10 : rand(80, 150) / 10,
            'Bakery' => rand(30, 120) / 10,
            'Meat' => rand(200, 600) / 10,
            'Ready-to-Eat' => rand(200, 500) / 10,
            default => rand(50, 200) / 10
        };
        return round($basePrice, 2);
    }

    private function getSellPrice($category, $product)
    {
        $buyPrice = $this->getBuyPrice($category, $product);
        $markup = match ($category) {
            'Dairy' => 1.3,
            'Produce' => 1.4,
            'Prepared Foods' => 1.8,
            'Beverages' => 2.0,
            'Bakery' => 1.7,
            'Meat' => 1.5,
            'Ready-to-Eat' => 2.2,
            default => 1.5
        };
        return round($buyPrice * $markup, 2);
    }
}
