<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PartnerPersonality;
use App\Models\PartnerApiKey;
use App\Models\User;

class PartnerChatController extends Controller
{
    private function requirePartnerOwner(): array
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $user = User::findById($userId);
        
        if (!$user) {
            unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
            header('Location: /login');
            exit;
        }

        // Verifica se está em ambiente de parceiro
        if (empty($_SERVER['TUQ_PARTNER_SITE']) || empty($_SERVER['TUQ_PARTNER_USER_ID'])) {
            header('Location: /');
            exit;
        }

        // Verifica se o usuário é o dono do ambiente
        $partnerUserId = (int)$_SERVER['TUQ_PARTNER_USER_ID'];
        if ($userId !== $partnerUserId) {
            header('Location: /');
            exit;
        }

        return $user;
    }

    public function index(): void
    {
        $user = $this->requirePartnerOwner();
        $userId = (int)$user['id'];

        // Verificar se tem API key configurada
        if (!PartnerApiKey::hasAnyActiveKey($userId)) {
            $_SESSION['partner_error'] = 'Configure sua API Key primeiro para acessar o chat.';
            header('Location: /parceiro/configuracoes/api');
            exit;
        }

        // Buscar personalidades do parceiro
        $personalities = PartnerPersonality::allActiveByUserId($userId);
        
        if (!$personalities) {
            $_SESSION['partner_error'] = 'Crie pelo menos uma personalidade para acessar o chat.';
            header('Location: /parceiro/configuracoes/personalidades');
            exit;
        }

        // Buscar personalidade padrão
        $defaultPersonality = PartnerPersonality::findDefaultByUserId($userId);
        $selectedPersonality = $defaultPersonality ?: $personalities[0];

        $this->view('partner/chat', [
            'pageTitle' => 'Chat IA',
            'user' => $user,
            'personalities' => $personalities,
            'selectedPersonality' => $selectedPersonality,
            'hasApiKey' => PartnerApiKey::hasAnyActiveKey($userId),
        ]);
    }

    public function sendMessage(): void
    {
        $user = $this->requirePartnerOwner();
        $userId = (int)$user['id'];

        $message = trim($_POST['message'] ?? '');
        $personalityId = (int)($_POST['personality_id'] ?? 0);

        if ($message === '') {
            $this->json(['error' => 'Mensagem não pode ser vazia'], 400);
            return;
        }

        // Validar personalidade
        $personality = PartnerPersonality::findById($personalityId);
        if (!$personality || (int)$personality['user_id'] !== $userId || empty($personality['active'])) {
            $this->json(['error' => 'Personalidade inválida'], 400);
            return;
        }

        // Buscar API key
        $apiKey = PartnerApiKey::getActiveKey($userId, 'openai');
        if (!$apiKey) {
            $this->json(['error' => 'Nenhuma API Key configurada'], 400);
            return;
        }

        try {
            // Simular resposta da IA (implementação real viria aqui)
            $response = $this->simulateAIResponse($message, $personality, $apiKey);
            
            $this->json([
                'success' => true,
                'response' => $response,
                'personality' => $personality['name'],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Erro ao processar mensagem: ' . $e->getMessage()], 500);
        }
    }

    public function stream(): void
    {
        $user = $this->requirePartnerOwner();
        $userId = (int)$user['id'];

        // Configurar headers para streaming
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        $message = trim($_GET['message'] ?? '');
        $personalityId = (int)($_GET['personality_id'] ?? 0);

        if ($message === '') {
            echo "data: " . json_encode(['error' => 'Mensagem não pode ser vazia']) . "\n\n";
            exit;
        }

        // Validar personalidade
        $personality = PartnerPersonality::findById($personalityId);
        if (!$personality || (int)$personality['user_id'] !== $userId || empty($personality['active'])) {
            echo "data: " . json_encode(['error' => 'Personalidade inválida']) . "\n\n";
            exit;
        }

        // Buscar API key
        $apiKey = PartnerApiKey::getActiveKey($userId, 'openai');
        if (!$apiKey) {
            echo "data: " . json_encode(['error' => 'Nenhuma API Key configurada']) . "\n\n";
            exit;
        }

        // Simular streaming (implementação real viria aqui)
        $response = $this->simulateAIResponse($message, $personality, $apiKey);
        
        // Enviar resposta como stream
        $words = explode(' ', $response);
        foreach ($words as $word) {
            echo "data: " . json_encode(['content' => $word . ' ']) . "\n\n";
            ob_flush();
            flush();
            usleep(50000); // 50ms delay
        }

        echo "data: " . json_encode(['done' => true]) . "\n\n";
    }

    private function simulateAIResponse(string $message, array $personality, string $apiKey): string
    {
        // Simulação simples - implementação real usaria a API OpenAI
        $personalityName = $personality['name'] ?? 'Assistente';
        $area = $personality['area'] ?? 'Geral';
        
        $responses = [
            "Olá! Sou {$personalityName}, especialista em {$area}. Como posso ajudar você com '{$message}'?",
            "Entendi sua pergunta sobre '{$message}'. Como especialista em {$area}, posso dizer que...",
            "Ótima pergunta! Na área de {$area}, geralmente recomendamos...",
            "Baseado na minha experiência em {$area}, a resposta para '{$message}' é..."
        ];
        
        return $responses[array_rand($responses)];
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
