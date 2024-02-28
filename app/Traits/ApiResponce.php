<?php
namespace App\Traits;
trait ApiResponce {
    public function success($message,$status=200) {
        return response([
            'success' => true,
            'message' => $message,
            'status' => $status
        ],$status);
    }
    public function successWithData($data,$status=200) {
        return response([
            'success' => true,
            'status' => $status,
            'data' => $data
        ],$status);
    }
    public function error($message,$status=422,$exception=null) {
        return response([
            'success' => false,
            'message' => $message,
            'status' => $status
        ]);
    }
}
