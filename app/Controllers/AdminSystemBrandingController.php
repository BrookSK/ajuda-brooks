<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Setting;

class AdminSystemBrandingController extends Controller
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

        $systemName       = Setting::get('system_name', 'Resenha 2.0') ?: 'Resenha 2.0';
        $systemAiName     = Setting::get('system_ai_name', 'Tuquinha') ?: 'Tuquinha';
        $systemSubtitle   = Setting::get('system_subtitle', 'Branding vivo na veia') ?? '';
        $accentColor      = Setting::get('brand_accent_color', '#e53935') ?: '#e53935';
        $accentSoftColor  = Setting::get('brand_accent_soft', '#ff6f60') ?: '#ff6f60';
        $btnTextColor     = Setting::get('brand_btn_text_color', '#050509') ?: '#050509';
        $btnStyle         = Setting::get('brand_btn_style', 'gradient') ?: 'gradient';
        $logoPath         = Setting::get('brand_logo_path', '') ?? '';
        $faviconPath      = Setting::get('brand_favicon_path', '') ?? '';
        $newsRssFeeds     = Setting::get('news_rss_feeds', "https://www.meioemensagem.com.br/feed\nhttps://www.meioemensagem.com.br/categoria/marketing/feed\nhttps://www.publicitarioscriativos.com/feed\nhttps://mundodomarketing.com.br/feed\nhttps://www.promoview.com.br/feed\nhttps://gkpb.com.br/feed") ?? '';
        $blockedDomains   = Setting::get('news_blocked_domains', 'jornaldocomercio.com.br') ?? '';
        $blockedSources   = Setting::get('news_blocked_sources', 'jornal do comércio') ?? '';

        $this->view('admin/personalizacao', [
            'pageTitle'       => 'Personalização do sistema',
            'systemName'      => $systemName,
            'systemAiName'    => $systemAiName,
            'systemSubtitle'  => $systemSubtitle,
            'accentColor'     => $accentColor,
            'accentSoftColor' => $accentSoftColor,
            'btnTextColor'    => $btnTextColor,
            'btnStyle'        => $btnStyle,
            'logoPath'        => $logoPath,
            'faviconPath'     => $faviconPath,
            'newsRssFeeds'    => $newsRssFeeds,
            'blockedDomains'  => $blockedDomains,
            'blockedSources'  => $blockedSources,
            'saved'           => false,
            'error'           => null,
        ]);
    }

    public function save(): void
    {
        $this->ensureAdmin();

        $systemName = trim((string)($_POST['system_name'] ?? ''));
        if ($systemName === '') {
            $systemName = 'Resenha 2.0';
        }

        $systemAiName = trim((string)($_POST['system_ai_name'] ?? ''));
        if ($systemAiName === '') {
            $systemAiName = 'Tuquinha';
        }

        $systemSubtitle  = trim((string)($_POST['system_subtitle'] ?? ''));
        $newsRssFeeds    = trim((string)($_POST['news_rss_feeds'] ?? ''));
        $blockedDomains  = trim((string)($_POST['news_blocked_domains'] ?? ''));
        $blockedSources  = trim((string)($_POST['news_blocked_sources'] ?? ''));

        $accentColor = trim((string)($_POST['accent_color'] ?? '#e53935'));
        if (!preg_match('/^#[0-9a-fA-F]{3,8}$/', $accentColor)) {
            $accentColor = '#e53935';
        }

        $accentSoftColor = trim((string)($_POST['accent_soft_color'] ?? '#ff6f60'));
        if (!preg_match('/^#[0-9a-fA-F]{3,8}$/', $accentSoftColor)) {
            $accentSoftColor = '#ff6f60';
        }

        $btnTextColor = trim((string)($_POST['btn_text_color'] ?? '#050509'));
        if (!preg_match('/^#[0-9a-fA-F]{3,8}$/', $btnTextColor)) {
            $btnTextColor = '#050509';
        }

        $btnStyle = trim((string)($_POST['btn_style'] ?? 'gradient'));
        if (!in_array($btnStyle, ['gradient', 'solid'], true)) {
            $btnStyle = 'gradient';
        }

        $logoPath    = Setting::get('brand_logo_path', '') ?? '';
        $faviconPath = Setting::get('brand_favicon_path', '') ?? '';
        $error       = null;

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!empty($_FILES['logo_upload']['tmp_name'])) {
            $fileErr      = (int)($_FILES['logo_upload']['error'] ?? UPLOAD_ERR_NO_FILE);
            $fileTmp      = (string)($_FILES['logo_upload']['tmp_name'] ?? '');
            $fileOrigName = (string)($_FILES['logo_upload']['name'] ?? '');
            $fileExt      = strtolower(pathinfo($fileOrigName, PATHINFO_EXTENSION));
            $allowed      = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];

            if ($fileErr === UPLOAD_ERR_OK && $fileTmp !== '' && is_uploaded_file($fileTmp)) {
                if (in_array($fileExt, $allowed, true)) {
                    $filename = 'brand_logo_' . time() . '.' . $fileExt;
                    $dest     = $uploadDir . $filename;
                    if (move_uploaded_file($fileTmp, $dest)) {
                        $logoPath = '/public/uploads/' . $filename;
                    } else {
                        $error = 'Falha ao salvar o arquivo de logo.';
                    }
                } else {
                    $error = 'Formato de logo inválido. Use PNG, JPG, SVG ou WebP.';
                }
            }
        }

        if (!empty($_FILES['favicon_upload']['tmp_name'])) {
            $fileErr      = (int)($_FILES['favicon_upload']['error'] ?? UPLOAD_ERR_NO_FILE);
            $fileTmp      = (string)($_FILES['favicon_upload']['tmp_name'] ?? '');
            $fileOrigName = (string)($_FILES['favicon_upload']['name'] ?? '');
            $fileExt      = strtolower(pathinfo($fileOrigName, PATHINFO_EXTENSION));
            $allowed      = ['png', 'jpg', 'jpeg', 'ico', 'gif', 'webp', 'svg'];

            if ($fileErr === UPLOAD_ERR_OK && $fileTmp !== '' && is_uploaded_file($fileTmp)) {
                if (in_array($fileExt, $allowed, true)) {
                    $filename = 'brand_favicon_' . time() . '.' . $fileExt;
                    $dest     = $uploadDir . $filename;
                    if (move_uploaded_file($fileTmp, $dest)) {
                        $faviconPath = '/public/uploads/' . $filename;
                    } else {
                        $error = 'Falha ao salvar o arquivo de favicon.';
                    }
                } else {
                    $error = 'Formato de favicon inválido. Use PNG, ICO ou SVG.';
                }
            }
        }

        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO settings (`key`, `value`) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );

        $toSave = [
            'system_name'          => $systemName,
            'system_ai_name'       => $systemAiName,
            'system_subtitle'      => $systemSubtitle,
            'brand_accent_color'   => $accentColor,
            'brand_accent_soft'    => $accentSoftColor,
            'brand_btn_text_color' => $btnTextColor,
            'brand_btn_style'      => $btnStyle,
            'brand_logo_path'      => $logoPath,
            'brand_favicon_path'   => $faviconPath,
            'news_rss_feeds'       => $newsRssFeeds,
            'news_blocked_domains' => $blockedDomains,
            'news_blocked_sources' => $blockedSources,
        ];

        foreach ($toSave as $key => $value) {
            $stmt->execute(['key' => $key, 'value' => $value]);
        }

        $this->view('admin/personalizacao', [
            'pageTitle'       => 'Personalização do sistema',
            'systemName'      => $systemName,
            'systemAiName'    => $systemAiName,
            'systemSubtitle'  => $systemSubtitle,
            'accentColor'     => $accentColor,
            'accentSoftColor' => $accentSoftColor,
            'btnTextColor'    => $btnTextColor,
            'btnStyle'        => $btnStyle,
            'logoPath'        => $logoPath,
            'faviconPath'     => $faviconPath,
            'newsRssFeeds'    => $newsRssFeeds,
            'blockedDomains'  => $blockedDomains,
            'blockedSources'  => $blockedSources,
            'saved'           => $error === null,
            'error'           => $error,
        ]);
    }
}
