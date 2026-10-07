<?php

require_once __DIR__ . '/page_data.php';

class ProfileController
{
    public function show(): void
    {
        requireLogin();

        $user = getCurrentUser();
        $pageTitle = 'Hồ sơ cá nhân';
        $nguoiDungModel = new NguoiDungModel();
        $userInfo = $nguoiDungModel->getUserFullInfo($user['id']);

        // The legacy header emits its UTF-8 BOM before preparing header data.
        echo "\xEF\xBB\xBF";
        View::render('partials/header.php', preparePageData($pageTitle));

        // Legacy header consumes flashes only after navigation is rendered.
        $flashSuccess = getFlashMessage('success');
        $flashError = getFlashMessage('error');
        $flashWarning = getFlashMessage('warning');
        $flashInfo = getFlashMessage('info');
        View::render('partials/header_flashes.php', [
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'flashWarning' => $flashWarning,
            'flashInfo' => $flashInfo,
        ]);

        View::render('account/profile.php', [
            'userInfo' => $userInfo,
            'referer' => $_SERVER['HTTP_REFERER'] ?? '',
            'mode' => $_GET['mode'] ?? '',
            'sessionRole' => $_SESSION['vai_tro'] ?? null,
        ]);
    }
}
