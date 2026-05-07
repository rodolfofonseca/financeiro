<?php
function env_load_dotenv($path)
{
    if (!is_string($path) || $path === '') {
        return false;
    }

    if (file_exists($path) !== true) {
        return false;
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        return false;
    }

    $lines = preg_split("/\r\n|\n|\r/", $contents);
    if (is_array($lines) !== true) {
        return false;
    }

    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $equalsPos = strpos($line, '=');
        if ($equalsPos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $equalsPos));
        $value = trim(substr($line, $equalsPos + 1));

        if ($key === '') {
            continue;
        }

        $firstChar = substr($value, 0, 1);
        $lastChar = substr($value, -1);
        if (($firstChar === '"' && $lastChar === '"') || ($firstChar === "'" && $lastChar === "'")) {
            $value = substr($value, 1, -1);
        }

        if (getenv($key) !== false) {
            continue;
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }

    return true;
}

