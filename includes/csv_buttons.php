<?php if (isset($csvTable)): ?>
<div class="csv-buttons" style="display: inline-block; margin-left: 10px;">
    <a href="export.php?table=<?php echo $csvTable; ?>" class="btn btn-success">
        <i class="fa-solid fa-download"></i> 匯出 CSV
    </a>
    <button type="button" class="btn" onclick="document.getElementById('importFile_<?php echo $csvTable; ?>').click()">
        <i class="fa-solid fa-upload"></i> 匯入 CSV
    </button>
    <input type="file" id="importFile_<?php echo $csvTable; ?>" accept=".csv" style="display: none;" onchange="importCSV_<?php echo $csvTable; ?>(this)">
</div>

<script>
function importCSV_<?php echo $csvTable; ?>(input) {
    if (!input.files || !input.files[0]) return;

    if (!confirm('確定要匯入 CSV 嗎？已存在的資料將會被更新。')) {
        input.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('table', '<?php echo $csvTable; ?>');
    formData.append('file', input.files[0]);

    fetch('import.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert('匯入完成！\n成功匯入: ' + res.imported + ' 筆');
            location.reload();
        } else {
            alert('匯入失敗: ' + (res.error || '未知錯誤'));
        }
    })
    .catch(err => {
        alert('匯入失敗: ' + err.message);
    });

    input.value = '';
}
</script>
<?php endif; ?>
