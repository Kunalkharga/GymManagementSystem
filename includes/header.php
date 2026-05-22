<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="en" class="dark">
    <style>
        /* Add inside <style> tag in header.php */
.table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

table {
    min-width: 800px;
}

@media (max-width: 1024px) {
    table {
        min-width: 700px;
    }
}

/* Better cards on mobile */
.card-grid {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}
    </style>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymSaas - <?= $page_title ?? 'Dashboard' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            content: [],
            theme: {
                extend: {}
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .sidebar { transition: all 0.3s; }
    </style>
</head>
<body class="bg-gray-950 text-white">