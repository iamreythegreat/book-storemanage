<?php
function custom_exception_handler($exception) {
    global $config;

    ob_clean();
    // unser all previously set headers
    try {
        header_remove();
    } catch (Exception $e) {
    }

    $error_id     = uniqid();
    $current_time = date("Y-m-d H:i:s");
    $user_name    = "[{$config->getUser()->getId()}] {$config->getUser()->first_name} {$config->getUser()->last_name}";

    $error_msg   = "\nERROR_ID **{$error_id}**\n";
    $error_msg  .= "\tTime    = {$current_time}\n";
    $error_msg  .= "\tUser    = {$user_name}\n";
    $error_msg  .= "\tFile    = {$exception->getFile()}\n";
    $error_msg  .= "\tLine    = {$exception->getLine()}\n";
    $error_msg  .= "\tMessage = {$exception->getMessage()}\n";
    $error_msg  .= "\tTrace\n{$exception->getTraceAsString()}\n";

    file_put_contents(__DIR__ . "/../../error_log-aster.txt", $error_msg, FILE_APPEND);

    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "errors"  => ["An error has occurred. If this issue continues please provide this error code to Intoview: {$error_id}"],
    ]);
}

function custom_warning_handler($error_number, $error_string) {
    throw new \Exception($error_string, $error_number);
}

function log_backtrace(string $head = "Backtrace Log:\n", bool $log_as_error = true, bool $include_first = false) {
    /** @var Config $config **/
    global $config;

    $current_time = date("Y-m-d H:i:s");
    $user_name    = "[{$config->getUser()->getId()}] {$config->getUser()->first_name} {$config->getUser()->last_name}";

    $head .= "  Time = {$current_time}\n";
    $head .= "  User = {$user_name}\n";
    $head .= "  Trace:";

    $backtrace = debug_backtrace();

    $parts = [];
    foreach ($backtrace as $i => $stack) {
        if (!$include_first && $i === 0) {
            continue;
        }

        $arg_string = join(", ", map($stack["args"], fn ($arg) => json_encode($arg)));
        $parts[] = "    #{$i} {$stack["file"]}({$stack["line"]}): {$stack["function"]}({$arg_string})";
    }

    $formatted = join("\n", $parts);

    if (!$log_as_error) {
        file_put_contents(__DIR__ . "/../../notice_log-aster.txt", "\n{$head}\n{$formatted}", FILE_APPEND);
        return;
    }

    error_log("{$head}\n{$formatted}");
}

function get_ip() {
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        return $_SERVER["HTTP_CLIENT_IP"];
    }

    if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        return $_SERVER["HTTP_X_FORWARDED_FOR"];
    }

    return $_SERVER["REMOTE_ADDR"] ?? null;
}

/* array iterators */
function map($array, $function) {
    $pass_key = (new ReflectionFunction($function))->getNumberOfParameters() > 1;

    $items = [];
    foreach ($array as $key => $item) {
        $items[$key] = $pass_key ? $function($key, $item) : $function($item);
    }

    return $items;
}

/**
 * Map an array to an associative array
 * @param Callable $function should return an array [$key, $value]
 */
function kmap($array, $function) {
    $pass_key = (new ReflectionFunction($function))->getNumberOfParameters() > 1;

    $items = [];
    foreach ($array as $key => $item) {
        $result = $pass_key ? $function($key, $item) : $function($item);
        $items[$result[0]] = $result[1];
    }

    return $items;
}

function filter($array, $filter, ?bool $preserve_keys = null) {
    $pass_key = (new ReflectionFunction($filter))->getNumberOfParameters() > 1;
    $keep_keys = false;

    $items = [];
    foreach ($array as $key => $item) {
        if (!$keep_keys && gettype($key) !== "integer") {
            $keep_keys = true;
        }

        $passed = $pass_key ? $filter($key, $item) : $filter($item);
        if (!$passed) {
            continue;
        }

        $items[$key] = $item;
    }

    $preserve_keys ??= $keep_keys;

    if (!$preserve_keys) {
        return array_values($items);
    }

    return $items;
}

/**
 * Filters $array using $filter_fn then maps each value using $map_fn
 * Similar to `map(filter($array, $filter_fn), $map_fn)`
 */
function filter_map($array, $filter_fn, $map_fn) {
    $filter_pass_key = (new ReflectionFunction($filter_fn))->getNumberOfParameters() > 1;
    $map_pass_key = (new ReflectionFunction($map_fn))->getNumberOfParameters() > 1;
    $keep_keys = false;

    $items = [];
    foreach ($array as $key => $item) {
        if (!$keep_keys && gettype($key) !== "integer") {
            $keep_keys = true;
        }

        $passed = $filter_pass_key ? $filter_fn($key, $item) : $filter_fn($item);
        if (!$passed) {
            continue;
        }

        $items[$key] = $map_pass_key ? $map_fn($key, $item) : $map_fn($item);
    }

    if (!$keep_keys) {
        return array_values($items);
    }

    return $items;
}

