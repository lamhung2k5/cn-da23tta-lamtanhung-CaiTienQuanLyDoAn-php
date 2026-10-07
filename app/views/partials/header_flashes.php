<?php ?>

<?php if ($flashSuccess || $flashError || $flashWarning || $flashInfo): ?>
<style>
/* Custom Alert Styles - Nổi bật hơn */
.custom-alert {
    border: none;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    font-size: 1rem;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    animation: slideInDown 0.4s ease-out;
    position: relative;
    overflow: hidden;
}

.custom-alert::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 5px;
}

.custom-alert i {
    font-size: 1.5rem;
    margin-right: 0.75rem;
    vertical-align: middle;
}

.custom-alert .btn-close {
    padding: 0.75rem;
    opacity: 0.7;
}

.custom-alert .btn-close:hover {
    opacity: 1;
}

/* Success Alert */
.custom-alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    border-left: 5px solid #28a745;
}

.custom-alert-success::before {
    background: #28a745;
}

.custom-alert-success i {
    color: #28a745;
}

/* Error Alert */
.custom-alert-danger {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
    border-left: 5px solid #dc3545;
}

.custom-alert-danger::before {
    background: #dc3545;
}

.custom-alert-danger i {
    color: #dc3545;
}

/* Warning Alert */
.custom-alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
    border-left: 5px solid #ffc107;
}

.custom-alert-warning::before {
    background: #ffc107;
}

.custom-alert-warning i {
    color: #ffc107;
}

/* Info Alert */
.custom-alert-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    color: #0c5460;
    border-left: 5px solid #17a2b8;
}

.custom-alert-info::before {
    background: #17a2b8;
}

.custom-alert-info i {
    color: #17a2b8;
}

@keyframes slideInDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Auto dismiss animation */
.custom-alert.dismissing {
    animation: slideOutUp 0.3s ease-out forwards;
}

@keyframes slideOutUp {
    from {
        transform: translateY(0);
        opacity: 1;
    }
    to {
        transform: translateY(-100%);
        opacity: 0;
    }
}
</style>

<div class="container-fluid flash-messages-container" style="padding-top: 1rem; padding-right: 1rem; padding-left: 1rem;">
    <?php if ($flashSuccess): ?>
        <div class="alert custom-alert custom-alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <strong>Thành công</strong> <?= $flashSuccess ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($flashError): ?>
        <div class="alert custom-alert custom-alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Lỗi</strong> <?= $flashError ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($flashWarning): ?>
        <div class="alert custom-alert custom-alert-warning alert-dismissible fade show" role="alert">
            <strong>Cảnh báo</strong> <?= $flashWarning ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($flashInfo): ?>
        <div class="alert custom-alert custom-alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle-fill"></i>
            <strong>Thông tin</strong> <?= $flashInfo ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>

<script>
// Auto dismiss alerts after 5 seconds (longer for error messages)
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.custom-alert');
    alerts.forEach(function(alert) {
        // Thời gian hiển thị: 5 giây cho lỗi, 3 giây cho success
        const dismissTime = alert.classList.contains('custom-alert-danger') ? 5000 : 3000;
        
        setTimeout(function() {
            alert.classList.add('dismissing');
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 300);
        }, dismissTime);
    });
});
</script>
<?php endif; ?>

