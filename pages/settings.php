<?php $pageTitle = '系統設定'; ?>

<div class="content-header">
    <h1>鋒兄設定</h1>
</div>

<div class="content-body">
    <div class="card">
        <h3 class="card-title">資料庫設定</h3>
        <table class="table">
            <tr>
                <th style="width: 200px;">目前環境</th>
                <td><span class="badge <?php echo $GLOBALS['ENV'] === 'remote' ? 'badge-danger' : 'badge-success'; ?>"><?php echo strtoupper($GLOBALS['ENV']); ?></span></td>
            </tr>
            <tr>
                <th>資料庫主機</th>
                <td><?php echo DB_HOST; ?></td>
            </tr>
            <tr>
                <th>資料庫名稱</th>
                <td><?php echo DB_NAME; ?></td>
            </tr>
            <tr>
                <th>資料庫使用者</th>
                <td><?php echo DB_USER; ?></td>
            </tr>
        </table>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h3 class="card-title">系統資訊</h3>
        <table class="table">
            <tr>
                <th style="width: 200px;">PHP 版本</th>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <th>伺服器軟體</th>
                <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
            </tr>
            <tr>
                <th>伺服器時間</th>
                <td><?php echo date('Y-m-d H:i:s'); ?></td>
            </tr>
        </table>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h3 class="card-title">資料庫統計</h3>
        <?php
        $pdo = getConnection();
        $tables = [
            'subscription' => '訂閱',
            'food' => '食品',
            'article' => '筆記/文章',
            'commonaccount' => '常用帳號',
            'image' => '圖片',
            'music' => '音樂',
            'podcast' => '播客',
            'commondocument' => '文件',
            'bank' => '銀行',
            'routine' => '例行事項'
        ];
        ?>
        <table class="table">
            <thead>
                <tr>
                    <th>資料表</th>
                    <th>名稱</th>
                    <th>筆數</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tables as $table => $name): ?>
                    <?php
                    try {
                        $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
                    } catch (Exception $e) {
                        $count = '表格不存在';
                    }
                    ?>
                    <tr>
                        <td><code><?php echo $table; ?></code></td>
                        <td><?php echo $name; ?></td>
                        <td><?php echo $count; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
