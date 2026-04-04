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

        // Salva mensagem do usuário
        Message::create($conversationId, 'user', $text);

        // Carrega contexto
        $onboarding = UserOnboarding::findByUserId($userId);
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
        $isVoiceMode = !empty($_POST['voice_mode']);
        if ($isVoiceMode) {
            $existing = trim((string)($userContext['global_instructions'] ?? ''));
            $userContext['global_instructions'] = ($existing !== '' ? $existing . "\n\n" : '')
                . 'MODO VOZ ATIVO: O usuário está conversando por voz. Responda de forma MUITO curta e direta, no máximo 2-3 frases. '
                . 'Seja conversacional como numa ligação telefônica. Não use listas, bullet points, markdown ou formatação. '
                . 'Não use emojis. Fale de forma natural e fluida como se estivesse conversando pessoalmente.';
        }

        // Gera resposta
        $engine = new TuquinhaEngine();
        $engine->setAiLearnings($learnings);
        $result = $engine->generateResponseWithContext($history, null, $userContext, $convSettings, $persona);

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
