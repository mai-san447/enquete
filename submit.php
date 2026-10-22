<?php
/*
 * submit.php … フォームから送られてきた回答を受け取り、
 *              チェックして、CSVファイルに1行追記する係。
 */
require __DIR__ . '/config.php';

// ① POST以外（URLを直接たたいた等）でここに来たら、フォームに戻す
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ② 入力を受け取る。?? '' は「もし無ければ空っぽにする」というおまじない
$name         = trim($_POST['name']         ?? '');
$age          = $_POST['age']               ?? '';
$satisfaction = $_POST['satisfaction']      ?? '';
$helpful      = $_POST['helpful']           ?? [];   // チェックボックスは「配列」で届く
$comment      = trim($_POST['comment']      ?? '');

// ③ 必須チェック（空っぽならエラーにする）
$errors = [];
if ($name === '')         { $errors[] = 'お名前'; }
if ($age === '')          { $errors[] = '年代'; }
if ($satisfaction === '') { $errors[] = '満足度'; }

if ($errors) {
    show_error('次の項目が未入力です：' . implode('、', $errors));
}

try {
    require_option($age, $AGE_OPTIONS, '年代');
    require_option($satisfaction, $SATISFACTION_OPTIONS, '満足度');
    foreach ((array)$helpful as $item) {
        require_option($item, $HELPFUL_OPTIONS, '役立った点');
    }
} catch (InvalidArgumentException $e) {
    show_error($e->getMessage());
}

// ④ チェックボックスは複数選べるので「・」でつないで1つの文字に
$helpfulText = is_array($helpful) ? implode('・', $helpful) : '';

// ⑤ CSVに書く1行ぶんのデータ（config.php の $CSV_HEADER と同じ並び順）
$row = [date('Y-m-d H:i:s'), $name, $age, $satisfaction, $helpfulText, $comment];

// ⑥ data フォルダが無ければ作る
try {
    ensure_csv_storage();
} catch (RuntimeException $e) {
    show_error($e->getMessage());
}

// ⑦ ファイルに追記する
//    'a' = 追記モード（消さずに末尾に足す）。初回だけ見出し行を書く。
$isNewFile = !file_exists(CSV_PATH);
$fp = fopen(CSV_PATH, 'ab');
if ($fp === false) {
    show_error('回答を保存できませんでした。data フォルダの書き込み権限を確認してください。');
}
flock($fp, LOCK_EX);                      // 他の人と同時に書いても壊れないよう「鍵」をかける
if ($isNewFile) {
    fputcsv($fp, $GLOBALS['CSV_HEADER']); // 1行目：見出し
}
fputcsv($fp, $row);                       // 2行目以降：回答
flock($fp, LOCK_UN);                      // 鍵をはずす
fclose($fp);

// ⑧ お礼ページを表示
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ご回答ありがとうございました</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <main class="card center">
    <h1>送信が完了しました 🎉</h1>
    <!-- 入力された名前を出すときは必ず h() を通す（安全装置） -->
    <p class="lead"><?= h($name) ?> さん、ご協力ありがとうございました。</p>
    <p>いただいたご意見は、今後のセミナー改善に活用させていただきます。</p>
    <a href="index.php" class="btn">もう一度回答する</a>
  </main>
</body>
</html>
<?php
// ── エラーをやさしく表示して止める関数 ──
function show_error($message) {
    ?>
    <!DOCTYPE html>
    <html lang="ja">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>入力エラー</title>
      <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
      <main class="card center">
        <h1>入力エラー</h1>
        <p class="error"><?= h($message) ?></p>
        <a href="index.php" class="btn">フォームに戻る</a>
      </main>
    </body>
    </html>
    <?php
    exit;
}
