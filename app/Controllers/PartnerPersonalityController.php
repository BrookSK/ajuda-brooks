<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PartnerPersonality;
use App\Models\User;

class PartnerPersonalityController extends Controller
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

        $personalities = PartnerPersonality::allByUserId($userId);

        $this->view('partner/personalidades_list', [
            'pageTitle' => 'Personalidades',
            'user' => $user,
            'personalities' => $personalities,
            'success' => $_SESSION['partner_success'] ?? null,
            'error' => $_SESSION['partner_error'] ?? null,
        ]);

        unset($_SESSION['partner_success'], $_SESSION['partner_error']);
    }

    public function create(): void
    {
        $user = $this->requirePartnerOwner();

        $this->view('partner/personality_form', [
            'pageTitle' => 'Nova Personalidade',
            'user' => $user,
            'personality' => null,
            'isEdit' => false,
            'success' => $_SESSION['partner_success'] ?? null,
            'error' => $_SESSION['partner_error'] ?? null,
        ]);

        unset($_SESSION['partner_success'], $_SESSION['partner_error']);
    }

    public function save(): void
    {
        $user = $this->requirePartnerOwner();
        $userId = (int)$user['id'];

        $name = trim($_POST['name'] ?? '');
        $area = trim($_POST['area'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $prompt = trim($_POST['prompt'] ?? '');
        $isDefault = !empty($_POST['is_default']);
        $active = !empty($_POST['active']);

        if ($name === '' || $area === '' || $slug === '' || $prompt === '') {
            $_SESSION['partner_error'] = 'Preencha todos os campos obrigatórios.';
            header('Location: /parceiro/personalidades/novo');
            exit;
        }

        // Validar slug único
        if (!PartnerPersonality::isSlugUnique($userId, $slug)) {
            $_SESSION['partner_error'] = 'Este slug já está em uso. Escolha outro.';
            header('Location: /parceiro/personalidades/novo');
            exit;
        }

        try {
            PartnerPersonality::create([
                'user_id' => $userId,
                'name' => $name,
                'area' => $area,
                'slug' => $slug,
                'prompt' => $prompt,
                'is_default' => $isDefault,
                'active' => $active,
            ]);

            $_SESSION['partner_success'] = 'Personalidade criada com sucesso!';
            header('Location: /parceiro/configuracoes/personalidades');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['partner_error'] = 'Erro ao criar personalidade: ' . $e->getMessage();
            header('Location: /parceiro/personalidades/novo');
            exit;
        }
    }

    public function edit(): void
    {
        $user = $this->requirePartnerOwner();
        $userId = (int)$user['id'];

        $personalityId = (int)($_GET['id'] ?? 0);
        if ($personalityId <= 0) {
            $_SESSION['partner_error'] = 'Personalidade inválida.';
            header('Location: /parceiro/configuracoes/personalidades');
            exit;
        }

        $personality = PartnerPersonality::findById($personalityId);
        if (!$personality || (int)$personality['user_id'] !== $userId) {
            $_SESSION['partner_error'] = 'Personalidade não encontrada.';
            header('Location: /parceiro/configuracoes/personalidades');
            exit;
        }

        $this->view('partner/personality_form', [
            'pageTitle' => 'Editar Personalidade',
            'user' => $user,
            'personality' => $personality,
            'isEdit' => true,
            'success' => $_SESSION['partner_success'] ?? null,
            'error' => $_SESSION['partner_error'] ?? null,
        ]);

        unset($_SESSION['partner_success'], $_SESSION['partner_error']);
    }

    public function update(): void
    {
        $user = $this->requirePartnerOwner();
        $userId = (int)$user['id'];

        $personalityId = (int)($_POST['id'] ?? 0);
        if ($personalityId <= 0) {
            $_SESSION['partner_error'] = 'Personalidade inválida.';
            header('Location: /parceiro/configuracoes/personalidades');
            exit;
        }

        $personality = PartnerPersonality::findById($personalityId);
        if (!$personality || (int)$personality['user_id'] !== $userId) {
            $_SESSION['partner_error'] = 'Personalidade não encontrada.';
            header('Location: /parceiro/configuracoes/personalidades');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $area = trim($_POST['area'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $prompt = trim($_POST['prompt'] ?? '');
        $isDefault = !empty($_POST['is_default']);
        $active = !empty($_POST['active']);

        if ($name === '' || $area === '' || $slug === '' || $prompt === '') {
            $_SESSION['partner_error'] = 'Preencha todos os campos obrigatórios.';
            header('Location: /parceiro/personalidades/editar?id=' . $personalityId);
            exit;
        }

        // Validar slug único (exceto o atual)
        if (!PartnerPersonality::isSlugUnique($userId, $slug, $personalityId)) {
            $_SESSION['partner_error'] = 'Este slug já está em uso. Escolha outro.';
            header('Location: /parceiro/personalidades/editar?id=' . $personalityId);
            exit;
        }

        try {
            PartnerPersonality::update($personalityId, [
                'name' => $name,
                'area' => $area,
                'slug' => $slug,
                'prompt' => $prompt,
                'is_default' => $isDefault,
                'active' => $active,
            ]);

            $_SESSION['partner_success'] = 'Personalidade atualizada com sucesso!';
            header('Location: /parceiro/configuracoes/personalidades');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['partner_error'] = 'Erro ao atualizar personalidade: ' . $e->getMessage();
            header('Location: /parceiro/personalidades/editar?id=' . $personalityId);
            exit;
        }
    }

    public function delete(): void
    {
        $user = $this->requirePartnerOwner();
        $userId = (int)$user['id'];

        $personalityId = (int)($_GET['id'] ?? 0);
        if ($personalityId <= 0) {
            $_SESSION['partner_error'] = 'Personalidade inválida.';
            header('Location: /parceiro/configuracoes/personalidades');
            exit;
        }

        $personality = PartnerPersonality::findById($personalityId);
        if (!$personality || (int)$personality['user_id'] !== $userId) {
            $_SESSION['partner_error'] = 'Personalidade não encontrada.';
            header('Location: /parceiro/configuracoes/personalidades');
            exit;
        }

        try {
            PartnerPersonality::delete($personalityId);
            $_SESSION['partner_success'] = 'Personalidade excluída com sucesso!';
        } catch (\Throwable $e) {
            $_SESSION['partner_error'] = 'Erro ao excluir personalidade: ' . $e->getMessage();
        }

        header('Location: /parceiro/configuracoes/personalidades');
        exit;
    }

    public function toggleActive(): void
    {
        $user = $this->requirePartnerOwner();
        $userId = (int)$user['id'];

        $personalityId = (int)($_GET['id'] ?? 0);
        if ($personalityId <= 0) {
            $_SESSION['partner_error'] = 'Personalidade inválida.';
            header('Location: /parceiro/configuracoes/personalidades');
            exit;
        }

        $personality = PartnerPersonality::findById($personalityId);
        if (!$personality || (int)$personality['user_id'] !== $userId) {
            $_SESSION['partner_error'] = 'Personalidade não encontrada.';
            header('Location: /parceiro/configuracoes/personalidades');
            exit;
        }

        try {
            PartnerPersonality::toggleActive($personalityId);
            $_SESSION['partner_success'] = 'Status da personalidade atualizado!';
        } catch (\Throwable $e) {
            $_SESSION['partner_error'] = 'Erro ao atualizar personalidade: ' . $e->getMessage();
        }

        header('Location: /parceiro/configuracoes/personalidades');
        exit;
    }

    public function setDefault(): void
    {
        $user = $this->requirePartnerOwner();
        $userId = (int)$user['id'];

        $personalityId = (int)($_GET['id'] ?? 0);
        if ($personalityId <= 0) {
            $_SESSION['partner_error'] = 'Personalidade inválida.';
            header('Location: /parceiro/configuracoes/personalidades');
            exit;
        }

        $personality = PartnerPersonality::findById($personalityId);
        if (!$personality || (int)$personality['user_id'] !== $userId) {
            $_SESSION['partner_error'] = 'Personalidade não encontrada.';
            header('Location: /parceiro/configuracoes/personalidades');
            exit;
        }

        try {
            PartnerPersonality::setDefault($personalityId);
            $_SESSION['partner_success'] = 'Personalidade definida como padrão!';
        } catch (\Throwable $e) {
            $_SESSION['partner_error'] = 'Erro ao definir personalidade padrão: ' . $e->getMessage();
        }

        header('Location: /parceiro/configuracoes/personalidades');
        exit;
    }
}
