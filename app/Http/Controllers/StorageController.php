<?php

namespace App\Http\Controllers;

use App\Events\FileUsed;
use Throwable;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class StorageController extends Controller
{
    private const MAX_FILE_SIZE = 20971520;

    public function __construct()
    {
        //Event for deleting expired files
        // event(new FileUsed());
    }

    /**
     * Upload file to the storage
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request, $dirname = false)
    {

        $params = $request->validate([
            'file' => 'required|file',
            'expires_in' => 'int',
        ]);

        $file = $params['file'];
        $path = md5($request->user()->email);
        $fileName = $file->getClientOriginalName();
        $user = $request->user();

        if ($dirname) {
            $path .= "/${dirname}";
        }

        $address = "${path}/${fileName}";

        if ($file->getClientOriginalExtension() == "php") {
            return response()->json([
                'status' => false,
                'message' => "files with extension '.php' are forbidden for upload",
            ], 400);
        }

        if ($file->getSize() >= self::MAX_FILE_SIZE) {
            return response()->json([
                'status' => false,
                'message' => "files larger than 20mb are forbidden for upload ",
            ], 400);
        }

        if (!$user->canStore($file->getSize())) {
            return response()->json([
                'status' => false,
                'message' => "you reached limit capacity of your storage (100mb)",
            ], 400);
        }

        try {
            if (!Storage::exists($address)) {

                $path = $file->storeAs("${path}", $fileName);
                $dbFile = new File();
                $dbFile->user_id = $request->user()->id;
                $dbFile->extension = $file->extension();
                $dbFile->fileTitle = $fileName;
                $dbFile->size = Storage::size($path);
                $dbFile->directory = $dirname ? $dirname : null;
                if (array_key_exists('expires_in', $params)) {
                    $dbFile->delete_at = date('Y-m-d H:i:s', time() + 10800 + $params['expires_in']);
                }
                $dbFile->save();

                return response()->json([
                    'status' => true,
                    'message' => "file '${fileName}' uploaded successfully",
                ], 201);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "file with name '${fileName}' already exists",
                ], 400);
            }
        } catch (Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download file from storage
     * 
     * @param \Illuminate\Http\Request $request
     * @return file
     */
    public function download(Request $request, $name, $dirname = null)
    {
        if ($file = File::where('public_link', $name)->first()) {
            if ($file && $file->isPublic == 1) {
                $name = $file->fileTitle;
                $dirname = $file->directory;
                $path = md5(User::where('id', $file->user_id)->first()->email);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "this file does not exists or not published"
                ], 400);
            }
        } else {

            $path = md5($request->user()->email);

            if ($dirname) {
                $temp = $name;
                $name = $dirname;
                $dirname = $temp;
                $path .= "/${dirname}";
            }
        }

        $address = "${path}/${name}";

        if (!Storage::exists($address)) {
            return response()->json([
                'status' => false,
                'message' => "file with name '${name}' does not exists"
            ], 400);
        }

        return Storage::download($address);
    }

    /**
     * Delete file of choosing
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $name, $dirname = null)
    {
        $path = md5($request->user()->email);

        if ($dirname) {
            $temp = $name;
            $name = $dirname;
            $dirname = $temp;
            $path .= "/${dirname}";
        }

        $address = "${path}/${name}";

        try {
            if (!Storage::exists($address)) {
                return response()->json([
                    'status' => false,
                    'message' => "file with name '${name}' does not exists"
                ], 400);
            }

            Storage::delete($address);
            File::where('fileTitle', $name)->delete();

            return response()->json([
                'status' => true,
                'message' => "file ${name} deleted"
            ], 200);
        } catch (Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Creates directory in storage of current user
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response 
     */
    public function createDirectory(Request $request)
    {
        $params = $request->validate([
            'dirname' => 'required|string',
        ]);

        $path = md5($request->user()->email);

        if (Storage::exists("${path}/${params['dirname']}")) {
            return response()->json([
                'status' => false,
                'message' => "directory with name '${params['dirname']}' already exists",
            ], 400);
        }

        try {
            Storage::makeDirectory("${path}/${params['dirname']}");

            return response()->json([
                'status' => true,
                'message' => "directory '${params['dirname']}' created successfully",
            ], 201);
        } catch (Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rename file of choosing
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response 
     */
    public function update(Request $request, $name, $dirname = null)
    {

        $params = $request->validate([
            'newName' => 'required|string',
        ]);

        $path = md5($request->user()->email);
        if ($dirname) {
            $temp = $name;
            $name = $dirname;
            $dirname = $temp;
            $path .= "/${dirname}";
        }

        $fromFile = "${path}/${name}";
        $toFile = "${path}/${params['newName']}";

        try {
            if (!Storage::exists($fromFile)) {
                return response()->json([
                    'status' => false,
                    'message' => "file with name '${name}' does not exists",
                ], 400);
            }

            if (Storage::exists($toFile)) {
                return response()->json([
                    'status' => false,
                    'message' => "file with name '${params['newName']}' already exists",
                ], 400);
            }

            if (Storage::move($fromFile, $toFile)) {

                File::where('fileTitle', $name)->where('directory', $dirname)->update(['fileTitle' => $params['newName']]);

                return response()->json([
                    'status' => true,
                    'message' => "file '${name}' successfully renamed to '${params['newName']}'",
                ], 200);
            }
        } catch (Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of all files in user storage
     * 
     * @param \Illuminate\Http\Request $request
     * @return array 
     */
    public function getList(Request $request)
    {  
        info('net');
        FileUsed::dispatch();
        $data = File::select('fileTitle', 'directory')->where('user_id', $request->user()->id)->get();

        return response()->json([
            'status' => true,
            'data' => $data,
        ], 200);
    }

    /**
     * Get basic information about current user
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response 
     */
    public function getUserInfo(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => true,
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    /**
     * Get size of a directory
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response 
     */
    public function getDirectorySize(Request $request, $dirname = null)
    {
        $user = $request->user();
        $path = md5($request->user()->email);

        if ($dirname) {
            if (!Storage::exists("${path}/${dirname}")) {
                return response()->json([
                    'status' => false,
                    'message' => "directory with name '${dirname}' does not exists",
                ], 400);
            }
        }

        $dbFiles = File::where('user_id', $user->id)->where('directory', $dirname)->sum('size');

        return response()->json([
            'status' => true,
            'data' => $dbFiles,
        ], 200);
    }

    /**
     * Makes file public and creates unique link for it
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response 
     */
    public function publish(Request $request, $name, $dirname = null)
    {
        $path = md5($request->user()->email);
        if ($dirname) {
            $temp = $name;
            $name = $dirname;
            $dirname = $temp;
            $path .= "/${dirname}";
        }

        $file = File::where('fileTitle', $name)->where('directory', $dirname)->first();
        if ($file) {
            $file->isPublic = 1;
            $publicLink = $file->public_link;
            $file->save();

            return response()->json([
                'status' => true,
                'message' => "file '${name}' is published now",
                'data' => $publicLink,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "file with name '${name}' does not exists",
            ], 400);
        }
    }
}
