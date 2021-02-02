<?php

define('DEFAULT_NUMBER_OF_PASSWORDS', 5);
define('DEFAULT_NUMBER_OF_CHARACTERS', 32);
define('MAX_PASSWORDS' , 100);
define('MAX_CHARACTERS', 1000);
define('SP_CHARS', ['!', '"', '#', '$', '%',
    '&', '\'', '-', '=', '^', '~', '\\', '|',
    ',', '.', '/', '<', '>', '?', '_', '@', '`',
    ';', ':', '+', '*', '(', ')', '[', ']', '{', '}'
]);
define('NUMBER_RADIO', [
    ['label' => 'use'   , 'value' => '1'],
    ['label' => 'unused', 'value' => '0'],
]);
define('ALPHABET_RADIO', [
    ['label' => 'both'     , 'value' => '3'],
    ['label' => 'lowercase', 'value' => '2'],
    ['label' => 'uppercase', 'value' => '1'],
    ['label' => 'unused'   , 'value' => '0'],
]);

// 言語
define('TRANSLATE_FILE', './translate.yml');
global $_TRANSLATE, $lang;
$lang   = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0 ,2);
$_TRANSLATE = yaml_parse_file(TRANSLATE_FILE);
$_TRANSLATE = $_TRANSLATE[$lang] ?? $_TRANSLATE['en'];

// 初期化
global $chars, $chars_count;
$chars  = [];
$number_of_passwords    = intval($_GET['number_of_passwords']  ?? DEFAULT_NUMBER_OF_PASSWORDS);
$number_of_characters   = intval($_GET['number_of_characters'] ?? DEFAULT_NUMBER_OF_CHARACTERS);
$use_numbers            = $_GET['use_numbers']   ?? '1';
$use_alphabets          = $_GET['use_alphabets'] ?? '3';
$use_specialchars       = isset($_GET['use_specialchars']) && $_GET['use_specialchars'] === '1';
$use_all_specialchars   = isset($_GET['use_all_specialchars']) && $_GET['use_all_specialchars'] === '1';

// 制限
if ($number_of_passwords  > MAX_PASSWORDS)  $number_of_passwords  = DEFAULT_NUMBER_OF_PASSWORDS;
if ($number_of_characters > MAX_CHARACTERS) $number_of_characters = DEFAULT_NUMBER_OF_CHARACTERS;

?><!DOCTYPE html>

<html lang="<?=$lang?>">
<head>
    <meta charset="utf-8">
    <title><?=_t('password-generator')?></title>
    <link rel="stylesheet" href="./style.css">
    <!-- GA start -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-12497769-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'UA-12497769-1');
    </script>
    <!-- GA end -->
</head>
<body>
<?php
// 値の取得
if ($use_numbers === '1') {
    $chars  = array_merge(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], $chars);
}
if ($use_alphabets === '2' || $use_alphabets === '3') {
    $chars  = array_merge([
        'A', 'B', 'C', 'D', 'E', 'F', 'G',
        'H', 'I', 'J', 'K', 'L', 'M', 'N',
        'O', 'P', 'Q', 'R', 'S', 'T', 'U',
        'V', 'W', 'X', 'T', 'Z',
    ], $chars);
}
if ($use_alphabets === '1' || $use_alphabets === '3') {
    $chars  = array_merge([
        'a', 'b', 'c', 'd', 'e', 'f', 'g',
        'h', 'i', 'j', 'k', 'l', 'm', 'n',
        'o', 'p', 'q', 'r', 's', 't', 'u',
        'v', 'w', 'x', 't', 'z'
    ], $chars);
}

// 記号のインデックスはチェックボックスの選択状態を左右するので、必ず取得する。
$sc = array_map('intval', $_GET['sc'] ?? []);
if ($use_specialchars) {
    if (!$use_all_specialchars) {
        $chars  = array_merge(getSpecialChars($sc), $chars);
    } else {
        $chars  = array_merge(SP_CHARS, $chars);
    }
}

// 生成と出力
$chars_count = count($chars);
if ($chars_count >= 1) : ?>
    <ol class="passwords">
<?php for ($password_loop_count = 0; $password_loop_count < $number_of_passwords; $password_loop_count++) : ?>
        <li class="pw">
            <input type="text" name="output_password" id="OutputPassword<?=$password_loop_count?>" value="<?=_e(generatePassword($number_of_characters))?>" size="<?=$number_of_characters?>">
            <button onclick="const pw = document.getElementById('OutputPassword<?=$password_loop_count?>'); pw.select(); pw.setSelectionRange(0, <?=$number_of_characters?>); document.execCommand('copy');"><?=_t('copy')?></button>
        </li>
<?php endfor; ?>
    </ol>
<?php else : ?>
    <p class="notice"><?=_t('specify-conditions')?></p>
