<?php
$pageTitle = '首頁';
$pdo = getConnection();

$subscriptionCount = $pdo->query("SELECT COUNT(*) FROM subscription")->fetchColumn();
$foodCount = $pdo->query("SELECT COUNT(*) FROM food")->fetchColumn();
$bankCount = $pdo->query("SELECT COUNT(*) FROM bank")->fetchColumn();
$routineCount = $pdo->query("SELECT COUNT(*) FROM routine")->fetchColumn();
$imageCount = $pdo->query("SELECT COUNT(*) FROM image")->fetchColumn();
$musicCount = $pdo->query("SELECT COUNT(*) FROM music")->fetchColumn();
$podcastCount = $pdo->query("SELECT COUNT(*) FROM podcast")->fetchColumn();
$documentCount = $pdo->query("SELECT COUNT(*) FROM commondocument")->fetchColumn();
?>

<div class="content-header">
    <h1>鋒兄首頁</h1>
</div>

<div class="content-body">
    <h2>歡迎來到鋒兄系統</h2>
    <p style="margin-top: 20px; line-height: 1.8;">
        這是您的個人管理系統，您可以透過左側選單瀏覽各項功能。
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px;">
        <a href="index.php?page=subscription" class="card" style="text-decoration: none; color: inherit;">
            <h3 class="card-title">訂閱管理</h3>
            <p style="font-size: 2rem; color: #3498db;"><?php echo $subscriptionCount; ?></p>
            <p>筆訂閱服務</p>
        </a>
        <a href="index.php?page=food" class="card" style="text-decoration: none; color: inherit;">
            <h3 class="card-title">食品管理</h3>
            <p style="font-size: 2rem; color: #27ae60;"><?php echo $foodCount; ?></p>
            <p>筆食品紀錄</p>
        </a>
        <a href="index.php?page=bank" class="card" style="text-decoration: none; color: inherit;">
            <h3 class="card-title">銀行管理</h3>
            <p style="font-size: 2rem; color: #e74c3c;"><?php echo $bankCount; ?></p>
            <p>個銀行帳戶</p>
        </a>
        <a href="index.php?page=routine" class="card" style="text-decoration: none; color: inherit;">
            <h3 class="card-title">例行事項</h3>
            <p style="font-size: 2rem; color: #9b59b6;"><?php echo $routineCount; ?></p>
            <p>筆例行事項</p>
        </a>
        <a href="index.php?page=images" class="card" style="text-decoration: none; color: inherit;">
            <h3 class="card-title">圖片庫</h3>
            <p style="font-size: 2rem; color: #f39c12;"><?php echo $imageCount; ?></p>
            <p>張圖片</p>
        </a>
        <a href="index.php?page=music" class="card" style="text-decoration: none; color: inherit;">
            <h3 class="card-title">音樂庫</h3>
            <p style="font-size: 2rem; color: #1abc9c;"><?php echo $musicCount; ?></p>
            <p>首音樂</p>
        </a>
        <a href="index.php?page=podcast" class="card" style="text-decoration: none; color: inherit;">
            <h3 class="card-title">播客</h3>
            <p style="font-size: 2rem; color: #e67e22;"><?php echo $podcastCount; ?></p>
            <p>個播客</p>
        </a>
        <a href="index.php?page=documents" class="card" style="text-decoration: none; color: inherit;">
            <h3 class="card-title">文件庫</h3>
            <p style="font-size: 2rem; color: #34495e;"><?php echo $documentCount; ?></p>
            <p>份文件</p>
        </a>
    </div>
</div>
