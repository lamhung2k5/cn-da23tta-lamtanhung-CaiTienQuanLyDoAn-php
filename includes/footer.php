<?php
/**
 * FOOTER CHUNG
 * File footer dùng chung cho toàn bộ hệ thống
 */

// Lấy năm hiện tại
$currentYear = date('Y');

// Kiểm tra user đã đăng nhập chưa
$isLoggedIn = isLoggedIn();
?>

<!-- Footer -->
<footer class="footer mt-auto py-4 bg-light border-top">
    <div class="container">
        <div class="row">
            <!-- Left Section - Info -->
            <div class="col-md-8">
                <div class="footer-title mb-3">
                    <h4 style="font-weight: 700; color: #000; margin-bottom: 0.5rem;">
                        Khoa Công nghệ thông tin - Đại học Trà Vinh © <?= $currentYear ?>
                    </h4>
                    <p class="footer-subtitle text-muted mb-0" style="font-size: 0.9rem;">
                        Faculty of Information Technology - Tra Vinh University
                    </p>
                </div>
                
                <div class="footer-divider mb-3" style="width: 100px; height: 3px; background: linear-gradient(135deg, #003d82 0%, #0052a8 100%);"></div>
                
                <div class="footer-contact" style="font-size: 0.9rem;">
                    <p class="mb-2">
                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                        Số 126, Nguyễn Thiện Thành, Khóm 4, Phường Hòa Thuận, Tỉnh Vĩnh Long
                    </p>
                    <p class="mb-2">
                        <i class="bi bi-telephone-fill text-success me-2"></i>
                        (+84) 294.3855246 (Ext: 135 - 203)
                    </p>
                    <p class="mb-2">
                        <i class="bi bi-envelope-fill text-primary me-2"></i>
                        <a href="mailto:ktcn@tvu.edu.vn" class="text-decoration-none">ktcn@tvu.edu.vn</a>
                    </p>
                    <p class="mb-3">
                        <i class="bi bi-globe text-info me-2"></i>
                        <a href="https://cet.tvu.edu.vn" target="_blank" class="text-decoration-none">https://cet.tvu.edu.vn</a>
                    </p>
                </div>
                
                <div class="footer-brand d-flex align-items-center gap-3">
                    <div class="brand-line" style="width: 150px; height: 2px; background: linear-gradient(90deg, #003d82 0%, transparent 100%);"></div>
                    <span style="font-weight: 600; color: #003d82;">Tra Vinh University</span>
                </div>
            </div>
            
            <!-- Right Section - Social -->
            <div class="col-md-4 text-end">
                <div class="social-icons d-flex justify-content-end gap-3">
                    <a href="https://facebook.com" target="_blank" class="social-icon" 
                       style="width: 50px; height: 50px; border-radius: 50%; background-color: #1877f2; color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 1.5rem; transition: all 0.3s ease;"
                       onmouseover="this.style.transform='scale(1.1)'" 
                       onmouseout="this.style.transform='scale(1)'">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://youtube.com" target="_blank" class="social-icon" 
                       style="width: 50px; height: 50px; border-radius: 50%; background-color: #ff0000; color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 1.5rem; transition: all 0.3s ease;"
                       onmouseover="this.style.transform='scale(1.1)'" 
                       onmouseout="this.style.transform='scale(1)'">
                        <i class="bi bi-youtube"></i>
                    </a>
                    <a href="https://github.com" target="_blank" class="social-icon" 
                       style="width: 50px; height: 50px; border-radius: 50%; background-color: #333; color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 1.5rem; transition: all 0.3s ease;"
                       onmouseover="this.style.transform='scale(1.1)'" 
                       onmouseout="this.style.transform='scale(1)'">
                        <i class="bi bi-github"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button type="button" class="btn btn-primary btn-floating btn-lg" id="btn-back-to-top" 
        style="position: fixed; bottom: 20px; right: 20px; display: none; z-index: 1000;">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (Optional - nếu cần) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Custom JavaScript -->
<script>
// Back to top button
let mybutton = document.getElementById("btn-back-to-top");

window.onscroll = function () {
    scrollFunction();
};

function scrollFunction() {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        mybutton.style.display = "block";
    } else {
        mybutton.style.display = "none";
    }
}

mybutton.addEventListener("click", function() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
});

// Auto hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Confirm before delete
document.querySelectorAll('[data-confirm]').forEach(function(element) {
    element.addEventListener('click', function(e) {
        if (!confirm(this.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    });
});

// Form validation
(function () {
    'use strict'
    const forms = document.querySelectorAll('.needs-validation')
    Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})();

// Tooltips
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

// Popovers
const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
</script>

<!-- Custom JS file (nếu có) -->
<script src="<?= BASE_URL ?>assets/js/main.js?v=<?= time() ?>"></script>

</body>
</html>