/** Returns true if EVERY value in $array passes $check otherwise returns false */
function every($array, $check) {
    // $pass_key = (new ReflectionFunction($check))->getNumberOfParameters() > 1;

    foreach ($array as $key => $item) {
        // $passed = $pass_key ? $check($key, $item) : $check($item);
        if (!$passed) {
            return false;
        }
    }

    return true;
}

/** Returns true if ANY value in $array passes $check otherwise returns false */
function some($array, $check) {
    $pass_key = (new ReflectionFunction($check))->getNumberOfParameters() > 1;

    foreach ($array as $key => $item) {
        $passed = $pass_key ? $check($key, $item) : $check($item);
        if ($passed) {
            return true;
        }
    }

    return false;
}

/** Returns FIRST element in $array that matched $filter otherwise returns null */
function first($array, $filter) {
    $pass_key = (new ReflectionFunction($filter))->getNumberOfParameters() > 1;
    $keep_keys = false;

    foreach ($array as $key => $item) {
        if (!$keep_keys && gettype($key) !== "integer") {
            $keep_keys = true;
        }

        $passed = $pass_key ? $filter($key, $item) : $filter($item);
        if ($passed) {
            return $item;
        }
    }

    return null;
}
/* array iterators */

/** Converts string to lowercase and replaces all whitespace with underlines `"_"` */
function normalise_string(string $input): string {
    return strtolower(trim(preg_replace("/\s/", "_", $input)));
}


function get_file_extension(string $input) {
    
    if (count($parts) === 0) {
        return "";
    }

    return $parts[count($parts) - 1];
}


function create_temp_file(?string $extension = null): ?string {
    $tmp_folder = __DIR__ . "/../_tmp/";
    $tmp_name = random_string(12);
    if ($extension !== null) {
        $tmp_name = "{$tmp_name}.{$extension}";
    }

    $i = 0;
    while (file_exists("{$tmp_folder}{$tmp_name}") && $i < 100) {
        $tmp_name = random_string(12);
        if ($extension !== null) {
            $tmp_name = "{$tmp_name}.{$extension}";
        }

        $i++;
    }

    if ($i === 100) {
        return null;
    }

    $handle = fopen("{$tmp_folder}{$tmp_name}", "w");
    fclose($handle);

    return "{$tmp_folder}{$tmp_name}";
}

function random_string(int $length = 6, bool $simple = true, bool $include_uppercase = true, bool $include_numbers = true): string {
    $keyspace = 'abcdefghijklmnopqrstuvwxyz';
    if ($include_numbers) {
        $keyspace .= '0123456789';
    }

    if ($include_uppercase) {
        $keyspace .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }

    if (!$simple) {
        $keyspace .= '?!@#$%^&*()-=_+[]{};:",./';
    }

    $output = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $output .= $keyspace[random_int(0, $max)];
    }

    return $output;
}

/** If value to 7dp is zero */
function about_zero($input) {
    if (abs($input) < 0.0000001) {
        return true;
    }

    return false;
}

/**
 * Safe Division - returns 0 if denominator is 0. Useful for preventing DivideByZero errors
 */
function sdiv($numerator, $denominator) {
    if (abs($denominator) < 0.0000001) {
        return 0;
    }

    return $numerator / $denominator;
}

function get_file_ext(int $file_type) {
    return APP::GetFileTypeData()[$file_type]["extension"] ?? "";
}

function get_file_type(int $file_type) {
    return APP::GetFileTypeData()[$file_type]["type"] ?? "unknown";
}

function get_file_const(string $file_type) {
    return APP::GetFileTypeData(true)["_map"][$file_type] ?? null;
}

function get_title(int $title) {
    return APP::GetTitleData()[$title]["label"] ?? "Unknown";
}

function datetime($input = null) {
    return new DateTime($input);
}

function valid_date(?string $input) {
    if ($input === null || $input === "" || $input === "0000-00-00" || $input === "0000-00-00 00:00" || $input === "0000-00-00 00:00:00") {
        return false;
    }

    try {
        new DateTime($input);
    } catch (Exception $e) {
        return false;
    }

    return true;
}

/**
 * Remaps $_FILES formatted to more expected (nested associative array).
 * From `["key" => ["name" => ["field1" => "field1_name_value] ]]`

 * To `["key" => ["field1" => ["name" => "field1_name_value"] ]]`
 */
function remap_files_array(array $file_array) {
    $files = [];

    foreach ($file_array as $key => $property_list) {
        foreach ($property_list as $property => $fields) {
            if (gettype($fields) === "array") {
                $files[$key] = array_replace_recursive($files[$key] ?? [], map_child_array($property, $fields));
                continue;
            }

            $files[$key] = $fields;
        }
    }

    return $files;
}

/** Used specifically within `remap_files_array` for recursive scenarios */
function map_child_array($property, $fields) {
    $keys = [];

    foreach ($fields as $field_name => $field_value) {
        if (gettype($field_value) === "array") {
            $keys[$field_name] = map_child_array($property, $field_value);
            continue;
        }

        $keys[$field_name][$property] = $field_value;
    }

    return $keys;
}

