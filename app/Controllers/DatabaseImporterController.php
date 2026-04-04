<?php

namespace App\Controllers;

use App\Core\Database;
use PDO;
use PDOException;

/**
 * Importador de banco de dados para migração de servidor.
 * Roda o schema.sql e todas as migrations em sequência,
 * verificando o que já foi criado para não duplicar.
 *
 * Acesso: GET /importar-banco
 */
class DatabaseImporterController
{
    private PDO $db;
    private array $log = [];
    private int $successCount = 0;
    private int $skipCount = 0;
    private int $errorCount = 0;

    public function index(): void
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        ignore_user_abort(true);

        header('Content-Type: text/html; charset=utf-8');
        ob_implicit_flush(true);
        if (ob_get_level()) {
            ob_end_flush();
        }

        $this->db = Database::getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        echo $this->renderHeader();
        $this->flush();

        // 1. Criar tabela de controle de migrations
        $this->ensureMigrationsTable();

        // 2. Rodar schema.sql (tabelas base)
        $this->runSchemaFile();

        // 3. Rodar migrations da pasta database/migrations
        $this->runMigrationsFromDir('database/migrations');

        // 4. Rodar migrations da pasta migrations (raiz)
        $this->runMigrationsFromDir('migrations');

        echo $this->renderSummary();
        echo $this->renderFooter();
    }

    private function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `_migrations` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL UNIQUE,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->exec($sql);
        $this->addLog('info', 'Tabela de controle _migrations verificada/criada.');
    }

    private function runSchemaFile(): void
    {
        $schemaPath = $this->basePath('database/schema.sql');
        if (!is_file($schemaPath)) {
            $this->addLog('warn', 'Arquivo database/schema.sql não encontrado. Pulando.');
            return;
        }

        $this->addLog('info', '=== Executando schema.sql (tabelas base) ===');

        $sql = file_get_contents($schemaPath);
        $statements = $this->splitStatements($sql);

        foreach ($statements as $stmt) {
            $trimmed = trim($stmt);
            if ($trimmed === '') {
                continue;
            }

            // Detectar nome da tabela em CREATE TABLE
            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"]?(\w+)[`"]?/i', $trimmed, $m)) {
                $tableName = $m[1];
                if ($this->tableExists($tableName)) {
                    $this->addLog('skip', "Tabela <code>{$tableName}</code> já existe. Pulando CREATE.");
                    $this->skipCount++;
                    continue;
                }
            }

            // Detectar INSERT — executar com try/catch para ignorar duplicatas
            $isInsert = (bool) preg_match('/^\s*INSERT\s+/i', $trimmed);

            try {
                $this->db->exec($trimmed);
                $label = mb_substr($trimmed, 0, 80);
                $this->addLog('ok', "Executado: <code>" . htmlspecialchars($label, ENT_QUOTES) . "...</code>");
                $this->successCount++;
            } catch (PDOException $e) {
                if ($isInsert && $this->isDuplicateError($e)) {
                    $this->addLog('skip', 'INSERT ignorado (dados já existem).');
                    $this->skipCount++;
                } else {
                    $this->addLog('error', 'Erro: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES));
                    $this->errorCount++;
                }
            }
        }
    }

    private function runMigrationsFromDir(string $relativeDir): void
    {
        $dir = $this->basePath($relativeDir);
        if (!is_dir($dir)) {
            $this->addLog('warn', "Pasta {$relativeDir} não encontrada. Pulando.");
            return;
        }

        $files = glob($dir . '/*.sql');
        if (!$files) {
            $this->addLog('info', "Nenhuma migration em {$relativeDir}.");
            return;
        }

        sort($files);

        $this->addLog('info', "=== Executando migrations de {$relativeDir} (" . count($files) . " arquivos) ===");

        foreach ($files as $file) {
            $filename = basename($file);
            $migrationKey = $relativeDir . '/' . $filename;

            if ($this->migrationAlreadyRan($migrationKey)) {
                $this->addLog('skip', "Migration <code>{$filename}</code> já executada. Pulando.");
                $this->skipCount++;
                continue;
            }

            $this->addLog('info', "Rodando: <code>{$filename}</code>");
            $this->flush();

            $sql = file_get_contents($file);
            $statements = $this->splitStatements($sql);
            $migrationOk = true;

            foreach ($statements as $stmt) {
                $trimmed = trim($stmt);
                if ($trimmed === '' || $this->isComment($trimmed)) {
                    continue;
                }

                // Verificar CREATE TABLE — pular se tabela já existe
                if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"]?(\w+)[`"]?/i', $trimmed, $m)) {
                    $tableName = $m[1];
                    if ($this->tableExists($tableName)) {
                        $this->addLog('skip', "&nbsp;&nbsp;Tabela <code>{$tableName}</code> já existe.");
                        $this->skipCount++;
                        continue;
                    }
                }

                // Verificar ALTER TABLE ADD COLUMN — pular se coluna já existe
                if (preg_match('/ALTER\s+TABLE\s+[`"]?(\w+)[`"]?\s+ADD\s+(?:COLUMN\s+)?[`"]?(\w+)[`"]?/i', $trimmed, $m)) {
                    $table = $m[1];
                    $column = $m[2];
                    if ($this->columnExists($table, $column)) {
                        $this->addLog('skip', "&nbsp;&nbsp;Coluna <code>{$table}.{$column}</code> já existe.");
                        $this->skipCount++;
                        continue;
                    }
                }

                // Verificar CREATE INDEX — pular se index já existe
                if (preg_match('/CREATE\s+(?:UNIQUE\s+)?INDEX\s+[`"]?(\w+)[`"]?\s+ON\s+[`"]?(\w+)[`"]?/i', $trimmed, $m)) {
                    $indexName = $m[1];
                    $table = $m[2];
                    if ($this->indexExists($table, $indexName)) {
                        $this->addLog('skip', "&nbsp;&nbsp;Index <code>{$indexName}</code> já existe em <code>{$table}</code>.");
                        $this->skipCount++;
                        continue;
                    }
                }

                $isInsert = (bool) preg_match('/^\s*INSERT\s+/i', $trimmed);

                try {
                    $this->db->exec($trimmed);
                    $this->successCount++;
                } catch (PDOException $e) {
                    if ($isInsert && $this->isDuplicateError($e)) {
                        $this->addLog('skip', '&nbsp;&nbsp;INSERT ignorado (duplicata).');
                        $this->skipCount++;
                    } elseif ($this->isAlreadyExistsError($e)) {
                        $this->addLog('skip', '&nbsp;&nbsp;Já existe: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES));
                        $this->skipCount++;
                    } else {
                        $this->addLog('error', '&nbsp;&nbsp;Erro: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES));
                        $this->errorCount++;
                        $migrationOk = false;
                    }
                }
            }

            // Registrar migration como executada
            if ($migrationOk) {
                $this->recordMigration($migrationKey);
                $this->addLog('ok', "Migration <code>{$filename}</code> concluída.");
            } else {
                $this->addLog('warn', "Migration <code>{$filename}</code> teve erros (registrada mesmo assim).");
                $this->recordMigration($migrationKey);
            }

            $this->flush();
        }
    }

    // ── Helpers ──────────────────────────────────────────────

    private function tableExists(string $table): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1");
        $stmt->execute([$table]);
        return (bool) $stmt->fetchColumn();
    }

    private function columnExists(string $table, string $column): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1");
        $stmt->execute([$table, $column]);
        return (bool) $stmt->fetchColumn();
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1");
        $stmt->execute([$table, $indexName]);
        return (bool) $stmt->fetchColumn();
    }

    private function migrationAlreadyRan(string $key): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM `_migrations` WHERE `migration` = ? LIMIT 1");
        $stmt->execute([$key]);
        return (bool) $stmt->fetchColumn();
    }

    private function recordMigration(string $key): void
    {
        $stmt = $this->db->prepare("INSERT IGNORE INTO `_migrations` (`migration`) VALUES (?)");
        $stmt->execute([$key]);
    }

    private function isDuplicateError(PDOException $e): bool
    {
        return str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'Duplicate entry');
    }

    private function isAlreadyExistsError(PDOException $e): bool
    {
        $msg = $e->getMessage();
        return str_contains($msg, '1050')  // Table already exists
            || str_contains($msg, '1060')  // Duplicate column
            || str_contains($msg, '1061')  // Duplicate key name
            || str_contains($msg, '1068'); // Multiple primary key
    }

    private function isComment(string $sql): bool
    {
        return str_starts_with($sql, '--') || str_starts_with($sql, '/*');
    }

    /**
     * Divide o conteúdo SQL em statements individuais pelo delimitador ";".
     * Respeita strings entre aspas para não quebrar no meio de um valor.
     */
    private function splitStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];

            // Pular comentários de linha
            if (!$inString && $char === '-' && isset($sql[$i + 1]) && $sql[$i + 1] === '-') {
                $end = strpos($sql, "\n", $i);
                if ($end === false) {
                    break;
                }
                $i = $end;
                continue;
            }

            // Pular comentários de bloco
            if (!$inString && $char === '/' && isset($sql[$i + 1]) && $sql[$i + 1] === '*') {
                $end = strpos($sql, '*/', $i + 2);
                if ($end === false) {
                    break;
                }
                $i = $end + 1;
                continue;
            }

            if ($inString) {
                $current .= $char;
                if ($char === $stringChar && ($i === 0 || $sql[$i - 1] !== '\\')) {
                    $inString = false;
                }
                continue;
            }

            if ($char === '\'' || $char === '"') {
                $inString = true;
                $stringChar = $char;
                $current .= $char;
                continue;
            }

            if ($char === ';') {
                $trimmed = trim($current);
                if ($trimmed !== '') {
                    $statements[] = $trimmed;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $trimmed = trim($current);
        if ($trimmed !== '') {
            $statements[] = $trimmed;
        }

        return $statements;
    }

    private function basePath(string $relative): string
    {
        return dirname(__DIR__, 2) . '/' . $relative;
    }

    private function addLog(string $type, string $message): void
    {
        $colors = [
            'ok'    => '#4caf50',
            'skip'  => '#ff9800',
            'error' => '#f44336',
            'warn'  => '#ff5722',
            'info'  => '#2196f3',
        ];
        $color = $colors[$type] ?? '#ccc';
        $icon = match ($type) {
            'ok'    => '✅',
            'skip'  => '⏭️',
            'error' => '❌',
            'warn'  => '⚠️',
            'info'  => 'ℹ️',
            default => '•',
        };

        echo "<div style=\"padding:3px 0;color:{$color};font-size:13px;\">{$icon} {$message}</div>\n";
    }

    private function flush(): void
    {
        if (ob_get_level()) {
            ob_flush();
        }
        flush();
    }

    private function renderHeader(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Importador de Banco de Dados</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: #0d1117; color: #c9d1d9; font-family: 'Courier New', monospace; padding: 20px; }
    h1 { color: #58a6ff; margin-bottom: 10px; font-size: 20px; }
    .subtitle { color: #8b949e; margin-bottom: 20px; font-size: 13px; }
    .log-container { background: #161b22; border: 1px solid #30363d; border-radius: 8px; padding: 16px; max-height: 70vh; overflow-y: auto; }
    code { background: #21262d; padding: 1px 5px; border-radius: 3px; font-size: 12px; }
    .summary { margin-top: 20px; padding: 16px; background: #161b22; border: 1px solid #30363d; border-radius: 8px; }
    .summary span { margin-right: 20px; font-size: 14px; }
    .ok-count { color: #4caf50; }
    .skip-count { color: #ff9800; }
    .error-count { color: #f44336; }
    .warning-box { background: #2d1b00; border: 1px solid #f0883e; border-radius: 8px; padding: 12px; margin-bottom: 16px; color: #f0883e; font-size: 13px; }
</style>
</head>
<body>
<h1>🗄️ Importador de Banco de Dados</h1>
<p class="subtitle">Executando schema + migrations com verificação de duplicatas...</p>
<div class="warning-box">⚠️ Remova esta rota após concluir a migração. Acesso sem autenticação.</div>
<div class="log-container">
HTML;
    }

    private function renderSummary(): string
    {
        return <<<HTML
</div>
<div class="summary">
    <span class="ok-count">✅ Sucesso: {$this->successCount}</span>
    <span class="skip-count">⏭️ Pulados: {$this->skipCount}</span>
    <span class="error-count">❌ Erros: {$this->errorCount}</span>
</div>
HTML;
    }

    private function renderFooter(): string
    {
        return <<<'HTML'
<p style="margin-top:16px;color:#8b949e;font-size:12px;">
    Importação concluída. Você pode acessar esta página novamente — migrations já executadas serão puladas automaticamente.
</p>
</body>
</html>
HTML;
    }
}
