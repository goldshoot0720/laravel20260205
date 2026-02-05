<?php
$pageTitle = '例行事項';
$pdo = getConnection();
$items = $pdo->query("SELECT * FROM routine ORDER BY created_at DESC")->fetchAll();
?>

<div class="content-header">
    <h1>鋒兄例行</h1>
</div>

<div class="content-body">
    <button class="btn btn-primary" onclick="openModal()">新增例行事項</button>
    <?php $csvTable = 'routine'; include 'includes/csv_buttons.php'; ?>

    <table class="table" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>名稱</th>
                <th>備註</th>
                <th>上次執行 1</th>
                <th>上次執行 2</th>
                <th>上次執行 3</th>
                <th>連結</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="7" style="text-align: center; color: #999;">暫無例行事項</td></tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['note'] ?? '-'); ?></td>
                        <td><?php echo formatDateTime($item['lastdate1']); ?></td>
                        <td><?php echo formatDateTime($item['lastdate2']); ?></td>
                        <td><?php echo formatDateTime($item['lastdate3']); ?></td>
                        <td><?php echo $item['link'] ? '<a href="'.htmlspecialchars($item['link']).'" target="_blank">連結</a>' : '-'; ?></td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="markDone('<?php echo $item['id']; ?>')">完成</button>
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
        <h2 id="modalTitle">新增例行事項</h2>
        <form id="itemForm">
            <input type="hidden" id="itemId" name="id">
            <div class="form-group">
                <label>名稱 *</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label>備註</label>
                <input type="text" class="form-control" id="note" name="note">
            </div>
            <div class="form-group">
                <label>連結</label>
                <input type="url" class="form-control" id="link" name="link">
            </div>
            <div class="form-group">
                <label>圖片網址</label>
                <input type="url" class="form-control" id="photo" name="photo">
            </div>
            <div class="form-row">
                <div class="form-group" style="flex:1">
                    <label>上次執行 1</label>
                    <input type="datetime-local" class="form-control" id="lastdate1" name="lastdate1">
                </div>
                <div class="form-group" style="flex:1">
                    <label>上次執行 2</label>
                    <input type="datetime-local" class="form-control" id="lastdate2" name="lastdate2">
                </div>
                <div class="form-group" style="flex:1">
                    <label>上次執行 3</label>
                    <input type="datetime-local" class="form-control" id="lastdate3" name="lastdate3">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">儲存</button>
        </form>
    </div>
</div>

<script>
const TABLE = 'routine';

function openModal() {
    document.getElementById('modal').style.display = 'flex';
    document.getElementById('modalTitle').textContent = '新增例行事項';
    document.getElementById('itemForm').reset();
    document.getElementById('itemId').value = '';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

function markDone(id) {
    fetch(`api.php?action=get&table=${TABLE}&id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data) {
                const d = res.data;
                const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
                const data = {
                    lastdate3: d.lastdate2,
                    lastdate2: d.lastdate1,
                    lastdate1: now
                };
                fetch(`api.php?action=update&table=${TABLE}&id=${id}`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) location.reload();
                    else alert('更新失敗');
                });
            }
        });
}

function editItem(id) {
    fetch(`api.php?action=get&table=${TABLE}&id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data) {
                const d = res.data;
                document.getElementById('itemId').value = d.id;
                document.getElementById('name').value = d.name || '';
                document.getElementById('note').value = d.note || '';
                document.getElementById('link').value = d.link || '';
                document.getElementById('photo').value = d.photo || '';
                document.getElementById('lastdate1').value = d.lastdate1 ? d.lastdate1.replace(' ', 'T').slice(0, 16) : '';
                document.getElementById('lastdate2').value = d.lastdate2 ? d.lastdate2.replace(' ', 'T').slice(0, 16) : '';
                document.getElementById('lastdate3').value = d.lastdate3 ? d.lastdate3.replace(' ', 'T').slice(0, 16) : '';
                document.getElementById('modalTitle').textContent = '編輯例行事項';
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
        note: document.getElementById('note').value,
        link: document.getElementById('link').value,
        photo: document.getElementById('photo').value,
        lastdate1: document.getElementById('lastdate1').value ? document.getElementById('lastdate1').value.replace('T', ' ') + ':00' : null,
        lastdate2: document.getElementById('lastdate2').value ? document.getElementById('lastdate2').value.replace('T', ' ') + ':00' : null,
        lastdate3: document.getElementById('lastdate3').value ? document.getElementById('lastdate3').value.replace('T', ' ') + ':00' : null
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
