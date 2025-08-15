<?php
// FILE: includes/notification.php
// This file handles displaying notifications from session

// Function to show notification and clear it from session
function showNotifications()
{
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];

        // Check if notification is less than 5 seconds old
        if ((time() - $notification['time']) < 5) {
            $type = htmlspecialchars($notification['type']);
            $message = htmlspecialchars($notification['message']);

            $icon = 'info-circle';
            $color = '#17a2b8'; // info blue

            switch ($type) {
                case 'success':
                    $icon = 'check-circle';
                    $color = '#28a745'; // success green
                    break;
                case 'error':
                    $icon = 'exclamation-circle';
                    $color = '#dc3545'; // danger red
                    break;
                case 'warning':
                    $icon = 'exclamation-triangle';
                    $color = '#ffc107'; // warning yellow
                    break;
            }

            // Output toast notification HTML
            echo <<<HTML
            <div class="toast-notification" style="background-color: {$color};">
                <div class="toast-icon">
                    <i class="fas fa-{$icon}"></i>
                </div>
                <div class="toast-content">
                    <p>{$message}</p>
                </div>
                <button class="toast-close">×</button>
            </div>
            
            <style>
            .toast-notification {
                position: fixed;
                top: 20px;
                right: 20px;
                display: flex;
                align-items: center;
                padding: 12px 20px;
                border-radius: 4px;
                color: white;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 9999;
                min-width: 300px;
                max-width: 450px;
                opacity: 0;
                transform: translateY(-20px);
                animation: slideIn 0.3s forwards, fadeOut 0.5s 4s forwards;
            }
            
            .toast-icon {
                margin-right: 12px;
                font-size: 20px;
            }
            
            .toast-content {
                flex-grow: 1;
            }
            
            .toast-content p {
                margin: 0;
                font-size: 14px;
                font-weight: 500;
            }
            
            .toast-close {
                background: none;
                border: none;
                color: white;
                font-size: 20px;
                cursor: pointer;
                opacity: 0.8;
                padding: 0;
                margin-left: 10px;
            }
            
            .toast-close:hover {
                opacity: 1;
            }
            
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes fadeOut {
                from {
                    opacity: 1;
                }
                to {
                    opacity: 0;
                    transform: translateY(-20px);
                }
            }
            </style>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.querySelector('.toast-notification');
                if (toast) {
                    toast.querySelector('.toast-close').addEventListener('click', function() {
                        toast.style.animation = 'fadeOut 0.3s forwards';
                        setTimeout(() => {
                            toast.remove();
                        }, 300);
                    });
                    
                    // Auto remove after 5 seconds
                    setTimeout(() => {
                        if (toast.parentElement) {
                            toast.remove();
                        }
                    }, 5000);
                }
            });
            </script>
            HTML;
        }

        // Clear the notification
        unset($_SESSION['notification']);
    }
}
