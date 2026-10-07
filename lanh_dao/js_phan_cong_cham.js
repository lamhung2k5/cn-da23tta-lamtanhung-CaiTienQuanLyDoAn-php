// JavaScript cho chức năng phân công giảng viên chấm

let currentDangKyId = null;
let currentButton = null;
let allButtons = [];
let currentIndex = 0;
let currentViewIndex = 0; // Index cho modal xem chi tiết
const modal = new bootstrap.Modal(document.getElementById('modalChonGiangVien'));
const modalXemChiTiet = new bootstrap.Modal(document.getElementById('modalXemChiTiet'));

// Khởi tạo danh sách tất cả các nút chọn giảng viên
function initButtonList() {
    allButtons = Array.from(document.querySelectorAll('.sinh-vien-row'));
}

// Cập nhật trạng thái nút điều hướng
function updateNavigationButtons() {
    const btnTruoc = document.getElementById('btnSinhVienTruoc');
    const btnSau = document.getElementById('btnSinhVienSau');
    
    btnTruoc.disabled = currentIndex === 0;
    btnSau.disabled = currentIndex === allButtons.length - 1;
    
    // Hiển thị số thứ tự
    document.getElementById('sttSinhVienBadge').textContent = `${currentIndex + 1}/${allButtons.length}`;
}

// Mở modal cho một sinh viên
function openModalForStudent(row, index) {
    currentButton = row;
    currentIndex = index;
    currentDangKyId = row.getAttribute('data-dang-ky-id');
    const tenSinhVien = row.getAttribute('data-sinh-vien');
    const giangVienChamId = row.getAttribute('data-gv-cham-id');
    
    document.getElementById('tenSinhVienModal').textContent = tenSinhVien;
    document.getElementById('searchGiangVien').value = '';
    
    // Reset hiển thị tất cả giảng viên
    document.querySelectorAll('.giang-vien-item').forEach(item => {
        item.style.display = 'block';
    });
    document.getElementById('noResultMessage').style.display = 'none';
    
    const container = document.getElementById('danhSachGiangVienModal');
    let selectedItem = null;
    
    // Highlight giảng viên đang được chọn
    document.querySelectorAll('.chon-gv-item').forEach(btn => {
        const gvId = btn.getAttribute('data-gv-id');
        if (gvId == giangVienChamId) {
            btn.classList.remove('btn-light');
            btn.classList.add('btn-primary', 'text-white');
            selectedItem = btn.closest('.giang-vien-item');
        } else {
            btn.classList.remove('btn-primary', 'text-white');
            btn.classList.add('btn-light');
        }
    });
    
    // Di chuyển giảng viên được chọn lên đầu danh sách
    if (selectedItem && container) {
        container.insertBefore(selectedItem, container.firstChild);
    }
    
    updateNavigationButtons();
}

// Khởi tạo khi trang load
document.addEventListener('DOMContentLoaded', function() {
    initButtonList();
});

// Nút phân công nhanh - mở modal cho sinh viên đầu tiên
document.getElementById('btnPhanCongNhanh')?.addEventListener('click', function() {
    if (allButtons.length > 0) {
        openModalForStudent(allButtons[0], 0);
        modal.show();
    }
});

// Nút sinh viên trước
document.getElementById('btnSinhVienTruoc')?.addEventListener('click', function() {
    if (currentIndex > 0) {
        currentIndex--;
        openModalForStudent(allButtons[currentIndex], currentIndex);
    }
});

// Nút sinh viên sau
document.getElementById('btnSinhVienSau')?.addEventListener('click', function() {
    if (currentIndex < allButtons.length - 1) {
        currentIndex++;
        openModalForStudent(allButtons[currentIndex], currentIndex);
    }
});

// Tìm kiếm giảng viên
document.getElementById('searchGiangVien')?.addEventListener('input', function() {
    const searchText = this.value.toLowerCase().trim();
    const items = document.querySelectorAll('.giang-vien-item');
    let hasResult = false;
    
    items.forEach(item => {
        const ten = item.getAttribute('data-ten');
        const ma = item.getAttribute('data-ma');
        
        if (ten.includes(searchText) || ma.includes(searchText)) {
            item.style.display = 'block';
            hasResult = true;
        } else {
            item.style.display = 'none';
        }
    });
    
    document.getElementById('noResultMessage').style.display = hasResult ? 'none' : 'block';
});

