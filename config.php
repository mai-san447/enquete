<?php
/*
 * config.php … アプリ全体で使う「設定」と「共通の道具」をまとめたファイル。
 * index.php / submit.php / result.php の3つから読み込んで使う（DRY＝設定は1か所だけ）。
 */

// ── 管理ページ(result.php)のパスワード ──
// パスワードは公開リポジトリに載せないよう、git に入れない config.local.php に書く。
// 初回は config.local.example.php をコピーして config.local.php を作り、値を変える。
$localConfig = __DIR__ . '/config.local.php';
if (!is_file($localConfig)) {
    http_response_code(500);
    exit('config.local.php がありません。config.local.example.php をコピーして作ってください。');
}
require $localConfig;

// ── 回答を保存するCSVファイルの場所 ──
// __DIR__ は「このファイルがあるフォルダ」という意味。さくらでもXAMPPでも同じ場所を指す。
define('CSV_PATH', __DIR__ . '/data/answers.csv');

// ── アンケートの選択肢（ここを直すだけで画面も集計も変わる） ──
$AGE_OPTIONS          = ['10代', '20代', '30代', '40代', '50代', '60代以上'];
$SATISFACTION_OPTIONS = ['とても満足', '満足', 'ふつう', 'やや不満', '不満'];
$HELPFUL_OPTIONS      = ['内容', '講師', '資料', '進行', '会場・設備'];

// ── CSVの見出し行（1行目に書く列の名前） ──
$CSV_HEADER = ['日時', 'お名前', '年代', '満足度', '役立った点', 'ご意見・ご感想'];

/**
 * h() … 画面に文字を出すときの「安全装置」。
 * 利用者が入力した < > " などをそのまま出すと、わるいプログラムを埋め込まれる危険(XSS)がある。
 * この関数を通すと、ただの文字として安全に表示できる。
 */
function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
