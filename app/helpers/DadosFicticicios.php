<?php

namespace app\helpers;

/**
 * Dados fictícios para preenchimento rápido em formulários durante apresentação.
 *
 * REMOVÍVEL: deletar este arquivo e remover os botões "Preencher Exemplo" das views.
 */
class DadosFicticicios
{
    // ========================================================================
    // DADOS DE VAGA
    // ========================================================================

    public static function vagaExemplo(): array
    {
        return [
            'titulo' => 'Garçom para Evento Corporativo',
            'descricao' => 'Necessário profissional experiente para atuar em evento corporativo. Uniforme fornecido, treinamento no local. Experiência com atendimento premium é um diferencial.',
            'id_categoria' => '2',
            'bairro' => 'Centro',
            'localizacao' => 'Rua das Flores, 456 - Centro de Eventos',
            'remuneracao' => '350,00',
            'data_servico' => date('Y-m-d', strtotime('+5 days')),
            'horario' => '18:00',
            'duracao' => '5 horas',
            'trabalhadores_limite' => '2',
            'data_limite' => date('Y-m-d', strtotime('+3 days')),
            'observacoes' => 'Preferencialmente com experiência em eventos corporativos. Uniforme e materiais fornecidos.'
        ];
    }

    // ========================================================================
    // DADOS DE CADASTRO (PESSOA FÍSICA - TRABALHADOR)
    // ========================================================================

    public static function cadastroPFTrabalhador(): array
    {
        return [
            'nome' => 'João Silva Santos',
            'email' => 'joao.silva.' . time() . '@example.com',
            'senha' => 'Senha@123',
            'confirma_senha' => 'Senha@123',
            'documento' => '123.456.789-10',
            'tipo_pessoa' => 'PF',
            'telefone' => '(46) 99999-0001',
            'bairro' => 'Centro',
            'descricao' => 'Profissional com experiência em atendimento ao público. Responsável, pontual e comprometido com qualidade do trabalho.',
            'papeis' => ['is_trabalhador']
        ];
    }

    // ========================================================================
    // DADOS DE CADASTRO (PESSOA JURÍDICA - CONTRATANTE)
    // ========================================================================

    public static function cadastroPJContratante(): array
    {
        return [
            'nome' => 'Empresa Exemplo LTDA',
            'email' => 'contato.empresa.' . time() . '@example.com',
            'senha' => 'Senha@123',
            'confirma_senha' => 'Senha@123',
            'documento' => '12.345.678/0001-90',
            'tipo_pessoa' => 'PJ',
            'razao_social' => 'Empresa Exemplo Serviços LTDA',
            'nome_fantasia' => 'Empresa Exemplo',
            'nome_responsavel' => 'Maria das Flores',
            'telefone' => '(46) 3333-0001',
            'bairro' => 'Centro',
            'descricao' => 'Empresa especializada em eventos e serviços corporativos. Mais de 10 anos no mercado.',
            'papeis' => ['is_contratante']
        ];
    }

    // ========================================================================
    // DADOS DE EDIÇÃO DE PERFIL (TRABALHADOR)
    // ========================================================================

    public static function perfilTrabalhador(): array
    {
        return [
            'nome' => 'João Silva Santos Atualizado',
            'telefone' => '(46) 98888-0001',
            'bairro' => 'Vila Central',
            'descricao' => 'Profissional experiente em atendimento, garçom, cozinha e eventos. Disponível para trabalhos pontuais. Responsável e comprometido.'
        ];
    }

    // ========================================================================
    // DADOS DE EDIÇÃO DE PERFIL (CONTRATANTE)
    // ========================================================================

    public static function perfilContratante(): array
    {
        return [
            'nome' => 'Restaurante Sabor Real',
            'razao_social' => 'Sabor Real Gastronomia LTDA',
            'nome_fantasia' => 'Sabor Real',
            'telefone' => '(46) 3333-0001',
            'bairro' => 'Centro Histórico',
            'descricao' => 'Restaurante de culinária regional com mais de 15 anos de tradição. Buscamos profissionais responsáveis e experientes.'
        ];
    }

    // ========================================================================
    // DADOS DE DENÚNCIA
    // ========================================================================

    public static function denunciaExemplo(): array
    {
        return [
            'motivo' => 'Conteúdo impróprio',
            'descricao' => 'A descrição da vaga contém informações inconsistentes com o que foi acordado anteriormente. Solicitamos revisão.'
        ];
    }
}
