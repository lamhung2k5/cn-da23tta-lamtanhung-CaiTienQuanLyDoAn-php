<?php

function preparePageData(string $pageTitle): array
{
    $currentUser = getCurrentUser();
    $isLoggedIn = isLoggedIn();
    $soThongBaoChuaDoc = 0;
    $danhSachThongBao = [];

    if ($isLoggedIn) {
        $thongBaoModel = new ThongBaoModel();
        $soThongBaoChuaDoc = $thongBaoModel->countChuaDoc($currentUser['id']);
        $danhSachThongBao = $thongBaoModel->getThongBaoByNguoiDung($currentUser['id'], 5);
    }

    return [
        'currentUser' => $currentUser,
        'isLoggedIn' => $isLoggedIn,
        'soThongBaoChuaDoc' => $soThongBaoChuaDoc,
        'danhSachThongBao' => $danhSachThongBao,
        'currentPage' => basename($_SERVER['PHP_SELF']),
        'currentDir' => basename(dirname($_SERVER['PHP_SELF'])),
        'pageTitle' => $pageTitle,
    ];
}