// Chọn giảng viên
document.querySelectorAll('.chon-gv-item').forEach(btn => {
    btn.addEventListener('click', function() {
        const giangVienId = this.getAttribute('data-gv-id');
        const giangVienTen = this.getAttribute('data-gv-ten');
        const currentGvChamId = currentButton.getAttribute('data-gv-cham-id');
        
        // Nếu click vào giảng viên đang được chọn thì xóa phân công
        if (giangVienId == currentGvChamId) {
            phanCongGiangVien(currentDangKyId, 0, null, false);
        } else {
            // Chọn giảng viên mới
            phanCongGiangVien(currentDangKyId, giangVienId, giangVienTen, false);
        }
    });
});

// Nút xem chi tiết
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-xem-chi-tiet')) {
        const btn = e.target.closest('.btn-xem-chi-tiet');
        const row = btn.closest('.sinh-vien-row');
        currentViewIndex = allButtons.indexOf(row);
        
        showDetailModal(currentViewIndex);
    }
});

// Hiển thị modal xem chi tiết
function showDetailModal(index) {
    if (index < 0 || index >= allButtons.length) return;
    
    const row = allButtons[index];
    const btn = row.querySelector('.btn-xem-chi-tiet');
    
    if (!btn) {
        alert('Sinh viên này chưa được phân công giảng viên chấm');
        return;
    }
    
    const detailSinhVien = document.getElementById('detailSinhVien');
    const detailMSSV = document.getElementById('detailMSSV');
    const detailLop = document.getElementById('detailLop');
    const detailDeTai = document.getElementById('detailDeTai');
    const detailGVCham = document.getElementById('detailGVCham');
    const sttXemChiTiet = document.getElementById('sttXemChiTiet');
    
    if (detailSinhVien) detailSinhVien.textContent = btn.getAttribute('data-sv-ten');
    if (detailMSSV) detailMSSV.textContent = btn.getAttribute('data-sv-mssv');
    if (detailLop) detailLop.textContent = btn.getAttribute('data-sv-lop');
    if (detailDeTai) detailDeTai.textContent = btn.getAttribute('data-de-tai');
    if (detailGVCham) detailGVCham.textContent = btn.getAttribute('data-gv-ten');
    if (sttXemChiTiet) sttXemChiTiet.textContent = `${index + 1}/${allButtons.length}`;
    
    // Cập nhật trạng thái nút
    updateViewNavigationButtons();
    
    modalXemChiTiet.show();
}

// Cập nhật trạng thái nút điều hướng modal xem
function updateViewNavigationButtons() {
    const btnTruoc = document.getElementById('btnXemTruoc');
    const btnSau = document.getElementById('btnXemSau');
    
    if (btnTruoc) btnTruoc.disabled = currentViewIndex === 0;
    if (btnSau) btnSau.disabled = currentViewIndex === allButtons.length - 1;
}

// Nút xem trước
document.getElementById('btnXemTruoc')?.addEventListener('click', function() {
    if (currentViewIndex > 0) {
        currentViewIndex--;
        // Tìm sinh viên có GV chấm
        while (currentViewIndex >= 0 && !allButtons[currentViewIndex].querySelector('.btn-xem-chi-tiet')) {
            currentViewIndex--;
        }
        if (currentViewIndex >= 0) {
            showDetailModal(currentViewIndex);
        }
    }
});

// Nút xem sau
document.getElementById('btnXemSau')?.addEventListener('click', function() {
    if (currentViewIndex < allButtons.length - 1) {
        currentViewIndex++;
        // Tìm sinh viên có GV chấm
        while (currentViewIndex < allButtons.length && !allButtons[currentViewIndex].querySelector('.btn-xem-chi-tiet')) {
            currentViewIndex++;
        }
        if (currentViewIndex < allButtons.length) {
            showDetailModal(currentViewIndex);
        }
    }
});

// Nút sửa phân công
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-sua-phan-cong')) {
        const row = e.target.closest('.sinh-vien-row');
        const index = allButtons.indexOf(row);
        openModalForStudent(row, index);
        modal.show();
    }
});

// Nút thêm phân công
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-them-phan-cong')) {
        const row = e.target.closest('.sinh-vien-row');
        const index = allButtons.indexOf(row);
        openModalForStudent(row, index);
        modal.show();
    }
});

