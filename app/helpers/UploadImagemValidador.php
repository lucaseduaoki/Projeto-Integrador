<?php

namespace app\helpers;

use Exception;
use finfo;

/**
 * Valida um arquivo de imagem recebido via $_FILES antes de ele ser salvo em
 * qualquer lugar (disco local, S3, R2...). Não move nem grava nada — só valida
 * e devolve os dados normalizados do arquivo.
 */
final class UploadImagemValidador
{
    private const TIPOS_PERMITIDOS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
    ];

    /**
     * @param array $arquivo Uma entrada de $_FILES (ex.: $_FILES['foto_perfil'])
     * @return array{tmp: string, mime: string, extensao: string}
     */
    public static function validar(array $arquivo): array
    {
        $erroUpload = $arquivo['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($erroUpload !== UPLOAD_ERR_OK) {
            throw new Exception('Não foi possível enviar a imagem. Tente novamente.');
        }

        $temporario = $arquivo['tmp_name'] ?? '';

        // Garante que o arquivo veio mesmo do mecanismo de upload do PHP, não de um
        // caminho arbitrário forjado no payload (ex.: /etc/passwd).
        if (!is_uploaded_file($temporario)) {
            throw new Exception('Arquivo de imagem inválido.');
        }

        // MIME detectado pelo conteúdo real do arquivo, não pela extensão ou pelo
        // Content-Type enviado pelo navegador (ambos podem ser forjados).
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporario);

        if (!isset(self::TIPOS_PERMITIDOS[$mime])) {
            throw new Exception('Formato inválido. Envie uma imagem JPG, PNG ou GIF.');
        }

        $dimensoes = @getimagesize($temporario);

        if ($dimensoes === false) {
            throw new Exception('Formato inválido. Envie uma imagem JPG, PNG ou GIF.');
        }

        // Sem GD não dá para recodificar a imagem; barra o polyglot mais comum (imagem válida com PHP anexado).
        // Só "<?php": "<?" sozinho aparece por acaso em dados binários de imagens legítimas.
        if (stripos((string)file_get_contents($temporario), '<?php') !== false) {
            throw new Exception('Formato inválido. Envie uma imagem JPG, PNG ou GIF.');
        }

        return [
            'tmp' => $temporario,
            'mime' => $mime,
            'extensao' => self::TIPOS_PERMITIDOS[$mime],
        ];
    }
}