<?php endif; ?>

    <div class="content">
        <form name="password-creator" method="get" action=".">
            <div class="option">
                <?=_t('number-of-passwords')?> <input type="number" name="number_of_passwords" class="number_of_passwords" value="<?=_e($number_of_passwords)?>">
            </div>
            <div class="option">
                <?=_t('characters')?> <input type="number" name="number_of_characters" class="number_of_characters" value="<?=_e($number_of_characters)?>">
            </div>
            <div class="option">
                <?=_t('alphabets')?>
                <?php foreach (ALPHABET_RADIO as $radio) : ?>
                    &nbsp;<label><input type="radio" name="use_alphabets" value="<?=_e($radio['value'])?>"<?=$use_alphabets===$radio['value']?' checked':''?>> <?=_t($radio['label'])?></label>
                <?php endforeach; ?>
            </div>
            <div class="option">
                <?=_t('numbers')?>
                <?php foreach (NUMBER_RADIO as $radio) : ?>
                    &nbsp;<label><input type="radio" name="use_numbers" value="<?=_e($radio['value'])?>"<?=$use_numbers===$radio['value']?' checked':''?>> <?=_t($radio['label'])?></label>
                <?php endforeach; ?>
            </div>
            <div class="option">
                <?=_t('special-chars')?> <label><input type="radio" name="use_specialchars" value="1"<?=$use_specialchars?' checked':''?>> <?=_t('use')?></label>
                &nbsp;
                <label><input type="radio" name="use_specialchars" value="0"<?=!$use_specialchars?' checked':''?>> <?=_t('unused')?></label>
                <ul class="char-list">
                    <li><label><input type="checkbox" name="use_all_specialchars" value="1"<?=$use_all_specialchars?' checked':''?>> <?=_t('all')?></label></li>
                    <?php foreach (SP_CHARS as $i => $sp_char) : ?>
                        <li><label><input type="checkbox" name="sc[]" value="<?=$i?>"<?=in_array($i, $sc, true)?' checked':''?>> <?=_e($sp_char)?></label></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="buttons">
                <input type="submit" name="generate" value="<?=_t('generate')?>">&emsp;
                <input type="button" name="reset" value="<?=_t('reset')?>" onclick="location.href='.';">
            </div>
        </form>
    </div>
    <script>
        // チェックの切り替え
        function toggleCheck(nodes, check) {
            for (const node of nodes) {
                node.checked    = check;
            }
        }
        // 非活性状態の切り替え
        function toggleDisability(nodes, disabled) {
            for (const node of nodes) {
                node.disabled   = disabled;
            }
        }
        function getSCCheckboxes() {
            return document.querySelectorAll('input[name="sc[]"]');
        }
        function getSCCheckboxesAll() {
            return document.querySelectorAll('input[name="sc[]"],input[name="use_all_specialchars"]');
        }
        function isAnySCCheckboxChecked() {
            let checked = false;
            for (const checkbox of getSCCheckboxes()) {
                if (checkbox.checked === true) {
                    checked = true;
                    break;
                }
            }
            return checked;
        }
        function checkTheAllSCCheckbox() {
            // すべてチェックされたら「全部」にチェックを入れる。
            let check   = true;
            for (const scCheckbox of getSCCheckboxes()) {
                if (scCheckbox.checked === false) {
                    check   = false;
                    break;
                }
            }
            document.querySelector('input[name="use_all_specialchars"]').checked    = check;
        }
        // 記号関連の処理
        for (const radio of document.querySelectorAll('input[name="use_specialchars"]')) {
            // 記号の使う・使わないとdisabled
            const disableSpecialChars   = function() {
                toggleDisability(getSCCheckboxesAll(), radio.checked && radio.value === '0');
                // 初めて記号を「使う」に切り替えたら全部にチェックを入れる。
                if (radio.value === '1' && !isAnySCCheckboxChecked()) {
                    for (const scCheckbox of getSCCheckboxesAll()) {
                        scCheckbox.checked  = true;
                    }
                }
            };
            radio.addEventListener('change', disableSpecialChars, false);
            // 初期化
            disableSpecialChars();
        }
        for (const checkbox of getSCCheckboxes()) {
            // ドラッグで連続チェック解除
            const beginScDrag   = function(e) {
                window.isDragging    = true;
                window.isChecking    = e.target.checked === false;
                window.addEventListener('mouseup', function() {
                    window.isDragging    = false;
                }, {once: true, capture: true});
            };
            const dragScAndMove = function(e) {
                if (window.isDragging === true) {
                    e.target.checked    = window.isChecking;
                    checkTheAllSCCheckbox();
                }
            };
            checkbox.addEventListener('mousedown', beginScDrag, true);
            checkbox.addEventListener('mousemove', dragScAndMove, true);
            checkbox.addEventListener('change', checkTheAllSCCheckbox, true);
        }
        // すべて使う
        document.querySelector('input[name="use_all_specialchars"]').addEventListener('change', function() {
            toggleCheck(getSCCheckboxes(), this.checked);
        }, true);
    </script>
</body>
</html><?php
function generatePassword(int $number_of_characters) : string {
    global $chars, $chars_count;
    $password   = '';
    mt_srand();
    for ($i = 0; $i < $number_of_characters; $i++) {
        $password   .= $chars[mt_rand(0, $chars_count-1)];
    }
    return $password;
}
function getSpecialChars(array $filteredIndexes = []) : array {
    return array_filter(SP_CHARS, function($i) use ($filteredIndexes) {
        return in_array($i, $filteredIndexes, true);
    }, ARRAY_FILTER_USE_KEY);
}
function _t(string $text) : string {
    global $_TRANSLATE;
    return $_TRANSLATE[$text] ?? $text;
}
function _e(string $html) : string {
    return htmlentities($html, ENT_QUOTES | ENT_DISALLOWED | ENT_HTML5, "utf-8");
}
?>