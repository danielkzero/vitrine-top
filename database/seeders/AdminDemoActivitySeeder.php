<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\CrmNote;
use App\Models\CrmProfile;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminDemoActivitySeeder extends Seeder
{
    public function run(): void
    {
        foreach (AdminDemoClientsSeeder::CLIENTS as $index => $clientData) {
            $user = User::where('email', "cliente.demo{$index}@vitrine.test")->firstOrFail();
            Page::ensureDefaultPages($user->id);

            $category = Category::firstOrCreate(
                ['user_id' => $user->id, 'slug' => 'produtos-demo'],
                ['name' => 'Produtos de demonstração', 'order' => 1, 'is_active' => true],
            );

            $productCount = 4 + ($index * 2);
            for ($productIndex = 1; $productIndex <= $productCount; $productIndex++) {
                $product = Product::firstOrCreate(
                    ['user_id' => $user->id, 'code' => "DEMO-{$index}-{$productIndex}"],
                    [
                        'category_id' => $category->id,
                        'name' => "Produto demonstrativo {$productIndex}",
                        'description' => 'Produto fictício para representar o consumo de recursos da conta.',
                        'price' => 19.90 + ($productIndex * 3),
                        'stock' => 20,
                        'is_public' => true,
                        'featured' => $productIndex <= 2,
                        'allow_whatsapp' => true,
                    ],
                );

                for ($imageIndex = 1; $imageIndex <= (($productIndex % 3) + 1); $imageIndex++) {
                    ProductImage::firstOrCreate(
                        ['product_id' => $product->id, 'image_path' => "https://picsum.photos/seed/admin-{$index}-{$productIndex}-{$imageIndex}/800/800"],
                        ['is_cover' => $imageIndex === 1],
                    );
                }
            }

            for ($bannerIndex = 1; $bannerIndex <= min(3, $index + 1); $bannerIndex++) {
                Banner::updateOrCreate(
                    ['user_id' => $user->id, 'title' => "Campanha demo {$bannerIndex}"],
                    [
                        'subtitle' => 'Banner fictício para o painel administrativo',
                        'image_url' => "https://picsum.photos/seed/admin-banner-{$index}-{$bannerIndex}/1500/400",
                        'order' => $bannerIndex,
                        'is_active' => true,
                    ],
                );
            }

            $subscription = $user->subscription;
            $paymentStates = $index % 4 === 0 ? ['paid', 'paid', 'pending'] : ($index % 4 === 1 ? ['paid', 'pending'] : ($index % 4 === 2 ? ['paid', 'failed'] : ['paid', 'paid', 'refunded']));
            foreach ($paymentStates as $paymentIndex => $paymentStatus) {
                $paidAt = $paymentStatus === 'paid' ? now()->subMonths(count($paymentStates) - $paymentIndex) : null;
                Payment::updateOrCreate(
                    ['transaction_id' => "admin-demo-{$index}-{$paymentIndex}"],
                    [
                        'user_id' => $user->id,
                        'subscription_id' => $subscription?->id,
                        'amount' => $subscription?->price ?? 29.90,
                        'currency' => 'BRL',
                        'method' => $paymentIndex % 2 === 0 ? 'pix' : 'credit_card',
                        'status' => $paymentStatus,
                        'details' => ['seeded_demo' => true, 'source' => self::class],
                        'paid_at' => $paidAt,
                        'refunded_at' => $paymentStatus === 'refunded' ? now()->subDays(3) : null,
                        'created_at' => now()->subDays(($paymentIndex + 1) * 12),
                    ],
                );
            }

            $health = $index === 3 || $index === 4 ? 'risk' : ($index === 6 ? 'attention' : 'good');
            CrmProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'health' => $health,
                    'score' => $health === 'good' ? 85 - $index : ($health === 'attention' ? 58 : 32 + $index),
                    'tags' => array_values(array_filter([$index === 7 ? 'VIP' : null, $index % 2 === 0 ? 'WhatsApp' : 'E-mail', $health === 'risk' ? 'Risco financeiro' : 'Engajado'])),
                    'summary' => $health === 'risk'
                        ? 'Cliente exige acompanhamento financeiro e contato proativo antes de novas cobranças.'
                        : 'Cliente receptivo, utiliza bem a plataforma e costuma responder rapidamente.',
                    'last_contact_at' => now()->subDays($index + 1),
                    'next_follow_up_at' => now()->addDays($index + 2),
                ],
            );

            $notePayloads = [
                ['whatsapp', 'Contato inicial realizado. Cliente confirmou que conseguiu configurar a vitrine.'],
                ['support', $health === 'risk' ? 'Relatou dificuldade com a última cobrança; acompanhar antes do vencimento.' : 'Orientação enviada sobre cadastro e organização dos produtos.'],
                ['call', 'Retorno feito para confirmar se ainda havia dúvidas. Atendimento concluído com sucesso.'],
            ];
            foreach ($notePayloads as $noteIndex => [$type, $content]) {
                CrmNote::updateOrCreate(
                    ['user_id' => $user->id, 'content' => $content],
                    ['author_id' => User::where('is_admin', true)->value('id'), 'type' => $type, 'occurred_at' => now()->subDays(15 - ($noteIndex * 5) + $index)],
                );
            }

            $ticket = SupportTicket::updateOrCreate(
                ['number' => 'TKT-DEMO-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'user_id' => $user->id,
                    'assigned_to' => User::where('is_admin', true)->value('id'),
                    'subject' => $index % 3 === 0 ? 'Dúvida sobre pagamento da assinatura' : ($index % 3 === 1 ? 'Ajuda para cadastrar produtos' : 'Sugestão para a página da loja'),
                    'category' => $index % 3 === 0 ? 'billing' : ($index % 3 === 1 ? 'technical' : 'suggestion'),
                    'priority' => $health === 'risk' ? 'high' : 'normal',
                    'status' => $index % 4 === 0 ? 'resolved' : ($index % 4 === 1 ? 'waiting_customer' : 'in_progress'),
                    'last_activity_at' => now()->subDays($index % 3),
                    'resolved_at' => $index % 4 === 0 ? now()->subDays(1) : null,
                ],
            );
            $ticket->messages()->updateOrCreate(
                ['message' => 'Olá, preciso de ajuda com este assunto. Poderiam me orientar?'],
                ['author_id' => $user->id, 'is_internal' => false, 'created_at' => now()->subDays(4)],
            );
            $ticket->messages()->updateOrCreate(
                ['message' => 'Claro! Analisamos sua conta e enviamos abaixo as orientações para resolver a solicitação.'],
                ['author_id' => User::where('is_admin', true)->value('id'), 'is_internal' => false, 'created_at' => now()->subDays(3)],
            );
        }

        $this->command?->info('Produtos, imagens, banners e pagamentos fictícios foram populados.');
    }
}
