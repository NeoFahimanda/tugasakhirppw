<?php
class Flash
{
    public static function set($type, $message)
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public static function get()
    {
        if (isset($_SESSION['flash'])) {
            $data = $_SESSION['flash'];
            unset($_SESSION['flash']); // Hapus setelah diambil
            return $data;
        }
        return null;
    }
}
