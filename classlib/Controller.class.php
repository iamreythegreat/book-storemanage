<?php

class Controller {
    public static function AccessDenied(?Route $route = null) {
        echo $route->url;
        echo "401 - Access Denied";
    }

    public static function NotFound(?Route $route = null) {
        echo $route->url;
        echo "404 - Not Found";
    }

    public static function JsonResponse($output, ?Route $route = null) {
        ob_clean();
        header("Content-Type: application/json");
        echo json_encode($output);
    }

    public static function SetResponseHeader($status) {
        if (headers_sent()) {
            return;
        }

        switch (intval($status)) {
            case 200:
                header("HTTP/1.1 200 OK");
                break;

            case 400:
                header("HTTP/1.1 400 Bad Request");
                break;

            case 401:
                header("HTTP/1.1 401 Unauthorized");
                break;

            case 404:
                header("HTTP/1.1 404 Not Found");
                break;

            case 405:
                header("HTTP/1.1 405 Method Not Allowed");
                break;
        }
    }

    public static function SendFile(string $file_path, ?string $output_name = null, bool $die_on_send = true, bool $clear_buffer = true) {

        if (file_exists($file_path)) {
            $file_info = pathinfo($file_path);

            $file_name = $output_name ?? "{$file_info["filename"]}.{$file_info["extension"]}";
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header("Content-Disposition: attachment; filename=\"{$file_name}\"");
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));

            if ($clear_buffer) {
                flush(); // Flush system output buffer
            }

            readfile($file_path);
        }

        if ($die_on_send) {
            die;
        }
    }
}
