<?php

function readAccountImport($file) {
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
        !is_uploaded_file($file['tmp_name'] ?? '') || ($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new DomainException('File tải lên không hợp lệ hoặc vượt 2 MB.');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $rows = [];
    if ($ext === 'csv' && in_array($mime, ['text/plain','text/csv','application/csv','application/vnd.ms-excel'], true)) {
        $handle = fopen($file['tmp_name'], 'r');
        try {
            while (($row = fgetcsv($handle, 65536)) !== false) {
                if ($row === [null]) { continue; }
                $rows[] = $row;
                if (count($rows) > 2001 || count($row) > 30) { throw new DomainException('Tối đa 2000 dòng dữ liệu và 30 cột.'); }
            }
        } finally { fclose($handle); }
    } elseif ($ext === 'xlsx' && in_array($mime, ['application/zip','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/octet-stream'], true)) {
        if (!class_exists('ZipArchive')) { throw new DomainException('Máy chủ chưa hỗ trợ XLSX. Vui lòng dùng CSV.'); }
        $zip = new ZipArchive();
        if ($zip->open($file['tmp_name']) !== true) { throw new DomainException('File XLSX không hợp lệ.'); }
        try {
            $total = 0;
            if ($zip->numFiles > 1000) { throw new DomainException('File XLSX quá phức tạp.'); }
            for ($i=0; $i<$zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $total += $stat['size'];
                if ($stat['size'] > 10*1024*1024 || $total > 20*1024*1024) { throw new DomainException('Nội dung giải nén vượt giới hạn.'); }
            }
            $xml = function ($path) use ($zip) {
                $source = $zip->getFromName($path);
                if ($source === false || preg_match('/<!DOCTYPE|<!ENTITY/i', $source)) { throw new DomainException('XML không hợp lệ.'); }
                $previous = libxml_use_internal_errors(true);
                try {
                    $doc = simplexml_load_string($source, 'SimpleXMLElement', LIBXML_NONET);
                    if (!$doc) { throw new DomainException('Không đọc được XML.'); }
                    return $doc;
                } finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
            };
            $shared = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                foreach ($xml('xl/sharedStrings.xml')->xpath('//*[local-name()="si"]') as $si) {
                    $parts = $si->xpath('.//*[local-name()="t"]');
                    $shared[] = implode('', array_map('strval', $parts));
                }
            }
            foreach ($xml('xl/worksheets/sheet1.xml')->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') as $row) {
                $cells = [];
                foreach ($row->children() as $cell) {
                    if (!preg_match('/^([A-Z]+)[0-9]+$/', (string)$cell['r'], $match)) { throw new DomainException('Địa chỉ ô không hợp lệ.'); }
                    $column = 0;
                    foreach (str_split($match[1]) as $letter) { $column = $column * 26 + ord($letter) - 64; }
                    if ($column > 30) { throw new DomainException('Tối đa 30 cột.'); }
                    $value = (string)$cell->v;
                    if ((string)$cell['t'] === 's') { $value = $shared[(int)$value] ?? ''; }
                    if ((string)$cell['t'] === 'inlineStr') { $value = implode('', array_map('strval', $cell->xpath('.//*[local-name()="t"]'))); }
                    $cells[$column-1] = $value;
                }
                if (!$cells) { continue; }
                $values = array_fill(0, max(array_keys($cells))+1, '');
                foreach ($cells as $column => $value) { $values[$column] = $value; }
                $rows[] = $values;
                if (count($rows) > 2001) { throw new DomainException('Tối đa 2000 dòng dữ liệu.'); }
            }
        } finally { $zip->close(); }
    } else { throw new DomainException('Chỉ chấp nhận file CSV hoặc XLSX đúng định dạng.'); }
    if (count($rows) < 2) { throw new DomainException('File không có dữ liệu.'); }
    $headers = array_map('trim', array_shift($rows));
    $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
    if (count(array_unique($headers)) !== count($headers) || in_array('', $headers, true)) { throw new DomainException('Tên cột trống hoặc trùng.'); }
    foreach (['email','ho_ten','mat_khau'] as $required) {
        if (!in_array($required, $headers, true)) { throw new DomainException('Thiếu cột ' . $required . '.'); }
    }
    $data = [];
    foreach ($rows as $row) {
        if (count($row) > count($headers)) { throw new DomainException('Dòng dữ liệu vượt số cột tiêu đề.'); }
        $data[] = array_combine($headers, array_pad($row, count($headers), ''));
    }
    return $data;
}

function importSchoolAccounts($rows, $role) {
    requireRole(ROLE_LANH_DAO);
    $definitions = [ROLE_SINH_VIEN => ['sinh_vien', 'ma_sinh_vien', ['lop','khoa_hoc','chuyen_nganh','so_dien_thoai']],
        ROLE_GIANG_VIEN => ['giang_vien', 'ma_giang_vien', ['khoa','chuyen_mon','hoc_ham_hoc_vi','so_dien_thoai']]];
    if (!isset($definitions[$role])) { throw new DomainException('Không cho import tài khoản Lãnh đạo.'); }
    [$table, $code, $fields] = $definitions[$role];
    $db = Database::getInstance()->getConnection();
    $roleRow = (new VaiTroModel())->findByMa($role);
    $db->beginTransaction();
    try {
        foreach ($rows as $index => $row) {
            $email = trim($row['email'] ?? '');
            $password = $row['mat_khau'] ?? '';
            $name = trim($row['ho_ten'] ?? '');
            $identifier = trim($row[$code] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email)>255 || $name==='' || mb_strlen($name)>255 ||
                $identifier==='' || strlen($identifier)>50 || strlen($password)<PASSWORD_MIN_LENGTH || strlen($password)>72 ||
                (!empty($row['vai_tro']) && $row['vai_tro'] !== $role)) {
                throw new DomainException('Dòng ' . ($index+2) . ' không hợp lệ. Bắt buộc mật khẩu riêng và đúng vai trò.');
            }
            $stmt = $db->prepare('INSERT INTO nguoi_dung (email,mat_khau,ho_ten,vai_tro_id,trang_thai) VALUES (?,?,?,?,?)');
            $stmt->execute([$email, hashPassword($password), $name, $roleRow['id'], 'active']);
            $id = $db->lastInsertId();
            $profile = ['nguoi_dung_id' => $id, $code => $identifier];
            foreach ($fields as $field) { $profile[$field] = trim($row[$field] ?? ''); }
            $columns = array_keys($profile);
            $stmt = $db->prepare('INSERT INTO ' . $table . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')');
            $stmt->execute(array_values($profile));
        }
        auditSecurity('account.import.' . $role, count($rows));
        $db->commit();
        return count($rows);
    } catch (Throwable $error) {
        if ($db->inTransaction()) { $db->rollBack(); }
        throw $error;
    }
}
