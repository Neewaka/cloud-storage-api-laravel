<?php

namespace App\Listeners;

use App\Models\File;
use App\Events\FileUsed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteExpiredFiles
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\FileUsed  $event
     * @return void
     */
    public function handle(FileUsed $event)
    {
        $files = File::whereRaw('TIMESTAMPDIFF(SECOND, now(), delete_at) < ?', [0]);

        foreach($files->join('users', 'user_id', '=', 'users.id')->get() as $item){
            $fileDirectory = $item->directory;
            $fileName = $item->fileTitle;
            $userPath = md5($item->email);
            $path = "${userPath}/${fileDirectory}/${fileName}";

            Storage::delete($path);
        }

        $files->delete();
    }
}
