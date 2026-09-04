<?php

it('mantém os textos apresentados ao usuário em português do Brasil', function () {
    $projectRoot = dirname(__DIR__, 2);
    $directories = [
        $projectRoot.'/app',
        $projectRoot.'/database/seeders',
        $projectRoot.'/resources/js',
        $projectRoot.'/resources/views',
    ];

    $incorrectPhrases = [
        'Area do cliente',
        'Avaliacao enviada',
        'Avaliacoes (',
        'Carregar mais avaliacoes',
        'Cartao (',
        'CEP invalido',
        'Cliente nao pertence',
        'Endereco de entrega',
        'Endereco padrao',
        'Endereco removido',
        'Endereco salvo',
        'Falha ao remover endereco',
        'Falha ao salvar endereco',
        'Formato invalido',
        'Nao autenticado',
        'Nenhum produto disponivel',
        'Observacoes do pedido',
        'Ola, tenho interesse',
        'Pedido de demonstracao',
        'Plano Basico',
        'Preco:',
        'Sao Paulo',
        'Selecione um endereco',
        'Token de cliente invalido',
        'trial gratis',
        'Voce precisa',
        'Atualizacao do pedido',
    ];

    $occurrences = [];

    foreach ($directories as $directory) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($files as $file) {
            if (! $file->isFile() || ! in_array($file->getExtension(), ['php', 'ts', 'vue'], true)) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            foreach ($incorrectPhrases as $phrase) {
                if (str_contains($contents, $phrase)) {
                    $occurrences[] = str_replace($projectRoot.'/', '', $file->getPathname()).': '.$phrase;
                }
            }
        }
    }

    expect($occurrences)->toBeEmpty();
});
