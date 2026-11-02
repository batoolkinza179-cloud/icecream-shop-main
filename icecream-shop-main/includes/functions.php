<?php
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

function getProductImage($image) {
    return !empty($image) ? $image : ASSETS_URL . 'images/no-image.jpg';
}

function getRatingStars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '★';
        } else {
            $stars .= '☆';
        }
    }
    return $stars;
}

function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);
    
    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
    } else if ($days <= 7) {
        return ($days == 1) ? "yesterday" : "$days days ago";
    } else if ($weeks <= 4.3) {
        return ($weeks == 1) ? "1 week ago" : "$weeks weeks ago";
    } else if ($months <= 12) {
        return ($months == 1) ? "1 month ago" : "$months months ago";
    } else {
        return ($years == 1) ? "1 year ago" : "$years years ago";
    }
}

function generateOrderNumber() {
    return 'ORD' . strtoupper(uniqid());
}

function getOrderStatusBadge($status) {
    $colors = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'preparing' => 'primary',
        'out_for_delivery' => 'secondary',
        'delivered' => 'success',
        'cancelled' => 'danger'
    ];
    $color = isset($colors[$status]) ? $colors[$status] : 'secondary';
    return "<span class='badge badge-$color'>" . ucfirst($status) . "</span>";
}

function uploadImage($file, $target_dir) {
    $target_file = $target_dir . time() . '_' . basename($file['name']);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return ['success' => false, 'error' => 'File is not an image.'];
    }
    
    if ($file['size'] > 5000000) {
        return ['success' => false, 'error' => 'File is too large. Max 5MB.'];
    }
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'error' => 'Only JPG, JPEG, PNG, GIF & WEBP files are allowed.'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => true, 'path' => str_replace(ROOT_PATH, SITE_URL, $target_file)];
    } else {
        return ['success' => false, 'error' => 'Failed to upload image.'];
    }
}
?>