// Nút xóa phân công
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-xoa-phan-cong')) {
        if (confirm('Bạn có chắc muốn xóa phân công giảng viên chấm?')) {
            const row = e.target.closest('.sinh-vien-row');
            const dangKyId = row.getAttribute('data-dang-ky-id');
            
            fetch('xu_ly_phan_cong_cham.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `dang_ky_id=${dangKyId}&giang_vien_cham_id=0`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cập nhật cell
                    const cell = row.querySelector('.gv-cham-cell');
                    cell.innerHTML = '<span class="text-muted">Chưa được phân công</span>';
                    
                    // Ẩn nút thao tác
                    const actionCell = row.querySelector('td:last-child');
                    actionCell.innerHTML = '<span class="text-muted">-</span>';
                    
                    // Highlight
                    row.style.backgroundColor = '#f8d7da';
                    setTimeout(() => {
                        row.style.backgroundColor = '';
                    }, 1000);
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xóa phân công');
            });
        }
    }
});

// Hàm phân công giảng viên
function phanCongGiangVien(dangKyId, giangVienId, giangVienTen, autoNext = false) {
    fetch('xu_ly_phan_cong_cham.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `dang_ky_id=${dangKyId}&giang_vien_cham_id=${giangVienId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật cell trong bảng
            const cell = currentButton.querySelector('.gv-cham-cell');
            const actionCell = currentButton.querySelector('td:last-child');
            
            if (giangVienId == 0) {
                cell.innerHTML = '<span class="text-muted">Chưa được phân công</span>';
                actionCell.innerHTML = `
                    <button class="btn btn-sm btn-info btn-them-phan-cong" 
                            title="Thêm giảng viên chấm">
                        <i class="bi bi-person-plus"></i>
                    </button>
                `;
                
                // Cập nhật data attributes
                currentButton.setAttribute('data-gv-cham-id', '');
                currentButton.setAttribute('data-gv-cham-ten', '');
                
                // Xóa highlight trong modal
                document.querySelectorAll('.chon-gv-item').forEach(btn => {
                    btn.classList.remove('btn-primary', 'text-white');
                    btn.classList.add('btn-light');
                });
            } else {
                cell.innerHTML = `<span class="text-dark">${giangVienTen}</span>`;
                // Cập nhật data attributes
                currentButton.setAttribute('data-gv-cham-id', giangVienId);
                currentButton.setAttribute('data-gv-cham-ten', giangVienTen);
                
                // Cập nhật highlight trong modal
                document.querySelectorAll('.chon-gv-item').forEach(btn => {
                    const gvId = btn.getAttribute('data-gv-id');
                    if (gvId == giangVienId) {
                        btn.classList.remove('btn-light');
                        btn.classList.add('btn-primary', 'text-white');
                    } else {
                        btn.classList.remove('btn-primary', 'text-white');
                        btn.classList.add('btn-light');
                    }
                });
                
                // Cập nhật action cell với các nút
                const svTen = currentButton.getAttribute('data-sinh-vien') || '';
                const svMssv = currentButton.querySelector('td:nth-child(3)')?.textContent || '';
                const svLop = currentButton.querySelector('td:nth-child(4)')?.textContent || '';
                const deTai = currentButton.querySelector('td:nth-child(5)')?.textContent || '';
                
                actionCell.innerHTML = `
                    <button class="btn btn-sm btn-info btn-xem-chi-tiet" 
                            data-sv-ten="${svTen}"
                            data-sv-mssv="${svMssv}"
                            data-sv-lop="${svLop}"
                            data-de-tai="${deTai}"
                            data-gv-ten="${giangVienTen}"
                            data-gv-ma=""
                            title="Xem chi tiết">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning btn-sua-phan-cong" 
                            title="Sửa phân công">
                        <i class="bi bi-pencil"></i>
                    </button>
                `;
            }
            
            // Highlight row
            currentButton.style.backgroundColor = '#d4edda';
            setTimeout(() => {
                currentButton.style.backgroundColor = '';
            }, 1000);
            
            // Tự động chuyển sang sinh viên tiếp theo
            if (autoNext && currentIndex < allButtons.length - 1) {
                setTimeout(() => {
                    currentIndex++;
                    openModalForStudent(allButtons[currentIndex], currentIndex);
                }, 500);
            }
            // Modal vẫn mở, không đóng
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi phân công giảng viên chấm');
    });
}