/**
 * @param mixed $input
 *
 * Return null if $input matches APP::NULL_SEQUENCE.
 * Equivalent to `$input === $null_sequence ? null : $input`
 */
function val_null($input) {
    if ($input === APP::NULL_SEQUENCE) {
        return null;
    }
    return $input;
}

/**
 * @param mixed $input
 * @param bool $convert_null_sequence Returns null if $input matches APP::NULL_SEQUENCE
 *
 * Similar to strval() except this returns null if $input is null.
 * Equivalent to `$input === null ? null : strval($input)`
 */
function strval_null($input, bool $convert_null_sequence = false) {
    if ($convert_null_sequence && val_null($input) === null) {
        return null;
    }
    return $input === null ? null : strval($input);
}

/**
 * Similar to strval() except this returns null if $input is null or empty.
 */
function strval_null_empty($input, bool $convert_null_sequence = false) {
    if ($convert_null_sequence && val_null($input) === null) {
        return null;
    }
    return ($input === null || trim(strval($input)) === '') ? null : strval($input);
}

/**
 * @param mixed $input
 * @param bool $convert_null_sequence Returns null if $input matches APP::NULL_SEQUENCE
 *
 * Similar to intval() except this returns null if $input is null.
 * Equivalent to `$input === null ? null : intval($input)`
 */
function intval_null($input, bool $convert_null_sequence = false) {
    if ($convert_null_sequence && val_null($input) === null) {
        return null;
    }
    return $input === null ? null : intval($input);
}

/**
 * Similar to floatval() except this returns null if $input is null.
 * Equivalent to `$input === null ? null : floatval($input)`
 */
function floatval_null($input, int $round_dp = 0) {
    if ($input === null) {
        return null;
    }

    $input = floatval($input);
    if ($round_dp <= 0) {
        return $input;
    }

    return intval(round($input * pow(10, $round_dp))) / pow(10, $round_dp);
}

/**
 * Returns a \DateTime object if $input is a valid date, otherwise returns null.
 * Equivalent to `$input === null ? null : (valid_date($input) ? datetime($input) : null)`
 */
function dateval_null(?string $input, bool $skip_validation = false) {
    if ($input === null) {
        return null;
    }

    if ($skip_validation) {
        return datetime($input);
    }

    return valid_date($input) ? datetime($input) : null;
}

// adapted from: https://stackoverflow.com/questions/3302857/algorithm-to-get-the-excel-like-column-name-of-a-number
function number_to_letters($input) {
    $numeric = $input % 26;
    $letter = chr(65 + $numeric);

    $excess = intval($input / 26);
    if ($excess > 0) {
        return number_to_letters($excess - 1) . $letter;
    } else {
        return $letter;
    }
}

function format_phone1($phone) {
    $state_areacodes = ['02', '03', '07', '08',];
    $fourX_areacodes = ['1800', '1300',];
    $oneThree_areacode = ['13',];

    if ($phone === null) {
        return;
    }
    if (preg_match('/[a-z]/i', $phone)) {
        //inputted string has a character, return the string as is.
        return $phone;
    }
    //1800 || 1300
    if (in_array(substr($phone, 0, 4), $fourX_areacodes)) {
        $areacode = substr($phone, 0, 4);
        $phone = substr($phone, 4);
        return $areacode . chunk_split($phone, 4, ' ');
    }
    // 13
    if (in_array(substr($phone, 0, 2), $oneThree_areacode)) {
        return chunk_split($phone, 2, ' ');
    }
    // 07 || 02 etc
    if (in_array(substr($phone, 0, 2), $state_areacodes)) {
        $areacode = substr($phone, 0, 2);
        $phone = substr($phone, 2);
        return $areacode . ' ' . chunk_split($phone, 4, ' ');
    }
    //else return 04 number format
    $phone = ltrim($phone, $phone[0]);
    return '0' . chunk_split($phone, 3, ' ');
}

function format_phone(?string $input) {
    if ($input === null) {
        return "";
    }

    $o_input = $input;

    $input = preg_replace("/\D*/", "", $input);

    if (strpos($input, "04") === 0) {
        return substr($input, 0, 4) . " " . substr($input, 4, 3) . " " . substr($input, 7);
    }

    if (strpos($input, "61") === 0 && $o_input[0] === "+") {
        return "04" . substr($input, 3, 2) . " " . substr($input, 5, 3) . " " . substr($input, 8);
    }

    if (strpos($input, "1300") === 0 || strpos($input, "1800") === 0) {
        return substr($input, 0, 4) . " " . substr($input, 4);
    }

    if (strpos($input, "0") === 0) {
        return substr($input, 0, 2) . " " . substr($input, 2, 4) . " " . substr($input, 6);
    }

    if (strlen($input) === 8) {
        return substr($input, 0, 4) . " " . substr($input, 4);
    }

    return $o_input;
}
