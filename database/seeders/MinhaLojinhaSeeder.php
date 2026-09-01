<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MinhaLojinhaSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'danikzero@hotmail.com')->first();

        if (! $user) {
            $this->command?->warn('Usuário demo não encontrado. Execute o DatabaseSeeder completo.');

            return;
        }

        $assets = [
            'background' => '/storage/backgrounds/Se2BxakocfDuNDCCf0TXiARS7y5dHUNpycN698HG.png',
            'logo' => '/storage/logos/uvmCoA6HCyhF4p90sMcTK3tNvLSUegFnTFYeVuwE.png',
            'caneca' => '/storage/products/6hfF3NyUT4FAaWJevUTrS7xAzgmp8ns80szSD3EV.png',
            'luminaria' => '/storage/products/OpOZhKv277oXz6r3aBxumUjt4svrrUShnvaVP9o0.png',
            'bolsa' => '/storage/products/NBP65qrPS5jhBM31P9Ng6Flno6loQPlcxKOhfVCi.png',
            'vela' => '/storage/products/vyW1nfvebnCFo2y72GSTvWVKxQf5kRzhTRnoJGOq.png',
        ];

        foreach ($assets as $name => $path) {
            if (! File::exists(public_path(ltrim($path, '/')))) {
                $this->command?->warn("Imagem '{$name}' não encontrada em {$path}.");
            }
        }

        $user->update([
            'business_name' => 'Minha Lojinha',
            'subtitle' => 'Bem vindo à minha lojinha',
            'slug' => Str::slug('Minha Lojinha'),
            'description' => 'Achadinhos escolhidos com carinho para facilitar seu dia a dia. Produtos úteis, bons preços e novidades que valem a pena. Gostou de algo? Fale com a gente pelo WhatsApp!',
            'theme_color' => '#e98fa0',
            'logo_path' => $assets['logo'],
            'background_path' => $assets['background'],
            'is_active' => true,
        ]);

        Page::ensureDefaultPages($user->id);
        Page::query()
            ->where('user_id', $user->id)
            ->where('key', 'catalogo')
            ->update([
                'title' => 'Catálogo',
                'is_active' => true,
                'type' => 'products',
                'catalog_mode' => 'store',
            ]);

        $products = [
            [
                'category' => 'Casa & Cozinha',
                'name' => 'Caneca Nuvem Estrelada',
                'price' => 39.90,
                'description' => 'Uma caneca encantadora em formato de nuvem, com pequenas estrelas em tons pastel. Perfeita para deixar cafés, chocolates e momentos de descanso ainda mais fofinhos.',
                'image' => $assets['caneca'],
            ],
            [
                'category' => 'Iluminação',
                'name' => 'Luminária Cogumelo Encantado',
                'price' => 79.90,
                'description' => 'Luminária decorativa em formato de cogumelo com luz suave e aconchegante. Ideal para mesinhas de cabeceira, quartos infantis ou cantinhos de leitura.',
                'image' => $assets['luminaria'],
            ],
            [
                'category' => 'Bolsas & Acessórios',
                'name' => 'Bolsa Lilás Sweet Heart',
                'price' => 119.90,
                'description' => 'Bolsa compacta em tom lilás pastel, com fecho de coração e delicado pingente de laço. Um acessório romântico, moderno e perfeito para o dia a dia.',
                'image' => $assets['bolsa'],
            ],
            [
                'category' => 'Aromas & Bem-estar',
                'name' => 'Vela Aromática Jardim de Algodão',
                'price' => 49.90,
                'description' => 'Vela aromática com fragrância suave de algodão e flores delicadas. Seu recipiente rosado e a tampa floral também ajudam a decorar o ambiente.',
                'image' => $assets['vela'],
            ],
        ];

        foreach ($products as $order => $item) {
            $category = Category::query()->updateOrCreate(
                ['user_id' => $user->id, 'slug' => Str::slug($item['category'])],
                ['name' => $item['category'], 'order' => $order + 1, 'is_active' => true],
            );

            $product = Product::query()->updateOrCreate(
                ['user_id' => $user->id, 'name' => $item['name']],
                [
                    'category_id' => $category->id,
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'discount_price' => null,
                    'stock' => 20,
                    'is_public' => true,
                    'featured' => $order < 2,
                    'allow_whatsapp' => true,
                    'conversion_type' => 'cart',
                    'external_url' => null,
                    'cta_label' => null,
                ],
            );

            $product->images()->delete();
            ProductImage::query()->create([
                'product_id' => $product->id,
                'image_path' => $item['image'],
                'is_cover' => true,
            ]);
        }

        $this->command?->info('Demonstração "Minha Lojinha" criada com sucesso.');
    }
}
