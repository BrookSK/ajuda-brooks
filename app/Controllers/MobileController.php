<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Models\Personality;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\TuquinhaEngine;
use App\Models\AiLearning;
use App\Models\Setting;
use App\Models\Plan;
use App\Models\ConversationSetting;
use App\Services\ElevenLabsService;
use App\Services\OpenAiTtsService;

class MobileController extends Controller
{
    private function requireLogin(): array
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /m/login');
            exit;
        }
        $user = User::findById((int)$_SESSION['user_id']);
        if (!$user) {
            unset($_SESSION['user_id']);
            header('Location: /m/login');
            exit;
        }
        return $user;
    }

    /**
     * Tela principal mobile — redireciona para onboarding ou chat.
     */
    public function index(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /m/login');
            exit;
        }
        $userId = (int)$_SESSION['user_id'];

        if (!UserOnboarding::isComplete($userId)) {
            header('Location: /m/onboarding');
            exit;
        }

        header('Location: /m/chat');
        exit;
    }

    /**
     * Tela de login mobile.
     */
    public function showLogin(): void
    {
        $this->view('mobile/login', [
            'pageTitle' => 'Entrar',
            'error' => null,
            'layout' => 'mobile',
        ]);
    }

    /**
     * Processa login mobile.
     */
    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->view('mobile/login', [
                'pageTitle' => 'Entrar',
                'error' => 'Informe seu e-mail e senha.',
                'layout' => 'mobile',
            ]);
            return;
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->view('mobile/login', [
                'pageTitle' => 'Entrar',
                'error' => 'E-mail ou senha inválidos.',
                'layout' => 'mobile',
            ]);
            return;
        }

        if (empty($user['is_admin']) && empty($user['email_verified_at'])) {
            $this->view('mobile/login', [
                'pageTitle' => 'Entrar',
                'error' => 'Confirme seu e-mail antes de entrar.',
                'layout' => 'mobile',
            ]);
            return;
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        if (!empty($user['default_persona_id'])) {
            $_SESSION['default_persona_id'] = (int)$user['default_persona_id'];
        }

        $defaultPlan = Plan::findDefaultForUsers();
        if ($defaultPlan && !empty($defaultPlan['slug'])) {
            $_SESSION['plan_slug'] = $defaultPlan['slug'];
        }

        header('Location: /m');
        exit;
    }

    /**
     * Tela de registro mobile.
     */
    public function showRegister(): void
    {
        $this->view('mobile/register', [
            'pageTitle' => 'Criar conta',
            'error' => null,
            'layout' => 'mobile',
        ]);
    }

    /**
     * Processa registro mobile.
     */
    public function register(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            $this->view('mobile/register', [
                'pageTitle' => 'Criar conta',
                'error' => 'Preencha todos os campos.',
                'layout' => 'mobile',
            ]);
            return;
        }

        if (User::findByEmail($email)) {
            $this->view('mobile/register', [
                'pageTitle' => 'Criar conta',
                'error' => 'Este e-mail já está cadastrado.',
                'layout' => 'mobile',
            ]);
            return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $userId = User::createUser($name, $email, $hash);

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        $defaultPlan = Plan::findDefaultForUsers();
        if ($defaultPlan && !empty($defaultPlan['slug'])) {
            $_SESSION['plan_slug'] = $defaultPlan['slug'];
        }

        header('Location: /m/onboarding');
        exit;
    }

    /**
     * Onboarding mobile — steps conversacionais.
     */
    public function onboarding(): void
    {
        $user = $this->requireLogin();
        $userId = (int)$user['id'];

        if (UserOnboarding::isComplete($userId)) {
            header('Location: /m/chat');
            exit;
        }

        $onboarding = UserOnboarding::findByUserId($userId);
        $personalities = Personality::allVisibleForUsers();
        $projects = \App\Models\Project::allForUser($userId);

        $this->view('mobile/onboarding', [
            'pageTitle' => 'Configurar',
            'user' => $user,
            'onboarding' => $onboarding,
            'personalities' => $personalities,
            'projects' => $projects,
            'layout' => 'mobile',
        ]);
    }

    /**
     * Salva cada step do onboarding via AJAX.
     */
    public function saveOnboardingStep(): void
    {
        header('Content-Type: application/json');
        $user = $this->requireLogin();
        $userId = (int)$user['id'];

        $step = trim($_POST['step'] ?? '');
        $value = trim($_POST['value'] ?? '');

        $data = [];
        switch ($step) {
            case 'preferred_name':
                $data['preferred_name'] = $value;
                // Atualiza também no perfil do usuário
                User::updateProfile($userId, $user['name'], $value, $user['global_memory'] ?? null, $user['global_instructions'] ?? null, $user['default_persona_id'] ?? null);
                break;
            case 'tool_name':
                $data['tool_name'] = $value;
                break;
            case 'personality':
                $data['personality_id'] = (int)$value;
                User::updateProfile($userId, $user['name'], $user['preferred_name'] ?? null, $user['global_memory'] ?? null, $user['global_instructions'] ?? null, (int)$value);
                $_SESSION['default_persona_id'] = (int)$value;
                break;
            case 'conversation_tone':
                $allowedTones = ['descontraido', 'amigavel', 'profissional', 'formal', 'empresarial', 'motivacional'];
                if (in_array($value, $allowedTones, true)) {
                    $data['conversation_tone'] = $value;
                }
                break;
            case 'preferences':
                $prefs = json_decode($value, true) ?: [];
                $data['wants_projects'] = !empty($prefs['wants_projects']) ? 1 : 0;
                $data['wants_documents'] = !empty($prefs['wants_documents']) ? 1 : 0;
                break;
            case 'project':
                // Selecionar projeto existente ou criar novo
                $projectAction = trim($_POST['action'] ?? '');
                if ($projectAction === 'create') {
                    $projectName = trim($value);
                    if ($projectName !== '') {
                        $projectId = \App\Models\Project::create($userId, $projectName);
                        if ($projectId > 0) {
                            $data['project_id'] = $projectId;
                        }
                    }
                } elseif ($projectAction === 'select' && (int)$value > 0) {
                    $data['project_id'] = (int)$value;
                } elseif ($projectAction === 'skip') {
                    $data['project_id'] = 0;
                }
                break;
            case 'complete':
                $data['completed_at'] = date('Y-m-d H:i:s');
                break;
            default:
                echo json_encode(['ok' => false, 'error' => 'Step inválido']);
                return;
        }

        UserOnboarding::upsert($userId, $data);
        echo json_encode(['ok' => true]);
    }

    /**
     * Upload de documentos de aprendizado durante onboarding.
     */
    public function uploadDocument(): void
    {
        header('Content-Type: application/json');
        $user = $this->requireLogin();
        $userId = (int)$user['id'];

        if (empty($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['ok' => false, 'error' => 'Nenhum arquivo enviado.']);
            return;
        }

        $file = $_FILES['document'];
        $maxSize = 50 * 1024 * 1024; // 50MB
        if ($file['size'] > $maxSize) {
            echo json_encode(['ok' => false, 'error' => 'Arquivo muito grande (máx 50MB).']);
            return;
        }

        $allowedTypes = ['application/pdf', 'text/plain', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes, true)) {
            echo json_encode(['ok' => false, 'error' => 'Tipo de arquivo não suportado.']);
            return;
        }

        // Verifica se o usuário tem um projeto selecionado no onboarding
        $onboarding = UserOnboarding::findByUserId($userId);
        $projectId = isset($onboarding['project_id']) ? (int)$onboarding['project_id'] : 0;

        if ($projectId > 0) {
            // Upload para o projeto como arquivo base (igual ao fluxo de projetos do desktop)
            $remoteUrl = \App\Services\MediaStorageService::uploadFile($file['tmp_name'], $file['name'], $mime);
            if (!$remoteUrl) {
                echo json_encode(['ok' => false, 'error' => 'Falha ao enviar arquivo.']);
                return;
            }

            $path = '/base/' . $file['name'];
            $fileId = \App\Models\ProjectFile::create($projectId, null, $file['name'], $path, $mime, true, $userId);

            if ($fileId > 0) {
                // Extrai texto do documento
                $extractedText = '';
                try {
                    $extractedText = \App\Services\TextExtractionService::extractFromFile($file['tmp_name'], $file['name'], $mime) ?? '';
                } catch (\Throwable $e) {}

                // Cria versão do arquivo com URL e texto extraído
                \App\Models\ProjectFileVersion::createNewVersion($fileId, $remoteUrl, (int)$file['size'], null, $extractedText !== '' ? $extractedText : null, $userId);
            }

            echo json_encode(['ok' => true, 'filename' => $file['name'], 'project' => true]);
            return;
        }

        // Fallback: sem projeto, salva como learning do usuário (comportamento antigo)
        $text = '';
        try {
            $text = \App\Services\TextExtractionService::extractFromFile($file['tmp_name'], $file['name'], $mime) ?? '';
        } catch (\Throwable $e) {
            $text = file_get_contents($file['tmp_name']) ?: '';
        }

        if (trim($text) === '') {
            echo json_encode(['ok' => false, 'error' => 'Não foi possível extrair texto do documento.']);
            return;
        }

        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO ai_learnings (scope, scope_id, content, category, learning_type, quality_score, source, created_at)
            VALUES (:scope, :scope_id, :content, :category, :learning_type, :quality_score, :source, NOW())');
        $stmt->execute([
            'scope' => 'user',
            'scope_id' => $userId,
            'content' => mb_substr($text, 0, 50000),
            'category' => 'documento_usuario',
            'learning_type' => 'fact',
            'quality_score' => 8,
            'source' => 'mobile_onboarding_upload',
        ]);

        echo json_encode(['ok' => true, 'filename' => $file['name']]);
    }

    /**
     * Chat mobile principal.
     */
    public function chat(): void
    {
        $user = $this->requireLogin();
        $userId = (int)$user['id'];

        if (!UserOnboarding::isComplete($userId)) {
            header('Location: /m/onboarding');
            exit;
        }

        $onboarding = UserOnboarding::findByUserId($userId);
        $toolName = $onboarding['tool_name'] ?? 'Assistente';

        $conversationId = isset($_GET['c']) ? (int)$_GET['c'] : 0;
        $isNew = isset($_GET['new']);

        $personaId = $onboarding['personality_id'] ?? ($_SESSION['default_persona_id'] ?? null);

        if ($isNew || $conversationId === 0) {
            // Vincula ao projeto do onboarding se houver
            $projectId = isset($onboarding['project_id']) ? (int)$onboarding['project_id'] : 0;
            $conversation = Conversation::createForUser($userId, session_id(), $personaId ? (int)$personaId : null, $projectId > 0 ? $projectId : null);
            $_SESSION['current_conversation_id'] = $conversation->id;

            if ($isNew) {
                header('Location: /m/chat?c=' . $conversation->id);
                exit;
            }
            $conversationId = $conversation->id;
        }

        $messages = Message::allByConversation($conversationId);

        $elevenlabs = new ElevenLabsService();

        $this->view('mobile/chat', [
            'pageTitle' => $toolName,
            'user' => $user,
            'onboarding' => $onboarding,
            'toolName' => $toolName,
            'conversationId' => $conversationId,
            'messages' => $messages,
            'voiceEnabled' => $elevenlabs->isConfigured() && ($onboarding['voice_enabled'] ?? 1),
            'layout' => 'mobile',
        ]);
    }

    /**
     * Envia mensagem no chat mobile (AJAX).
     */
    public function sendMessage(): void
    {
        header('Content-Type: application/json');
        $user = $this->requireLogin();
        $userId = (int)$user['id'];

        $conversationId = (int)($_POST['conversation_id'] ?? 0);
        $text = trim($_POST['message'] ?? '');

        if ($conversationId <= 0 || $text === '') {
            echo json_encode(['ok' => false, 'error' => 'Mensagem vazia.']);
            return;
        }

        // Verifica se a conversa pertence ao usuário
        $conv = Conversation::findByIdForUser($conversationId, $userId);
        if (!$conv) {
            echo json_encode(['ok' => false, 'error' => 'Conversa não encontrada.']);
            return;
        }

        // Se a conversa não tem projeto mas o onboarding tem, vincula automaticamente
        $onboarding = UserOnboarding::findByUserId($userId);
        $onboardingProjectId = isset($onboarding['project_id']) ? (int)$onboarding['project_id'] : 0;
        if (empty($conv['project_id']) && $onboardingProjectId > 0) {
            Conversation::updateProjectId($conversationId, $onboardingProjectId);
            $conv['project_id'] = $onboardingProjectId;
        }

        // Salva mensagem do usuário
        Message::create($conversationId, 'user', $text);

        // Carrega contexto (onboarding já foi lido acima)
        $personaId = $conv['persona_id'] ?? ($onboarding['personality_id'] ?? null);
        $persona = $personaId ? Personality::findById((int)$personaId) : null;
        $convSettings = ConversationSetting::findForConversation($conversationId, $userId);

        // Carrega learnings do usuário
        $learnings = AiLearning::allRelevantForMessage($text, $personaId ? (int)$personaId : null);

        // Busca learnings específicos do usuário
        $userLearnings = $this->getUserLearnings($userId, $text);
        $learnings = array_merge($learnings, $userLearnings);

        // Monta histórico
        $allMessages = Message::allByConversation($conversationId);
        $history = [];
        foreach ($allMessages as $msg) {
            $history[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }

        // Injeta nome da ferramenta e tom de conversa no contexto do usuário
        $userContext = $user;
        if (!empty($onboarding['tool_name'])) {
            $userContext['tool_name'] = $onboarding['tool_name'];
        }

        // Injeta tom de conversa como instrução global
        $tone = $onboarding['conversation_tone'] ?? '';
        if ($tone !== '') {
            $toneMap = [
                'descontraido' => 'Responda de forma descontraída, leve, com humor e linguagem informal. Use gírias quando fizer sentido e seja divertido.',
                'amigavel' => 'Responda de forma amigável, próxima e acolhedora, como se fosse um amigo de confiança conversando.',
                'profissional' => 'Responda de forma profissional, direta e objetiva, com foco em resultados e eficiência.',
                'formal' => 'Responda de forma formal, elegante e respeitosa, usando linguagem culta e bem estruturada.',
                'empresarial' => 'Responda de forma corporativa, técnica e estratégica, como um consultor empresarial experiente.',
                'motivacional' => 'Responda de forma energética, inspiradora e encorajadora, motivando o usuário a agir e evoluir.',
            ];
            $toneInstruction = $toneMap[$tone] ?? '';
            if ($toneInstruction !== '') {
                $existing = trim((string)($userContext['global_instructions'] ?? ''));
                $userContext['global_instructions'] = ($existing !== '' ? $existing . "\n\n" : '') . 'TOM DE CONVERSA: ' . $toneInstruction;
            }
        }

        // Injeta nome da ferramenta como identidade
        if (!empty($onboarding['tool_name'])) {
            $toolNameVal = trim($onboarding['tool_name']);
            $existing = trim((string)($userContext['global_instructions'] ?? ''));
            $userContext['global_instructions'] = ($existing !== '' ? $existing . "\n\n" : '') . 'Seu nome é "' . $toolNameVal . '". Quando perguntarem seu nome, responda que se chama ' . $toolNameVal . '.';
        }

        // Modo voz: instrui a IA a ser breve pra conversa fluir rápido
        // Mas se tem projeto, não limita tanto pra permitir citações dos arquivos
        $isVoiceMode = !empty($_POST['voice_mode']);
        $convHasProject = (int)($conv['project_id'] ?? 0) > 0 || $onboardingProjectId > 0;
        if ($isVoiceMode) {
            $existing = trim((string)($userContext['global_instructions'] ?? ''));
            if ($convHasProject) {
                $userContext['global_instructions'] = ($existing !== '' ? $existing . "\n\n" : '')
                    . 'MODO VOZ ATIVO: O usuário está conversando por voz. Seja direto e objetivo, mas mantenha as citações dos arquivos. '
                    . 'Não use formatação complexa (tabelas, listas longas). Fale de forma natural.';
            } else {
                $userContext['global_instructions'] = ($existing !== '' ? $existing . "\n\n" : '')
                    . 'MODO VOZ ATIVO: O usuário está conversando por voz. Responda de forma MUITO curta e direta, no máximo 2-3 frases. '
                    . 'Seja conversacional como numa ligação telefônica. Não use listas, bullet points, markdown ou formatação. '
                    . 'Não use emojis. Fale de forma natural e fluida como se estivesse conversando pessoalmente.';
            }
        }

        // Gera resposta
        $engine = new TuquinhaEngine();
        $engine->setAiLearnings($learnings);

        // Carrega contexto do projeto (arquivos base) se a conversa tiver projeto vinculado
        $projectContext = null;
        $projectFileInputs = null;
        $projectId = isset($conv['project_id']) ? (int)$conv['project_id'] : 0;
        @file_put_contents('/tmp/tuq_mobile_debug.log', date('Y-m-d H:i:s') . " conv_id={$conversationId} project_id={$projectId} conv_project=" . ($conv['project_id'] ?? 'NULL') . " onboarding_project=" . ($onboardingProjectId ?? 'N/A') . "\n", FILE_APPEND);
        if ($projectId > 0) {
            $projectRow = \App\Models\Project::findById($projectId);
            $baseFiles = \App\Models\ProjectFile::allBaseFiles($projectId);
            @file_put_contents('/tmp/tuq_mobile_debug.log', date('Y-m-d H:i:s') . " baseFiles=" . count($baseFiles) . "\n", FILE_APPEND);
            $baseFileIds = array_map(fn($f) => (int)($f['id'] ?? 0), $baseFiles);
            $latestByFileId = \App\Models\ProjectFileVersion::latestForFiles($baseFileIds);

            $parts = [];
            if (is_array($projectRow)) {
                $pName = trim((string)($projectRow['name'] ?? ''));
                if ($pName !== '') $parts[] = 'DADOS DO PROJETO: nome=' . $pName;
                $pDesc = trim((string)($projectRow['description'] ?? ''));
                if ($pDesc !== '') $parts[] = "DESCRIÇÃO DO PROJETO:\n" . $pDesc;
            }

            // Memórias do projeto
            $autoMem = \App\Models\ProjectMemoryItem::allActiveForProject($projectId, 60);
            if (!empty($autoMem)) {
                $memLines = [];
                foreach ($autoMem as $mi) {
                    $c = trim((string)($mi['content'] ?? ''));
                    if ($c !== '') $memLines[] = '- ' . $c;
                }
                if ($memLines) $parts[] = "MEMÓRIAS DO PROJETO:\n" . implode("\n", $memLines);
            }

            // Conteúdo dos arquivos base
            $chatModel = $_SESSION['chat_model'] ?? '';
            $isClaudeModel = str_starts_with(strtolower((string)$chatModel), 'claude-');
            $budgetMax = $isClaudeModel ? 50000 : 30000;
            $budgetUsed = 0;

            foreach ($baseFiles as $bf) {
                $fid = (int)($bf['id'] ?? 0);
                $ver = $latestByFileId[$fid] ?? null;
                $label = trim((string)($bf['name'] ?? ''));
                $extractedText = is_array($ver) ? trim((string)($ver['extracted_text'] ?? '')) : '';
                $mime = trim((string)($bf['mime_type'] ?? ''));
                $url = is_array($ver) ? (string)($ver['storage_url'] ?? '') : '';

                // Tenta extrair texto de PDFs sem extracted_text
                if ($extractedText === '' && $mime === 'application/pdf' && $url !== '') {
                    $pdfBin = @file_get_contents($url);
                    if (is_string($pdfBin) && $pdfBin !== '') {
                        $tmpPdf = tempnam(sys_get_temp_dir(), 'tuq_mpdf_') . '.pdf';
                        $tmpTxt = tempnam(sys_get_temp_dir(), 'tuq_mtxt_');
                        if (@file_put_contents($tmpPdf, $pdfBin) !== false) {
                            @\shell_exec('timeout 30 pdftotext -layout ' . escapeshellarg($tmpPdf) . ' ' . escapeshellarg($tmpTxt) . ' 2>&1');
                            if (is_file($tmpTxt) && @filesize($tmpTxt) > 0) {
                                $t = @file_get_contents($tmpTxt);
                                if (is_string($t) && trim($t) !== '') {
                                    $extractedText = trim($t);
                                    $verId = is_array($ver) ? (int)($ver['id'] ?? 0) : 0;
                                    if ($verId > 0) {
                                        try { \App\Models\ProjectFileVersion::updateExtractedText($verId, $extractedText); } catch (\Throwable $e) {}
                                    }
                                }
                            }
                        }
                        @unlink($tmpPdf);
                        @unlink($tmpTxt);
                    }
                }

                if ($extractedText !== '' && $budgetUsed < $budgetMax) {
                    $remaining = $budgetMax - $budgetUsed;
                    if (mb_strlen($extractedText, 'UTF-8') > $remaining) {
                        $extractedText = mb_substr($extractedText, 0, $remaining, 'UTF-8') . "\n[...truncado...]";
                    }
                    $budgetUsed += mb_strlen($extractedText, 'UTF-8');
                    $parts[] = "CONTEÚDO DO ARQUIVO BASE: {$label}\n\n" . $extractedText;
                }
            }

            if (!empty($parts)) {
                $projectContext = "MODO PROJETO — INSTRUÇÃO ABSOLUTA\n\n"
                    . "VOCÊ É UM ESPECIALISTA NO CONTEÚDO DOS ARQUIVOS ABAIXO. NADA MAIS.\n\n"
                    . "REGRAS ABSOLUTAS (violá-las é proibido):\n"
                    . "1. TODA resposta DEVE ser construída a partir do conteúdo dos arquivos abaixo. SEM EXCEÇÃO.\n"
                    . "2. NUNCA diga 'não usei os arquivos', 'respondi com experiência própria' ou 'o arquivo não cobre isso'.\n"
                    . "3. NUNCA diga que algo 'não é da sua área' ou redirecione para outra personalidade.\n"
                    . "4. Leia o conteúdo dos arquivos ANTES de responder. Encontre trechos relevantes e USE-OS.\n"
                    . "5. Cite trechos LITERAIS entre aspas com página. Ex: O autor diz: \"trecho\" (pág. X)\n"
                    . "6. Se o arquivo aborda o tema de forma indireta (ex: processos, diagnóstico, controles), APLIQUE esses conceitos ao problema do usuário.\n"
                    . "7. NUNCA responda com 'conhecimento geral' ou 'experiência prática'. USE OS ARQUIVOS.\n"
                    . "8. Ao final: 📚 **Fontes** [N] Arquivo — \"trecho literal\" (pág. X)\n\n"
                    . "ARQUIVOS DO PROJETO (sua ÚNICA fonte de conhecimento):\n\n" . implode("\n\n---\n\n", $parts);
            }
        }

        @file_put_contents('/tmp/tuq_mobile_debug.log', date('Y-m-d H:i:s') . " projectContext=" . ($projectContext !== null ? 'SET(' . mb_strlen($projectContext, 'UTF-8') . ' chars)' : 'NULL') . "\n", FILE_APPEND);

        // Determina modelo: usa o do projeto ou o da sessão
        // Projetos precisam de modelo forte pra seguir instruções com arquivos
        $mobileModel = null;
        if ($projectId > 0 && isset($projectRow['chat_model']) && trim((string)$projectRow['chat_model']) !== '') {
            $mobileModel = trim((string)$projectRow['chat_model']);
        } elseif ($projectId > 0) {
            // Projeto sem modelo configurado: usa Claude Sonnet 4.5 (forte o suficiente pra seguir instruções)
            $mobileModel = 'claude-sonnet-4-5';
        } elseif (isset($_SESSION['chat_model']) && is_string($_SESSION['chat_model']) && $_SESSION['chat_model'] !== '') {
            $mobileModel = $_SESSION['chat_model'];
        }

        @file_put_contents('/tmp/tuq_mobile_debug.log', date('Y-m-d H:i:s') . " model=" . ($mobileModel ?? 'NULL(default)') . "\n", FILE_APPEND);

        // Injeta conteúdo dos arquivos diretamente na mensagem do usuário (mais eficaz que system prompt)
        if ($projectId > 0 && is_string($projectContext) && $projectContext !== '' && !empty($history)) {
            $lastIdx = count($history) - 1;
            if (($history[$lastIdx]['role'] ?? '') === 'user') {
                $originalMsg = $history[$lastIdx]['content'];
                $history[$lastIdx]['content'] = "ATENÇÃO: Você recebeu o conteúdo completo dos arquivos do projeto abaixo. "
                    . "REGRA ABSOLUTA: Construa sua resposta usando APENAS o texto dos arquivos. "
                    . "Cada parágrafo da sua resposta deve conter uma citação literal entre aspas com número de página. "
                    . "NÃO adicione nenhuma informação que não esteja nos arquivos. "
                    . "NÃO use conhecimento próprio, experiência prática ou informações externas. "
                    . "Se perguntarem se você usou os arquivos, diga SIM — porque você DEVE usar.\n\n"
                    . $projectContext . "\n\n"
                    . "PERGUNTA DO USUÁRIO (responda usando SOMENTE o conteúdo acima):\n" . $originalMsg;
                @file_put_contents('/tmp/tuq_mobile_debug.log', date('Y-m-d H:i:s') . " INJECTED into message, total_len=" . mb_strlen($history[$lastIdx]['content'], 'UTF-8') . "\n", FILE_APPEND);
            }
            $projectContext = null;
        } else {
            @file_put_contents('/tmp/tuq_mobile_debug.log', date('Y-m-d H:i:s') . " NOT injected: projectId={$projectId} contextIsString=" . (is_string($projectContext) ? 'Y' : 'N') . " contextEmpty=" . (empty($projectContext) ? 'Y' : 'N') . " historyCount=" . count($history) . "\n", FILE_APPEND);
        }

        $result = $engine->generateResponseWithContext($history, $mobileModel, $userContext, $convSettings, $persona, $projectFileInputs, $projectContext);

        $reply = $result['content'] ?? 'Desculpe, não consegui gerar uma resposta.';
        $tokensUsed = $result['total_tokens'] ?? null;

        // Salva resposta
        Message::create($conversationId, 'assistant', $reply, $tokensUsed);

        // Auto-título na primeira mensagem
        if (count($allMessages) <= 1) {
            $title = TuquinhaEngine::generateShortTitle($text);
            if ($title) {
                Conversation::updateTitle($conversationId, $title);
            }
        }

        // Enfileira extração de aprendizados globais (igual desktop)
        if (
            (string)Setting::get('ai_learning_enabled', '1') !== '0'
            && $reply !== ''
            && mb_strlen($reply, 'UTF-8') >= 120
            && mb_strlen($text, 'UTF-8') >= 20
        ) {
            try {
                $learningPersonaId = $persona ? (int)($persona['id'] ?? 0) : 0;
                \App\Models\LearningJob::enqueue(
                    $conversationId,
                    $text,
                    $reply,
                    $learningPersonaId > 0 ? $learningPersonaId : null,
                    $_SESSION['chat_model'] ?? null
                );
            } catch (\Throwable $le) {}
        }

        // Enfileira extração de sugestões de projeto (igual desktop)
        $isFallback = strpos($reply, 'Não consegui acessar a IA agora') !== false || mb_strlen($reply, 'UTF-8') < 100;
        if ($projectId > 0 && $reply !== '' && !$isFallback && mb_strlen($text, 'UTF-8') >= 20) {
            $enabled = (string)Setting::get('project_auto_memory_enabled', '1');
            if ($enabled !== '0') {
                try {
                    $sgJobId = \App\Models\ProjectSuggestionJob::enqueue($projectId, $conversationId, $text, $reply);
                    if ($sgJobId > 0) {
                        $cronToken = trim((string)Setting::get('news_cron_token', ''));
                        if ($cronToken !== '') {
                            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                            $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
                            $cronUrl = $scheme . '://' . $host . '/cron/learning/project-suggestions?token=' . urlencode($cronToken) . '&batch=1';
                            @\App\Services\AsyncHttpService::fireAndForget($cronUrl);
                        }
                    }
                } catch (\Throwable $sgErr) {}
            }
        }

        echo json_encode([
            'ok' => true,
            'reply' => $reply,
            'conversation_id' => $conversationId,
        ]);
    }

    /**
     * Busca learnings específicos do usuário (documentos enviados).
     */
    private function getUserLearnings(int $userId, string $message): array
    {
        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM ai_learnings
            WHERE scope = 'user' AND scope_id = :uid AND deleted_at IS NULL
            ORDER BY quality_score DESC, created_at DESC LIMIT 50");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * TTS via ElevenLabs — tenta streaming, fallback pra blob.
     */
    public function textToSpeech(): void
    {
        $this->requireLogin();

        $text = trim($_POST['text'] ?? '');
        if ($text === '') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false]);
            exit;
        }

        // Limpa todos os output buffers pra streaming funcionar
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Desabilita compressão que pode bufferizar
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', '0');
        @ini_set('implicit_flush', '1');

        // Provedor primário: OpenAI TTS (gpt-4o-mini-tts com streaming)
        $openai = new OpenAiTtsService();
        if ($openai->isConfigured()) {
            $ok = $openai->textToSpeechStream($text);
            if ($ok) {
                exit;
            }

            // Fallback não-streaming OpenAI
            $audio = $openai->textToSpeech($text);
            if ($audio && strlen($audio) > 100) {
                header('Content-Type: audio/mpeg');
                header('Content-Length: ' . strlen($audio));
                echo $audio;
                exit;
            }
        }

        // Fallback: ElevenLabs
        $elevenlabs = new ElevenLabsService();
        if ($elevenlabs->isConfigured()) {
            $ok = $elevenlabs->textToSpeechStream($text);
            if ($ok) {
                exit;
            }

            $audio = $elevenlabs->textToSpeech($text);
            if ($audio && strlen($audio) > 100) {
                header('Content-Type: audio/mpeg');
                header('Content-Length: ' . strlen($audio));
                echo $audio;
                exit;
            }
        }

        http_response_code(503);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false]);
        exit;
    }

    /**
     * Histórico de conversas mobile.
     */
    public function history(): void
    {
        $user = $this->requireLogin();
        $userId = (int)$user['id'];
        $onboarding = UserOnboarding::findByUserId($userId);

        $conversations = Conversation::allByUser($userId);

        $this->view('mobile/history', [
            'pageTitle' => 'Conversas',
            'conversations' => $conversations,
            'toolName' => $onboarding['tool_name'] ?? 'Assistente',
            'layout' => 'mobile',
        ]);
    }

    /**
     * Logout mobile.
     */
    public function logout(): void
    {
        session_destroy();
        header('Location: /m/login');
        exit;
    }
}
