<?php
/*
 * result.php … 集まった回答を読み込んで「集計」して見せる管理者用ページ。
 *              簡単なパスワードでログインしてから見られるようにしている。
 */
session_start();                 // ログイン状態を覚えておくための仕組み
require __DIR__ . '/config.php';

// ── ログアウト ──
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: result.php');
    exit;
}

// ── ログイン処理（パスワードが合っていれば「合言葉OK」を記録） ──
$loginError = '';
if (isset($_POST['pass'])) {
    // hash_equals は比較にかかる時間が一定なので、応答時間からパスワードを推測されにくい
    if (is_string($_POST['pass']) && hash_equals(ADMIN_PASS, $_POST['pass'])) {
        $_SESSION['admin'] = true;
    } else {
        $loginError = 'パスワードが違います。';
    }
}

// ── まだログインしていなければ、ログイン画面を出して終了 ──
if (empty($_SESSION['admin'])) {
    render_login($loginError);
    exit;
}

// ── ここから先はログイン済みの人だけ ──

// ① CSVを読み込む
$rows = [];
if (file_exists(CSV_PATH) && ($fp = fopen(CSV_PATH, 'r'))) {
    flock($fp, LOCK_SH);                       // 読むときは「共有の鍵」
    while (($line = fgetcsv($fp)) !== false) {
        $rows[] = $line;
    }
    flock($fp, LOCK_UN);
    fclose($fp);
}
array_shift($rows);                            // 1行目（見出し）を取り除く
$total = count($rows);                         // 回答件数

// ② 満足度(4列目=index 3)と年代(3列目=index 2)を数える
$satCount = array_fill_keys($SATISFACTION_OPTIONS, 0);
$ageCount = array_fill_keys($AGE_OPTIONS, 0);
foreach ($rows as $r) {
    $sat = $r[3] ?? '';
    $age = $r[2] ?? '';
    if (isset($satCount[$sat])) { $satCount[$sat]++; }
    if (isset($ageCount[$age])) { $ageCount[$age]++; }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>アンケート集計結果</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <main class="card wide">
    <div class="topbar">
      <h1>集計結果</h1>
      <a href="result.php?logout=1" class="logout">ログアウト</a>
    </div>
    <p class="lead">これまでの回答：<strong><?= $total ?></strong> 件</p>

    <!-- 満足度のグラフ（CSSの横棒で表現） -->
    <h2>満足度</h2>
    <?php foreach ($satCount as $label => $count): ?>
      <?php $percent = $total ? round($count / $total * 100) : 0; ?>
      <div class="bar-row">
        <span class="bar-label"><?= h($label) ?></span>
        <span class="bar-track"><span class="bar-fill" style="width: <?= $percent ?>%"></span></span>
        <span class="bar-value"><?= $count ?>件（<?= $percent ?>%）</span>
      </div>
    <?php endforeach; ?>

    <!-- 年代の内訳 -->
    <h2>年代の内訳</h2>
    <?php foreach ($ageCount as $label => $count): ?>
      <?php $percent = $total ? round($count / $total * 100) : 0; ?>
      <div class="bar-row">
        <span class="bar-label"><?= h($label) ?></span>
        <span class="bar-track"><span class="bar-fill alt" style="width: <?= $percent ?>%"></span></span>
        <span class="bar-value"><?= $count ?>件（<?= $percent ?>%）</span>
      </div>
    <?php endforeach; ?>

    <!-- 自由記述の一覧（新しい順） -->
    <h2>ご意見・ご感想</h2>
    <?php
      $comments = [];
      foreach ($rows as $r) {
          $c = trim($r[5] ?? '');
          if ($c !== '') { $comments[] = [$r[0] ?? '', $r[1] ?? '', $c]; }
      }
      $comments = array_reverse($comments);     // 新しい回答を上に
    ?>
    <?php if (!$comments): ?>
      <p class="muted">まだ自由記述の回答はありません。</p>
    <?php else: ?>
      <ul class="comments">
        <?php foreach ($comments as $c): ?>
          <li>
            <p class="comment-text"><?= nl2br(h($c[2])) ?></p>
            <p class="comment-meta"><?= h($c[1]) ?> さん / <?= h($c[0]) ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <p class="footer-link"><a href="index.php">▶ アンケート画面へ戻る</a></p>
  </main>
</body>
</html>
<?php
// ── ログイン画面を出す関数 ──
function render_login($error) {
    ?>
    <!DOCTYPE html>
    <html lang="ja">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>管理者ログイン</title>
      <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
      <main class="card center">
        <h1>管理者ログイン</h1>
        <?php if ($error): ?><p class="error"><?= h($error) ?></p><?php endif; ?>
        <form method="post" class="login-form">
          <input type="password" name="pass" placeholder="パスワード" required autofocus>
          <button type="submit" class="btn">ログイン</button>
        </form>
        <p class="footer-link"><a href="index.php">▶ アンケート画面へ戻る</a></p>
      </main>
    </body>
    </html>
    <?php
}
