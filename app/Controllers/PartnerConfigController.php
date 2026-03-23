<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PartnerApiKey;
use App\Models\PartnerSetting;
use App\Models\User;

class PartnerConfigController extends Controller
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

        // Buscar configurações atuais
        $apiKeys = PartnerApiKey::findByUserId($userId);
        $hasApiKey = PartnerApiKey::hasAnyActiveKey($userId);

        $this->view('partner/config_index', [
            'pageTitle' => 'Configurações',
            'user' => $user,
            'apiKeys' => $apiKeys,
            'hasApiKey' => $hasApiKey,
            'success' => $_SESSION['partner_success'] ?? null,
            'error' => $_SESSION['partner_error'] ?? null,
        ]);

        unset($_SESSION['partner_success'], $_SESSION['partner_error']);
    }

    public function apiConfig(): void
    {
        $user = $this->requirePartnerOwner();
        $userId = (int)$user['id'];

        $apiKeys = PartnerApiKey::findByUserId($userId);

        $this->view('partner/config_api', [
            'pageTitle' => 'Configurar API Keys',
            'user' => $user,
            'apiKeys' => $apiKeys,
            'success' => $_SESSION['partner_success'] ?? null,
            'error' => $_SESSION['partner_error'] ?? null,
        ]);

        unset($_SESSION['partner_success'], $_SESSION['partner_error']);
    }

    public function saveApiKey(): void
    {
        $user = $this->requirePartnerOwner();
        $userId = (int)$user['id'];

        $provider = trim($_POST['provider'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $isActive = !empty($_POST['is_active']);

        if ($provider === '' || $apiKey === '') {
            $_SESSION['partner_error'] = 'Preencha o provider e a API Key.';
            header('Location: /painel-externo/config/api');
            exit;
        }

        $validProviders = ['openai', 'anthropic', 'perplexity'];
        if (!in_array($provider, $validProviders)) {
            $_SESSION['partner_error'] = 'Provider inválido. Use: openai, anthropic ou perplexity.';
            header('Location: /painel-externo/config/api');
            exit;
        }

        try {
            // Verificar se já existe uma key para este provider
            $existing = PartnerApiKey::findByUserIdAndProvider($userId, $provider);
            
            if ($existing) {
                PartnerApiKey::update($existing['id'], [
                    'api_key' => $apiKey,
                    'model' => $model !== '' ? $model : null,
                    'is_active' => $isActive,
                ]);
            } else {
                PartnerApiKey::create([
                    'user_id' => $userId,
                    'provider' => $provider,
                    'api_key' => $apiKey,
                    'model' => $model !== '' ? $model : null,
                    'is_active' => $isActive,
                ]);
            }

            $_SESSION['partner_success'] = 'API Key salva com sucesso!';
            header('Location: /painel-externo/config/api');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['partner_error'] = 'Erro ao salvar API Key: ' . $e->getMessage();
            header('Location: /painel-externo/config/api');
            exit;
        }
    }

    public function toggleApiKey(): void
    {
        $user = $this->requirePartnerOwner();
        $userId = (int)$user['id'];

        $keyId = (int)($_GET['id'] ?? 0);
        if ($keyId <= 0) {
            $_SESSION['partner_error'] = 'ID inválido.';
            header('Location: /painel-externo/config/api');
            exit;
        }

        try {
            PartnerApiKey::toggleActive($keyId);
            $_SESSION['partner_success'] = 'Status da API Key atualizado!';
        } catch (\Throwable $e) {
            $_SESSION['partner_error'] = 'Erro ao atualizar API Key: ' . $e->getMessage();
        }

        header('Location: /painel-externo/config/api');
        exit;
    }

    public function deleteApiKey(): void
    {
        $user = $this->requirePartnerOwner();
        $userId = (int)$user['id'];

        $keyId = (int)($_GET['id'] ?? 0);
        if ($keyId <= 0) {
            $_SESSION['partner_error'] = 'ID inválido.';
            header('Location: /painel-externo/config/api');
            exit;
        }

        try {
            PartnerApiKey::delete($keyId);
            $_SESSION['partner_success'] = 'API Key excluída com sucesso!';
        } catch (\Throwable $e) {
            $_SESSION['partner_error'] = 'Erro ao excluir API Key: ' . $e->getMessage();
        }

        header('Location: /painel-externo/config/api');
        exit;
    }
}
