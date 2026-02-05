<?php
$pageTitle = '銀行管理';
$pdo = getConnection();
$items = $pdo->query("SELECT * FROM bank ORDER BY created_at DESC")->fetchAll();
$totalDeposit = $pdo->query("SELECT COALESCE(SUM(deposit), 0) FROM bank")->fetchColumn();
$totalWithdrawals = $pdo->query("SELECT COALESCE(SUM(withdrawals), 0) FROM bank")->fetchColumn();
?>

<div class="content-header">
    <h1>鋒兄銀行</h1>
</div>

<div class="content-body">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="card" style="background: linear-gradient(135deg, #27ae60, #219a52); color: #fff;">
            <h3>總存款</h3>
            <p style="font-size: 2rem; margin-top: 10px;"><?php echo formatMoney($totalDeposit); ?></p>
        </div>
        <div class="card" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: #fff;">
            <h3>總提款</h3>
            <p style="font-size: 2rem; margin-top: 10px;"><?php echo formatMoney($totalWithdrawals); ?></p>
        </div>
        <div class="card" style="background: linear-gradient(135deg, #3498db, #2980b9); color: #fff;">
            <h3>銀行數量</h3>
            <p style="font-size: 2rem; margin-top: 10px;"><?php echo count($items); ?></p>
        </div>
    </div>

    <button class="btn btn-primary" onclick="openModal()">新增銀行</button>
    <?php $csvTable = 'bank'; include 'includes/csv_buttons.php'; ?>

    <table class="table" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>名稱</th>
                <th>存款</th>
                <th>提款</th>
                <th>轉帳</th>
                <th>帳號</th>
                <th>卡號</th>
                <th>網站</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="8" style="text-align: center; color: #999;">暫無銀行資料</td></tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo formatMoney($item['deposit']); ?></td>
                        <td><?php echo formatMoney($item['withdrawals']); ?></td>
                        <td><?php echo formatMoney($item['transfer']); ?></td>
                        <td><?php echo htmlspecialchars($item['account'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($item['card'] ?? '-'); ?></td>
                        <td><?php echo $item['site'] ? '<a href="'.htmlspecialchars($item['site']).'" target="_blank">連結</a>' : '-'; ?></td>
                        <td>
                            <button class="btn btn-sm" onclick="editItem('<?php echo $item['id']; ?>')">編輯</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteItem('<?php echo $item['id']; ?>')">刪除</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="modal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">新增銀行</h2>
        <form id="itemForm">
            <input type="hidden" id="itemId" name="id">
            <div class="form-group">
                <label>名稱 *</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-row">
                <div class="form-group" style="flex:1">
                    <label>存款</label>
                    <input type="number" class="form-control" id="deposit" name="deposit">
                </div>
                <div class="form-group" style="flex:1">
                    <label>提款</label>
                    <input type="number" class="form-control" id="withdrawals" name="withdrawals">
                </div>
                <div class="form-group" style="flex:1">
                    <label>轉帳</label>
                    <input type="number" class="form-control" id="transfer" name="transfer">
                </div>
            </div>
            <div class="form-group">
                <label>帳號</label>
                <input type="text" class="form-control" id="account" name="account">
            </div>
            <div class="form-group">
                <label>卡號</label>
                <input type="text" class="form-control" id="card" name="card">
            </div>
            <div class="form-group">
                <label>地址</label>
                <input type="text" class="form-control" id="address" name="address">
            </div>
            <div class="form-group">
                <label>網站</label>
                <input type="url" class="form-control" id="site" name="site">
            </div>
            <div class="form-group">
                <label>活動網址</label>
                <input type="url" class="form-control" id="activity" name="activity">
            </div>
            <button type="submit" class="btn btn-primary">儲存</button>
        </form>
    </div>
</div>

<script>
const TABLE = 'bank';

function openModal() {
    document.getElementById('modal').style.display = 'flex';
    document.getElementById('modalTitle').textContent = '新增銀行';
    document.getElementById('itemForm').reset();
    document.getElementById('itemId').value = '';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

function editItem(id) {
    fetch(`api.php?action=get&table=${TABLE}&id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data) {
                const d = res.data;
                document.getElementById('itemId').value = d.id;
                document.getElementById('name').value = d.name || '';
                document.getElementById('deposit').value = d.deposit || '';
                document.getElementById('withdrawals').value = d.withdrawals || '';
                document.getElementById('transfer').value = d.transfer || '';
                document.getElementById('account').value = d.account || '';
                document.getElementById('card').value = d.card || '';
                document.getElementById('address').value = d.address || '';
                document.getElementById('site').value = d.site || '';
                document.getElementById('activity').value = d.activity || '';
                document.getElementById('modalTitle').textContent = '編輯銀行';
                document.getElementById('modal').style.display = 'flex';
            }
        });
}

function deleteItem(id) {
    if (confirm('確定要刪除嗎？')) {
        fetch(`api.php?action=delete&table=${TABLE}&id=${id}`)
            .then(r => r.json())
            .then(res => {
                if (res.success) location.reload();
                else alert('刪除失敗');
            });
    }
}

document.getElementById('itemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('itemId').value;
    const action = id ? 'update' : 'create';
    const url = id ? `api.php?action=${action}&table=${TABLE}&id=${id}` : `api.php?action=${action}&table=${TABLE}`;

    const data = {
        name: document.getElementById('name').value,
        deposit: document.getElementById('deposit').value || 0,
        withdrawals: document.getElementById('withdrawals').value || 0,
        transfer: document.getElementById('transfer').value || 0,
        account: document.getElementById('account').value,
        card: document.getElementById('card').value,
        address: document.getElementById('address').value,
        site: document.getElementById('site').value,
        activity: document.getElementById('activity').value
    };

    fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) location.reload();
        else alert('儲存失敗: ' + (res.error || ''));
    });
});
</script>
