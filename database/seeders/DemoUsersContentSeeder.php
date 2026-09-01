<?php

namespace Database\Seeders;

use App\Enums\BillingPeriod;
use App\Enums\SubscriptionStatus;
use App\Models\Category;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoUsersContentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->orderBy('id')->take(2)->get();
        $bannerImageColumn = null;
        foreach (['image_url', 'image', 'image_base64'] as $columnName) {
            if (Schema::hasColumn('banners', $columnName)) {
                $bannerImageColumn = $columnName;
                break;
            }
        }

        if ($users->isEmpty()) {
            $this->command?->warn('Nenhum usuário encontrado para popular dados demo.');
            return;
        }

        $basicPlan = Plan::where('code', 'basic')->first();
        $mediumPlan = Plan::where('code', 'medium')->first();

        foreach ($users as $index => $user) {
            $plan = $index === 0 ? ($mediumPlan ?? $basicPlan) : ($basicPlan ?? $mediumPlan);
            if (!$plan) {
                continue;
            }

            $billingPeriod = $index === 0 ? BillingPeriod::ANNUAL->value : BillingPeriod::MONTHLY->value;
            $price = $billingPeriod === BillingPeriod::ANNUAL->value
                ? (float) $plan->annual_monthly_equivalent
                : (float) $plan->monthly_price;

            $subscription = Subscription::firstOrNew(['user_id' => $user->id]);
            $subscription->fill([
                'plan_id' => $plan->id,
                'plan' => is_object($plan->code) ? $plan->code->value : (string) $plan->code,
                'price' => $price,
                'billing_period' => $billingPeriod,
                'status' => SubscriptionStatus::ACTIVE->value,
                'trial_starts_at' => now()->subDays(20),
                'trial_ends_at' => now()->subDays(6),
                'next_billing_at' => $billingPeriod === BillingPeriod::ANNUAL->value
                    ? now()->addMonths(10)
                    : now()->addDays(22),
                'current_period_starts_at' => now()->subDays(8),
                'current_period_ends_at' => $billingPeriod === BillingPeriod::ANNUAL->value
                    ? now()->addMonths(10)
                    : now()->addDays(22),
                'payment_method' => 'pix',
            ]);
            $subscription->save();

            $user->update([
                'plan' => $subscription->plan,
                'is_active' => true,
                'business_name' => $user->business_name ?: 'Loja Teste '.$user->id,
                'slug' => $user->slug ?: 'loja-teste-'.$user->id,
                'description' => $user->description ?: 'Conta de demonstração para visualizar o comportamento do painel.',
                'whatsapp' => $user->whatsapp ?: '+55249990000'.str_pad((string) $user->id, 2, '0', STR_PAD_LEFT),
            ]);

            Page::ensureDefaultPages($user->id);

            $linksPage = Page::where('user_id', $user->id)->where('key', 'links')->first();
            if ($linksPage) {
                $linksPage->update([
                    'is_active' => true,
                    'content' => json_encode([
                        ['icon' => 'Instagram', 'url' => 'https://instagram.com/vitrine.top', 'text' => 'Instagram oficial', 'showText' => true],
                        ['icon' => 'MessageCircle', 'url' => 'https://wa.me/5524999699849', 'text' => 'Fale no WhatsApp', 'showText' => true],
                    ]),
                ]);
            }

            $aboutPage = Page::where('user_id', $user->id)->where('key', 'sobre')->first();
            if ($aboutPage) {
                $aboutPage->update([
                    'is_active' => true,
                    'content' => 'Somos uma loja demo para validar o comportamento da plataforma com dados reais de teste.',
                ]);
            }

            $categories = collect([
                "Destaques {$user->id}",
                "Promoções {$user->id}",
                "Novidades {$user->id}",
            ])->map(function (string $name, int $order) use ($user) {
                return Category::firstOrCreate(
                    ['user_id' => $user->id, 'name' => $name],
                    ['order' => $order, 'is_active' => true]
                );
            });

            $productNames = [
                'Kit Premium',
                'Produto Essencial',
                'Combo Econômico',
                'Linha Profissional',
                'Lançamento do Mês',
                'Oferta Especial',
            ];

            $products = collect($productNames)->map(function (string $baseName, int $idx) use ($user, $categories) {
                $name = "{$baseName} U{$user->id}";
                $category = $categories[$idx % $categories->count()];

                return Product::firstOrCreate(
                    ['user_id' => $user->id, 'name' => $name],
                    [
                        'category_id' => $category->id,
                        'description' => 'Produto de demonstração para preenchimento visual.',
                        'price' => 29.9 + ($idx * 6),
                        'discount_price' => 24.9 + ($idx * 5),
                        'stock' => 30 + ($idx * 5),
                        'is_public' => true,
                        'featured' => $idx % 2 === 0,
                        'allow_whatsapp' => true,
                    ]
                );
            });

            foreach ($products as $idx => $product) {
                ProductImage::firstOrCreate([
                    'product_id' => $product->id,
                    'image_path' => "https://picsum.photos/seed/u{$user->id}p{$idx}a/1200/900",
                ], [
                    'is_cover' => true,
                ]);

                ProductImage::firstOrCreate([
                    'product_id' => $product->id,
                    'image_path' => "https://picsum.photos/seed/u{$user->id}p{$idx}b/1200/900",
                ], [
                    'is_cover' => false,
                ]);
            }

            $reviewPayload = [
                ['Carla Mendes', 5, 'Atendimento excelente e entrega super rápida!'],
                ['Rafael Souza', 4, 'Gostei bastante da qualidade, recomendo.'],
                ['Amanda Torres', 5, 'Produto veio impecável, comprarei novamente.'],
                ['Bruno Lima', 4, 'Ótimo custo-benefício para o que entrega.'],
                ['Fernanda Alves', 5, 'Experiência muito boa do início ao fim.'],
            ];

            foreach ($reviewPayload as $idx => [$customer, $rating, $comment]) {
                Review::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'product_id' => $products[$idx % $products->count()]->id,
                        'customer_name' => "{$customer} U{$user->id}",
                    ],
                    [
                        'whatsapp' => '+55249980000'.str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT),
                        'rating' => $rating,
                        'comment' => $comment,
                        'status' => 'approved',
                    ]
                );
            }

            if ($bannerImageColumn) {
                for ($i = 1; $i <= 3; $i++) {
                    $bannerImage = "https://picsum.photos/seed/banner-u{$user->id}-{$i}/1500/400";
                    DB::table('banners')->updateOrInsert(
                        [
                            'user_id' => $user->id,
                            $bannerImageColumn => $bannerImage,
                        ],
                        [
                            'title' => "Banner {$i}",
                            'subtitle' => 'Banner de demonstração',
                            $bannerImageColumn => $bannerImage,
                            'order' => $i,
                            'is_active' => true,
                        ]
                    );
                }
            }

            $paymentRows = [
                ['demo-u'.$user->id.'-pay-1', 'pix', 'paid', 79.90, now()->subMonths(4), now()->subMonths(4)],
                ['demo-u'.$user->id.'-pay-2', 'credit_card', 'paid', 79.90, now()->subMonths(3), now()->subMonths(3)],
                ['demo-u'.$user->id.'-pay-3', 'pix', 'paid', 79.90, now()->subMonths(2), now()->subMonths(2)],
                ['demo-u'.$user->id.'-pay-4', 'boleto', 'pending', 79.90, now()->subDays(20), null],
                ['demo-u'.$user->id.'-pay-5', 'credit_card', 'failed', 79.90, now()->subDays(8), null],
            ];

            foreach ($paymentRows as [$transactionId, $method, $status, $amount, $createdAt, $paidAt]) {
                Payment::updateOrCreate(
                    ['transaction_id' => $transactionId],
                    [
                        'user_id' => $user->id,
                        'subscription_id' => $subscription->id,
                        'amount' => $amount,
                        'currency' => 'BRL',
                        'method' => $method,
                        'status' => $status,
                        'details' => ['seeded_demo' => true, 'source' => 'DemoUsersContentSeeder'],
                        'paid_at' => $paidAt,
                        'created_at' => $createdAt,
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command?->info('Dados demo populados para até 2 usuários.');
    }
}
