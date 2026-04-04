<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AiPromptSuggestion;
use App\Models\AiLearning;
use App\Models\Setting;

class AdminAiPromptSuggestionsController extends Controller
{
    private function ensureAdmin(): void
    {
        if (empty($_SESSION['is_admin'])) {
            header('Location: /admin/login');
            exit;
        }
    }

    public function index(): void
    {
        $this->ensureAdmin();

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $suggestions = AiPromptSuggestion::allForAdmin($perPage, $offset);
        $pendingCount = AiPromptSuggestion::countPending();
        $totalLearnings = AiLearning::countActive();
        $categories = AiLearning::distinctCategories();
        $suggestionInterval = max(10, (int)Setting::get('ai_suggestion_interval', '50'));

        $this->view('admin/ai_prompt_suggestions/index', [
            'pageTitle' => 'Sugestões de Melhoria do Prompt',
            'suggestions' => $suggestions,
            'pendingCount' => $pendingCount,
            'totalLearnings' => $totalLearnings,
            'categories' => $categories,
            'suggestionInterval' => $suggestionInterval,
            'page' => $page,
        ]);
    }

    public function approve(): void
    {
        $this->ensureAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            AiPromptSuggestion::approve($id);
        }
        header('Location: /admin/ia-sugestoes-prompt');
        exit;
    }

    public function applyAndApprove(): void
    {
        $this->ensureAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $row = AiPromptSuggestion::findById($id);
            if ($row && !empty($row['project_id'])) {
                // Sugestão de projeto: aprova e cria memória no projeto
                AiPromptSuggestion::approveAndApplyToProject($id);
            } else {
                // Sugestão global: aprova e aplica ao system prompt
                AiPromptSuggestion::approve($id);
                AiPromptSuggestion::applyApproved($id);
            }
        }
        header('Location: /admin/ia-sugestoes-prompt');
        exit;
    }

    public function reject(): void
    {
        $this->ensureAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            AiPromptSuggestion::reject($id);
        }
        header('Location: /admin/ia-sugestoes-prompt');
        exit;
    }

    public function delete(): void
    {
        $this->ensureAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            AiPromptSuggestion::softDelete($id);
        }
        header('Location: /admin/ia-sugestoes-prompt');
        exit;
    }

    public function saveSuggestionInterval(): void
    {
        $this->ensureAdmin();
        $interval = max(10, min(1000, (int)($_POST['ai_suggestion_interval'] ?? 50)));
        Setting::set('ai_suggestion_interval', (string)$interval);
        header('Location: /admin/ia-sugestoes-prompt');
        exit;
    }
}
