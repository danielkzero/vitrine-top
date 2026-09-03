<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Page;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class MinhaLojinhaContentSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('slug', 'minha-lojinha')->first();

        if (! $user) {
            $this->command?->warn('A vitrine "Minha Lojinha" não foi encontrada. Execute MinhaLojinhaSeeder primeiro.');

            return;
        }

        $user->update([
            'whatsapp' => '5524999699849',
            'description' => 'Achadinhos escolhidos com carinho para facilitar seu dia a dia. Produtos úteis, bons preços, atendimento próximo e novidades para deixar sua casa ainda mais especial.',
        ]);

        $this->seedPages($user);
        $this->seedReviews($user);
        $this->seedBanners($user);

        $this->command?->info('Conteúdo institucional, avaliações e banners da demonstração criados com sucesso.');
    }

    private function seedPages(User $user): void
    {
        Page::query()->updateOrCreate(
            ['user_id' => $user->id, 'key' => 'sobre'],
            [
                'title' => 'Sobre a loja',
                'icon' => 'BadgeInfo',
                'is_active' => true,
                'order' => 2,
                'type' => 'simple',
                'content' => <<<'HTML'
                    <h2>Um cantinho feito para você</h2>
                    <p>A Minha Lojinha nasceu para reunir produtos úteis, delicados e selecionados com cuidado. Nosso objetivo é oferecer uma compra simples, segura e com atendimento humano.</p>
                    <h3>Como comprar</h3>
                    <p>Escolha seus produtos, adicione ao carrinho e finalize o pedido. Se preferir, fale conosco pelo WhatsApp para tirar dúvidas sobre disponibilidade, pagamento e entrega.</p>
                    <h3>Atendimento</h3>
                    <p>Respondemos de segunda a sexta-feira, das 9h às 18h. Esta é uma loja fictícia criada para demonstrar os recursos da plataforma Vitrine Top.</p>
                    HTML,
                'seo_title' => 'Sobre a Minha Lojinha',
                'seo_description' => 'Conheça a loja demonstrativa e veja como apresentar sua marca na Vitrine Top.',
            ],
        );

        Page::query()->updateOrCreate(
            ['user_id' => $user->id, 'key' => 'links'],
            [
                'title' => 'Fale conosco',
                'icon' => 'Link',
                'is_active' => true,
                'order' => 3,
                'type' => 'links',
                'content' => json_encode([
                    ['icon' => 'MessageCircle', 'url' => 'https://wa.me/5524999699849', 'text' => 'Atendimento pelo WhatsApp', 'showText' => true],
                    ['icon' => 'Instagram', 'url' => 'https://instagram.com/vitrine.top', 'text' => 'Acompanhe no Instagram', 'showText' => true],
                    ['icon' => 'Globe', 'url' => 'https://vitrinetop.hydradigital.com.br', 'text' => 'Conheça a Vitrine Top', 'showText' => true],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'seo_title' => 'Contato e redes sociais',
                'seo_description' => 'Canais de atendimento e redes sociais da loja.',
            ],
        );

        Page::query()->updateOrCreate(
            ['user_id' => $user->id, 'key' => 'avaliacoes'],
            [
                'title' => 'Avaliações',
                'icon' => 'Star',
                'is_active' => true,
                'order' => 4,
                'type' => 'reviews',
                'content' => '',
                'seo_title' => 'Avaliações de clientes',
                'seo_description' => 'Confira as avaliações e notas dos clientes da Minha Lojinha.',
            ],
        );

        Page::query()->updateOrCreate(
            ['user_id' => $user->id, 'key' => 'extra'],
            [
                'title' => 'Termos e Privacidade',
                'icon' => 'ShieldCheck',
                'is_active' => true,
                'order' => 5,
                'type' => 'simple',
                'content' => <<<'HTML'
                    <h2>Termos de uso e privacidade</h2>
                    <p><strong>Última atualização:</strong> setembro de 2026.</p>
                    <p>Esta página demonstra como uma loja pode informar suas condições de uso e o tratamento de dados pessoais na plataforma Vitrine Top. Ao enviar seus dados, o cliente declara que leu e concorda com estas condições.</p>
                    <h3>Dados coletados</h3>
                    <p>Podemos coletar nome, e-mail, telefone, endereço de entrega, itens do carrinho e informações necessárias ao atendimento e à realização do pedido.</p>
                    <h3>Como os dados são utilizados</h3>
                    <p>Os dados são usados para identificar o cliente, processar e acompanhar pedidos, prestar suporte, prevenir fraudes e cumprir obrigações legais. Comunicações promocionais somente devem ser enviadas quando houver consentimento.</p>
                    <h3>Compartilhamento e armazenamento</h3>
                    <p>As informações podem ser tratadas por serviços essenciais à operação, como hospedagem, pagamento, entrega e atendimento, sempre dentro da finalidade informada. Os dados devem ser mantidos somente pelo período necessário ou exigido por lei.</p>
                    <h3>Direitos do titular</h3>
                    <p>O cliente pode solicitar confirmação do tratamento, acesso, correção, portabilidade ou exclusão dos seus dados, observadas as hipóteses legais de conservação.</p>
                    <h3>Contato</h3>
                    <p>Para dúvidas ou solicitações relacionadas à privacidade, utilize os canais exibidos na página “Fale conosco”. Em uma loja real, este texto deve ser revisado e adaptado aos dados do responsável pelo negócio.</p>
                    HTML,
                'seo_title' => 'Termos de Uso e Política de Privacidade',
                'seo_description' => 'Condições de uso e informações sobre o tratamento de dados pessoais.',
            ],
        );

        Page::query()->where('user_id', $user->id)->where('key', 'catalogo')->update(['order' => 1]);
    }

    private function seedReviews(User $user): void
    {
        $products = Product::query()->where('user_id', $user->id)->orderBy('id')->get();

        if ($products->isEmpty()) {
            return;
        }

        $reviews = [
            ['Mariana Costa', 5, 'Produto lindo e muito bem embalado. Chegou exatamente como nas fotos.'],
            ['Ana Paula', 5, 'Atendimento atencioso e entrega rápida. Comprarei novamente!'],
            ['Beatriz Souza', 4, 'Gostei muito da qualidade e o pedido chegou em perfeito estado.'],
            ['Camila Martins', 5, 'A experiência de compra foi simples e recebi todas as orientações pelo WhatsApp.'],
            ['Juliana Lima', 4, 'Produto delicado, ótimo acabamento e bom custo-benefício.'],
            ['Renata Alves', 5, 'Minha encomenda veio preparada com muito carinho. Recomendo a loja.'],
            ['Patrícia Gomes', 4, 'Tudo conforme a descrição. O suporte respondeu rapidamente.'],
            ['Larissa Rocha', 5, 'Adorei o produto e já indiquei a loja para minhas amigas.'],
        ];

        foreach ($reviews as $index => [$customerName, $rating, $comment]) {
            Review::query()->updateOrCreate(
                ['user_id' => $user->id, 'customer_name' => $customerName],
                [
                    'product_id' => $products[$index % $products->count()]->id,
                    'whatsapp' => '552499900'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'rating' => $rating,
                    'comment' => $comment,
                    'status' => 'approved',
                ],
            );
        }
    }

    private function seedBanners(User $user): void
    {
        Banner::query()->updateOrCreate(
            ['user_id' => $user->id, 'title' => 'Achadinhos para deixar seu dia mais especial'],
            [
                'subtitle' => 'Conheça os produtos escolhidos com carinho para você.',
                'image_url' => '/storage/backgrounds/Se2BxakocfDuNDCCf0TXiARS7y5dHUNpycN698HG.png',
                'order' => 1,
                'is_active' => true,
            ],
        );
    }
}
