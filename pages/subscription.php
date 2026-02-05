<?php
$pageTitle = '訂閱管理';
$pdo = getConnection();
$items = $pdo->query("SELECT * FROM subscription ORDER BY nextdate IS NULL, nextdate ASC")->fetchAll();

// 取得已有的服務名稱、網站、帳號（去重複）
$existingNames = $pdo->query("SELECT DISTINCT name FROM subscription WHERE name IS NOT NULL AND name != '' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$existingSites = $pdo->query("SELECT DISTINCT site FROM subscription WHERE site IS NOT NULL AND site != '' ORDER BY site")->fetchAll(PDO::FETCH_COLUMN);
$existingAccounts = $pdo->query("SELECT DISTINCT account FROM subscription WHERE account IS NOT NULL AND account != '' ORDER BY account")->fetchAll(PDO::FETCH_COLUMN);

// 匯率轉換 (轉為新台幣)
$exchangeRates = [
    'TWD' => 1,
    'USD' => 35,
    'EUR' => 40,
    'JPY' => 0.35,
    'CNY' => 4.5,
    'HKD' => 4
];

function convertToTWD($price, $currency, $rates) {
    $currency = strtoupper($currency ?? 'TWD');
    $rate = $rates[$currency] ?? 1;
    return round($price * $rate);
}
?>

<div class="content-header">
    <h1>鋒兄訂閱</h1>
</div>

<div class="content-body">
    <button class="btn btn-primary" onclick="openModal()">新增訂閱</button>
    <?php $csvTable = 'subscription'; include 'includes/csv_buttons.php'; ?>

    <table class="table" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>服務名稱</th>
                <th>價格 (TWD)</th>
                <th>下次付款日期</th>
                <th>續訂</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="5" style="text-align: center; color: #999;">暫無訂閱資料</td></tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <?php if ($item['site']): ?>
                                <?php $domain = parse_url($item['site'], PHP_URL_HOST); ?>
                                <img src="https://www.google.com/s2/favicons?domain=<?php echo $domain; ?>&sz=16" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 5px;">
                                <a href="<?php echo htmlspecialchars($item['site']); ?>" target="_blank"><?php echo htmlspecialchars($item['name']); ?></a>
                            <?php else: ?>
                                <?php echo htmlspecialchars($item['name']); ?>
                            <?php endif; ?>
                            <?php if (!empty($item['account'])): ?>
                                <br><span style="font-size: 0.85rem; color: #666;"><?php echo htmlspecialchars($item['account']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($item['note'])): ?>
                                <br><span style="font-size: 0.8rem; color: #999;"><?php echo htmlspecialchars($item['note']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatMoney(convertToTWD($item['price'], $item['currency'], $exchangeRates)); ?></td>
                        <td><?php echo formatDate($item['nextdate']); ?></td>
                        <td><span class="badge <?php echo $item['continue'] ? 'badge-success' : 'badge-danger'; ?>"><?php echo $item['continue'] ? '✓ 續訂' : '✗ 不續'; ?></span></td>
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
        <h2 id="modalTitle">新增訂閱</h2>
        <form id="itemForm">
            <input type="hidden" id="itemId" name="id">
            <div class="form-group" style="position: relative;">
                <label>服務名稱 *</label>
                <input type="text" class="form-control" id="name" name="name" autocomplete="off" required onfocus="showNameSuggestions()" oninput="filterNameSuggestions()">
                <div id="nameSuggestions" class="suggestions-dropdown" style="display: none;">
                    <?php foreach ($existingNames as $existingName): ?>
                        <div class="suggestion-item" onclick="selectName('<?php echo htmlspecialchars($existingName, ENT_QUOTES); ?>')"><?php echo htmlspecialchars($existingName); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-group" style="position: relative;">
                <label>網站</label>
                <input type="url" class="form-control" id="site" name="site" autocomplete="off" onfocus="showSiteSuggestions()" oninput="filterSiteSuggestions()">
                <div id="siteSuggestions" class="suggestions-dropdown" style="display: none;">
                    <?php foreach ($existingSites as $existingSite): ?>
                        <div class="suggestion-item" onclick="selectSite('<?php echo htmlspecialchars($existingSite, ENT_QUOTES); ?>')"><?php echo htmlspecialchars($existingSite); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group" style="flex:1">
                    <label>價格</label>
                    <input type="number" class="form-control" id="price" name="price">
                </div>
                <div class="form-group" style="flex:1">
                    <label>幣別</label>
                    <select class="form-control" id="currency" name="currency">
                        <option value="TWD">TWD 新台幣</option>
                        <option value="USD">USD 美元</option>
                        <option value="EUR">EUR 歐元</option>
                        <option value="JPY">JPY 日圓</option>
                        <option value="CNY">CNY 人民幣</option>
                        <option value="HKD">HKD 港幣</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>下次付款日</label>
                <input type="date" class="form-control" id="nextdate" name="nextdate">
            </div>
            <div class="form-group" style="position: relative;">
                <label>帳號</label>
                <input type="text" class="form-control" id="account" name="account" autocomplete="off" onfocus="showAccountSuggestions()" oninput="filterAccountSuggestions()">
                <div id="accountSuggestions" class="suggestions-dropdown" style="display: none;">
                    <?php foreach ($existingAccounts as $existingAccount): ?>
                        <div class="suggestion-item" onclick="selectAccount('<?php echo htmlspecialchars($existingAccount, ENT_QUOTES); ?>')"><?php echo htmlspecialchars($existingAccount); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-group">
                <label>備註</label>
                <textarea class="form-control" id="note" name="note" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label><input type="checkbox" id="continue" name="continue" checked> 續訂</label>
            </div>
            <button type="submit" class="btn btn-primary">儲存</button>
        </form>
    </div>
</div>

<style>
.suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--card-bg, #fff);
    border: 1px solid var(--input-border, #ddd);
    border-top: none;
    border-radius: 0 0 5px 5px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.suggestion-item {
    padding: 10px 15px;
    cursor: pointer;
}
.suggestion-item:hover {
    background: var(--table-header-bg, #f8f9fa);
}
</style>

<script>
const TABLE = 'subscription';
const allNames = <?php echo json_encode($existingNames, JSON_UNESCAPED_UNICODE); ?>;
const allSites = <?php echo json_encode($existingSites, JSON_UNESCAPED_UNICODE); ?>;
const allAccounts = <?php echo json_encode($existingAccounts, JSON_UNESCAPED_UNICODE); ?>;

// 服務名稱
function showNameSuggestions() {
    if (allNames.length > 0) {
        document.getElementById('nameSuggestions').style.display = 'block';
    }
}

function filterNameSuggestions() {
    filterSuggestions('name', 'nameSuggestions');
}

function selectName(name) {
    document.getElementById('name').value = name;
    document.getElementById('nameSuggestions').style.display = 'none';
}

// 網站
function showSiteSuggestions() {
    if (allSites.length > 0) {
        document.getElementById('siteSuggestions').style.display = 'block';
    }
}

function filterSiteSuggestions() {
    filterSuggestions('site', 'siteSuggestions');
}

function selectSite(site) {
    document.getElementById('site').value = site;
    document.getElementById('siteSuggestions').style.display = 'none';
}

// 帳號
function showAccountSuggestions() {
    if (allAccounts.length > 0) {
        document.getElementById('accountSuggestions').style.display = 'block';
    }
}

function filterAccountSuggestions() {
    filterSuggestions('account', 'accountSuggestions');
}

function selectAccount(account) {
    document.getElementById('account').value = account;
    document.getElementById('accountSuggestions').style.display = 'none';
}

// 通用篩選函數
function filterSuggestions(inputId, containerId) {
    const input = document.getElementById(inputId).value.toLowerCase();
    const container = document.getElementById(containerId);
    const items = container.querySelectorAll('.suggestion-item');
    let hasVisible = false;

    items.forEach(item => {
        if (item.textContent.toLowerCase().includes(input)) {
            item.style.display = 'block';
            hasVisible = true;
        } else {
            item.style.display = 'none';
        }
    });

    container.style.display = hasVisible ? 'block' : 'none';
}

// 點擊其他地方關閉所有下拉選單
document.addEventListener('click', function(e) {
    if (!e.target.closest('#name') && !e.target.closest('#nameSuggestions')) {
        document.getElementById('nameSuggestions').style.display = 'none';
    }
    if (!e.target.closest('#site') && !e.target.closest('#siteSuggestions')) {
        document.getElementById('siteSuggestions').style.display = 'none';
    }
    if (!e.target.closest('#account') && !e.target.closest('#accountSuggestions')) {
        document.getElementById('accountSuggestions').style.display = 'none';
    }
});

function openModal() {
    document.getElementById('modal').style.display = 'flex';
    document.getElementById('modalTitle').textContent = '新增訂閱';
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
                document.getElementById('site').value = d.site || '';
                document.getElementById('price').value = d.price || '';
                document.getElementById('currency').value = d.currency || 'TWD';
                document.getElementById('nextdate').value = d.nextdate ? d.nextdate.split(' ')[0] : '';
                document.getElementById('account').value = d.account || '';
                document.getElementById('note').value = d.note || '';
                document.getElementById('continue').checked = d.continue == 1;
                document.getElementById('modalTitle').textContent = '編輯訂閱';
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
        site: document.getElementById('site').value,
        price: document.getElementById('price').value || 0,
        currency: document.getElementById('currency').value,
        nextdate: document.getElementById('nextdate').value || null,
        account: document.getElementById('account').value,
        note: document.getElementById('note').value,
        continue: document.getElementById('continue').checked ? 1 : 0
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
