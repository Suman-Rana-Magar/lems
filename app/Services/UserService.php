<?php

namespace App\Services;

use App\Models\User;
use App\Upload;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isNull;

class UserService
{
    use Upload;

    public function update($user, array $data)
    {
        DB::beginTransaction();
        try {
            if (!isset($data['profile_picture']))
                unset($data['profile_picture']);
            if (isset($data['profile_picture']) && is_file($data['profile_picture'])) {
                $previousPicture = $user->profile_picture;
                if ($previousPicture)
                    $this->deleteFile($previousPicture);
                $path = $this->UploadFile($data['profile_picture'], 'profile_pictures');
                $data['profile_picture'] = $path['path'];
            }
            $user->update($data);
            if (isset($data['interests'])) {
                $user->interests()->sync($data['interests']);
            }
            DB::commit();
            return $user->load('interests');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return $exception->getMessage();
        }
    }
}
