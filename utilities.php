<?php

define('KB', 1024);
define('MB', 1048576);

define('DEBUG_LOGS_FILE', 'path/to/debug/file');

function randomPassword()
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 10; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

/**
 * Fonction pour sauvegarder les images(et d'autres fichiers) sur le serveur
 */
function save_image_from_form(
    string $field,
    &$image_new_path,
    string $target_dir,
    bool $is_image = true
): bool {
    $uploadOk = false;
    if (isset($_FILES[$field])) {
        $ext = '.' . pathinfo($_FILES[$field]["name"], PATHINFO_EXTENSION);
        $target_file = $target_dir . uniqid()
            . strtolower(
                trim(
                    preg_replace(
                        '/[^A-Za-z0-9-]+/',
                        '-',
                        basename($_FILES[$field]["name"], $ext)
                    )
                )
            )
            . $ext;

        // Check if image file is a actual image or fake image
        $image_new_path = NULL;
        if ($_FILES[$field]['name'] != null) {
            $check = $is_image ? getimagesize($_FILES[$field]["tmp_name"]) : true;
            if ($check !== false) {
                // File is an image
                if ($_FILES[$field]["size"] <= 5 * MB) {
                    if (is_dir($target_dir) !== false || mkdir($target_dir, 0, true)) {
                        if (move_uploaded_file($_FILES[$field]["tmp_name"], $target_file)) {
                            // Déplacement réussi
                            $image_new_path = $target_file;
                            $uploadOk = true;
                        }
                    } else {
                        error_log(
                            "Impossible de créer le repertoire: $target_dir :" . print_r(error_get_last(), true) . PHP_EOL,
                            3,
                            DEBUG_LOGS_FILE
                        );
                    }
                } else {
                    error_log(
                        "La taille du fichier dépasse la limite:" . 5 * MB . ' bits : ' . print_r(error_get_last(), true) . PHP_EOL,
                        3,
                        DEBUG_LOGS_FILE
                    );
                }
            }
        }
    }
    return $uploadOk;
}

/**
 * Fonction qui compare deux noms d'entreprise et dit s'il s'agit de la meme entreprise
 * 
 * @param string $str1
 * @param string $str2
 * @return true si les deux entreprises ont des noms -tres- similaires
 */
function similar_companies_names($str1, $str2): bool
{
    $str1 = remove_punctuation($str1);
    $str2 = remove_punctuation($str2);

    $str1 = reduce_multiplespaces_to_singlespace($str1);
    $str2 = reduce_multiplespaces_to_singlespace($str2);

    $str1 = strtolower($str1);
    $str2 = strtolower($str2);

    // La recherche sera erronée si un mot est trop court
    if (min(strlen($str1), strlen($str2)) < 4) {
        // trigger_error("Longueur des paramètres non valide", E_USER_WARNING);
        // return false;

        $str1 .= ' tmp';
        $str2 .= ' tmp';
    }

    $sim = similar_text($str1, $str2, $percent);

    if (soundex($str1) != soundex($str2)) {
        $tmp = (strlen($str1) + strlen($str2)) / ($sim * 2);
        $tmp = 100 / $tmp;

        return $tmp >= 50 && $percent >= $tmp;
    } else {
        $gap = 5;
        if (levenshtein($str1, $str2) <= $gap && $percent > 75)
            return true;

        return $percent > 95;
    }
}

function remove_punctuation($text)
{
    return preg_replace('/\p{P}+/u', '', $text);
}

function reduce_multiplespaces_to_singlespace($text)
{
    return preg_replace('/\s+/', ' ', $text);
}

/** @link https://www.tutos.eu/?n=6436 */
function remove_all_accents(string $str)
{
    $search  = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
    $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');

    $str = str_replace($search, $replace, $str);
    return $str;
}

/**
 * @return true if the urls are the same
 * @link https://stackoverflow.com/a/19074617
 */
function compareUrls($a, $b)
{
    $a = parse_url($a, PHP_URL_HOST);
    $b = parse_url($b, PHP_URL_HOST);

    return trimWord($a) === trimWord($b);
}

function trimWord($str)
{
    if (stripos($str, 'www.') === 0) {
        return substr($str, 4);
    }
    return $str;
}

function html_table_to_php_array($ressource)
{
    error_reporting(E_ERROR | E_PARSE);

    $htmlContent = file_get_contents($ressource);

    $DOMDoc = new DOMDocument();
    $DOMDoc->loadHTML($htmlContent);
    $DOM = $DOMDoc->getElementsByTagName('table')->item(0);

    $Footer = $DOMDoc->getElementsByTagName('tfoot')->item(0);
    $DOM->removeChild($Footer);

    $Header = $DOMDoc->getElementsByTagName('th');
    $Detail = $DOMDoc->getElementsByTagName('td');

    //#Get header name of the table
    foreach ($Header as $NodeHeader) {
        $aDataTableHeaderHTML[] = trim($NodeHeader->textContent);
    }

    //#Get row data/detail table without header name as key
    $i = 0;
    $j = 0;
    foreach ($Detail as $sNodeDetail) {
        $aDataTableDetailHTML[$j][] = trim($sNodeDetail->textContent);
        $i = $i + 1;
        $j = $i % count($aDataTableHeaderHTML) == 0 ? $j + 1 : $j;
    }

    //#Get row data/detail table with header name as key and outer array index as row number
    for ($i = 0; $i < count($aDataTableDetailHTML); $i++) {
        for ($j = 0; $j < count($aDataTableHeaderHTML); $j++) {
            $aTempData[$i][$aDataTableHeaderHTML[$j]] = $aDataTableDetailHTML[$i][$j];
        }
    }
    $aDataTableDetailHTML = $aTempData;
    unset($aTempData);
    return $aDataTableDetailHTML;
}

/** 
 * Simple function to sort an array by a specific key. Maintains index association. 
 * Source: https://www.php.net/manual/en/function.sort.php#99419 
 */
function array_sort($array, $on, $order = SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();
    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}
