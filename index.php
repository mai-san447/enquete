<?php
// 設定ファイルを読み込む（選択肢の配列などをここから使う）
require __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>セミナー満足度アンケート</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <main class="card">
    <h1>セミナー満足度アンケート</h1>
    <p class="lead">本日はご参加ありがとうございました。今後の改善のため、ご回答をお願いします（所要1分）。</p>

    <!-- 入力した内容は submit.php に「POST」という方法で送られる -->
    <form action="submit.php" method="post">

      <!-- ① お名前（自由入力） -->
      <label class="field">
        <span class="label">お名前（ニックネーム可）<em class="req">必須</em></span>
        <input type="text" name="name" required placeholder="例：えむこ">
      </label>

      <!-- ② 年代（1つだけ選ぶ＝ラジオボタン）。選択肢は config.php の配列から自動で作る -->
      <fieldset class="field">
        <legend class="label">年代<em class="req">必須</em></legend>
        <div class="options">
          <?php foreach ($AGE_OPTIONS as $age): ?>
            <label class="option">
              <input type="radio" name="age" value="<?= h($age) ?>" required>
              <?= h($age) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <!-- ③ 満足度（1つだけ選ぶ） -->
      <fieldset class="field">
        <legend class="label">セミナーの満足度<em class="req">必須</em></legend>
        <div class="options">
          <?php foreach ($SATISFACTION_OPTIONS as $sat): ?>
            <label class="option">
              <input type="radio" name="satisfaction" value="<?= h($sat) ?>" required>
              <?= h($sat) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <!-- ④ 役立った点（いくつでも選べる＝チェックボックス）。
           name を helpful[] にすると、複数選んでも配列でまとめて受け取れる -->
      <fieldset class="field">
        <legend class="label">役立った点（複数選択可）</legend>
        <div class="options">
          <?php foreach ($HELPFUL_OPTIONS as $item): ?>
            <label class="option">
              <input type="checkbox" name="helpful[]" value="<?= h($item) ?>">
              <?= h($item) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <!-- ⑤ 自由記述 -->
      <label class="field">
        <span class="label">ご意見・ご感想</span>
        <textarea name="comment" rows="4" placeholder="自由にお書きください"></textarea>
      </label>

      <button type="submit" class="btn">回答を送信する</button>
    </form>

    <p class="footer-link"><a href="result.php">▶ 集計結果を見る（管理者用）</a></p>
  </main>
</body>
</html>
