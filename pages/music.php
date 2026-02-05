<?php
$pageTitle = '音樂管理';
$pdo = getConnection();
$items = $pdo->query("SELECT * FROM music ORDER BY created_at DESC")->fetchAll();
?>

<div class="content-header">
    <h1>鋒兄音樂</h1>
</div>

<div class="content-body">
    <button class="btn btn-primary" onclick="openModal()">新增音樂</button>
    <?php $csvTable = 'music'; include 'includes/csv_buttons.php'; ?>

    <table class="table" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>封面</th>
                <th>名稱</th>
                <th>分類</th>
                <th>語言</th>
                <th>備註</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="6" style="text-align: center; color: #999;">暫無音樂</td></tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <?php if ($item['cover']): ?>
                                <img src="<?php echo htmlspecialchars($item['cover']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['category'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($item['language'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($item['note'] ?? '-'); ?></td>
                        <td>
                            <button class="btn btn-sm" onclick="viewLyrics('<?php echo $item['id']; ?>')">歌詞</button>
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
    <div class="modal-content" style="max-width: 600px;">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">新增音樂</h2>
        <form id="itemForm">
            <input type="hidden" id="itemId" name="id">
            <div class="form-group">
                <label>名稱 *</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label>檔案路徑</label>
                <input type="text" class="form-control" id="file" name="file">
            </div>
            <div class="form-group">
                <label>封面圖網址</label>
                <input type="url" class="form-control" id="cover" name="cover">
            </div>
            <div class="form-row">
                <div class="form-group" style="flex:1">
                    <label>分類</label>
                    <input type="text" class="form-control" id="category" name="category">
                </div>
                <div class="form-group" style="flex:1">
                    <label>語言</label>
                    <input type="text" class="form-control" id="language" name="language">
                </div>
            </div>
            <div class="form-group">
                <label>參考</label>
                <input type="text" class="form-control" id="ref" name="ref">
            </div>
            <div class="form-group">
                <label>備註</label>
                <input type="text" class="form-control" id="note" name="note">
            </div>
            <div class="form-group">
                <label>歌詞</label>
                <textarea class="form-control" id="lyrics" name="lyrics" rows="6"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">儲存</button>
        </form>
    </div>
</div>

<div id="lyricsModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeLyricsModal()">&times;</span>
        <h2 id="lyricsTitle">歌詞</h2>
        <pre id="lyricsContent" style="white-space: pre-wrap; font-family: inherit; line-height: 1.8;"></pre>
    </div>
</div>

<script>
const TABLE = 'music';

function openModal() {
    document.getElementById('modal').style.display = 'flex';
    document.getElementById('modalTitle').textContent = '新增音樂';
    document.getElementById('itemForm').reset();
    document.getElementById('itemId').value = '';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

function closeLyricsModal() {
    document.getElementById('lyricsModal').style.display = 'none';
}

function viewLyrics(id) {
    fetch(`api.php?action=get&table=${TABLE}&id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data) {
                document.getElementById('lyricsTitle').textContent = res.data.name + ' - 歌詞';
                document.getElementById('lyricsContent').textContent = res.data.lyrics || '暫無歌詞';
                document.getElementById('lyricsModal').style.display = 'flex';
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
                document.getElementById('file').value = d.file || '';
                document.getElementById('cover').value = d.cover || '';
                document.getElementById('category').value = d.category || '';
                document.getElementById('language').value = d.language || '';
                document.getElementById('ref').value = d.ref || '';
                document.getElementById('note').value = d.note || '';
                document.getElementById('lyrics').value = d.lyrics || '';
                document.getElementById('modalTitle').textContent = '編輯音樂';
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
        file: document.getElementById('file').value,
        cover: document.getElementById('cover').value,
        category: document.getElementById('category').value,
        language: document.getElementById('language').value,
        ref: document.getElementById('ref').value,
        note: document.getElementById('note').value,
        lyrics: document.getElementById('lyrics').value
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
